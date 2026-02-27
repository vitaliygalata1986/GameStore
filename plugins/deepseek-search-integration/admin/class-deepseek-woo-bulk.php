<?php

namespace Vitos\DeepSeek\Admin;

defined('ABSPATH') || exit;

use Vitos\DeepSeek\Admin\DeepSeek_Admin;

/**
 * Bulk генерация Short description (post_excerpt) для WooCommerce товаров через Bulk actions.
 *
 * Как работает:
 * 1) Добавляем пункт в выпадашку Bulk actions на странице Products.
 * 2) Когда жмём Apply — проходимся по выбранным товарам, вызываем DeepSeek API, пишем результат в post_excerpt.
 * 3) После редиректа показываем admin notice с количеством успешных/ошибок.
 *
 * Важно:
 * - Это синхронный вариант (без Action Scheduler). Для 5–20 товаров обычно ок.
 * - Для 50–500 лучше делать фоновые задачи (Action Scheduler), иначе можно упереться в таймаут.
 */
class DeepSeek_Woo_Bulk
{
    /**
     * Идентификатор bulk action (ключ в dropdown).
     * WordPress передаст это значение в handle_bulk_action() как $action.
     */
    const ACTION = 'deepseek_bulk_generate_excerpt';

    public function __construct()
    {
        // Добавляем пункт в Bulk actions на списке товаров (edit.php?post_type=product)
        add_filter('bulk_actions-edit-product', [$this, 'register_bulk_action']);

        // Обрабатываем нажатие Apply для Bulk actions на списке товаров
        add_filter('handle_bulk_actions-edit-product', [$this, 'handle_bulk_action'], 10, 3);

        // Показываем уведомление в админке после редиректа
        add_action('admin_notices', [$this, 'admin_notice']);
    }

    /**
     * Добавляет наш пункт в dropdown Bulk actions.
     *
     * @param array $bulk_actions Массив существующих bulk actions: [action_key => label]
     * @return array Обновлённый массив bulk actions
     */
    public function register_bulk_action(array $bulk_actions): array
    {
        $bulk_actions[self::ACTION] = 'DeepSeek: Generate short description';
        return $bulk_actions;
    }

    /**
     * Обработчик bulk action.
     *
     * WordPress передаёт:
     * @param string $redirect_to URL, куда WP редиректит после обработки
     * @param string $action      Выбранный bulk action (ключ из dropdown)
     * @param array  $post_ids    ID выбранных товаров (product IDs)
     *
     * @return string URL редиректа (мы добавляем query args, чтобы показать notice)
     */
    public function handle_bulk_action(string $redirect_to, string $action, array $post_ids): string
    {
        // Если action не наш — ничего не делаем
        if ($action !== self::ACTION) {
            return $redirect_to;
        }

        // Проверка прав: только те, кто может редактировать товары
        if (!current_user_can('edit_products')) {
            return add_query_arg([
                'deepseek_bulk_done' => 0,
                'deepseek_bulk_fail' => count($post_ids),
                'deepseek_bulk_msg'  => 'forbidden',
            ], $redirect_to);
        }

        // Берём API key из настроек (wp_options), чтобы ключ не светился на фронте
        $api_key = DeepSeek_Admin::get_api_key();
        if (!$api_key) {
            return add_query_arg([
                'deepseek_bulk_done' => 0,
                'deepseek_bulk_fail' => count($post_ids),
                'deepseek_bulk_msg'  => 'no_api_key',
            ], $redirect_to);
        }

        $done = 0;
        $fail = 0;

        // Идём по выбранным товарам
        foreach ($post_ids as $product_id) {
            $product_id = (int) $product_id;
            if (!$product_id) {
                $fail++;
                continue;
            }

            // Получаем объект товара WooCommerce
            $product = wc_get_product($product_id);
            if (!$product) {
                $fail++;
                continue;
            }

            /**
             * Собираем минимальный контекст товара для prompt.
             * Почему не “одно поле”?
             * Потому что LLM генерирует текст КАЧЕСТВЕННЕЕ, когда понимает контекст:
             * - название,
             * - категории,
             * - атрибуты (цвет, платформа, жанр, размер, материал и т.п.).
             */
            $name = $product->get_name();
            $cats = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'names']);

            // Атрибуты товара (taxonomy + custom)
            $attrs = [];
            foreach ($product->get_attributes() as $attr) {
                if ($attr->is_taxonomy()) {
                    // Пример: pa_color -> "Color"
                    $label  = wc_attribute_label($attr->get_name());
                    $values = wc_get_product_terms($product_id, $attr->get_name(), ['fields' => 'names']);
                    if (!empty($values)) {
                        $attrs[] = $label . ': ' . implode(', ', $values);
                    }
                } else {
                    // Custom attributes
                    $opts = $attr->get_options();
                    if (!empty($opts)) {
                        $attrs[] = $attr->get_name() . ': ' . implode(', ', $opts);
                    }
                }
            }

            // Prompt для генерации short description
            $prompt = "Write a short WooCommerce product description (80-120 words) for:\n"
                . "Name: {$name}\n"
                . (!empty($cats) ? "Categories: " . implode(', ', $cats) . "\n" : "")
                . (!empty($attrs) ? "Attributes:\n- " . implode("\n- ", $attrs) . "\n" : "")
                . "Output plain text only (no HTML).";

            // Вызов DeepSeek API
            $text = $this->call_deepseek($api_key, $prompt);

            // Если API вернул пусто — считаем fail
            if (!$text) {
                $fail++;
                continue;
            }

            // Записываем результат в short description (excerpt)
            // true => вернуть WP_Error при ошибке
            $res = wp_update_post([
                'ID'           => $product_id,
                'post_excerpt' => $text,
            ], true);

            if (is_wp_error($res)) {
                $fail++;
            } else {
                $done++;
            }
        }

        // Возвращаем URL редиректа + параметры результата (их покажет admin_notice)
        return add_query_arg([
            'deepseek_bulk_done' => $done,
            'deepseek_bulk_fail' => $fail,
        ], $redirect_to);
    }

    /**
     * Делает запрос к DeepSeek chat/completions и возвращает text (choices[0].message.content).
     * Возвращает '' при ошибке.
     */
    private function call_deepseek(string $api_key, string $prompt): string
    {
        // HTTP POST через WordPress HTTP API
        $resp = wp_remote_post('https://api.deepseek.com/chat/completions', [
            'headers' => [
                // DeepSeek авторизация по Bearer токену
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'timeout' => 30, // чтобы не висеть бесконечно
            'body'    => wp_json_encode([
                'model'    => 'deepseek-chat',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'stream'   => false,
            ]),
        ]);

        // Ошибка сетевого уровня (DNS/SSL/timeout и т.п.)
        if (is_wp_error($resp)) {
            error_log('DeepSeek bulk wp_remote_post error: ' . $resp->get_error_message());
            return '';
        }

        // Читаем HTTP статус и тело ответа
        $code = wp_remote_retrieve_response_code($resp);
        $body = wp_remote_retrieve_body($resp);

        // Если не 200 — логируем полный ответ, на выход даём пусто
        if ($code !== 200) {
            error_log("DeepSeek bulk HTTP $code: $body");
            return '';
        }

        // Парсим JSON -> PHP массив (true => ассоциативные массивы)
        $data = json_decode($body, true);

        // Достаём текст в формате OpenAI-like: choices[0].message.content
        $text = $data['choices'][0]['message']['content'] ?? '';

        // Возвращаем безопасно строку
        return is_string($text) ? trim($text) : '';
    }

    /**
     * Показывает admin notice после bulk-редиректа.
     * Notice появляется, когда в URL есть deepseek_bulk_done / deepseek_bulk_fail.
     */
    public function admin_notice(): void
    {
        // Если нет параметров — ничего не показываем
        if (!isset($_GET['deepseek_bulk_done']) && !isset($_GET['deepseek_bulk_fail'])) {
            return;
        }

        // Берём значения из URL, приводим к int
        $done = isset($_GET['deepseek_bulk_done']) ? (int) $_GET['deepseek_bulk_done'] : 0;
        $fail = isset($_GET['deepseek_bulk_fail']) ? (int) $_GET['deepseek_bulk_fail'] : 0;

        // Цвет уведомления: success если fail=0, иначе warning
        $class = ($fail > 0) ? 'notice notice-warning' : 'notice notice-success';

        // Вывод notice
        echo '<div class="' . esc_attr($class) . '"><p>';
        echo 'DeepSeek bulk generation finished. Updated: ' . esc_html((string) $done) . '. Failed: ' . esc_html((string) $fail) . '.';
        echo '</p></div>';

        /**
         * Почему notice “может повторяться” после refresh?
         * Потому что параметры deepseek_bulk_done/deepseek_bulk_fail остаются в URL.
         * Если захочешь убрать повтор:
         * - делаем notice is-dismissible
         * - и чистим URL через JS (history.replaceState) или через redirect без query args.
         */
    }
}
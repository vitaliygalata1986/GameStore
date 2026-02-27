<?php
namespace Vitos\DeepSeek\Admin;

defined('ABSPATH') || exit;

use Vitos\DeepSeek\Admin\DeepSeek_Admin;

class DeepSeek_Woo_Bulk_Generator
// Объявляем класс, который добавляет Bulk Action и фоновые задания через Action Scheduler.
{
    const BULK_ACTION = 'deepseek_bulk_generate_short_desc';
    // Константа-идентификатор bulk action (то, что будет в <select> bulk actions).

    const JOB_HOOK = 'deepseek_bulk_generate_short_desc_job'; // Action Scheduler hook
    // Константа: имя hook'а для Action Scheduler.
    // По этому имени Action Scheduler будет вызывать process_job().

    const GROUP = 'deepseek';
    // Группа Action Scheduler задач. Удобно фильтровать их в Tools → Scheduled Actions.

    public function __construct()
        // Конструктор. Срабатывает, когда ты делаешь new DeepSeek_Woo_Bulk().
    {
        // 1) Добавляем action в выпадашку Bulk actions на списке товаров
        add_filter('bulk_actions-edit-product', [$this, 'register_bulk_action']);
        // WordPress вызовет register_bulk_action($actions), чтобы дополнить список bulk actions на экране Products.

        // 2) Обрабатываем нажатие Apply -> ставим задания в очередь
        add_filter('handle_bulk_actions-edit-product', [$this, 'handle_bulk_action'], 10, 3);
        // Когда админ нажимает Apply, WP вызывает handle_bulk_action($redirect_url, $action, $post_ids).

        // 3) Показываем notice после редиректа
        add_action('admin_notices', [$this, 'admin_notice']);
        // После bulk action WP делает редирект. На следующей загрузке админки показываем notice.

        // 4) Worker: что выполняется в фоне (Action Scheduler)
        add_action(self::JOB_HOOK, [$this, 'process_job'], 10, 1);
        // Регистрируем обработчик Action Scheduler: при выполнении JOB_HOOK вызовется process_job($args).
    }

    public function register_bulk_action(array $actions): array
        // Метод добавляет наш пункт в список bulk actions и возвращает обновлённый массив.
    {
        $actions[self::BULK_ACTION] = __('DeepSeek: Generate short descriptions', 'deepseek');
        // Добавляем пункт в dropdown. __() — функция перевода.
        // Ключ массива = идентификатор действия, значение = текст в UI.

        return $actions;
        // Возвращаем расширенный список действий обратно WordPress.
    }

    public function handle_bulk_action(string $redirect_url, string $action, array $post_ids): string
        // Метод вызывается после Apply: тут мы либо игнорируем, либо ставим задачи.
    {
        if ($action !== self::BULK_ACTION) {
            // Если выбрали НЕ наше действие — ничего не делаем.
            return $redirect_url;
            // Возвращаем оригинальный URL редиректа.
        }

        // Action Scheduler должен быть доступен (в WooCommerce он обычно есть)
        if (!function_exists('as_enqueue_async_action')) {
            // Если функции Action Scheduler нет — значит WooCommerce/Action Scheduler не активен.
            return add_query_arg([
                'deepseek_bulk' => 'missing_as',
                // Параметр в URL, чтобы потом показать notice об ошибке.
            ], $redirect_url);
            // Возвращаем URL редиректа, но с query args.
        }

        $queued = 0;
        // Счётчик: сколько товаров поставили в очередь.

        foreach ($post_ids as $product_id) {
            // Перебираем выбранные товары (ID постов типа product).
            $product_id = (int)$product_id;
            // Приводим к int (защита).
            if (!$product_id) continue;
            // Если ID 0/пусто — пропускаем.

            // Ставим async job в очередь
            as_enqueue_async_action(
                self::JOB_HOOK,
                // Имя hook'а задачи: позже Action Scheduler вызовет add_action(JOB_HOOK, ...)

                ['product_id' => $product_id],
                // Аргументы, которые попадут в process_job($args)

                self::GROUP
            // Группа задач (удобно фильтровать/искать в Scheduled Actions)
            );

            // опционально: пометим статус
            update_post_meta($product_id, '_deepseek_shortdesc_status', 'queued');
            // Сохраняем мета, чтобы видеть статус по каждому товару (queued/done/failed...)

            $queued++;
            // Увеличиваем счётчик.
        }

        return add_query_arg([
            'deepseek_bulk' => 'queued',
            // Статус в URL: queued (значит мы всё поставили)

            'queued' => $queued,
            // Сколько задач поставили — тоже в URL, чтобы показать в notice.
        ], $redirect_url);
        // Возвращаем URL редиректа с query args.
    }

    public function admin_notice(): void
        // Показывает уведомление в админке на основе query args.
    {
        if (!is_admin()) return;
        // На всякий случай: работаем только в админке.

        if (!isset($_GET['deepseek_bulk'])) return;
        // Если наш параметр отсутствует — ничего не показываем.

        $state = sanitize_text_field((string)$_GET['deepseek_bulk']);
        // Забираем параметр deepseek_bulk из URL и чистим его.

        if ($state === 'missing_as') {
            // Если нет Action Scheduler — показываем ошибку.
            echo '<div class="notice notice-error"><p><b>DeepSeek:</b> Action Scheduler not available. (WooCommerce должен быть активен)</p></div>';
            // HTML-уведомление WP.
            return;
        }

        if ($state === 'queued') {
            // Если мы поставили задачи — показываем успех.
            $queued = isset($_GET['queued']) ? (int)$_GET['queued'] : 0;
            // Забираем число queued из URL.
            echo '<div class="notice notice-success is-dismissible"><p><b>DeepSeek:</b> queued ' . esc_html($queued) . ' products for background generation. See Tools → Scheduled Actions.</p></div>';
            // is-dismissible — можно закрыть крестиком.
            return;
        }
    }

    /**
     * Это выполняется Action Scheduler-ом в фоне.
     * ВАЖНО: тут нет nonce (это не запрос из браузера), это серверная задача.
     */
    public function process_job(array $args): void
        // Worker: одна задача = один товар.
    {
        $product_id = (int)($args['product_id'] ?? 0);
        // Из аргументов задачи вытаскиваем product_id, приводим к int.
        if (!$product_id) return;
        // Если нет ID — выходим.

        $product = wc_get_product($product_id);
        // Получаем объект товара WooCommerce.
        if (!$product) {
            // Если товар не найден (удалён/битый ID) — отмечаем failure.
            update_post_meta($product_id, '_deepseek_shortdesc_status', 'failed:not_found');
            return;
        }

        $api_key = DeepSeek_Admin::get_api_key();
        // Берём API key из настроек (wp_options).
        if (!$api_key) {
            // Если ключ не задан — не можем сделать запрос.
            update_post_meta($product_id, '_deepseek_shortdesc_status', 'failed:no_api_key');
            return;
        }

        // ===== Собираем prompt (можно упростить/улучшить позже) =====
        $name = $product->get_name();
        // Название товара.
        $sku = $product->get_sku();
        // SKU товара.
        $price = $product->get_price();
        // Цена товара.

        $cats = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'names']);
        // Категории товара: возвращаем только names.
        $tags = wp_get_post_terms($product_id, 'product_tag', ['fields' => 'names']);
        // Теги товара: тоже только names.

        $prompt = "Write a short WooCommerce product description (80-120 words), plain text, no HTML.\n"
            // Начинаем собирать промпт.
            . "Name: {$name}\n"
            // Добавляем имя.
            . ($sku ? "SKU: {$sku}\n" : "")
            // Если есть SKU — добавляем, если нет — добавляем пустую строку.
            . ($price ? "Price: {$price}\n" : "")
            // Если есть цена — добавляем.
            . (!empty($cats) ? "Categories: " . implode(', ', $cats) . "\n" : "")
            // Если есть категории — превращаем массив в строку через запятую.
            . (!empty($tags) ? "Tags: " . implode(', ', $tags) . "\n" : "");
        // Если есть теги — аналогично.

        $resp = wp_remote_post('https://api.deepseek.com/chat/completions', [
            // Делаем HTTP POST к DeepSeek API.
            'headers' => [
                // Заголовки запроса:
                'Authorization' => 'Bearer ' . $api_key,
                // Авторизация ключом.
                'Content-Type' => 'application/json',
                // Тело — JSON.
            ],
            'timeout' => 30,
            // Таймаут 30 секунд, чтобы не висло бесконечно.
            'body' => wp_json_encode([
                // Формируем JSON-тело:
                'model' => 'deepseek-chat',
                // Модель.
                'messages' => [
                    // Сообщения в формате чата:
                    ['role' => 'user', 'content' => $prompt],
                    // Одно user-сообщение с промптом.
                ],
                'stream' => false,
                // Стрим выключен: хотим сразу полный ответ.
            ]),
        ]);

        if (is_wp_error($resp)) {
            // Если WordPress HTTP API вернул ошибку (DNS/SSL/timeout и т.д.)
            update_post_meta($product_id, '_deepseek_shortdesc_status', 'failed:http');
            // Ставим статус.
            error_log('DeepSeek bulk WP_Error: ' . $resp->get_error_message());
            // Пишем подробности в лог сервера.
            return;
        }

        $code = wp_remote_retrieve_response_code($resp);
        // Достаём HTTP статус (200, 401, 402, 429...).
        $body = wp_remote_retrieve_body($resp);
        // Достаём тело ответа (JSON строка).

        if ($code !== 200) {
            // Если статус не 200 — считаем ошибкой.
            update_post_meta($product_id, '_deepseek_shortdesc_status', 'failed:api_' . $code);
            // Сохраняем статус вида failed:api_402, failed:api_429...
            error_log("DeepSeek bulk HTTP $code: $body");
            // В лог кладём полный ответ API (для дебага).
            return;
        }

        $data = json_decode($body, true);
        // Превращаем JSON-строку в ассоциативный массив PHP (true = массивы, не объекты).
        $text = $data['choices'][0]['message']['content'] ?? '';
        // Берём основной текст ответа в формате OpenAI-like.
        // ?? '' — защита, если структура неожиданная.

        if (!$text) {
            // Если текст пустой — ошибка.
            update_post_meta($product_id, '_deepseek_shortdesc_status', 'failed:empty');
            return;
        }

        // Записываем short description
        wp_update_post([
            // Обновляем пост товара.
            'ID' => $product_id,
            // ID поста.
            'post_excerpt' => $text,
            // Woo short description — это post_excerpt.
        ]);

        update_post_meta($product_id, '_deepseek_shortdesc_status', 'done');
        // Ставим статус "done".
        update_post_meta($product_id, '_deepseek_shortdesc_generated_at', current_time('mysql'));
        // Сохраняем дату генерации (локальное время WP) в формате mysql.
    }
}

/*
    Bulk action →
    ставит задачи →
    Action Scheduler →
    обрабатывает по одному товару →
    DeepSeek API →
    wp_update_post()
    Это:
        не блокирует админку
        устойчиво к таймаутам
        не падает при 20–100 товарах
        масштабируется
 */
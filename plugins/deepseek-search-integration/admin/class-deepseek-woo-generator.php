<?php

namespace Vitos\DeepSeek\Admin;

defined('ABSPATH') || exit;

class DeepSeek_Woo_Generator
{
    const AJAX_ACTION = 'deepseek_generate_product_desc';
    const NONCE_ACTION = 'deepseek_generate_product_desc_nonce';

    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'add_metabox']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_' . self::AJAX_ACTION, [$this, 'handle_ajax']);
    }

    public function add_metabox(): void
    {
        add_meta_box(
            'deepseek_generator',
            'DeepSeek Generator',
            [$this, 'render_metabox'],
            'product',
            'side',
            'high'
        );
    }

    public function render_metabox(\WP_Post $post): void
    {
        $nonce = wp_create_nonce(self::NONCE_ACTION);
        echo '<p><strong>Generate product short description</strong></p>';
        echo '<p><label>Extra instruction (optional)</label></p>';
        echo '<textarea id="deepseek_extra" style="width:100%;min-height:70px;" placeholder="e.g. Write SEO-friendly, 3 bullet points, CTA..."></textarea>';

        echo '<p style="margin-top:10px;">';
        echo '<button type="button" class="button button-primary" id="deepseek-generate-btn" data-product-id="' . esc_attr($post->ID) . '" data-nonce="' . esc_attr($nonce) . '">Generate</button>';
        echo '</p>';

        echo '<div id="deepseek-gen-status" style="margin-top:10px;"></div>';
    }

    public function enqueue_admin_assets(string $hook): void
    {
        // Только на странице редактирования товара
        if ($hook !== 'post.php' && $hook !== 'post-new.php') return;

        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'product') return;

        wp_enqueue_script(
            'deepseek-woo-generator',
            DEEPSEEK_PLUGIN_URL . 'admin/js/deepseek-woo-generator.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('deepseek-woo-generator', 'deepseekWoo', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'action' => self::AJAX_ACTION,
        ]);

    }

    public function handle_ajax(): void
    {
        // если пользователь не с правами edit_products
        if (!current_user_can('edit_products')) {
            wp_send_json_error(['message' => 'Forbidden'], 403);
        }

        $nonce = sanitize_text_field($_POST['nonce'] ?? '');
        if (!wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            wp_send_json_error(['message' => 'Bad nonce'], 403);
        }

        $product_id = (int)($_POST['product_id'] ?? 0);
        if (!$product_id) {
            wp_send_json_error(['message' => 'Missing product_id'], 400);
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error(['message' => 'Product not found'], 404);
        }

        $extra = sanitize_textarea_field($_POST['extra'] ?? '');

        // Собираем данные товара
        $name = $product->get_name();
        $sku = $product->get_sku();
        $price = $product->get_price();

        $cats = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'names']);
        $tags = wp_get_post_terms($product_id, 'product_tag', ['fields' => 'names']);

        $attrs = [];

        foreach ($product->get_attributes() as $attr) {
            if ($attr->is_taxonomy()) {
                $label = wc_attribute_label($attr->get_name());
                $values = wc_get_product_terms($product_id, $attr->get_name(), ['fields' => 'names']);
                $attrs[] = $label . ': ' . implode(', ', $values);
            } else {
                $attrs[] = $attr->get_name() . ': ' . implode(', ', $attr->get_options());
            }
        }

        $prompt = "Write a short WooCommerce product description (max 80-120 words) for:\n"
            . "Name: {$name}\n"
            . ($sku ? "SKU: {$sku}\n" : "")
            . ($price ? "Price: {$price}\n" : "")
            . (!empty($cats) ? "Categories: " . implode(', ', $cats) . "\n" : "")
            . (!empty($tags) ? "Tags: " . implode(', ', $tags) . "\n" : "")
            . (!empty($attrs) ? "Attributes:\n- " . implode("\n- ", $attrs) . "\n" : "")
            . ($extra ? "\nExtra instruction:\n{$extra}\n" : "")
            . "\nOutput plain text only (no HTML).";

        $api_key = DeepSeek_Admin::get_api_key();
        if (!$api_key) {
            wp_send_json_error(['message' => 'API key not set (Settings → DeepSeek)'], 500);
        }

        $resp = wp_remote_post('https://api.deepseek.com/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
            'body' => wp_json_encode([
                'model' => 'deepseek-chat',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'stream' => false,
            ]),
        ]);

        if (is_wp_error($resp)) {
            wp_send_json_error(['message' => $resp->get_error_message()], 500);
        }

        $code = wp_remote_retrieve_response_code($resp);
        $body = wp_remote_retrieve_body($resp);

        if ($code !== 200) {
            error_log("DeepSeek Woo HTTP $code: $body");
            wp_send_json_error(['message' => "DeepSeek error ($code)"], $code);
        }

        $data = json_decode($body, true);
        $text = $data['choices'][0]['message']['content'] ?? '';

        if (!$text) {
            wp_send_json_error(['message' => 'Empty response from DeepSeek'], 502);
        }

        // Вставляем в short description (excerpt)
        wp_update_post([
            'ID' => $product_id,
            'post_excerpt' => $text,
        ]);

        wp_send_json_success([
            'text' => $text,
        ]);
    }
}

/*
 add_action('add_meta_boxes', [$this, 'add_metabox']);
    Означает:
        Когда WordPress дойдёт до хука add_meta_boxes (момент, когда WP добавляет метабоксы на экран редактирования записи),
        он вызовет метод add_metabox() у текущего объекта этого класса.

Что такое [$this, 'add_metabox']
    Это “callback” (ссылка на функцию), в формате:
        первый элемент — объект ($this)
        второй элемент — имя метода ('add_metabox')
То есть по сути это эквивалент:
    $this->add_metabox(); но не сразу, а когда сработает хук.

    Почему не self::add_metabox
    Потому что self::... — это статический контекст (метод должен быть static).
    А у тебя обычный метод объекта, поэтому нужен $this.

const AJAX_ACTION = ... — это обычная константа класса. Она существует ещё с PHP 5.x.


Что означает self::AJAX_ACTION
    Это просто доступ к константе класса.
        self:... = “возьми из этого класса”
        это работает и в обычных (не static) методах, и в static методах.


    public function enqueue_admin_assets(string $hook): void
        означает:
            метод принимает один параметр
            этот параметр называется $hook
            его тип — string
            метод ничего не возвращает (: void)
    Что такое $hook. Этот параметр передаётся WordPress автоматически, потому что ты подписался на хук:
    add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    Когда WordPress вызывает admin_enqueue_scripts, он передаёт в callback строку текущей админ-страницы.
    То есть $hook — это: идентификатор текущего admin screen.
    | Страница                   | $hook значение        |
    | -------------------------- | --------------------- |
    | Dashboard                  | `index.php`           |
    | Settings → General         | `options-general.php` |
    | Posts list                 | `edit.php`            |
    | Edit post                  | `post.php`            |
    | Add new post               | `post-new.php`        |
    | Edit product (WooCommerce) | `post.php`            |
    Почему ты это используешь
    if ($hook !== 'post.php' && $hook !== 'post-new.php') return;
    Это значит: Не подключай JS, если мы не на странице редактирования записи.
    А дальше ты ещё проверяешь:
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'product') return;
    Это значит: Не подключай JS, если мы не на странице редактирования товара.
        То есть:
            Мы должны быть на post.php или post-new.php
            И тип записи должен быть product
            Тогда скрипт подключается только для WooCommerce товаров.

    public function render_metabox(\WP_Post $post): void
    Что такое \WP_Post.
    Это тип параметра. WordPress передаёт в функцию объект текущего поста — экземпляр класса WP_Post.
    Ты говоришь PHP: “Этот параметр $post обязательно должен быть объектом класса WP_Post”.
    А что за \ перед WP_Post?
    Это глобальное пространство имён (global namespace).
    Когда ты пишешь: \WP_Post Это значит:  Ищи класс WP_Post в корневом (глобальном) namespace.
    Почему иногда пишут без \
        Можно написать и так:
        public function render_metabox(WP_Post $post): void
        И будет работать.
    Но если твой файл использует namespace, например:
    namespace DeepSeek;
        Тогда WP_Post будет искаться как:
        DeepSeek\WP_Post
    А такого класса нет. Поэтому нужно указать глобальный namespace: \WP_Post
    Чтобы гарантировать, что это глобальный WordPress класс, а не что-то внутри namespace.
 * */
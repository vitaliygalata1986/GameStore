<?php

namespace Vitos\DeepSeek\Includes;

use Vitos\DeepSeek\Admin\DeepSeek_Admin;

defined('ABSPATH') || exit;

class DeepSeek_Search_Integration
{
    const NONCE_ACTION = 'deepseek_search_nonce';

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'register_frontend_assets']);
        add_shortcode('deepseek_search', [$this, 'render_shortcode']);

        add_action('wp_ajax_deepseek_search', [$this, 'handle_ajax']);
        add_action('wp_ajax_nopriv_deepseek_search', [$this, 'handle_ajax']);
    }

    public function register_frontend_assets()
    {

        $ver = '1.0.0';

        wp_enqueue_style(
            'deepseek-search-integration',
            DEEPSEEK_PLUGIN_URL . 'public/css/deepseek.css',
            [],
            $ver
        );

        wp_enqueue_script(
            'deepseek-search-integration',
            DEEPSEEK_PLUGIN_URL . 'public/js/deepseek-ajax.js',
            ['jquery'],
            $ver,
            true
        );

        wp_localize_script('deepseek-search-integration', 'deepseek_ajax_params', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(self::NONCE_ACTION),
        ]);
    }

    public function render_shortcode(): string
    {
        ob_start();

        $template = DEEPSEEK_PLUGIN_DIR . 'public/templates/deepseek-form.php';
        if (file_exists($template)) {
            include $template;
        } else {
            echo '<p>DeepSeek template not found.</p>';
        }

        return (string)ob_get_clean();
    }

    public function handle_ajax()
    {
        // 1) nonce
        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            wp_send_json_error(['message' => 'Bad nonce'], 403);
        }

        // 2) prompt
        $prompt = sanitize_text_field($_POST['prompt'] ?? '');
        if ($prompt === '') {
            wp_send_json_error(['message' => 'Empty prompt'], 400);
        }

        // 3) API key из настроек
        $api_key = DeepSeek_Admin::get_api_key();
        if (!$api_key) {
            wp_send_json_error(['message' => 'API key not set (Settings → DeepSeek)'], 500);
        }

        // 4) запрос к DeepSeek
        $response = wp_remote_post('https://api.deepseek.com/chat/completions', [
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

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()], 500);
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code !== 200) {
            error_log("DeepSeek HTTP $code: $body");
            wp_send_json_error(['message' => "DeepSeek error ($code)"], $code);
        }

        $data = json_decode($body, true);
        $text = $data['choices'][0]['message']['content'] ?? '';

        wp_send_json_success([
            'text' => $text,
        ]);
    }
}

// self в PHP внутри класса — это ссылка на сам класс, а не на объект.
/*
 То есть:

    self::NONCE_ACTION — берёт константу NONCE_ACTION из этого же класса
        это то же самое, что написать:
        DeepSeek_Search_Integration::NONCE_ACTION
    Чем отличается от $this
        - $this — это текущий объект (экземпляр класса)
        - self — это сам класс (статический контекст)
    Почему мы используем self тут
    Потому что NONCE_ACTION — это константа класса, она не зависит от объекта.
 * */


/*
 Nonce (защита от подделки запросов / CSRF)
    $nonce = $_POST['nonce'] ?? '';
    if (!wp_verify_nonce($nonce, self::NONCE_ACTION)) {
        wp_send_json_error(['message' => 'Bad nonce'], 403);
    }
    nonce — одноразовый токен, который ты передал в JS через wp_localize_script.
    wp_verify_nonce проверяет: это запрос реально с твоего сайта, а не кто-то снаружи стучится в AJAX.
    Если nonce неверный → возвращаем JSON ошибку и HTTP статус 403.
    Важно: после wp_send_json_error выполнение заканчивается (WP делает wp_die() внутри), поэтому дальше код не идёт.


Prompt (валидация и очистка)

    $prompt = sanitize_text_field($_POST['prompt'] ?? '');
    if ($prompt === '') {
        wp_send_json_error(['message' => 'Empty prompt'], 400);
    }

    sanitize_text_field вычищает:
        HTML
        странные символы/переносы
        потенциальный мусор
        Если пусто → ошибка 400 (bad request)
    Ты защищаешь сервер и не отправляешь пустые запросы к API.


API key из настроек (серверное хранение)

    $api_key = DeepSeek_Admin::get_api_key();
    if (!$api_key) {
        wp_send_json_error(['message' => 'API key not set (Settings → DeepSeek)'], 500);
    }

    Ключ НЕ в JS, НЕ в Network.
        Берётся на сервере из wp_options.
        Если ключа нет — это ошибка конфигурации сервера → 500.
    Правильно: ключ никогда не светится в браузере.

Запрос к DeepSeek через wp_remote_post

$response = wp_remote_post('https://api.deepseek.com/chat/completions', [
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

Что тут важно:
    URL
        /chat/completions — “чат-эндпоинт” DeepSeek (в стиле OpenAI).
    headers
        Authorization: Bearer <key> — как DeepSeek понимает, чей это запрос.
        Content-Type: application/json — тело в JSON.
timeout
    Если API зависнет — через 30 секунд оборвём.
    body
        Ты отправляешь:
            модель: deepseek-chat
            сообщения: массив сообщений (у тебя пока 1 user)
            stream: false (обычный ответ целиком)


Ошибка WordPress HTTP слоя (wp_remote_post)
    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()], 500);
    }
Это ошибки типа:
    DNS не резолвится
    таймаут
    SSL проблема
    нет сети


HTTP status + body
    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

$code — HTTP код ответа (200/401/402/429/500…)
$body — JSON строка ответа

Парсим JSON и вытаскиваем текст

$data = json_decode($body, true);
$text = $data['choices'][0]['message']['content'] ?? '';

json_decode(..., true) → массив PHP
    DeepSeek в формате OpenAI возвращает choices[0].message.content
    ?? '' защищает от notice, если структура неожиданная.

Успех
wp_send_json_success([
    'text' => $text,
]);
Отдаёт фронту:
JSON
{
  "success": true,
  "data": {
    "text": "..."
  }
}
Это то, что JS удобно обработает.
 * */


/*
// json_decode() — это функция PHP, которая превращает JSON-строку в данные PHP.
/*
 $data = json_decode($body, true);
 $body — это строка, которую вернул DeepSeek, типа:
 JSON
 {"choices":[{"message":{"content":"Привет"}}]}
    json_decode(..., true) превращает её в ассоциативный массив PHP:
    PHP
    [
      "choices" => [
        [
          "message" => [
            "content" => "Привет"
          ]
        ]
      ]
    ]

И поэтому ты можешь сделать:
$text = $data['choices'][0]['message']['content'];

Что значит true
    сли true — получишь массивы ([] и =>)
    Если убрать true — получишь объекты (->), тогда доступ был бы так:
    PHP
    $data->choices[0]->message->content
 * */
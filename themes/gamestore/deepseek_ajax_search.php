<?php
add_action('wp_ajax_deepseek_search', 'deepseek_search');
add_action('wp_ajax_nopriv_deepseek_search', 'deepseek_search');

function deepseek_search()
{

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'deepseek_search_nonce')) {
        wp_die('Bad nonce', 403);
    }

    $prompt = sanitize_text_field($_POST['prompt']);
    $api_key = defined('DEEPSEEK_API_KEY') ? DEEPSEEK_API_KEY : '';

    if (!$api_key) {
        echo 'Server: DEEPSEEK_API_KEY not set';
        wp_die();
    }

    $response = wp_remote_post('https://api.deepseek.com/chat/completions', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'model' => 'deepseek-chat',
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            )
        )),
    ));

    if (is_wp_error($response)) {
        echo 'Error: ' . $response->get_error_message();
        wp_die();
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    if ($code !== 200) {
        error_log("DeepSeek HTTP $code: $body");
        echo "DeepSeek error ($code).";
        wp_die();
    }

    $data = json_decode($body, true);
    $text = $data['choices'][0]['message']['content'] ?? '';
    echo $text;
    wp_die();
}
<?php
// поцепимся на хук woocommerce_product_data_tabs - там хранится информация по всем табам
add_filter('woocommerce_product_data_tabs', 'add_gamestore_tab');

/**
 * Helper function to get platforms list
 */
function get_gamestore_platforms() {
    return [
        'xbox'        => 'Xbox',
        'playstation' => 'PlayStation',
        'nintendo'    => 'Nintendo Switch'
    ];
}
function add_gamestore_tab($tabs) // добавляем новый таб
{
    $tabs['games'] = array(
        'label' => __('GameStore', 'core-gamestore'),
        'target' => 'gamestore_product_data', // id данного массива
        'class' => array('show_if_simple', 'show_if_variable'), // добавим class для simple/variable product
        'priority' => 21
    );
    return $tabs;
}

// дальше будет наполнять это поле полями
add_action('woocommerce_product_data_panels', 'add_gamestore_tab_content');

function add_gamestore_tab_content()
{
    // здесь будем генерировать мета-боксы
    global $post;
    echo '<div id="gamestore_product_data" class="panel woocommerce_options_panel">';
    echo '<div class="options_group">';
    // simple text field
    woocommerce_wp_text_input(
        array(
            'id' => '_gamestore_publisher',
            'label' => __('Publisher', 'core-gamestore'),
            'description' => __('Publisher of the game', 'core-gamestore'),
            'desc_tip' => true, // вывод descrition в виде подсказки
            'placeholder' => __('e.g. Ubisoft', 'core-gamestore'),
        )
    );

    // Multicheckbox field
    echo '<p class="form-field"><label><strong>' . __('Platforms', 'core-gamestore') . '</strong></label>';
    $platforms = get_gamestore_platforms();
    foreach ($platforms as $slug => $label) {
        woocommerce_wp_checkbox(
            array(
                'id' => "_platform_$slug",  // id для checkbox
                'label' => $label, // название checkbox
                'description' => sprintf(__('Available on %s', 'core-gamestore'), $label),
            )
        );
    }
    echo '</p>';
    echo '</div>';
    echo '</div>';
}

/*
 Синхронизация вкладки и панели идёт через совпадение target и id.
    Как это работает в WooCommerce admin:
    В фильтре woocommerce_product_data_tabs ты добавляешь таб:
        'target' => 'gamestore_product_data'
    WooCommerce из этого делает ссылку на панель (условно href="#gamestore_product_data").
    // http://localhost:8200/wp-admin/post.php?post=269&action=edit#gamestore_product_data

    В woocommerce_product_data_panels ты выводишь панель с таким же id:
    <div id="gamestore_product_data" class="panel woocommerce_options_panel">
    Когда кликаешь на вкладку, WooCommerce/JS просто показывает div с этим id, а остальные панели прячет.
    То есть правило простое: target == id панели.
 * */

// в целом работа с метабоксами сводится к: регистрация метабоксов, html, сохранение
add_action('woocommerce_process_product_meta', 'save_gamestore_tab_fields');

function save_gamestore_tab_fields($post_id)
{ // эта функция будет сохранять мета поле Publisher и значения чекбоксов

    // после сабмита формы
    if (isset($_POST['_gamestore_publisher'])) {
        // сохраняем для того поста $post_id откуда она пришла. '_gamestore_publisher', - сам ключ, а значение - sanitize_textarea_field($_POST['_gamestore_publisher']
        update_post_meta($post_id, '_gamestore_publisher', sanitize_textarea_field($_POST['_gamestore_publisher']));
    }

    $platforms = get_gamestore_platforms();
    foreach ($platforms as $slug => $label) {
        $key = "_platform_$slug";
        $checkbox_value = isset($_POST[$key]) ? 'yes' : 'no';
        update_post_meta(
            $post_id,
            $key,
            $checkbox_value
        );
    }
}
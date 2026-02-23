<?php
/**
 * Functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package gamestore
 * @since 1.0.0
 */

/**
 * Enqueue the CSS files.
 *
 * @return void
 * @since 1.0.0
 *
 */

require_once('deepseek_ajax_search.php');

add_action('after_setup_theme', function () {
    add_theme_support('woocommerce');
});


function gutenberg_activate_on_products($can_edit, $post_type)
{
    if ($post_type == 'product') {
        return true;
    }
    return $can_edit;
}

add_filter('use_block_editor_for_post_type', 'gutenberg_activate_on_products', 10, 2);

// Включаем поддержку REST API для таксономий товаров (категории и метки),
// чтобы они отображались в редакторе Gutenberg
function enable_taxonomy_rest($args)
{
    $args['show_in_rest'] = true;
    return $args;
}

add_filter('woocommerce_taxonomy_args_product_cat', 'enable_taxonomy_rest');
add_filter('woocommerce_taxonomy_args_product_tag', 'enable_taxonomy_rest');


// фильтр удаляет заголовок «Описание» (Description), который по умолчанию отображается внутри вкладки «Описание» на странице одного товара в WooCommerce...
add_filter('woocommerce_product_description_heading', '__return_null');


function gamestore_styles()
{
    wp_enqueue_style('gamestore-general', get_template_directory_uri() . '/assets/css/gamestore.css', [], wp_get_theme()->get('Version'));
    wp_enqueue_script('gamestore-theme-related', get_template_directory_uri() . '/assets/js/gamestore.js', [], wp_get_theme()->get('Version'), true);
    wp_localize_script('gamestore-theme-related', 'gamestore_params', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
    ));

    // wp_localize_script - позволяет динамически передавать данные внутрь JS


    //Swiper Slider
    wp_enqueue_style('swiper-bundle', get_template_directory_uri() . '/assets/css/swiper-bundle.min.css', [], wp_get_theme()->get('Version'));
    wp_enqueue_script('swiper-bundle', get_template_directory_uri() . '/assets/js/swiper-bundle.min.js', [], wp_get_theme()->get('Version'), true);
}

add_action('wp_enqueue_scripts', 'gamestore_styles');

function gamestore_google_font()
{
    $font_url = '';
    $font = 'Urbanist';
    $font_extra = 'ital,wght@0,400;0,700;1,400;1,700';


    if ('off' !== _x('on', 'Google font: on or off', 'gamestore')) {
        $query_args = array(
                'family' => urldecode($font . ':' . $font_extra),
                'subset' => urldecode('latin,latin-ext'),
                'display' => urldecode('swap'),
        );
        $font_url = add_query_arg($query_args, '//fonts.googleapis.com/css2');
    }

    return $font_url;
}

function gamestore_google_font_script()
{
    wp_enqueue_style('gamestore-google-font', gamestore_google_font(), [], '1.0.0');
}

add_action('wp_enqueue_scripts', 'gamestore_google_font_script');


// Load assets in Gutenberg
function gamestore_gutenberg_styles()
{
    wp_enqueue_style('gamestore-google-font', gamestore_google_font(), [], '1.0.0');
    if (is_admin()) {
        wp_enqueue_style('gamestore-editor-style', get_template_directory_uri() . '/assets/css/editor-style.css', ['gamestore-google-font'], wp_get_theme()->get('Version'));
        wp_enqueue_style('woo-cart', get_template_directory_uri() . '/assets/css/woo-cart.css', [], wp_get_theme()->get('Version'));
        wp_enqueue_style('woo-checkout', get_template_directory_uri() . '/assets/css/woo-checkout.css', [], wp_get_theme()->get('Version'));
        add_editor_style('assets/css/editor-style.css');
        add_editor_style('assets/css/woo-cart.css');
    }
    if (is_cart()) {
        wp_enqueue_style('woo-cart', get_template_directory_uri() . '/assets/css/woo-cart.css', [], wp_get_theme()->get('Version'));
    }
    if (is_checkout()) {
        wp_enqueue_style('woo-checkout', get_template_directory_uri() . '/assets/css/woo-checkout.css', [], wp_get_theme()->get('Version'));
    }
}

add_action('enqueue_block_editor_assets', 'gamestore_gutenberg_styles');
add_action('enqueue_block_assets', 'gamestore_gutenberg_styles');

// enqueue_block_editor_assets выполняется только в админке редактора.

/*
 Оба хука (enqueue_block_editor_assets и enqueue_block_assets) — это про подключение ассетов для блоков, но они срабатывают в разных местах и для разной аудитории.
enqueue_block_editor_assets

Срабатывает только в редакторе:

    Gutenberg editor (редактор записи/страницы)
    Site Editor (FSE: шаблоны/части шаблонов)
    Экран виджетов блоками (если используется)

То есть всё, что ты туда подключаешь, увидишь только в админке внутри редактора.
Типично туда кладут: CSS/JS, которые нужны для отображения блоков в редакторе (editor-only).


enqueue_block_assets

Срабатывает и на фронте, и в редакторе:
    на сайте (frontend), когда рендерятся блоки
    и в редакторе тоже

То есть это “общие” стили/скрипты для блоков, которые должны работать везде.
Типично туда кладут: CSS, который нужен и на сайте, и в редакторе, чтобы блоки выглядели одинаково.


use_block_editor_for_post_type — именно он отвечает за переключение между классическим редактором и Gutenberg для конкретных типов записей.
 * */


/////////////  DeeepSeek Search /////////////

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(/**/
            'deepseek',
            get_template_directory_uri() . '/assets/css/deepseek.css',
            [],
            wp_get_theme()->get('Version')
    );

    wp_enqueue_script(
            'deepseek-ajax',
            get_template_directory_uri() . '/assets/js/deepseek-ajax.js',
            ['jquery'],
            wp_get_theme()->get('Version'),
            true
    );

    wp_localize_script('deepseek-ajax', 'deepseek_ajax_params', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('deepseek_search_nonce'),
    ]);
});


function deepseek_search_form_shortcode()
{
    ob_start();
    ?>
    <form id="deepseek-search-form">
        <input type="text" id="deepseek-prompt" name="prompt" placeholder="Enter your prompt..." required>
        <button type="submit">Search</button>
    </form>
    <div id="deepseek-response"></div>
    <?php
    return ob_get_clean();
}

add_shortcode('deepseek_search', 'deepseek_search_form_shortcode');
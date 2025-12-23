<?php

// Подгрузим с помощью хука верстку с попап
function gamestore_footer_search_popup()
{
    ?>

    <div class="popup-games-search-container">
        <span id="close-search"></span>
        <div class="search-container">
            <div class="search-bar wrapper">
                <h2 class="search-label">Search</h2>
                <input type="text" name="game-title" id="popup-search-input" placeholder="Search for Games"/>
                <p class="search-popup-title"></p>
            </div>
            <div class="search-results-wrapper">
                <div class="popup-search-results wrapper"></div>
            </div>
        </div>
    </div>

    <?php

}

add_action('wp_footer', 'gamestore_footer_search_popup');

// Load Latest 18 Games
function load_latest_games()
{
    $args = array(
            'post_type' => 'product',
            'posts_per_page' => 18,
            'post_status' => 'publish',
            'orderby' => 'rand',
    );
    $games_query = new WP_Query($args);

    $result = array();

    if ($games_query->have_posts()) {
        while ($games_query->have_posts()) {
            $games_query->the_post(); // подготавливаем глобальный $post и данные текущего поста для шаблонных функций

            $product = wc_get_product(get_the_ID());

            $platforms_html = '';
            $platforms = array('Xbox', 'Playstation', 'Nintendo');
            foreach($platforms as $platform){
                $platforms_html .= (get_post_meta(get_the_ID(), '_platform_'.strtolower($platform), true ) == 'yes') ? '<div class="platform_'.strtolower($platform).'"></div>' : null;
            }

            $result[] = array(
                    'link' => get_the_permalink(),
                    'thumbnail' => $product->get_image('full'),
                    'price' => $product->get_price_html(),
                    'title' => get_the_title(),
                    'platforms' => $platforms_html,
            );
        }
    }
    wp_reset_postdata(); // сбрасываем query

    wp_send_json_success($result);
}

function search_games_by_title()
{
    $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            's' => $search_term
    );

    $games_query = new WP_Query($args);

    $result = array();

    if ($games_query->have_posts()) {
        while ($games_query->have_posts()) {
            $games_query->the_post();

            $product = wc_get_product(get_the_ID());

            $platforms_html = '';
            $platforms = array('Xbox', 'Playstation', 'Nintendo');
            foreach($platforms as $platform){
                $platforms_html .= (get_post_meta(get_the_ID(), '_platform_'.strtolower($platform), true ) == 'yes') ? '<div class="platform_'.strtolower($platform).'"></div>' : null;
            }

            $result[] = array(
                    'link' => get_the_permalink(),
                    'thumbnail' => $product->get_image('full'),
                    'price' => $product->get_price_html(),
                    'title' => get_the_title(),
                    'platforms' => $platforms_html,
            );
        }
    }
    wp_reset_postdata();
    wp_send_json_success($result);
}

add_action('wp_ajax_load_latest_games', 'load_latest_games');
add_action('wp_ajax_nopriv_load_latest_games', 'load_latest_games');

add_action('wp_ajax_search_games_by_title', 'search_games_by_title');
add_action('wp_ajax_nopriv_search_games_by_title', 'search_games_by_title');

/*
    1. wp_send_json_success($result);
    Это стандартная WordPress-функция для AJAX-ответов.
    Формирует JSON-ответ такого вида:
        {
          "success": true,
          "data": [...твой массив $result...]
        }
    Отправляет этот JSON в браузер.
    Сразу останавливает выполнение скрипта (wp_die() внутри).
    То есть, в твоём случае:

    $result = [
      [
        'link' => '...',        // ссылка на товар
        'thumbnail' => '...',   // HTML-код картинки
        'price' => '...',       // HTML цены
        'title' => '...',       // заголовок товара
      ],
      ...
    ];
    wp_send_json_success($result);

    На фронтенде в JS ты обычно получишь что-то вроде:
    {
      success: true,
      data: [ {...}, {...}, ... ]
    }


    2. $product = wc_get_product(get_the_ID());
    Это функция WooCommerce.
    Разбор по частям:
      get_the_ID() — возвращает ID текущего поста в цикле WP_Query (в твоём случае — это товар, т.е. product).
      wc_get_product( $post_id ) — по этому ID возвращает объект товара WooCommerce (WC_Product или его наследников).
    То есть:
    $product = wc_get_product(get_the_ID());
    Создаёт объект товара, через который ты можешь удобно получать данные:
        $product->get_price_html() — цена с валютой и форматированием (как у тебя в коде)
        $product->get_image('full') — HTML-код картинки товара
        $product->get_sku() — артикул
        $product->get_regular_price() — обычная цена
        $product->get_sale_price() — цена по скидке
    и т.д.
 * */


/*
   $games_query->the_post();
       Продвигает указатель цикла на следующий пост внутри $games_query.
       Устанавливает глобальный $post на текущий пост из этого запроса.
       Подготавливает шаблонные теги типа the_title(), the_permalink(), get_the_ID(), the_content() и т.д., чтобы они работали с этим постом.
*/

/* sanitize_text_field($_POST['search'])
   Это функция WordPress, которая очищает текст: убирает лишние/опасные символы, теги и т.п., чтобы защититься от мусора и XSS при вводе пользователем. */


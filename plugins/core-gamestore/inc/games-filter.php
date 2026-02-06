<?php

add_action('wp_ajax_filter_games', 'filter_games_ajax_handler');
add_action('wp_ajax_nopriv_filter_games', 'filter_games_ajax_handler');

function filter_games_ajax_handler()
{
    $posts_per_page = isset($_POST['posts_per_page']) ? absint($_POST['posts_per_page']) : 8;
    $paged = isset($_POST['page']) ? max(1, absint($_POST['page'])) : 1;

    $genres_ids = !empty($_POST['genres']) ? wp_parse_id_list($_POST['genres']) : [];
    $languages_ids = !empty($_POST['languages']) ? wp_parse_id_list($_POST['languages']) : [];
    $platform_id = !empty($_POST['platforms']) ? absint($_POST['platforms']) : 0;
    $released = isset($_POST['released']) ? sanitize_text_field($_POST['released']) : '';
    $sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : '';

    $publisher = !empty($_POST['publisher'])
        ? trim(sanitize_text_field(wp_unslash($_POST['publisher'])))
        : '';

    $singleplayer = !empty($_POST['singleplayer'])
        ? trim(sanitize_text_field(wp_unslash($_POST['singleplayer'])))
        : '';

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => $posts_per_page,
        'post_status' => 'publish',
        'paged' => $paged
    );

    $args['meta_query'] = ['relation' => 'AND']; // WP обычно сам “AND” делает, но явно — чище и предсказуеме

    if (!empty($languages_ids)) {
        $args['tax_query'][] = [
            'taxonomy' => 'languages',
            'field' => 'term_id',
            'terms' => $languages_ids,
        ];
    }

    if (!empty($genres_ids)) {
        $args['tax_query'][] = [
            'taxonomy' => 'genres',
            'field' => 'term_id',
            'terms' => $genres_ids,
        ];
    }

    if ($platform_id) {
        $args['tax_query'][] = [
            'taxonomy' => 'platforms',
            'field' => 'term_id',
            'terms' => $platform_id
        ];
    }

    if ($publisher) {
        $args['meta_query'][] = array(
            'key' => '_gamestore_publisher',
            'value' => $publisher,
            'compare' => '='
        );
    }


    if ($singleplayer) {
        $args['meta_query'][] = array(
            'key' => '_gamestore_single_player',
            'value' => $singleplayer,
            'compare' => '='
        );
    }

    if ($released) {
        $args['meta_query'][] = array(
            'key' => '_gamestore_released_date',
            'value' => array("{$released}-01-01", "{$released}-12-31"),
            'compare' => 'BETWEEN',
            'type' => 'DATE',
        );
    }

    switch ($sort) {
        case 'latest':
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
        case 'price_low_high':
            $args['meta_key'] = '_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC';
            break;
        case 'price_high_low':
            $args['meta_key'] = '_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'popularity':
            $args['meta_key'] = 'total_sales';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        default:
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
    }


    $filtered_games = get_posts($args);


    $html = '';
    if (!empty($filtered_games)) {
        $platforms_map = function_exists('get_gamestore_platforms') ? get_gamestore_platforms() : [];

        foreach ($filtered_games as $post) {
            $game = wc_get_product($post->ID);
            $html .= '<div class="game-result">';
            $html .= '<a href="' . esc_url($game->get_permalink()) . '">';
            $html .= '<div class="game-featured-image">' . wp_kses_post($game->get_image('full')) . '</div>';
            $html .= '<div class="game-meta">';
            $html .= '<div class="game-price">' . wp_kses_post($game->get_price_html()) . '</div>';
            $html .= '<h3>' . esc_html($game->get_name()) . '</h3>';
            $html .= '<div class="game-platforms">';
            foreach ($platforms_map as $slug => $label) {
                if (get_post_meta($game->get_id(), '_platform_' . strtolower($slug), true) === 'yes') {
                    $html .= '<div class="platform_' . esc_attr(strtolower($slug)) . '"></div>';
                }
            }
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</a>';
            $html .= '</div>';
        }
    } else {
        $html = '<p>No games found</p>';
    }

    echo $html;

    wp_die();
}


// WP делает фильтрацию и находит нужные товары (через get_posts), потом WooCommerce превращает каждый найденный товар в “умный” объект (через wc_get_product)

/*
 Почему так делают?
    WP_Post — это “сырой” объект WordPress (только данные поста).
    WC_Product — это объект WooCommerce, у которого есть удобные методы:
        $game->get_permalink() — ссылка на товар
        $game->get_image() — HTML картинки товара
        $game->get_price_html() — HTML цены (с валютой, скидкой и т.п.)
        $game->get_name() — имя товара
        $game->get_id() — ID товара

    wc_get_product() — функция WooCommerce.
    Она принимает ID товара и возвращает объект WC_Product.
 * */

// tax_query — это специальный параметр WP_Query (и похожих запросов), который говорит: отфильтруй записи по терминам таксономии.
/*
    [] в конце означает “добавь ещё одно условие в массив”.
        Это удобно, когда у тебя несколько таксономий (genres, languages, platforms и т.д.) — ты добавляешь условия одно за другим:
            $args['tax_query'][] = ... (для platforms)
            $args['tax_query'][] = ... (для genres)
        и т.д.

    'taxonomy' => 'platforms' - Какая именно таксономия используется — тут это platforms.
    field' => 'term_id' - Говорит, что ты передаёшь термины по ID (а не по slug или name). То есть terms будет содержать ID терминов.
    'terms' => $platforms - Какие именно термины выбирать.
 * */
// sanitize_text_field() — это функция в WordPress, которая очищает текстовое значение (строку) так, чтобы его было безопасно хранить/обрабатывать как обычный текст.


/*
    $languages_raw = isset($_POST['languages']) ? wp_unslash($_POST['languages']) : '';
    $languages_ids = array_filter(array_map('absint', explode(',', $languages_raw)));
    Вот эти две строки делают из того, что пришло из JS ("29,25,26"), чистый массив числовых ID [29, 25, 26].

    1) $languages_raw
        Если languages пришёл — берём его.
            wp_unslash() убирает “слэши”, которые WordPress иногда добавляет к входным данным (\).
        Если не пришёл — ставим пустую строку ''.
        Пример:
            было: $_POST['languages'] = "29,25,26"
            стало: $languages_raw = "29,25,26"
    2) explode(',', $languages_raw)
        Разрезает строку по запятым:
        "29,25,26" → ["29", "25", "26"]
    3) array_map('absint', ...)
       Проходит по каждому элементу и превращает в положительное целое число:
        ["29","25","26"] → [29, 25, 26]
        Если там будет мусор, типа "29,abc,-5", то:
            absint("abc") → 0
            absint("-5") → 5

 * */


// wp_parse_id_list() — это функция WordPress, которая берёт “список ID” и превращает его в чистый массив целых чисел.
/*
 Что она умеет
    принимает строку вида "29,25,26" или массив
    разбивает по запятым/пробелам
    приводит всё к int (по сути как absint)
    убирает мусор и нули
    обычно убирает дубликаты

    wp_parse_id_list("29,25,26");      // [29, 25, 26]
    wp_parse_id_list(" 29, 25 ,abc");  // [29, 25]
    wp_parse_id_list([ "10", 0, "7" ]);// [10, 7]
 * */


/*
 В админке ты создаёшь поле:
    woocommerce_wp_text_input([
      'id' => '_gamestore_released_date',
      'type' => 'date',
    ]);
type="date" в HTML всегда хранит value в формате ISO: YYYY-MM-DD.

Потом ты сохраняешь:
update_post_meta($post_id, '_gamestore_released_date', sanitize_text_field($_POST['_gamestore_released_date']));
Значит в postmeta.meta_value лежит строка 2026-01-31.

Что делает твой PHP-фильтр

if ($released) {
  $args['meta_query'][] = [
    'key' => '_gamestore_released_date',
    'value' => ["{$released}-01-01", "{$released}-12-31"],
    'compare' => 'BETWEEN',
    'type' => 'DATE',
  ];
}

Это фильтр по году, а не по конкретной дате.
    Если $released = "2026", то получится:
        lower bound: 2026-01-01
        upper bound: 2026-12-31
    И WP выберет все товары, у которых meta _gamestore_released_date попадает в этот диапазон.
    Например сохранённое 2026-01-31 попадёт между 2026-01-01 и 2026-12-31.

'type' => 'DATE' нужно как раз, чтобы сравнение шло как дата, а не как простая строка.

 * */


/*
    _price - WooCommerce хранит цену товара в таблице wp_postmeta (или префикс_ postmeta) под meta_key = '_price'. Это поле содержит текущую (активную) цену товара.
 * */


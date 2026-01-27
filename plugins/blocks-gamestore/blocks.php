<?php

function view_block_games_line($attributes)
{
	$count = isset($attributes['count']) ? absint($attributes['count']) : 10;

	$args = array(
		'post_type' => 'product',
		'posts_per_page' => $count,
		'orderby' => 'date',
		'order' => 'DESC',
	);

	$games_query = new WP_Query($args);

	ob_start();

	echo '<div ' . get_block_wrapper_attributes() . '>'; // // сюда попадут атрибуты из useBlockProps

	if ($games_query->have_posts()) {
		echo '<div class="games-line-container"><div class="swiper-wrapper">';

		while ($games_query->have_posts()) {
			$games_query->the_post();

			$product = wc_get_product(get_the_ID());
			if (!$product) {
				continue;
			}

			echo '<div class="swiper-slide game-item">';
			echo '<a href="' . esc_url(get_permalink()) . '">';
			echo $product->get_image('full');
			echo '</a>';
			echo '</div>';
		}

		echo '</div></div>';
	}

	echo '</div>';

	wp_reset_postdata();

	return ob_get_clean();
}

function view_block_recent_news($attributes)
{
	$count = isset($attributes['count']) ? absint($attributes['count']) : 3;

	$args = array(
		'post_type' => 'news',
		'posts_per_page' => $count,
		'orderby' => 'date',
		'order' => 'DESC',
	);

	$news_query = new WP_Query($args);

	$image_bg = !empty($attributes['image'])
		? 'style="background-image: url(' . esc_url($attributes['image']) . ')"'
		: '';

	ob_start();

	echo '<div ' . get_block_wrapper_attributes() . (!empty($image_bg) ? ' ' . $image_bg : '') . '>';

	if ($news_query->have_posts()) {

		if (!empty($attributes['title'])) {
			echo '<h2>' . esc_html($attributes['title']) . '</h2>';
		}

		if (!empty($attributes['description'])) {
			echo '<p>' . esc_html($attributes['description']) . '</p>';
		}

		echo '<div class="recent-news wrapper">';

		while ($news_query->have_posts()) {
			$news_query->the_post();

			$title = get_the_title();
			$link = get_permalink();

			echo '<div class="news-item">';

			if (has_post_thumbnail()) {
				$thumb = get_the_post_thumbnail_url(get_the_ID(), 'full');

				echo '<h3>' . esc_html($title) . '</h3>';
				echo '<div class="news-thumbnail">';
				echo '<img src="' . esc_url($thumb) . '" class="blur-image" alt="' . esc_attr($title) . '">';
				echo '<img src="' . esc_url($thumb) . '" class="original-image" alt="' . esc_attr($title) . '">';
				echo '</div>';
			}

			echo '<div class="news-excerpt">' . esc_html(get_the_excerpt()) . '</div>';
			echo '<a href="' . esc_url($link) . '" class="read-more">Open the post</a>';

			echo '</div>';
		}

		echo '</div>';

	} else {
		echo '<p>No recent news found.</p>';
	}

	echo '</div>';

	wp_reset_postdata();

	return ob_get_clean();
}

function view_block_subscribe($attributes)
{
	$image_bg = !empty($attributes['image'])
		? 'style="background-image: url(' . esc_url($attributes['image']) . ')"'
		: '';
	// передаваймый нами класс из 	<div {...useBlockProps({ className: 'alignfull',   (edit.js) -> будет подхватываться функцией get_block_wrapper_attributes
	ob_start();
	echo '<div ' . get_block_wrapper_attributes(array('class' => 'alignfull')) . (!empty($image_bg) ? ' ' . $image_bg : '') . '>';
	echo '<div class="subscribe-inner wrapper">';
	echo '<h2 class="subscribe-title"> ' . $attributes['title'] . ' </h2>';
	echo '<p class="subscribe-description"> ' . $attributes['description'] . ' </p>';
	echo '<div class="subscribe-shortcode"> ' . do_shortcode($attributes['shortcode']) . ' </div>';
	echo '</div>';
	echo '</div>';
	// Return the buffered content
	return ob_get_clean();
}

function view_block_featured_products($attributes)
{
	$count = isset($attributes['count']) ? absint($attributes['count']) : 6;

	$featured_games = wc_get_products(array(
		'status' => 'publish',
		'limit' => $count,
		'featured' => true,
	));

	ob_start();

	echo '<div ' . get_block_wrapper_attributes(array('class' => 'alignfull')) . '>';
	echo '<div class="wrapper">';

	if (!empty($attributes['title'])) {
		echo '<h2>' . esc_html($attributes['title']) . '</h2>';
	}

	if (!empty($attributes['description'])) {
		echo '<p>' . esc_html($attributes['description']) . '</p>';
	}

	$platforms = get_gamestore_platforms();

	if (!empty($featured_games)) {

		echo '<div class="games-list">';

		foreach ($featured_games as $game) {
			if (!$game instanceof WC_Product) {
				continue;
			}

			$platforms_html = '';

			echo '<div class="game-result">';
			echo '<a href="' . esc_url($game->get_permalink()) . '">';
			echo '<div class="game-featured-image">' . $game->get_image('full') . '</div>';
			echo '<div class="game-meta">';
			echo '<div class="game-price">' . $game->get_price_html() . '</div>';
			echo '<h3>' . esc_html($game->get_name()) . '</h3>';
			echo '<div class="game-platforms">';

			if (!empty($platforms) && is_array($platforms)) {
				foreach ($platforms as $slug => $label) {
					$has_platform = get_post_meta($game->get_id(), '_platform_' . strtolower($slug), true);
					if ($has_platform === 'yes') {
						$platforms_html .= '<div class="platform_' . esc_attr(strtolower($slug)) . '"></div>';
					}
				}
			}

			echo $platforms_html;

			echo '</div>'; // .game-platforms
			echo '</div>'; // .game-meta
			echo '</a>';
			echo '</div>'; // .game-result
		}

		echo '</div>';

	} else {
		echo '<p>No games found.</p>';
	}

	echo '</div>'; // .wrapper
	echo '</div>'; // block wrapper

	return ob_get_clean();
}

function view_block_single_news()
{
	ob_start();
	$placeholder_url = trailingslashit(BLOCKS_GAMESTORE_URL) . 'assets/img/placeholder.png';

	$bg_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
	if (!$bg_url) {
		$bg_url = $placeholder_url;
	}

	$bg_img = 'style="background-image: url(\'' . esc_url($bg_url) . '\');"';

	echo '<article ' . get_block_wrapper_attributes(array('class' => implode(' ', get_post_class('alignfull')))) . '>';
	echo '<div class="featured-image-section" ' . $bg_img . '>';
	echo '<div class="wrapper">';
	echo '<h1>' . esc_html(get_the_title()) . '</h1>';
	echo '<div class="news-meta">';
	echo '<div class="news-date"><svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M8 2.5V5.5" stroke="var(--text-secondary)" stroke-opacity="0.7" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M16 2.5V5.5" stroke="var(--text-secondary)" stroke-opacity="0.7" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M3.5 9.59009H20.5" stroke="var(--text-secondary)" stroke-opacity="0.7" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M21 9V17.5C21 20.5 19.5 22.5 16 22.5H8C4.5 22.5 3 20.5 3 17.5V9C3 6 4.5 4 8 4H16C19.5 4 21 6 21 9Z" stroke="var(--text-secondary)" stroke-opacity="0.7" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M15.6947 14.2H15.7037" stroke="var(--text-secondary)" stroke-opacity="0.7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M15.6947 17.2H15.7037" stroke="var(--text-secondary)" stroke-opacity="0.7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M11.9955 14.2H12.0045" stroke="var(--text-secondary)" stroke-opacity="0.7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M11.9955 17.2H12.0045" stroke="var(--text-secondary)" stroke-opacity="0.7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M8.29431 14.2H8.30329" stroke="var(--text-secondary)" stroke-opacity="0.7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M8.29431 17.2H8.30329" stroke="var(--text-secondary)" stroke-opacity="0.7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>' . esc_html(get_the_date()) . '</div>';
	echo '<div class="news-author"><svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M18.427 22.12C17.547 22.38 16.507 22.5 15.287 22.5H9.28697C8.06697 22.5 7.02697 22.38 6.14697 22.12C6.36697 19.52 9.03697 17.47 12.287 17.47C15.537 17.47 18.207 19.52 18.427 22.12Z" stroke="var(--text-secondary)" stroke-opacity="0.7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M15.2871 2.5H9.28711C4.28711 2.5 2.28711 4.5 2.28711 9.5V15.5C2.28711 19.28 3.42711 21.35 6.14711 22.12C6.36711 19.52 9.03711 17.47 12.2871 17.47C15.5371 17.47 18.2071 19.52 18.4271 22.12C21.1471 21.35 22.2871 19.28 22.2871 15.5V9.5C22.2871 4.5 20.2871 2.5 15.2871 2.5ZM12.2871 14.67C10.3071 14.67 8.70711 13.06 8.70711 11.08C8.70711 9.10002 10.3071 7.5 12.2871 7.5C14.2671 7.5 15.8671 9.10002 15.8671 11.08C15.8671 13.06 14.2671 14.67 12.2871 14.67Z" stroke="var(--text-secondary)" stroke-opacity="0.7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M15.867 11.08C15.867 13.06 14.267 14.67 12.287 14.67C10.307 14.67 8.70703 13.06 8.70703 11.08C8.70703 9.10002 10.307 7.5 12.287 7.5C14.267 7.5 15.867 9.10002 15.867 11.08Z" stroke="var(--text-secondary)" stroke-opacity="0.7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>' . esc_html(get_the_author()) . '</div>';
	echo '</div>';
	echo '</div>';
	echo '</div>';

	echo '<div class="wrapper news-container">';
	echo '<div class="news-social-share">Share' . gamestore_social_share(get_the_permalink(), get_the_title()) . '</div>';
	echo '<div class="news-content">' . apply_filters('the_content', get_the_content()) . '</div>';
	echo '</div>';

	echo '</article>';

	// Return the buffered content
	return ob_get_clean();
}

function view_block_news_header($attributes)
{
	$image_bg = !empty($attributes['image'])
		? 'style="background-image: url(' . esc_url($attributes['image']) . ');"'
		: '';

	ob_start();

	echo '<div ' . get_block_wrapper_attributes() . (!empty($image_bg) ? ' ' . $image_bg : '') . '>';
	echo '<div class="wrapper">';

	if (!empty($attributes['title'])) {
		echo '<h1 class="news-header-title">' . esc_html($attributes['title']) . '</h1>';
	}

	if (!empty($attributes['description'])) {
		echo '<p class="news-header-description">' . esc_html($attributes['description']) . '</p>';
	}

	$terms_news = get_terms(array(
		'taxonomy' => 'news_category',
		'hide_empty' => false,
	));

	if (!empty($terms_news) && !is_wp_error($terms_news)) {
		echo '<div class="news-categories">';

		foreach ($terms_news as $term) {
			$icon_meta = get_term_meta($term->term_id, 'news_category_icon', true);

			$icon_html = $icon_meta
				? '<img src="' . esc_url($icon_meta) . '" alt="' . esc_attr($term->name) . '" />'
				: '';

			echo '<div class="news-cat-item"><a href="' . esc_url(get_term_link($term)) . '">'
				. esc_html($term->name)
				. $icon_html
				. '</a></div>';
		}

		echo '</div>';
	}

	echo '</div>';
	echo '</div>';

	return ob_get_clean();
}

function view_block_news_box()
{
	ob_start();

	echo '<div ' . get_block_wrapper_attributes() . '>'; // сюда попадут атрибуты из useBlockProps

	$title = get_the_title();

	if (has_post_thumbnail()) {
		$thumb_url = get_the_post_thumbnail_url(get_the_ID(), 'full');

		echo '<h3>' . esc_html($title) . '</h3>';
		echo '<div class="news-thumbnail">';
		echo '<img src="' . esc_url($thumb_url) . '" class="blur-image" alt="' . esc_attr($title) . '">';
		echo '<img src="' . esc_url($thumb_url) . '" class="original-image" alt="' . esc_attr($title) . '">';
		echo '</div>';
	}

	echo '<div class="news-excerpt">' . esc_html(get_the_excerpt()) . '</div>';
	echo '<a href="' . esc_url(get_the_permalink()) . '" class="read-more">Open the post</a>';
	echo '</div>';

	return ob_get_clean();
}

function view_block_single_game()
{
	$game = wc_get_product(get_the_ID());
	if (!$game) {
		return '';
	}

	$img = get_post_meta($game->get_id(), '_gamestore_image', true);
	$game_badge = $img ? sprintf('<img src="%s" alt="" />', esc_url($img)) : '';

	$publisher_meta = get_post_meta($game->get_id(), '_gamestore_publisher', true);
	$publisher = $publisher_meta
		? '<div class="game-publisher"><div class="label-text">Publisher</div> <div class="item-text">' . esc_html($publisher_meta) . '</div></div>'
		: '';

	$single_player_meta = get_post_meta($game->get_id(), '_gamestore_single_player', true);
	$single_player = $single_player_meta
		? '<div class="game-single-player"><div class="label-text">Single Player</div> <div class="item-text">' . esc_html($single_player_meta) . '</div></div>'
		: '';

	$release_date_meta = get_post_meta($game->get_id(), '_gamestore_released_date', true);
	$release_date = $release_date_meta
		? '<div class="game-release-date"><div class="label-text">Released</div> <div class="item-text">' . esc_html(date('j F Y', strtotime($release_date_meta))) . '</div></div>'
		: '';

	$game_full_description_meta = get_post_meta($game->get_id(), '_gamestore_full_description', true);
	$game_full_description = $game_full_description_meta
		? '<div class="game-release-date"><h4>Game Description:</h4> ' . wp_kses_post($game_full_description_meta) . '</div>'
		: '';

	$platforms_terms = wp_get_post_terms($game->get_id(), 'platforms');
	$platforms_html = '';
	if (!empty($platforms_terms) && !is_wp_error($platforms_terms)) {
		$platforms_html .= '<div class="game-platforms-text"><div class="label-text">Platforms</div>';
		foreach ($platforms_terms as $platform) {
			$platforms_html .= '<div class="item-text"><a href="' . esc_url(get_term_link($platform)) . '">' . esc_html($platform->name) . '</a></div>';
		}
		$platforms_html .= '</div>';
	}

	$genres_terms = wp_get_post_terms($game->get_id(), 'genres');
	$genres_html = '';
	if (!empty($genres_terms) && !is_wp_error($genres_terms)) {
		$genres_html .= '<div class="game-genres"><div class="label-text">Genres</div>';
		foreach ($genres_terms as $genre) {
			$genres_html .= '<div class="item-text"><a href="' . esc_url(get_term_link($genre)) . '">' . esc_html($genre->name) . '</a></div>';
		}
		$genres_html .= '</div>';
	}

	$game_screens_images = $game->get_gallery_image_ids();
	$game_screens_html = '';
	if (!empty($game_screens_images)) {
		$game_screens_html .= '<div class="game-screens"><h4>Videos & Game Play:</h4><div class="game-single-slider"><div class="swiper-wrapper">';
		foreach ($game_screens_images as $image_id) {
			$game_screens_html .= '<div class="game-screen swiper-slide">' . wp_get_attachment_image($image_id, 'full') . '</div>';
		}
		$game_screens_html .= '</div><div class="swiper-game-next"></div><div class="swiper-game-prev"></div></div></div>';
	}

	$languages = wp_get_post_terms($game->get_id(), 'languages');
	$languages_html = '';
	if (!empty($languages) && !is_wp_error($languages)) {
		foreach ($languages as $language) {
			$languages_html .= '<div class="language-item">' . esc_html($language->name) . '</div>';
		}
	}

	$short_desc = apply_filters('woocommerce_short_description', $game->get_short_description());
	$short_desc = wp_kses_post($short_desc);

	ob_start();

	echo '<div ' . get_block_wrapper_attributes() . '>';
	echo '<div class="wrapper">';

	echo '<aside class="game-image">';
	echo '<div class="game-image-container">' . $game->get_image('large') . '</div>';

	echo '<div class="game-platforms">';
	$platforms = get_gamestore_platforms(); // slug => label
	foreach ($platforms as $slug => $label) {
		if (get_post_meta($game->get_id(), '_platform_' . strtolower($slug), true) === 'yes') {
			echo '<div class="platform_' . esc_attr(strtolower($slug)) . '"></div>';
		}
	}
	echo '</div>';

	echo '</aside>';

	echo '<div class="game-content">';
	echo '<div class="game-description-top"><h1>' . esc_html($game->get_name()) . '</h1> ' . $game_badge . ' </div>';
	echo '<div class="game-languages">' . $languages_html . '</div>';
	echo '<div class="game-description">' . $short_desc . '</div>';

	echo '<div class="game-meta-data">';
	echo $platforms_html;
	echo $genres_html;
	echo $publisher;
	echo $single_player;
	echo $release_date;
	echo '</div>';

	echo '<div class="game-price-button">';
	echo '<div class="game-price">' . $game->get_price_html() . '</div>';
	echo '<div class="game-add-to-cart"><a class="hero-button shadow" href="' . esc_url($game->add_to_cart_url()) . '">' . esc_html($game->add_to_cart_text()) . '</a></div>';
	echo '</div>';

	echo $game_screens_html;
	echo $game_full_description;

	echo '</div>'; // .game-content
	echo '</div>'; // .wrapper
	echo '</div>'; // block wrapper

	return ob_get_clean();
}




// ob_start() и ob_get_clean() — это функции для управления буфером вывода в PHP.
/*
 * ob_start(); говорит PHP: «Не отправляй вывод (echo, print и т.д.) сразу в браузер. Вместо этого складывай всё в буфер (память).»
 * То есть все echo после ob_start() не выводятся на экран сразу, а накапливаются внутри буфера.
 * ob_get_clean(); делает две вещи одновременно:
	Возвращает содержимое буфера как строку
	Очищает и выключает буфер
	return ob_get_clean();
	Ты возвращаешь весь сгенерированный HTML одной строкой — это как раз нужно для render_callback у блоков в WordPress: функция должна вернуть строку, а не напрямую выводить её.
 * */

/*
	Зачем это нужно здесь?
	Потому что:
	Удобно писать разметку через echo, как обычно.
	В конце ты получаешь весь HTML одной строкой и можешь её return-нуть (как требует WordPress для динамического блока).
	Ничего лишнего не улетает в вывод до того, как WordPress будет к этому готов.
 * */


/*
 * get_block_wrapper_attributes() в WordPress

Функция возвращает строку HTML-атрибутов для обёртки блока, которую Gutenberg ожидает вокруг контента блока.
 Когда ты пишешь динамический блок (PHP render_callback) или шаблон блока, WordPress хочет, чтобы у контейнера были:
	стандартный класс блока (wp-block-...)
	пользовательские классы (из “Additional CSS class(es)”)
	выравнивание (alignwide, alignfull и т.п.)
	стили из theme.json / глобальных стилей (style="...")
	другие data-атрибуты для block supports

Можно передать свои атрибуты массивом:
	echo '<section ' . get_block_wrapper_attributes( [
		'class' => 'my-extra-class',
		'id'    => 'hero-block',
	] ) . '>';
WordPress склеит их с теми, что нужны блоку по умолчанию.

Итого: функция ничего не выводит, а возвращает готовую строку атрибутов, чтобы ты вставил её после тега <div / <section и получил правильную обёртку блока.
 * */

/*
 wc_get_products() — это WooCommerce-функция, которая возвращает массив товаров (объектов WC_Product) по заданным параметрам (фильтрам).
 В твоём коде она выбирает опубликованные featured-товары и ограничивает их числом из $attributes['count'].
	status => 'publish': только опубликованные товары
	limit => N: максимум N товаров
	featured => true: только товары, отмеченные “Featured” в WooCommerce
 * */


/*
'<article '. get_block_wrapper_attributes( array('class' => implode(' ',get_post_class('alignfull')))) .'>';
1) get_post_class('alignfull')
Это WordPress-функция, которая возвращает массив классов для текущего поста (как post_class(), но return, а не echo), плюс добавляет твой класс 'alignfull'.

Примерно вернёт что-то вроде массива:

	[
	  'post-123',
	  'post',
	  'type-post',
	  'status-publish',
	  'format-standard',
	  'alignfull'
	]

2) implode(' ', get_post_class('alignfull'))
Склеивает этот массив в одну строку через пробел:
"post-123 post type-post status-publish format-standard alignfull"

3) get_block_wrapper_attributes([...])
Это функция для Gutenberg блоков. Она берёт твой массив атрибутов (например class) и возвращает готовую строку атрибутов для HTML-тега.
Обычно она добавляет ещё свои вещи, например:
	class="wp-block-..." (если контекст блока есть)
	иногда style="", id="", data-* и т.п.
То есть результат может стать примерно таким:
	class="alignfull post-363 news type-news status-publish has-post-thumbnail hentry wp-block-blocks-gamestore-single-news"
 * */


/*
 trailingslashit() — это WordPress-функция, которая гарантирует слэш / в конце строки.
	Если слэша нет → добавит.
	Если уже есть → оставит как есть.
	Нужна, чтобы нормально склеивать пути/URL и не получить ошибки типа ...pluginsblocks-gamestorebuild/... или двойные слэши.



is_wp_error() — это функция WordPress, которая проверяет: является ли переменная объектом ошибки WP_Error.
	Зачем это тут:
		wp_get_post_terms() обычно возвращает массив терминов (языков).
		Но если что-то пошло не так (неверная таксономия, проблема с БД и т.п.), она может вернуть WP_Error.
	Если не проверить и попытаться сделать foreach по WP_Error, получишь предупреждения/ошибки и кривой вывод.
 * */

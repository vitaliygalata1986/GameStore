<?php

function view_block_games_line($attributes)
{
	// print_r($attributes); // Array ( [count] => 10 )
	$args = array(
		'post_type' => 'product',
		'posts_per_page' => $attributes['count'],
		'orderby' => 'date',
		'order' => 'DESC',
	);
	$games_query = new WP_Query($args);

	// Start output buffering
	ob_start();
	echo '<div ' . get_block_wrapper_attributes() . '>'; // главный контейнер
	if ($games_query->have_posts()) {
		echo '<div class="games-line-container"><div class="swiper-wrapper">';
		while ($games_query->have_posts()) {
			$games_query->the_post();
			$product = wc_get_product(get_the_ID());
			echo '<div class="swiper-slide game-item">';
			echo '<a href="' . get_the_permalink() . '">';
			echo $product->get_image('full');
			echo '</a>';
			echo '</div>';
		}
		echo '</div></div>';
	}
	echo '</div>';


	wp_reset_postdata();

	// Return the buffered content
	return ob_get_clean();
}

function view_block_recent_news($attributes) {
	// print_r($attributes);
	$args = array(
		'post_type' => 'news',
		'posts_per_page' => $attributes['count'],
		'orderby' => 'date',
		'order' => 'DESC',
	);
	$news_query = new WP_Query($args);

	$image_bg = ($attributes['image']) ? 'style="background-image: url(' . $attributes['image'] . ')"' : '';

	ob_start();
	echo '<div '. get_block_wrapper_attributes() . $image_bg .'>';
	if ($news_query->have_posts()) {
		if($attributes['title']){
			echo '<h2>' . $attributes['title'] . '</h2>';
		}
		if($attributes['description']){
			echo '<p>' . $attributes['description'] . '</p>';
		}
		echo '<div class="recent-news wrapper">';
		while ($news_query->have_posts()) {
			$news_query->the_post();
			echo '<div class="news-item">';
			if (has_post_thumbnail()) {
				echo '<h3>' . get_the_title() . '</h3>';
				echo '<div class="news-thumbnail">';
				echo '<img src="' . get_the_post_thumbnail_url() . '" class="blur-image" alt="' . get_the_title() . '">';
				echo '<img src="' . get_the_post_thumbnail_url() . '" class="original-image" alt="' . get_the_title() . '">';
				echo '</div>';
			}
			echo '<div class="news-excerpt">'.get_the_excerpt().'</div>';
			echo '<a href="' . get_the_permalink() . '" class="read-more">Open the post</a>';
			echo '</div>';
		}
		echo '</div>';
	} else {
		echo '<p>No recent news found.</p>';
	}
	echo '</div>';

	wp_reset_postdata();

	// Return the buffered content
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

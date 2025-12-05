<?php
/**
 * Plugin Name:       Blocks Gamestore
 * Description:       Example block scaffolded with Create Block tool.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       blocks-gamestore
 *
 * @package CreateBlock
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

define('BLOCKS_GAMESTORE_PATH', plugin_dir_path(__FILE__));
require_once( BLOCKS_GAMESTORE_PATH . 'blocks.php' );

// зарегестририруем новую категорию gamestore для нашего плагина block-header
add_filter('block_categories_all', function ($categories) {
	return array_merge($categories, [
		[
			'slug' => 'gamestore',
			'title' => 'GameStore'
		]
	]);
});
function create_block_blocks_gamestore_block_init() {

	// Обычные статические блоки
	register_block_type( __DIR__ . '/build/block-header' );
	register_block_type( __DIR__ . '/build/block-hero' );
	register_block_type( __DIR__ . '/build/block-contact' );

	// динамический блок: Games Line
	register_block_type(
		__DIR__ . '/build/block-games-line',
		[
			'render_callback' => 'view_block_games_line',
		]
	);

	register_block_type(
		__DIR__ . '/build/block-recent-news',
		[
			'render_callback' => 'view_block_recent_news',
		]
	);
}
add_action( 'init', 'create_block_blocks_gamestore_block_init' );


/*
	require_once подключает один раз указанный PHP-файл в текущий скрипт.
	Если тот же файл попытаться подключить повторно — он не будет загружен второй раз, чтобы избежать ошибок вроде «Cannot redeclare function…».
 * */

/*
 * require_once(plugin_dir_path( __FILE__ ) . 'blocks.php');
 	Что делает plugin_dir_path(__FILE__)?
		Это WordPress функция, которая:
			принимает текущий файл (__FILE__)
			возвращает полный путь к папке плагина
	Пример:
		Если текущий файл расположен по пути:
		/var/www/html/wp-content/plugins/blocks-gamestore/blocks.php
	то plugin_dir_path(__FILE__) вернёт:
	/var/www/html/wp-content/plugins/blocks-gamestore/
	Далее склеиваются путь к папке плагина + имя файла:
	/var/www/html/wp-content/plugins/blocks-gamestore/blocks.php
 * */

<?php
/**
 * Plugin Name: Core Gamestore
 * Description: Core Code for Gamestore
 * Version: 1.0
 * Author:  Vitaliy Galata
 * Author URI: https://github.com/vitaliygalata1986
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: core-gamestore
 * Domain Path: /languages
 */

 define('GAMESTORE_PLUGIN_URL', plugin_dir_url(__FILE__));   // http://localhost:8200/wp-content/plugins/core-gamestore/
 define('GAMESTORE_PLUGIN_PATH', plugin_dir_path(__FILE__)); // /var/www/html/wp-content/plugins/core-gamestore/

require_once(GAMESTORE_PLUGIN_PATH . 'inc/core-game.php');
<?php
/*
Plugin Name: DeepSeek Search Integration
Plugin URI: https://github.com/vitaliygalata1986/sport-sland/
Description: Shortcode + AJAX DeepSeek chat/completions for WordPress.
Version: 1.0
Author: Vitaliy Galata
Author URI: https://github.com/vitaliygalata1986
Text Domain: deepseek
Domain Path: /languages
*/

use Vitos\DeepSeek\Includes\DeepSeek_Search_Integration;
use Vitos\DeepSeek\Admin\DeepSeek_Admin;
use Vitos\DeepSeek\Admin\DeepSeek_Woo_Generator;

defined('ABSPATH') or die;

define('DEEPSEEK_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DEEPSEEK_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DEEPSEEK_PLUGIN_NAME', dirname(plugin_basename(__FILE__)));

require_once DEEPSEEK_PLUGIN_DIR . 'admin/class-deepseek-admin.php';
require_once DEEPSEEK_PLUGIN_DIR . 'admin/class-deepseek-woo-generator.php';
require_once DEEPSEEK_PLUGIN_DIR . 'includes/class-deepseek-search-integration.php';


function run_deepseek_search_integrations()
{
    new DeepSeek_Search_Integration();

    if (is_admin()) {
        new DeepSeek_Admin();
        new DeepSeek_Woo_Generator();
    }
}

run_deepseek_search_integrations();


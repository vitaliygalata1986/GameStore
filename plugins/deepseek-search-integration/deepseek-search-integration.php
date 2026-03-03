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
use Vitos\DeepSeek\Admin\DeepSeek_Woo_Bulk;

// use Vitos\DeepSeek\Admin\DeepSeek_Woo_Bulk_Generator;
use Vitos\DeepSeek\Admin\DeepSeek_Woo_Generator;

defined('ABSPATH') or die;
define('DEEPSEEK_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DEEPSEEK_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DEEPSEEK_PLUGIN_NAME', dirname(plugin_basename(__FILE__)));

require_once DEEPSEEK_PLUGIN_DIR . 'includes/autoload.php';

register_activation_hook(__FILE__, [DeepSeek_Admin::class, 'activate']);
register_deactivation_hook(__FILE__, [DeepSeek_Admin::class, 'deactivate']);

function run_deepseek_search_integrations()
{
    new DeepSeek_Search_Integration();

    if (is_admin()) {
        new DeepSeek_Admin();
        new DeepSeek_Woo_Generator();
        new DeepSeek_Woo_Bulk();
        // new DeepSeek_Woo_Bulk_Generator();
    }
}

add_action('plugins_loaded', 'run_deepseek_search_integrations');

/*
    register_activation_hook(__FILE__, [DeepSeek_Admin::class, 'activate']);
        Срабатывает один раз, когда ты в админке нажимаешь Activate у этого плагина.
        WordPress вызовет статический метод: DeepSeek_Admin::activate().
        Создаётся/обновляется роль DeepSeek Manager и добавляется capability manage_deepseek_settings (и админу тоже).

   register_deactivation_hook(__FILE__, [DeepSeek_Admin::class, 'deactivate'])
        Срабатывает один раз, когда ты нажимаешь Deactivate у плагина.
        WordPress вызовет: DeepSeek_Admin::deactivate().
        У тебя там сейчас пусто (то есть ничего не делается), но туда можно добавить очистку/откат (обычно осторожно).
    Коротко: Activate → подготовить роли/права, Deactivate → при необходимости откатить/почистить.

 * */


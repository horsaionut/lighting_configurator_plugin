<?php
/**
 * Plugin Name: Lighting Configurator
 * Description: Multi-step lighting configurator with shortcode for WooCommerce products.
 * Version: 0.1.0
 * Author: Your Name
 * Text Domain: lighting-configurator
 */

if (!defined('ABSPATH')) {
    exit;
}

define('LIGHTING_CONFIGURATOR_VERSION', '0.1.0');
define('LIGHTING_CONFIGURATOR_OPTION', 'lighting_configurator_settings');
define('LIGHTING_CONFIGURATOR_PATH', plugin_dir_path(__FILE__));
define('LIGHTING_CONFIGURATOR_URL', plugin_dir_url(__FILE__));

require_once LIGHTING_CONFIGURATOR_PATH . 'includes/class-lighting-configurator-activator.php';
require_once LIGHTING_CONFIGURATOR_PATH . 'includes/class-lighting-configurator-deactivator.php';
require_once LIGHTING_CONFIGURATOR_PATH . 'includes/class-lighting-configurator-taxonomies.php';
require_once LIGHTING_CONFIGURATOR_PATH . 'includes/class-lighting-configurator-meta.php';
require_once LIGHTING_CONFIGURATOR_PATH . 'includes/class-lighting-configurator.php';

register_activation_hook(__FILE__, array('Lighting_Configurator_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('Lighting_Configurator_Deactivator', 'deactivate'));

function run_lighting_configurator()
{
    $plugin = new Lighting_Configurator();
    $plugin->run();
}

run_lighting_configurator();

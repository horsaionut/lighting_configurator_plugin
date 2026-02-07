<?php

if (!defined('ABSPATH')) {
    exit;
}

class Lighting_Configurator
{
    public function run()
    {
        add_action('init', array($this, 'register_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_shortcode()
    {
        add_shortcode('lighting_configurator', array($this, 'render_shortcode'));
    }

    public function render_shortcode($atts = array())
    {
        $settings = get_option(LIGHTING_CONFIGURATOR_OPTION, array());
        ob_start();
        include LIGHTING_CONFIGURATOR_PATH . 'public/partials/shortcode-view.php';
        return ob_get_clean();
    }

    public function enqueue_public_assets()
    {
        if (!$this->is_shortcode_present()) {
            return;
        }

        wp_enqueue_style(
            'lighting-configurator',
            LIGHTING_CONFIGURATOR_URL . 'assets/css/lighting-configurator.css',
            array(),
            LIGHTING_CONFIGURATOR_VERSION
        );

        wp_enqueue_script(
            'lighting-configurator',
            LIGHTING_CONFIGURATOR_URL . 'assets/js/lighting-configurator.js',
            array('jquery'),
            LIGHTING_CONFIGURATOR_VERSION,
            true
        );

        wp_localize_script('lighting-configurator', 'LightingConfiguratorData', array(
            'nonce' => wp_create_nonce('lighting_configurator'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'settings' => $this->get_public_settings(),
        ));
    }

    private function is_shortcode_present()
    {
        if (!is_singular()) {
            return false;
        }

        global $post;
        if (!$post) {
            return false;
        }

        return has_shortcode($post->post_content, 'lighting_configurator');
    }

    public function register_admin_menu()
    {
        add_options_page(
            __('Lighting Configurator', 'lighting-configurator'),
            __('Lighting Configurator', 'lighting-configurator'),
            'manage_options',
            'lighting-configurator',
            array($this, 'render_settings_page')
        );
    }

    public function render_settings_page()
    {
        include LIGHTING_CONFIGURATOR_PATH . 'admin/partials/settings-page.php';
    }

    public function register_settings()
    {
        register_setting(
            'lighting_configurator',
            LIGHTING_CONFIGURATOR_OPTION,
            array('sanitize_callback' => array($this, 'sanitize_settings'))
        );

        add_settings_section(
            'lighting_configurator_general',
            __('General Settings', 'lighting-configurator'),
            array($this, 'render_section_description'),
            'lighting-configurator'
        );

        add_settings_field(
            'results_limit',
            __('Results limit', 'lighting-configurator'),
            array($this, 'render_results_limit_field'),
            'lighting-configurator',
            'lighting_configurator_general'
        );
    }

    public function render_section_description()
    {
        echo '<p>' . esc_html__('Configure the default behavior for the configurator.', 'lighting-configurator') . '</p>';
    }

    public function render_results_limit_field()
    {
        $options = get_option(LIGHTING_CONFIGURATOR_OPTION, array());
        $value = isset($options['results_limit']) ? (int) $options['results_limit'] : 3;
        echo '<input type="number" name="' . esc_attr(LIGHTING_CONFIGURATOR_OPTION) . '[results_limit]" value="' . esc_attr($value) . '" min="1" max="12" />';
        echo '<p class="description">' . esc_html__('Maximum number of bundles to show in Step 5.', 'lighting-configurator') . '</p>';
    }

    public function sanitize_settings($input)
    {
        $output = array();
        $output['results_limit'] = isset($input['results_limit']) ? max(1, absint($input['results_limit'])) : 3;
        return $output;
    }

    private function get_public_settings()
    {
        $options = get_option(LIGHTING_CONFIGURATOR_OPTION, array());
        return array(
            'resultsLimit' => isset($options['results_limit']) ? (int) $options['results_limit'] : 3,
        );
    }
}

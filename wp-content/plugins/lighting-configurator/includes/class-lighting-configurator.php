<?php

if (!defined('ABSPATH')) {
    exit;
}

class Lighting_Configurator
{
    const CATEGORY_META_KEY = '_lc_show_in_configurator';
    const CATEGORY_COMPLEMENTARY_KEY = '_lc_is_complementary';
    const TERM_ICON_META_KEY = 'thumbnail_id';
    const PRODUCT_KELVIN_META_KEY = '_lc_kelvins';

    public function run()
    {
        add_action('init', array('Lighting_Configurator_Taxonomies', 'register'));
        add_action('init', array('Lighting_Configurator_Meta', 'register'));
        add_action('init', array($this, 'register_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('product_cat_add_form_fields', array($this, 'render_category_meta_add'));
        add_action('product_cat_edit_form_fields', array($this, 'render_category_meta_edit'), 10, 2);
        add_action('created_product_cat', array($this, 'save_category_meta'));
        add_action('edited_product_cat', array($this, 'save_category_meta'));
        add_action('woocommerce_product_options_general_product_data', array($this, 'render_product_kelvin_field'));
        add_action('woocommerce_product_options_general_product_data', array($this, 'render_product_specs_fields'));
        add_action('woocommerce_admin_process_product_object', array($this, 'save_product_kelvin_field'));
        add_action('woocommerce_admin_process_product_object', array($this, 'save_product_specs_fields'));
        add_action('wp_ajax_lc_get_recommendations', array($this, 'ajax_get_recommendations'));
        add_action('wp_ajax_nopriv_lc_get_recommendations', array($this, 'ajax_get_recommendations'));
        add_action('wp_ajax_lc_get_cart_map', array($this, 'ajax_get_cart_map'));
        add_action('wp_ajax_nopriv_lc_get_cart_map', array($this, 'ajax_get_cart_map'));
        add_action('wp_ajax_lc_get_product_modal', array($this, 'ajax_get_product_modal'));
        add_action('wp_ajax_nopriv_lc_get_product_modal', array($this, 'ajax_get_product_modal'));
        add_action('wp_ajax_lc_get_cart_sidebar', array($this, 'ajax_get_cart_sidebar'));
        add_action('wp_ajax_nopriv_lc_get_cart_sidebar', array($this, 'ajax_get_cart_sidebar'));
        add_action('wp_ajax_lc_update_cart_sidebar', array($this, 'ajax_update_cart_sidebar'));
        add_action('wp_ajax_nopriv_lc_update_cart_sidebar', array($this, 'ajax_update_cart_sidebar'));
        add_action('wp_ajax_lc_bulk_add_to_cart', array($this, 'ajax_bulk_add_to_cart'));
        add_action('wp_ajax_nopriv_lc_bulk_add_to_cart', array($this, 'ajax_bulk_add_to_cart'));

        $this->register_taxonomy_media_fields(Lighting_Configurator_Taxonomies::TAX_ROOM);
        $this->register_taxonomy_media_fields(Lighting_Configurator_Taxonomies::TAX_STYLE);
        $this->register_taxonomy_media_fields(Lighting_Configurator_Taxonomies::TAX_MATERIAL);
        $this->register_taxonomy_media_fields(Lighting_Configurator_Taxonomies::TAX_SOURCE_TYPE);
    }

    public function register_shortcode()
    {
        add_shortcode('lighting_configurator', array($this, 'render_shortcode'));
    }

    public function render_shortcode($atts = array())
    {
        $settings = get_option(LIGHTING_CONFIGURATOR_OPTION, array());
        $rooms = $this->get_taxonomy_terms(Lighting_Configurator_Taxonomies::TAX_ROOM);
        $styles = $this->get_taxonomy_terms(Lighting_Configurator_Taxonomies::TAX_STYLE);
        $materials = $this->get_taxonomy_terms(Lighting_Configurator_Taxonomies::TAX_MATERIAL);
        $source_types = $this->get_taxonomy_terms(Lighting_Configurator_Taxonomies::TAX_SOURCE_TYPE);
        $fixture_categories = $this->get_product_categories();
        $recommended_products = array();
        $cart_items = array();
        if (function_exists('WC') && WC()->cart) {
            foreach (WC()->cart->get_cart() as $item) {
                $cart_items[] = (int) $item['product_id'];
            }
        }
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
        add_menu_page(
            __('IluminatCasa Configurator', 'lighting-configurator'),
            __('IluminatCasa Configurator', 'lighting-configurator'),
            'manage_options',
            'lighting-configurator',
            array($this, 'render_settings_page'),
            'dashicons-lightbulb'
        );

        add_submenu_page(
            'lighting-configurator',
            __('Settings', 'lighting-configurator'),
            __('Settings', 'lighting-configurator'),
            'manage_options',
            'lighting-configurator',
            array($this, 'render_settings_page')
        );

        $this->register_taxonomy_submenu(Lighting_Configurator_Taxonomies::TAX_ROOM, __('Camere (Configurator)', 'lighting-configurator'));
        $this->register_taxonomy_submenu(Lighting_Configurator_Taxonomies::TAX_STYLE, __('Stil iluminat', 'lighting-configurator'));
        $this->register_taxonomy_submenu(Lighting_Configurator_Taxonomies::TAX_MATERIAL, __('Material corp iluminat', 'lighting-configurator'));
        $this->register_taxonomy_submenu(Lighting_Configurator_Taxonomies::TAX_SOURCE_TYPE, __('Tip sursa iluminat', 'lighting-configurator'));
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

        add_settings_field(
            'allow_broad_recs',
            __('Permite marjă largă de recomandări', 'lighting-configurator'),
            array($this, 'render_allow_broad_recs_field'),
            'lighting-configurator',
            'lighting_configurator_general'
        );

        add_settings_field(
            'visible_count',
            __('Număr produse afișate per pagină', 'lighting-configurator'),
            array($this, 'render_visible_count_field'),
            'lighting-configurator',
            'lighting_configurator_general'
        );

        add_settings_field(
            'enable_quick_cart',
            __('Enable quick cart', 'lighting-configurator'),
            array($this, 'render_enable_quick_cart_field'),
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
        $value = isset($options['results_limit']) ? (int) $options['results_limit'] : 50;
        echo '<input type="number" name="' . esc_attr(LIGHTING_CONFIGURATOR_OPTION) . '[results_limit]" value="' . esc_attr($value) . '" min="3" max="300" />';
        echo '<p class="description">' . esc_html__('Maximum number of products to consider for recommendations (min 3, max 300).', 'lighting-configurator') . '</p>';
    }

    public function render_allow_broad_recs_field()
    {
        $options = get_option(LIGHTING_CONFIGURATOR_OPTION, array());
        $value = !empty($options['allow_broad_recs']) ? '1' : '0';
        echo '<label><input type="checkbox" name="' . esc_attr(LIGHTING_CONFIGURATOR_OPTION) . '[allow_broad_recs]" value="1" ' . checked($value, '1', false) . ' /> ';
        echo esc_html__('Permite marjă largă de recomandări', 'lighting-configurator') . '</label>';
        echo '<p class="description">' . esc_html__('Extinde recomandările dacă nu există suficiente produse.', 'lighting-configurator') . '</p>';
    }

    public function render_visible_count_field()
    {
        $options = get_option(LIGHTING_CONFIGURATOR_OPTION, array());
        $value = isset($options['visible_count']) ? (int) $options['visible_count'] : 4;
        echo '<input type="number" name="' . esc_attr(LIGHTING_CONFIGURATOR_OPTION) . '[visible_count]" value="' . esc_attr($value) . '" min="3" max="6" />';
        echo '<p class="description">' . esc_html__('Numărul de produse afișate simultan în carusel (min 3).', 'lighting-configurator') . '</p>';
    }

    public function render_enable_quick_cart_field()
    {
        $options = get_option(LIGHTING_CONFIGURATOR_OPTION, array());
        $value = !empty($options['enable_quick_cart']) ? '1' : '0';
        echo '<label><input type="checkbox" name="' . esc_attr(LIGHTING_CONFIGURATOR_OPTION) . '[enable_quick_cart]" value="1" ' . checked($value, '1', false) . ' /> ';
        echo esc_html__('Afișează butonul de quick cart în configurator', 'lighting-configurator') . '</label>';
    }

    public function sanitize_settings($input)
    {
        $output = array();
        $limit = isset($input['results_limit']) ? absint($input['results_limit']) : 50;
        $output['results_limit'] = min(300, max(3, $limit));
        $output['allow_broad_recs'] = !empty($input['allow_broad_recs']) ? 1 : 0;
        $visible = isset($input['visible_count']) ? absint($input['visible_count']) : 4;
        $output['visible_count'] = min(6, max(3, $visible));
        $output['enable_quick_cart'] = !empty($input['enable_quick_cart']) ? 1 : 0;
        return $output;
    }

    private function get_public_settings()
    {
        $options = get_option(LIGHTING_CONFIGURATOR_OPTION, array());
        return array(
            'resultsLimit' => isset($options['results_limit']) ? (int) $options['results_limit'] : 50,
            'allowBroadRecs' => !empty($options['allow_broad_recs']),
            'visibleCount' => isset($options['visible_count']) ? (int) $options['visible_count'] : 4,
            'enableQuickCart' => !empty($options['enable_quick_cart']),
        );
    }

    private function register_taxonomy_submenu($taxonomy, $label)
    {
        $slug = 'lighting-configurator-tax-' . $taxonomy;
        $capability = 'manage_options';
        add_submenu_page(
            'lighting-configurator',
            $label,
            $label,
            $capability,
            $slug,
            array($this, 'render_taxonomy_redirect')
        );
    }

    public function render_taxonomy_redirect()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Sorry, you are not allowed to access this page.', 'lighting-configurator'));
        }

        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        $prefix = 'lighting-configurator-tax-';
        if (strpos($page, $prefix) !== 0) {
            wp_die(esc_html__('Invalid taxonomy page.', 'lighting-configurator'));
        }

        $taxonomy = substr($page, strlen($prefix));
        if (!$taxonomy || !taxonomy_exists($taxonomy)) {
            wp_die(esc_html__('Invalid taxonomy.', 'lighting-configurator'));
        }

        $url = admin_url('edit-tags.php?taxonomy=' . $taxonomy . '&post_type=product');
        wp_safe_redirect($url);
        exit;
    }

    public function enqueue_admin_assets($hook)
    {
        $this->enqueue_admin_list_styles($hook);

        if ($hook !== 'edit-tags.php' && $hook !== 'term.php') {
            return;
        }

        $taxonomy = isset($_GET['taxonomy']) ? sanitize_key($_GET['taxonomy']) : '';
        $allowed = array(
            Lighting_Configurator_Taxonomies::TAX_ROOM,
            Lighting_Configurator_Taxonomies::TAX_STYLE,
            Lighting_Configurator_Taxonomies::TAX_MATERIAL,
            Lighting_Configurator_Taxonomies::TAX_SOURCE_TYPE,
        );

        if (!in_array($taxonomy, $allowed, true)) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script('media-editor');
        wp_add_inline_script('media-editor', $this->get_term_media_script(), 'after');
    }

    private function enqueue_admin_list_styles($hook)
    {
        if ($hook !== 'edit.php') {
            return;
        }

        $post_type = isset($_GET['post_type']) ? sanitize_key($_GET['post_type']) : '';
        if ($post_type !== 'product') {
            return;
        }

        $css = '
            .fixed .column-taxonomy-lighting_room,
            .fixed .column-taxonomy-lighting_style,
            .fixed .column-taxonomy-lighting_material,
            .fixed .column-taxonomy-lighting_source_type {
                width: 140px;
                white-space: normal;
            }
            .fixed .column-taxonomy-lighting_room a,
            .fixed .column-taxonomy-lighting_style a,
            .fixed .column-taxonomy-lighting_material a,
            .fixed .column-taxonomy-lighting_source_type a {
                display: inline-block;
                margin: 0 4px 2px 0;
                white-space: nowrap;
            }
        ';
        wp_add_inline_style('wp-admin', $css);
    }

    private function get_taxonomy_terms($taxonomy)
    {
        if (!taxonomy_exists($taxonomy)) {
            return array();
        }

        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ));

        if (is_wp_error($terms)) {
            return array();
        }

        return $terms;
    }

    private function get_product_categories()
    {
        $terms = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key' => self::CATEGORY_META_KEY,
                    'value' => '1',
                    'compare' => '=',
                ),
            ),
        ));

        if (is_wp_error($terms)) {
            return array();
        }

        return $terms;
    }

    public function render_product_kelvin_field()
    {
        woocommerce_wp_text_input(array(
            'id' => self::PRODUCT_KELVIN_META_KEY,
            'label' => __('Intensitate luminoasă (Kelvin)', 'lighting-configurator'),
            'type' => 'number',
            'custom_attributes' => array(
                'min' => 1000,
                'step' => 1,
            ),
            'description' => __('Folosită pentru filtrarea temperaturii luminii în configurator.', 'lighting-configurator'),
        ));
    }

    public function render_product_specs_fields()
    {
        woocommerce_wp_text_input(array(
            'id' => Lighting_Configurator_Meta::META_POWER_W,
            'label' => __('Putere (W)', 'lighting-configurator'),
            'type' => 'number',
            'custom_attributes' => array(
                'min' => 0,
                'step' => '0.01',
            ),
        ));

        woocommerce_wp_text_input(array(
            'id' => Lighting_Configurator_Meta::META_LUMENS,
            'label' => __('Flux luminos (lm)', 'lighting-configurator'),
            'type' => 'number',
            'custom_attributes' => array(
                'min' => 0,
                'step' => '1',
            ),
        ));

        woocommerce_wp_text_input(array(
            'id' => Lighting_Configurator_Meta::META_CRI,
            'label' => __('Index redare culoare (CRI)', 'lighting-configurator'),
            'type' => 'number',
            'custom_attributes' => array(
                'min' => 0,
                'max' => 100,
                'step' => '1',
            ),
        ));

        woocommerce_wp_text_input(array(
            'id' => Lighting_Configurator_Meta::META_IP_RATING,
            'label' => __('Protecție IP', 'lighting-configurator'),
            'type' => 'number',
            'custom_attributes' => array(
                'min' => 0,
                'step' => '1',
            ),
        ));

        woocommerce_wp_text_input(array(
            'id' => Lighting_Configurator_Meta::META_HEIGHT_CM,
            'label' => __('Înălțime (cm)', 'lighting-configurator'),
            'type' => 'number',
            'custom_attributes' => array(
                'min' => 0,
                'step' => '0.1',
            ),
        ));

        woocommerce_wp_text_input(array(
            'id' => Lighting_Configurator_Meta::META_LENGTH_CM,
            'label' => __('Lungime (cm)', 'lighting-configurator'),
            'type' => 'number',
            'custom_attributes' => array(
                'min' => 0,
                'step' => '0.1',
            ),
        ));

        woocommerce_wp_text_input(array(
            'id' => Lighting_Configurator_Meta::META_DIAMETER_CM,
            'label' => __('Diametru (cm)', 'lighting-configurator'),
            'type' => 'number',
            'custom_attributes' => array(
                'min' => 0,
                'step' => '0.1',
            ),
        ));

        woocommerce_wp_select(array(
            'id' => Lighting_Configurator_Meta::META_FUNCTION_TYPE,
            'label' => __('Funcție', 'lighting-configurator'),
            'options' => array(
                '' => __('Selectează', 'lighting-configurator'),
                'standard' => __('Standard', 'lighting-configurator'),
                'dimmable' => __('Dimmable', 'lighting-configurator'),
                'smart' => __('Smart', 'lighting-configurator'),
            ),
        ));
    }

    public function save_product_kelvin_field($product)
    {
        if (isset($_POST[self::PRODUCT_KELVIN_META_KEY])) {
            $value = absint($_POST[self::PRODUCT_KELVIN_META_KEY]);
            if ($value) {
                $product->update_meta_data(self::PRODUCT_KELVIN_META_KEY, $value);
            } else {
                $product->delete_meta_data(self::PRODUCT_KELVIN_META_KEY);
            }
        }
    }

    public function save_product_specs_fields($product)
    {
        $fields = Lighting_Configurator_Meta::get_meta_fields();
        foreach ($fields as $key => $config) {
            if (!isset($_POST[$key])) {
                continue;
            }
            $raw = wp_unslash($_POST[$key]);
            $value = call_user_func($config['sanitize'], $raw);
            if ($value === '' || $value === null) {
                $product->delete_meta_data($key);
            } else {
                $product->update_meta_data($key, $value);
            }
        }
    }

    public function ajax_get_recommendations()
    {
        check_ajax_referer('lighting_configurator', 'nonce');

        $settings = get_option(LIGHTING_CONFIGURATOR_OPTION, array());
        $limit = isset($settings['results_limit']) ? (int) $settings['results_limit'] : 50;
        $limit = min(300, max(3, $limit));
        $allow_broad = !empty($settings['allow_broad_recs']);

        $payload = array(
            'rooms' => $this->sanitize_id_list(isset($_POST['rooms']) ? $_POST['rooms'] : array()),
            'styles' => $this->sanitize_id_list(isset($_POST['styles']) ? $_POST['styles'] : array()),
            'materials' => $this->sanitize_id_list(isset($_POST['materials']) ? $_POST['materials'] : array()),
            'sources' => $this->sanitize_id_list(isset($_POST['sources']) ? $_POST['sources'] : array()),
            'categories' => $this->sanitize_id_list(isset($_POST['categories']) ? $_POST['categories'] : array()),
            'temps' => $this->sanitize_text_list(isset($_POST['temps']) ? $_POST['temps'] : array()),
        );

        $cart_map = $this->get_cart_map();
        $results = $this->query_recommendations($payload, $limit, $allow_broad, $cart_map);
        $complementary = $this->query_complementary_products($limit, $results, $payload['temps'], $cart_map);
        wp_send_json_success(array(
            'recommended' => $results,
            'complementary' => $complementary,
        ));
    }

    public function ajax_get_cart_map()
    {
        check_ajax_referer('lighting_configurator', 'nonce');
        wp_send_json_success(array(
            'cart_map' => $this->get_cart_map(),
        ));
    }

    public function ajax_get_product_modal()
    {
        check_ajax_referer('lighting_configurator', 'nonce');

        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        if (!$product_id) {
            wp_send_json_error(array('message' => 'Invalid product.'));
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error(array('message' => 'Product not found.'));
        }

        $post = get_post($product_id);
        $short_desc = $post ? apply_filters('woocommerce_short_description', $post->post_excerpt) : '';
        $long_desc = $post ? apply_filters('the_content', $post->post_content) : '';
        $categories = wc_get_product_category_list($product_id, ', ');
        $permalink = get_permalink($product_id);

        ob_start();
        ?>
        <div class="lc-modal-product">
            <div class="lc-modal-media"><?php echo $product->get_image('large'); ?></div>
            <div class="lc-modal-content">
                <h3 class="lc-modal-title"><?php echo esc_html($product->get_name()); ?></h3>
                <div class="lc-modal-price"><?php echo wp_kses_post($product->get_price_html()); ?></div>
                <?php if ($categories) : ?>
                    <div class="lc-modal-categories"><?php echo wp_kses_post($categories); ?></div>
                <?php endif; ?>
                <?php if ($short_desc) : ?>
                    <div class="lc-modal-desc"><?php echo wp_kses_post($short_desc); ?></div>
                <?php endif; ?>
            </div>
            <?php if ($long_desc) : ?>
                <div class="lc-modal-long"><?php echo wp_kses_post($long_desc); ?></div>
            <?php endif; ?>
            <div class="lc-modal-footer">
                <a class="lc-modal-link" href="<?php echo esc_url($permalink); ?>" target="_blank" rel="noopener">
                    <?php echo esc_html__('Mergi la pagina produsului', 'lighting-configurator'); ?>
                </a>
            </div>
        </div>
        <?php
        $html = ob_get_clean();

        wp_send_json_success(array('html' => $html));
    }

    public function ajax_get_cart_sidebar()
    {
        check_ajax_referer('lighting_configurator', 'nonce');
        wp_send_json_success(array(
            'html' => $this->render_cart_sidebar_items(),
        ));
    }

    public function ajax_update_cart_sidebar()
    {
        check_ajax_referer('lighting_configurator', 'nonce');
        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(array('message' => 'Cart unavailable.'));
        }

        $items = isset($_POST['items']) && is_array($_POST['items']) ? $_POST['items'] : array();
        foreach ($items as $item) {
            $key = isset($item['key']) ? wc_clean(wp_unslash($item['key'])) : '';
            $qty = isset($item['qty']) ? absint($item['qty']) : 0;
            if (!$key) {
                continue;
            }
            WC()->cart->set_quantity($key, $qty, true);
        }

        WC()->cart->calculate_totals();

        wp_send_json_success(array(
            'html' => $this->render_cart_sidebar_items(),
        ));
    }

    public function ajax_bulk_add_to_cart()
    {
        check_ajax_referer('lighting_configurator', 'nonce');
        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(array('message' => 'Cart unavailable.'));
        }

        $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? $_POST['ids'] : array();
        $ids = array_values(array_filter(array_map('absint', $ids)));
        if (empty($ids)) {
            wp_send_json_error(array('message' => 'No products.'));
        }

        foreach ($ids as $product_id) {
            $product = wc_get_product($product_id);
            if (!$product || !$product->is_purchasable()) {
                continue;
            }
            WC()->cart->add_to_cart($product_id, 1);
        }

        WC()->cart->calculate_totals();

        wp_send_json_success(array(
            'cart_map' => $this->get_cart_map(),
        ));
    }

    private function render_cart_sidebar_items()
    {
        if (!function_exists('WC') || !WC()->cart) {
            return '<p class="lc-cart-empty">' . esc_html__('Coșul nu este disponibil.', 'lighting-configurator') . '</p>';
        }

        if (WC()->cart->is_empty()) {
            return '<p class="lc-cart-empty">' . esc_html__('Nu ai produse în coș.', 'lighting-configurator') . '</p>';
        }

        $items_html = '';
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            if (!$product) {
                continue;
            }
            $product_id = $cart_item['product_id'];
            $name = $product->get_name();
            $permalink = get_permalink($product_id);
            $thumb = $product->get_image('thumbnail');
            $price = WC()->cart->get_product_price($product);
            $qty = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 1;

            $items_html .= '<div class="lc-cart-item" data-cart-key="' . esc_attr($cart_item_key) . '">';
            $items_html .= '<a class="lc-cart-thumb" href="' . esc_url($permalink) . '" target="_blank" rel="noopener">' . $thumb . '</a>';
            $items_html .= '<div class="lc-cart-info">';
            $items_html .= '<a class="lc-cart-name" href="' . esc_url($permalink) . '" target="_blank" rel="noopener">' . esc_html($name) . '</a>';
            $items_html .= '<div class="lc-cart-price">' . wp_kses_post($price) . '</div>';
            $items_html .= '<label class="lc-cart-qty-label">' . esc_html__('Cantitate', 'lighting-configurator') . '</label>';
            $items_html .= '<input type="number" class="lc-cart-qty" min="0" value="' . esc_attr($qty) . '"/>';
            $items_html .= '</div>';
            $items_html .= '</div>';
        }

        return $items_html;
    }

    private function sanitize_id_list($items)
    {
        if (!is_array($items)) {
            return array();
        }
        return array_values(array_filter(array_map('absint', $items)));
    }

    private function sanitize_text_list($items)
    {
        if (!is_array($items)) {
            return array();
        }
        $clean = array();
        foreach ($items as $item) {
            $value = sanitize_key($item);
            if ($value) {
                $clean[] = $value;
            }
        }
        return array_values(array_unique($clean));
    }

    private function query_recommendations($payload, $limit, $allow_broad, $cart_map)
    {
        $results = array();
        $exclude = array();

        $strict = $this->build_query_args($payload, $limit, $exclude, true, true, true);
        $results = $this->run_reco_query($strict, $results, $exclude, $limit, $cart_map);

        if ($allow_broad && count($results) < $limit) {
            $relaxed_source = $this->build_query_args($payload, $limit, $exclude, true, false, true);
            $results = $this->run_reco_query($relaxed_source, $results, $exclude, $limit, $cart_map);
        }

        if ($allow_broad && count($results) < $limit) {
            $relaxed_material = $this->build_query_args($payload, $limit, $exclude, false, false, true);
            $results = $this->run_reco_query($relaxed_material, $results, $exclude, $limit, $cart_map);
        }

        return $results;
    }

    private function run_reco_query($args, $results, &$exclude, $limit, $cart_map)
    {
        $remaining = $limit - count($results);
        if ($remaining <= 0) {
            return $results;
        }

        $args['posts_per_page'] = $remaining;
        $args['post__not_in'] = $exclude;
        $query = new WP_Query($args);
        if (!$query->have_posts()) {
            return $results;
        }

        foreach ($query->posts as $product_id) {
            $product = wc_get_product($product_id);
            if (!$product) {
                continue;
            }
            $in_cart = isset($cart_map[$product_id]);
            $results[] = array(
                'id' => $product_id,
                'title' => $product->get_name(),
                'permalink' => get_permalink($product_id),
                'thumbnail' => $product->get_image('woocommerce_thumbnail'),
                'add_to_cart' => esc_url(add_query_arg('add-to-cart', $product_id, home_url('/'))),
                'in_cart' => $in_cart,
                'cart_item_key' => $in_cart ? $cart_map[$product_id] : '',
            );
            $exclude[] = $product_id;
            if (count($results) >= $limit) {
                break;
            }
        }

        return $results;
    }

    private function build_query_args($payload, $limit, $exclude, $use_material, $use_source, $exclude_complementary)
    {
        $tax_query = array('relation' => 'AND');
        $complementary_categories = $exclude_complementary ? $this->get_complementary_category_ids() : array();

        if (!empty($payload['rooms'])) {
            $tax_query[] = array(
                'taxonomy' => Lighting_Configurator_Taxonomies::TAX_ROOM,
                'field' => 'term_id',
                'terms' => $payload['rooms'],
            );
        }

        if (!empty($payload['styles'])) {
            $tax_query[] = array(
                'taxonomy' => Lighting_Configurator_Taxonomies::TAX_STYLE,
                'field' => 'term_id',
                'terms' => $payload['styles'],
            );
        }

        if ($use_material && !empty($payload['materials'])) {
            $tax_query[] = array(
                'taxonomy' => Lighting_Configurator_Taxonomies::TAX_MATERIAL,
                'field' => 'term_id',
                'terms' => $payload['materials'],
            );
        }

        if ($use_source && !empty($payload['sources'])) {
            $tax_query[] = array(
                'taxonomy' => Lighting_Configurator_Taxonomies::TAX_SOURCE_TYPE,
                'field' => 'term_id',
                'terms' => $payload['sources'],
            );
        }

        if (!empty($payload['categories'])) {
            $tax_query[] = array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $payload['categories'],
            );
        }

        if (!empty($complementary_categories)) {
            $tax_query[] = array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $complementary_categories,
                'operator' => 'NOT IN',
            );
        }

        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'fields' => 'ids',
        );

        if (count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        }

        return $args;
    }

    private function build_kelvin_meta_query($temps)
    {
        if (empty($temps)) {
            return null;
        }

        $temps = array_values(array_unique($temps));
        $all_temps = array('warm', 'neutral', 'cool');
        $covers_all = !array_diff($all_temps, $temps);
        if ($covers_all) {
            return null;
        }

        $ranges = array();
        foreach ($temps as $temp) {
            if ($temp === 'warm') {
                $ranges[] = array(2000, 3000);
            } elseif ($temp === 'neutral') {
                $ranges[] = array(3500, 4500);
            } elseif ($temp === 'cool') {
                $ranges[] = array(5000, 6500);
            }
        }

        if (empty($ranges)) {
            return null;
        }

        $meta_query = array('relation' => 'OR');
        foreach ($ranges as $range) {
            $meta_query[] = array(
                'key' => self::PRODUCT_KELVIN_META_KEY,
                'value' => $range,
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN',
            );
        }

        return $meta_query;
    }

    private function get_complementary_category_ids()
    {
        $terms = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key' => self::CATEGORY_COMPLEMENTARY_KEY,
                    'value' => '1',
                    'compare' => '=',
                ),
            ),
            'fields' => 'ids',
        ));

        if (is_wp_error($terms)) {
            return array();
        }

        return $terms;
    }

    private function query_complementary_products($limit, $exclude_products, $temps, $cart_map)
    {
        $complementary_categories = $this->get_complementary_category_ids();
        if (empty($complementary_categories)) {
            return array();
        }

        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => min(10, $limit),
            'fields' => 'ids',
            'post__not_in' => $exclude_products,
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $complementary_categories,
                ),
            ),
        );

        $meta_query = $this->build_kelvin_meta_query($temps);
        if ($meta_query) {
            $args['meta_query'] = $meta_query;
        }

        $query = new WP_Query($args);

        if (!$query->have_posts()) {
            return array();
        }

        $results = array();
        foreach ($query->posts as $product_id) {
            $product = wc_get_product($product_id);
            if (!$product) {
                continue;
            }
            $in_cart = isset($cart_map[$product_id]);
            $results[] = array(
                'id' => $product_id,
                'title' => $product->get_name(),
                'permalink' => get_permalink($product_id),
                'thumbnail' => $product->get_image('woocommerce_thumbnail'),
                'add_to_cart' => esc_url(add_query_arg('add-to-cart', $product_id, home_url('/'))),
                'in_cart' => $in_cart,
                'cart_item_key' => $in_cart ? $cart_map[$product_id] : '',
            );
        }

        return $results;
    }

    private function get_cart_map()
    {
        if (!function_exists('WC') || !WC()->cart) {
            return array();
        }

        $map = array();
        foreach (WC()->cart->get_cart() as $cart_item_key => $item) {
            if (!empty($item['product_id'])) {
                $map[(int) $item['product_id']] = $cart_item_key;
            }
        }

        return $map;
    }

    public function render_category_meta_add()
    {
        ?>
        <div class="form-field">
            <label for="lc_show_in_configurator"><?php echo esc_html__('Show in Lighting Configurator', 'lighting-configurator'); ?></label>
            <input type="checkbox" name="lc_show_in_configurator" id="lc_show_in_configurator" value="1" />
        </div>
        <div class="form-field">
            <label for="lc_is_complementary"><?php echo esc_html__('Categorie complementară (accesorii)', 'lighting-configurator'); ?></label>
            <input type="checkbox" name="lc_is_complementary" id="lc_is_complementary" value="1" />
        </div>
        <?php
    }

    public function render_category_meta_edit($term)
    {
        $value = get_term_meta($term->term_id, self::CATEGORY_META_KEY, true);
        $complementary = get_term_meta($term->term_id, self::CATEGORY_COMPLEMENTARY_KEY, true);
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="lc_show_in_configurator"><?php echo esc_html__('Show in Lighting Configurator', 'lighting-configurator'); ?></label>
            </th>
            <td>
                <input type="checkbox" name="lc_show_in_configurator" id="lc_show_in_configurator" value="1" <?php checked($value, '1'); ?> />
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="lc_is_complementary"><?php echo esc_html__('Categorie complementară (accesorii)', 'lighting-configurator'); ?></label>
            </th>
            <td>
                <input type="checkbox" name="lc_is_complementary" id="lc_is_complementary" value="1" <?php checked($complementary, '1'); ?> />
            </td>
        </tr>
        <?php
    }

    public function save_category_meta($term_id)
    {
        $value = isset($_POST['lc_show_in_configurator']) ? '1' : '0';
        update_term_meta($term_id, self::CATEGORY_META_KEY, $value);
        $complementary = isset($_POST['lc_is_complementary']) ? '1' : '0';
        update_term_meta($term_id, self::CATEGORY_COMPLEMENTARY_KEY, $complementary);
    }

    private function register_taxonomy_media_fields($taxonomy)
    {
        add_action($taxonomy . '_add_form_fields', array($this, 'render_term_media_add'));
        add_action($taxonomy . '_edit_form_fields', array($this, 'render_term_media_edit'), 10, 2);
        add_action('created_' . $taxonomy, array($this, 'save_term_media'));
        add_action('edited_' . $taxonomy, array($this, 'save_term_media'));
    }

    public function render_term_media_add()
    {
        ?>
        <div class="form-field">
            <label for="lc_term_icon_id"><?php echo esc_html__('Icon / Image', 'lighting-configurator'); ?></label>
            <input type="hidden" name="lc_term_icon_id" id="lc_term_icon_id" value="" />
            <div class="lc-term-media-preview"></div>
            <p>
                <button type="button" class="button lc-term-media-upload"><?php echo esc_html__('Upload image', 'lighting-configurator'); ?></button>
                <button type="button" class="button lc-term-media-remove" style="display:none;"><?php echo esc_html__('Remove', 'lighting-configurator'); ?></button>
            </p>
        </div>
        <?php
    }

    public function render_term_media_edit($term)
    {
        $image_id = (int) get_term_meta($term->term_id, self::TERM_ICON_META_KEY, true);
        $image = $image_id ? wp_get_attachment_image($image_id, 'thumbnail') : '';
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="lc_term_icon_id"><?php echo esc_html__('Icon / Image', 'lighting-configurator'); ?></label>
            </th>
            <td>
                <input type="hidden" name="lc_term_icon_id" id="lc_term_icon_id" value="<?php echo esc_attr($image_id); ?>" />
                <div class="lc-term-media-preview">
                    <?php echo wp_kses_post($image); ?>
                </div>
                <p>
                    <button type="button" class="button lc-term-media-upload"><?php echo esc_html__('Upload image', 'lighting-configurator'); ?></button>
                    <button type="button" class="button lc-term-media-remove" <?php echo $image_id ? '' : 'style="display:none;"'; ?>><?php echo esc_html__('Remove', 'lighting-configurator'); ?></button>
                </p>
            </td>
        </tr>
        <?php
    }

    public function save_term_media($term_id)
    {
        if (isset($_POST['lc_term_icon_id'])) {
            $image_id = absint($_POST['lc_term_icon_id']);
            update_term_meta($term_id, self::TERM_ICON_META_KEY, $image_id);
        }
    }

    private function get_term_media_script()
    {
        return <<<'JS'
(function($){
    function refreshButtons($wrap){
        var has = $wrap.find('#lc_term_icon_id').val();
        $wrap.find('.lc-term-media-remove').toggle(!!has);
    }

    function getPreviewUrl(attachment){
        if (attachment && attachment.sizes && attachment.sizes.thumbnail) {
            return attachment.sizes.thumbnail.url;
        }
        return attachment && attachment.url ? attachment.url : '';
    }

    $(document).on('click', '.lc-term-media-upload', function(e){
        e.preventDefault();
        var $wrap = $(this).closest('td, .form-field');
        var frame = wp.media({title: 'Select image', button: {text: 'Use image'}, multiple: false});
        frame.on('select', function(){
            var attachment = frame.state().get('selection').first().toJSON();
            var url = getPreviewUrl(attachment);
            $wrap.find('#lc_term_icon_id').val(attachment.id || '');
            $wrap.find('.lc-term-media-preview').html(url ? '<img src="' + url + '" alt="" style="max-width:100%;height:auto;" />' : '');
            refreshButtons($wrap);
        });
        frame.open();
    });

    $(document).on('click', '.lc-term-media-remove', function(e){
        e.preventDefault();
        var $wrap = $(this).closest('td, .form-field');
        $wrap.find('#lc_term_icon_id').val('');
        $wrap.find('.lc-term-media-preview').html('');
        refreshButtons($wrap);
    });

    $(function(){
        $('.lc-term-media-upload').each(function(){
            refreshButtons($(this).closest('td, .form-field'));
        });
    });
})(jQuery);
JS;
    }
}

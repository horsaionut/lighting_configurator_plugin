<?php

if (!defined('ABSPATH')) {
    exit;
}

class Lighting_Configurator_Meta
{
    const META_POWER_W = '_power_w';
    const META_LUMENS = '_lumens';
    const META_CRI = '_cri';
    const META_IP_RATING = '_ip_rating';
    const META_HEIGHT_CM = '_height_cm';
    const META_LENGTH_CM = '_length_cm';
    const META_DIAMETER_CM = '_diameter_cm';
    const META_FUNCTION_TYPE = '_function_type';

    public static function register()
    {
        self::register_post_meta_fields();
    }

    public static function get_meta_fields()
    {
        return array(
            self::META_POWER_W => array(
                'type' => 'number',
                'sanitize' => array(__CLASS__, 'sanitize_number'),
            ),
            self::META_LUMENS => array(
                'type' => 'number',
                'sanitize' => array(__CLASS__, 'sanitize_number'),
            ),
            self::META_CRI => array(
                'type' => 'number',
                'sanitize' => array(__CLASS__, 'sanitize_number'),
            ),
            self::META_IP_RATING => array(
                'type' => 'number',
                'sanitize' => array(__CLASS__, 'sanitize_ip_rating'),
            ),
            self::META_HEIGHT_CM => array(
                'type' => 'number',
                'sanitize' => array(__CLASS__, 'sanitize_number'),
            ),
            self::META_LENGTH_CM => array(
                'type' => 'number',
                'sanitize' => array(__CLASS__, 'sanitize_number'),
            ),
            self::META_DIAMETER_CM => array(
                'type' => 'number',
                'sanitize' => array(__CLASS__, 'sanitize_number'),
            ),
            self::META_FUNCTION_TYPE => array(
                'type' => 'string',
                'sanitize' => array(__CLASS__, 'sanitize_function_type'),
            ),
        );
    }

    public static function sanitize_number($value)
    {
        if ($value === '' || $value === null) {
            return '';
        }
        if (!is_numeric($value)) {
            return '';
        }
        return (float) $value;
    }

    public static function sanitize_ip_rating($value)
    {
        if ($value === '' || $value === null) {
            return '';
        }
        return absint($value);
    }

    public static function sanitize_function_type($value)
    {
        $allowed = array('standard', 'dimmable', 'smart');
        $value = sanitize_key($value);
        if (!in_array($value, $allowed, true)) {
            return '';
        }
        return $value;
    }

    public static function get_product_specs($product_id)
    {
        $product_id = absint($product_id);
        if (!$product_id) {
            return array();
        }

        $meta = array();
        foreach (self::get_meta_fields() as $key => $config) {
            $meta[$key] = get_post_meta($product_id, $key, true);
        }

        $taxonomies = array(
            Lighting_Configurator_Taxonomies::TAX_ROOM,
            Lighting_Configurator_Taxonomies::TAX_STYLE,
            Lighting_Configurator_Taxonomies::TAX_MATERIAL,
            Lighting_Configurator_Taxonomies::TAX_SOURCE_TYPE,
            Lighting_Configurator_Taxonomies::TAX_COLOR,
            Lighting_Configurator_Taxonomies::TAX_VOLTAGE,
        );

        $tax_data = array();
        foreach ($taxonomies as $taxonomy) {
            if (!taxonomy_exists($taxonomy)) {
                continue;
            }
            $tax_data[$taxonomy] = wp_get_post_terms($product_id, $taxonomy, array(
                'fields' => 'names',
            ));
        }

        return array(
            'meta' => $meta,
            'taxonomies' => $tax_data,
        );
    }

    private static function register_post_meta_fields()
    {
        $fields = self::get_meta_fields();
        foreach ($fields as $key => $config) {
            $sample_id = self::get_sample_product_id();
            if ($sample_id) {
                metadata_exists('post', $sample_id, $key);
            } else {
                metadata_exists('post', 0, $key);
            }

            if (function_exists('registered_meta_key_exists') && registered_meta_key_exists('post', $key, 'product')) {
                continue;
            }

            register_post_meta('product', $key, array(
                'type' => $config['type'],
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => $config['sanitize'],
                'auth_callback' => function () {
                    return current_user_can('edit_products');
                },
            ));
        }
    }

    private static function get_sample_product_id()
    {
        $ids = get_posts(array(
            'post_type' => 'product',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'post_status' => 'any',
        ));
        if (empty($ids)) {
            return 0;
        }
        return (int) $ids[0];
    }
}

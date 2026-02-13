<?php

if (!defined('ABSPATH')) {
    exit;
}

class Lighting_Configurator_Taxonomies
{
    const TAX_ROOM = 'lighting_room';
    const TAX_STYLE = 'lighting_style';
    const TAX_MATERIAL = 'lighting_material';
    const TAX_SOURCE_TYPE = 'lighting_source_type';
    const TAX_COLOR = 'lighting_color';
    const TAX_VOLTAGE = 'lighting_voltage';

    public static function register()
    {
        $shared_args = array(
            'public' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'hierarchical' => true,
            'query_var' => false,
            'rewrite' => false,
            'capabilities' => array(
                'manage_terms' => 'manage_product_terms',
                'edit_terms' => 'manage_product_terms',
                'delete_terms' => 'manage_product_terms',
                'assign_terms' => 'edit_products',
            ),
        );
        $flat_args = array_merge($shared_args, array(
            'hierarchical' => false,
        ));

        if (!taxonomy_exists(self::TAX_ROOM)) {
            register_taxonomy(
                self::TAX_ROOM,
                array('product'),
                array_merge($shared_args, array(
                    'labels' => self::build_labels(__('Camere (Configurator)', 'lighting-configurator')),
                ))
            );
        }

        if (!taxonomy_exists(self::TAX_STYLE)) {
            register_taxonomy(
                self::TAX_STYLE,
                array('product'),
                array_merge($shared_args, array(
                    'labels' => self::build_labels(__('Stil iluminat', 'lighting-configurator')),
                ))
            );
        }

        if (!taxonomy_exists(self::TAX_MATERIAL)) {
            register_taxonomy(
                self::TAX_MATERIAL,
                array('product'),
                array_merge($shared_args, array(
                    'labels' => self::build_labels(__('Material corp iluminat', 'lighting-configurator')),
                ))
            );
        }

        if (!taxonomy_exists(self::TAX_SOURCE_TYPE)) {
            register_taxonomy(
                self::TAX_SOURCE_TYPE,
                array('product'),
                array_merge($shared_args, array(
                    'labels' => self::build_labels(__('Tip sursa iluminat', 'lighting-configurator')),
                ))
            );
        }

        if (!taxonomy_exists(self::TAX_COLOR)) {
            register_taxonomy(
                self::TAX_COLOR,
                array('product'),
                array_merge($flat_args, array(
                    'labels' => self::build_labels(__('Culoare produs', 'lighting-configurator')),
                ))
            );
        }

        if (!taxonomy_exists(self::TAX_VOLTAGE)) {
            register_taxonomy(
                self::TAX_VOLTAGE,
                array('product'),
                array_merge($flat_args, array(
                    'labels' => self::build_labels(__('Voltaj', 'lighting-configurator')),
                ))
            );
        }
    }

    private static function build_labels($singular)
    {
        return array(
            'name' => $singular,
            'singular_name' => $singular,
            'search_items' => sprintf(__('Caută %s', 'lighting-configurator'), $singular),
            'all_items' => sprintf(__('Toate %s', 'lighting-configurator'), $singular),
            'edit_item' => sprintf(__('Editează %s', 'lighting-configurator'), $singular),
            'update_item' => sprintf(__('Actualizează %s', 'lighting-configurator'), $singular),
            'add_new_item' => sprintf(__('Adaugă %s', 'lighting-configurator'), $singular),
            'new_item_name' => sprintf(__('Nume nou %s', 'lighting-configurator'), $singular),
            'menu_name' => $singular,
        );
    }
}

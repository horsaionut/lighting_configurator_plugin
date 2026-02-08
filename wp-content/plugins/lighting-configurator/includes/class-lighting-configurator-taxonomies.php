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

        register_taxonomy(
            self::TAX_ROOM,
            array('product'),
            array_merge($shared_args, array(
                'labels' => self::build_labels(__('Camere (Configurator)', 'lighting-configurator')),
            ))
        );

        register_taxonomy(
            self::TAX_STYLE,
            array('product'),
            array_merge($shared_args, array(
                'labels' => self::build_labels(__('Stil iluminat', 'lighting-configurator')),
            ))
        );

        register_taxonomy(
            self::TAX_MATERIAL,
            array('product'),
            array_merge($shared_args, array(
                'labels' => self::build_labels(__('Material corp iluminat', 'lighting-configurator')),
            ))
        );

        register_taxonomy(
            self::TAX_SOURCE_TYPE,
            array('product'),
            array_merge($shared_args, array(
                'labels' => self::build_labels(__('Tip sursa iluminat', 'lighting-configurator')),
            ))
        );
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

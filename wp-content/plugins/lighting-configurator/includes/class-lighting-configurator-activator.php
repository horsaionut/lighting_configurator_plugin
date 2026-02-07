<?php

if (!defined('ABSPATH')) {
    exit;
}

class Lighting_Configurator_Activator
{
    public static function activate()
    {
        if (get_option(LIGHTING_CONFIGURATOR_OPTION) === false) {
            add_option(LIGHTING_CONFIGURATOR_OPTION, array(
                'results_limit' => 3,
            ));
        }
    }
}

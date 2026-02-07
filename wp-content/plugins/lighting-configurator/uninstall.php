<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('lighting_configurator_settings');

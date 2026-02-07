<?php

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="wrap">
    <h1><?php echo esc_html__('Lighting Configurator', 'lighting-configurator'); ?></h1>
    <form method="post" action="options.php">
        <?php
        settings_fields('lighting_configurator');
        do_settings_sections('lighting-configurator');
        submit_button();
        ?>
    </form>
</div>

<?php

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="wrap">
    <h1><?php echo esc_html__('Lighting Configurator', 'lighting-configurator'); ?></h1>
    <div class="notice notice-info inline">
        <p><strong><?php echo esc_html__('Shortcode', 'lighting-configurator'); ?></strong></p>
        <p><?php echo esc_html__('Folosește shortcode-ul de mai jos în paginile unde vrei să afișezi configuratorul:', 'lighting-configurator'); ?></p>
        <p><code>[lighting_configurator]</code></p>
    </div>
    <form method="post" action="options.php">
        <?php
        settings_fields('lighting_configurator');
        do_settings_sections('lighting-configurator');
        submit_button();
        ?>
    </form>
</div>

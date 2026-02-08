<?php

if (!defined('ABSPATH')) {
    exit;
}

?>
<section class="lc-configurator" data-results-limit="<?php echo esc_attr($settings['results_limit'] ?? 3); ?>">
    <header class="lc-header">
        <p class="lc-eyebrow"><?php echo esc_html__('Lighting Configurator', 'lighting-configurator'); ?></p>
        <h4 class="lc-title"><?php echo esc_html__('Răspunde la câteva întrebări și îți sugerăm soluții potrivite.', 'lighting-configurator'); ?></h4>
    </header>

    <nav class="lc-steps-bar" aria-label="<?php echo esc_attr__('Configurator steps', 'lighting-configurator'); ?>">
        <button class="lc-step-dot" type="button" data-step="1" aria-label="<?php echo esc_attr__('Step 1', 'lighting-configurator'); ?>">
            <span class="lc-step-label">1</span>
            <span class="lc-step-check" aria-hidden="true">✓</span>
        </button>
        <button class="lc-step-dot" type="button" data-step="2" aria-label="<?php echo esc_attr__('Step 2', 'lighting-configurator'); ?>">
            <span class="lc-step-label">2</span>
            <span class="lc-step-check" aria-hidden="true">✓</span>
        </button>
        <button class="lc-step-dot" type="button" data-step="3" aria-label="<?php echo esc_attr__('Step 3', 'lighting-configurator'); ?>">
            <span class="lc-step-label">3</span>
            <span class="lc-step-check" aria-hidden="true">✓</span>
        </button>
        <button class="lc-step-dot" type="button" data-step="4" aria-label="<?php echo esc_attr__('Step 4', 'lighting-configurator'); ?>">
            <span class="lc-step-label">4</span>
            <span class="lc-step-check" aria-hidden="true">✓</span>
        </button>
        <button class="lc-step-dot" type="button" data-step="5" aria-label="<?php echo esc_attr__('Step 5', 'lighting-configurator'); ?>">
            <span class="lc-step-label">5</span>
            <span class="lc-step-check" aria-hidden="true">✓</span>
        </button>
        <button class="lc-step-dot" type="button" data-step="6" aria-label="<?php echo esc_attr__('Step 6', 'lighting-configurator'); ?>">
            <span class="lc-step-label">6</span>
            <span class="lc-step-check" aria-hidden="true">✓</span>
        </button>
        <span class="lc-steps-line" aria-hidden="true"></span>
    </nav>

    <div class="lc-steps">
        <section class="lc-step is-active" data-step="1">
            <h3 class="lc-step-title">STEP 1</h3>
            <div class="lc-card-grid" data-multi="true">
                <?php if (!empty($rooms)) : ?>
                    <?php foreach ($rooms as $room) : ?>
                        <?php $room_thumb_id = (int) get_term_meta($room->term_id, 'thumbnail_id', true); ?>
                        <button class="lc-card" type="button" data-term-id="<?php echo esc_attr($room->term_id); ?>">
                            <span class="lc-card-media" aria-hidden="true">
                                <?php
                                if ($room_thumb_id) {
                                    echo wp_kses_post(wp_get_attachment_image($room_thumb_id, 'thumbnail', false, array('class' => 'lc-card-image')));
                                } else {
                                    echo '<span class="lc-card-icon"></span>';
                                }
                                ?>
                            </span>
                            <span class="lc-card-label"><?php echo esc_html($room->name); ?></span>
                        </button>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p><?php echo esc_html__('Nu există camere configurate.', 'lighting-configurator'); ?></p>
                <?php endif; ?>
            </div>
            <div class="lc-step-actions">
                <button class="lc-next" type="button">Continuă</button>
            </div>
        </section>

        <section class="lc-step" data-step="2">
            <h3 class="lc-step-title">STEP 2</h3>
            <div class="lc-form-grid">
                <label class="lc-field">
                    <span>Suprafață cameră / mp</span>
                    <input type="number" id="lc_area" min="1" placeholder="ex: 18" />
                </label>
                <label class="lc-field">
                    <span>Înălțime cameră / cm</span>
                    <input type="number" id="lc_height" min="150" step="1" placeholder="ex: 260" />
                </label>
            </div>
            <div class="lc-wall-tone">
                <p class="lc-pref-title">Ton culoare pereți</p>
                <div class="lc-wall-options" data-group="wall-tone">
                    <button class="lc-wall-option" type="button" data-value="dark">
                        <span class="lc-wall-icon lc-wall-icon-dark" aria-hidden="true"></span>
                        <span class="lc-style-label">Culori închise</span>
                    </button>
                    <button class="lc-wall-option" type="button" data-value="light">
                        <span class="lc-wall-icon lc-wall-icon-light" aria-hidden="true"></span>
                        <span class="lc-style-label">Culori deschise</span>
                    </button>
                </div>
            </div>
            <div class="lc-step-actions">
                <button class="lc-prev" type="button">Înapoi</button>
                <button class="lc-next" type="button">Continuă</button>
            </div>
        </section>

        <section class="lc-step" data-step="3">
            <h3 class="lc-step-title">STEP 3</h3>
            <div class="lc-card-grid">
                <?php if (!empty($styles)) : ?>
                    <?php foreach ($styles as $style) : ?>
                        <?php $style_thumb_id = (int) get_term_meta($style->term_id, 'thumbnail_id', true); ?>
                        <button class="lc-card" type="button" data-term-id="<?php echo esc_attr($style->term_id); ?>">
                            <span class="lc-card-media" aria-hidden="true">
                                <?php
                                if ($style_thumb_id) {
                                    echo wp_kses_post(wp_get_attachment_image($style_thumb_id, 'thumbnail', false, array('class' => 'lc-card-image')));
                                } else {
                                    echo '<span class="lc-card-icon"></span>';
                                }
                                ?>
                            </span>
                            <span class="lc-card-label"><?php echo esc_html($style->name); ?></span>
                        </button>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p><?php echo esc_html__('Nu există stiluri configurate.', 'lighting-configurator'); ?></p>
                <?php endif; ?>
            </div>
            <div class="lc-step-actions">
                <button class="lc-prev" type="button">Înapoi</button>
                <button class="lc-next" type="button">Continuă</button>
            </div>
        </section>

        <section class="lc-step" data-step="4">
            <h3 class="lc-step-title">STEP 4</h3>
            <div class="lc-preferences">
                <div class="lc-pref-group">
                    <p class="lc-pref-title">Tip corp iluminat</p>
                    <div class="lc-style-strip" data-group="fixture-type" data-multi="true">
                        <?php if (!empty($fixture_categories)) : ?>
                            <?php foreach ($fixture_categories as $category) : ?>
                                <button class="lc-style" type="button" data-term-id="<?php echo esc_attr($category->term_id); ?>">
                                    <span class="lc-style-label"><?php echo esc_html($category->name); ?></span>
                                </button>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p><?php echo esc_html__('Nu există categorii configurate.', 'lighting-configurator'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="lc-pref-group">
                    <p class="lc-pref-title">Material corp iluminat</p>
                    <div class="lc-style-strip" data-group="fixture-material" data-multi="true">
                        <?php if (!empty($materials)) : ?>
                            <?php foreach ($materials as $material) : ?>
                                <button class="lc-style" type="button" data-term-id="<?php echo esc_attr($material->term_id); ?>">
                                    <span class="lc-style-label"><?php echo esc_html($material->name); ?></span>
                                </button>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p><?php echo esc_html__('Nu există materiale configurate.', 'lighting-configurator'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="lc-step-actions">
                <button class="lc-prev" type="button">Înapoi</button>
                <button class="lc-next" type="button">Continuă</button>
            </div>
        </section>

        <section class="lc-step" data-step="5">
            <h3 class="lc-step-title">STEP 5</h3>
            <div class="lc-preferences">
                <div class="lc-pref-group">
                    <p class="lc-pref-title">Preferință temperatură lumină</p>
                    <div class="lc-style-strip" data-group="light-temp" data-multi="true">
                        <button class="lc-style" type="button"><span class="lc-style-label">Warm</span></button>
                        <button class="lc-style" type="button"><span class="lc-style-label">Neutral</span></button>
                        <button class="lc-style" type="button"><span class="lc-style-label">Cool</span></button>
                    </div>
                </div>

                <div class="lc-pref-group">
                    <p class="lc-pref-title">Tip sursă iluminat</p>
                    <div class="lc-style-strip" data-group="source-type" data-multi="true">
                        <?php if (!empty($source_types)) : ?>
                            <?php foreach ($source_types as $source_type) : ?>
                                <button class="lc-style" type="button" data-term-id="<?php echo esc_attr($source_type->term_id); ?>">
                                    <span class="lc-style-label"><?php echo esc_html($source_type->name); ?></span>
                                </button>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p><?php echo esc_html__('Nu există tipuri sursă configurate.', 'lighting-configurator'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="lc-step-actions">
                <button class="lc-prev" type="button">Înapoi</button>
                <button class="lc-next" type="button">Continuă</button>
            </div>
        </section>

        <section class="lc-step" data-step="6">
            <h3 class="lc-step-title">STEP 6</h3>
            <div class="lc-results">
                <div class="lc-result-card">
                    <p class="lc-summary-lumens">Necesar lumeni: <strong data-summary="lumens">-</strong></p>
                    <div class="lc-summary">
                        <div class="lc-summary-row">
                            <span>Camere</span>
                            <span class="lc-summary-value" data-summary="rooms">-</span>
                        </div>
                        <div class="lc-summary-row">
                            <span>Suprafață / înălțime</span>
                            <span class="lc-summary-value" data-summary="dimensions">-</span>
                        </div>
                        <div class="lc-summary-row">
                            <span>Ton pereți</span>
                            <span class="lc-summary-value" data-summary="wall-tone">-</span>
                        </div>
                        <div class="lc-summary-row">
                            <span>Stil</span>
                            <span class="lc-summary-value" data-summary="styles">-</span>
                        </div>
                        <div class="lc-summary-row">
                            <span>Tip corp</span>
                            <span class="lc-summary-value" data-summary="fixture-type">-</span>
                        </div>
                        <div class="lc-summary-row">
                            <span>Material</span>
                            <span class="lc-summary-value" data-summary="fixture-material">-</span>
                        </div>
                        <div class="lc-summary-row">
                            <span>Temperatură</span>
                            <span class="lc-summary-value" data-summary="light-temp">-</span>
                        </div>
                        <div class="lc-summary-row">
                            <span>Tip sursă</span>
                            <span class="lc-summary-value" data-summary="source-type">-</span>
                        </div>
                    </div>
                    <div class="lc-recommendations">
                        <p class="lc-reco-title">✔ Bundle recomandat</p>
                        <h4 class="lc-reco-heading">Produse recomandate</h4>
                        <div class="lc-reco-controls">
                            <button class="lc-reco-nav lc-reco-prev" type="button">←</button>
                            <button class="lc-reco-nav lc-reco-next" type="button">→</button>
                        </div>
                        <div class="lc-reco-carousel" data-carousel>
                            <?php if (!empty($recommended_products)) : ?>
                                <?php foreach ($recommended_products as $product_id) : ?>
                                    <?php
                                    $product = wc_get_product($product_id);
                                    if (!$product) {
                                        continue;
                                    }
                                    $thumbnail = $product->get_image('woocommerce_thumbnail');
                                    $title = $product->get_name();
                                    $add_to_cart_url = esc_url(add_query_arg('add-to-cart', $product_id, home_url('/')));
                                    $room_terms = wp_get_post_terms($product_id, Lighting_Configurator_Taxonomies::TAX_ROOM, array('fields' => 'ids'));
                                    $style_terms = wp_get_post_terms($product_id, Lighting_Configurator_Taxonomies::TAX_STYLE, array('fields' => 'ids'));
                                    $material_terms = wp_get_post_terms($product_id, Lighting_Configurator_Taxonomies::TAX_MATERIAL, array('fields' => 'ids'));
                                    $source_terms = wp_get_post_terms($product_id, Lighting_Configurator_Taxonomies::TAX_SOURCE_TYPE, array('fields' => 'ids'));
                                    $category_terms = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
                                    ?>
                                    <article
                                        class="lc-reco-card"
                                        data-product-id="<?php echo esc_attr($product_id); ?>"
                                        data-room="<?php echo esc_attr(implode(',', $room_terms)); ?>"
                                        data-style="<?php echo esc_attr(implode(',', $style_terms)); ?>"
                                        data-material="<?php echo esc_attr(implode(',', $material_terms)); ?>"
                                        data-source="<?php echo esc_attr(implode(',', $source_terms)); ?>"
                                        data-category="<?php echo esc_attr(implode(',', $category_terms)); ?>"
                                    >
                                        <label class="lc-reco-check">
                                            <input type="checkbox" class="lc-reco-select" />
                                            <span></span>
                                        </label>
                                        <a class="lc-reco-thumb" href="<?php echo esc_url(get_permalink($product_id)); ?>">
                                            <?php echo wp_kses_post($thumbnail); ?>
                                        </a>
                                        <h5 class="lc-reco-name"><?php echo esc_html($title); ?></h5>
                                        <div class="lc-reco-actions">
                                            <a class="lc-reco-cart" href="<?php echo $add_to_cart_url; ?>">Add to cart</a>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <p><?php echo esc_html__('Nu există produse recomandate.', 'lighting-configurator'); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="lc-reco-footer">
                            <button class="lc-reco-bulk" type="button">Adaugă selectate</button>
                        </div>
                    </div>
                    <button type="button" class="lc-primary">Add all to cart</button>
                </div>
            </div>
            <div class="lc-step-actions">
                <button class="lc-prev" type="button">Înapoi</button>
            </div>
        </section>
    </div>
</section>

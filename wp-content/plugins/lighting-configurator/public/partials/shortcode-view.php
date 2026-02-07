<?php

if (!defined('ABSPATH')) {
    exit;
}

?>
<section class="lc-configurator" data-results-limit="<?php echo esc_attr($settings['results_limit'] ?? 3); ?>">
    <header class="lc-header">
        <p class="lc-eyebrow"><?php echo esc_html__('Lighting Configurator', 'lighting-configurator'); ?></p>
        <h2 class="lc-title"><?php echo esc_html__('Ajută-mă să aleg iluminatul perfect', 'lighting-configurator'); ?></h2>
        <p class="lc-subtitle"><?php echo esc_html__('Răspunde la câteva întrebări și îți sugerăm soluții potrivite.', 'lighting-configurator'); ?></p>
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
        <span class="lc-steps-line" aria-hidden="true"></span>
    </nav>

    <div class="lc-steps">
        <section class="lc-step is-active" data-step="1">
            <h3 class="lc-step-title">STEP 1</h3>
            <div class="lc-card-grid">
                <button class="lc-card" type="button">Living</button>
                <button class="lc-card" type="button">Dormitor</button>
                <button class="lc-card" type="button">Bucătărie</button>
                <button class="lc-card" type="button">Baie</button>
                <button class="lc-card" type="button">Hol</button>
            </div>
            <div class="lc-step-actions">
                <button class="lc-next" type="button">Continuă</button>
            </div>
        </section>

        <section class="lc-step" data-step="2">
            <h3 class="lc-step-title">STEP 2</h3>
            <div class="lc-form-grid">
                <label class="lc-field">
                    <span>Mp camera</span>
                    <input type="number" min="1" placeholder="ex: 18" />
                </label>
                <label class="lc-field">
                    <span>Inălțime tavan</span>
                    <input type="number" min="2" step="0.1" placeholder="ex: 2.6" />
                </label>
                <label class="lc-field">
                    <span>Pereți</span>
                    <input type="text" placeholder="ex: alb mat" />
                </label>
                <div class="lc-image-placeholder" aria-hidden="true"></div>
            </div>
            <div class="lc-step-actions">
                <button class="lc-prev" type="button">Înapoi</button>
                <button class="lc-next" type="button">Continuă</button>
            </div>
        </section>

        <section class="lc-step" data-step="3">
            <h3 class="lc-step-title">STEP 3</h3>
            <div class="lc-card-grid">
                <button class="lc-card" type="button">Relaxare</button>
                <button class="lc-card" type="button">Lucru</button>
                <button class="lc-card" type="button">Ambient</button>
                <button class="lc-card" type="button">Decor</button>
            </div>
            <div class="lc-step-actions">
                <button class="lc-prev" type="button">Înapoi</button>
                <button class="lc-next" type="button">Continuă</button>
            </div>
        </section>

        <section class="lc-step" data-step="4">
            <h3 class="lc-step-title">STEP 4</h3>
            <div class="lc-style-strip">
                <button class="lc-style" type="button">Modern</button>
                <button class="lc-style" type="button">Minimal</button>
                <button class="lc-style" type="button">Classic</button>
                <button class="lc-style" type="button">Industrial</button>
            </div>
            <div class="lc-step-actions">
                <button class="lc-prev" type="button">Înapoi</button>
                <button class="lc-next" type="button">Continuă</button>
            </div>
        </section>

        <section class="lc-step" data-step="5">
            <h3 class="lc-step-title">STEP 5</h3>
            <div class="lc-results">
                <div class="lc-result-card">
                    <p>✔ Necesar lumeni</p>
                    <p>✔ Bundle recomandat</p>
                    <button type="button" class="lc-primary">Add all to cart</button>
                </div>
            </div>
            <div class="lc-step-actions">
                <button class="lc-prev" type="button">Înapoi</button>
            </div>
        </section>
    </div>
</section>

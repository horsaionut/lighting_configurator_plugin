(function ($) {
    function setActiveStep($configurator, step) {
        var $steps = $configurator.find('.lc-step');
        var $dots = $configurator.find('.lc-step-dot');

        $steps.removeClass('is-active');
        $steps.filter('[data-step="' + step + '"]').addClass('is-active');

        $dots.removeClass('is-active is-complete');
        $dots.each(function () {
            var dotStep = parseInt($(this).data('step'), 10);
            if (dotStep < step) {
                $(this).addClass('is-complete');
            } else if (dotStep === step) {
                $(this).addClass('is-active');
            }
        });

        updateSummary($configurator);
    }

    function toggleSelection($items, $target, isMulti) {
        if (isMulti) {
            $target.toggleClass('is-selected');
            return;
        }

        $items.removeClass('is-selected');
        $target.addClass('is-selected');
    }

    function initConfigurator($configurator) {
        $configurator.data('totalSteps', $configurator.find('.lc-step').length);
        setActiveStep($configurator, 1);
    }

    function getSelectedLabels($container, selector) {
        var labels = [];
        $container.find(selector + '.is-selected').each(function () {
            var text = $(this).find('.lc-card-label, .lc-style-label').first().text().trim();
            if (!text) {
                text = $(this).text().trim();
            }
            if (text) {
                labels.push(text);
            }
        });
        return labels;
    }

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function calculateLumens(area, heightCm, wallTone) {
        if (!area || !heightCm) {
            return null;
        }
        var heightM = heightCm / 100;
        var baseLux = wallTone === 'dark' ? 200 : 150;
        var heightFactor = clamp(heightM / 2.5, 0.85, 1.4);
        var lumens = Math.round(area * baseLux * heightFactor);
        return lumens;
    }

    function updateSummary($configurator) {
        var area = parseFloat($configurator.find('#lc_area').val());
        var heightCm = parseFloat($configurator.find('#lc_height').val());
        var wallTone = $configurator.find('.lc-wall-option.is-selected').data('value') || '';

        var rooms = getSelectedLabels($configurator.find('[data-step="1"]'), '.lc-card');
        var styles = getSelectedLabels($configurator.find('[data-step="3"]'), '.lc-card');
        var fixtureType = getSelectedLabels($configurator.find('[data-group="fixture-type"]'), '.lc-style');
        var fixtureMaterial = getSelectedLabels($configurator.find('[data-group="fixture-material"]'), '.lc-style');
        var lightTemp = getSelectedLabels($configurator.find('[data-group="light-temp"]'), '.lc-style');
        var sourceType = getSelectedLabels($configurator.find('[data-group="source-type"]'), '.lc-style');

        var dimsText = '-';
        if (area || heightCm) {
            dimsText = (area ? area + ' mp' : '-') + ' / ' + (heightCm ? heightCm + ' cm' : '-');
        }

        var lumens = calculateLumens(area, heightCm, wallTone);
        $configurator.find('[data-summary="lumens"]').text(lumens ? lumens.toLocaleString('ro-RO') + ' lm' : '-');
        $configurator.find('[data-summary="rooms"]').text(rooms.length ? rooms.join(', ') : '-');
        $configurator.find('[data-summary="dimensions"]').text(dimsText);
        $configurator.find('[data-summary="wall-tone"]').text(wallTone === 'dark' ? 'Culori închise' : wallTone === 'light' ? 'Culori deschise' : '-');
        $configurator.find('[data-summary="styles"]').text(styles.length ? styles.join(', ') : '-');
        $configurator.find('[data-summary="fixture-type"]').text(fixtureType.length ? fixtureType.join(', ') : '-');
        $configurator.find('[data-summary="fixture-material"]').text(fixtureMaterial.length ? fixtureMaterial.join(', ') : '-');
        $configurator.find('[data-summary="light-temp"]').text(lightTemp.length ? lightTemp.join(', ') : '-');
        $configurator.find('[data-summary="source-type"]').text(sourceType.length ? sourceType.join(', ') : '-');

        updateRecommendations($configurator);
    }

    function parseIds(value) {
        if (!value) {
            return [];
        }
        return value
            .toString()
            .split(',')
            .map(function (v) { return parseInt(v, 10); })
            .filter(function (v) { return !isNaN(v); });
    }

    function matchesAny(selected, available) {
        if (!selected.length) {
            return true;
        }
        for (var i = 0; i < selected.length; i++) {
            if (available.indexOf(selected[i]) !== -1) {
                return true;
            }
        }
        return false;
    }

    function updateRecommendations($configurator) {
        var rooms = $configurator.find('[data-step="1"] .lc-card.is-selected').map(function () {
            return parseInt($(this).data('term-id'), 10);
        }).get();
        var styles = $configurator.find('[data-step="3"] .lc-card.is-selected').map(function () {
            return parseInt($(this).data('term-id'), 10);
        }).get();
        var fixtureType = $configurator.find('[data-group="fixture-type"] .lc-style.is-selected').map(function () {
            return parseInt($(this).data('term-id'), 10);
        }).get();
        var materials = $configurator.find('[data-group="fixture-material"] .lc-style.is-selected').map(function () {
            return parseInt($(this).data('term-id'), 10);
        }).get();
        var sourceTypes = $configurator.find('[data-group="source-type"] .lc-style.is-selected').map(function () {
            return parseInt($(this).data('term-id'), 10);
        }).get();

        var $cards = $configurator.find('.lc-reco-card');
        $cards.each(function () {
            var $card = $(this);
            var productRooms = parseIds($card.data('room'));
            var productStyles = parseIds($card.data('style'));
            var productMaterials = parseIds($card.data('material'));
            var productSources = parseIds($card.data('source'));
            var productCategories = parseIds($card.data('category'));

            var visible = matchesAny(rooms, productRooms)
                && matchesAny(styles, productStyles)
                && matchesAny(materials, productMaterials)
                && matchesAny(sourceTypes, productSources)
                && matchesAny(fixtureType, productCategories);

            $card.toggleClass('is-hidden', !visible);
        });

        updateCarousel($configurator, 0);
    }

    function updateCarousel($configurator, startIndex) {
        var $visibleCards = $configurator.find('.lc-reco-card').not('.is-hidden');
        var total = $visibleCards.length;
        var index = Math.max(0, startIndex || 0);
        var maxStart = Math.max(0, total - 5);
        index = Math.min(index, maxStart);

        $configurator.data('carouselIndex', index);
        $visibleCards.removeClass('is-visible');
        $visibleCards.slice(index, index + 5).addClass('is-visible');
    }

    $(document).on('click', '.lc-card', function () {
        var $container = $(this).closest('.lc-card-grid');
        var isMulti = $container.is('[data-multi]');
        toggleSelection($container.find('.lc-card'), $(this), isMulti);
        updateSummary($(this).closest('.lc-configurator'));
    });

    $(document).on('click', '.lc-wall-option', function () {
        var $container = $(this).closest('.lc-wall-options');
        toggleSelection($container.find('.lc-wall-option'), $(this), false);
        updateSummary($(this).closest('.lc-configurator'));
    });

    $(document).on('click', '.lc-style', function () {
        var $container = $(this).closest('[data-group]');
        if (!$container.length) {
            $container = $(this).closest('.lc-style-strip');
        }
        var isMulti = $container.is('[data-multi]');
        toggleSelection($container.find('.lc-style'), $(this), isMulti);
        updateSummary($(this).closest('.lc-configurator'));
    });

    $(document).on('click', '.lc-next', function () {
        var $configurator = $(this).closest('.lc-configurator');
        var $currentStep = $(this).closest('.lc-step');
        var step = parseInt($currentStep.data('step'), 10);
        var totalSteps = $configurator.data('totalSteps') || 1;
        var nextStep = Math.min(step + 1, totalSteps);
        setActiveStep($configurator, nextStep);
    });

    $(document).on('click', '.lc-prev', function () {
        var $configurator = $(this).closest('.lc-configurator');
        var $currentStep = $(this).closest('.lc-step');
        var step = parseInt($currentStep.data('step'), 10);
        var prevStep = Math.max(step - 1, 1);
        setActiveStep($configurator, prevStep);
    });

    $(document).on('click', '.lc-step-dot', function () {
        var $configurator = $(this).closest('.lc-configurator');
        var step = parseInt($(this).data('step'), 10);
        setActiveStep($configurator, step);
    });

    $(document).on('input', '#lc_area, #lc_height', function () {
        updateSummary($(this).closest('.lc-configurator'));
    });

    $(document).on('click', '.lc-reco-prev', function () {
        var $configurator = $(this).closest('.lc-configurator');
        var index = $configurator.data('carouselIndex') || 0;
        updateCarousel($configurator, index - 1);
    });

    $(document).on('click', '.lc-reco-next', function () {
        var $configurator = $(this).closest('.lc-configurator');
        var index = $configurator.data('carouselIndex') || 0;
        updateCarousel($configurator, index + 1);
    });

    $(document).on('click', '.lc-reco-bulk', function () {
        var $configurator = $(this).closest('.lc-configurator');
        var $selected = $configurator.find('.lc-reco-select:checked').closest('.lc-reco-card');
        $selected.each(function () {
            var productId = $(this).data('product-id');
            if (!productId) {
                return;
            }
            var url = '/?add-to-cart=' + productId;
            fetch(url, { credentials: 'same-origin' });
        });
    });

    $(function () {
        $('.lc-configurator').each(function () {
            initConfigurator($(this));
            updateRecommendations($(this));
        });
    });
})(jQuery);

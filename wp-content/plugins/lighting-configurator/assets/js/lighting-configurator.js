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
        var countAttr = parseInt($configurator.data('visible-count'), 10);
        if (!isNaN(countAttr) && countAttr > 0) {
            $configurator[0].style.setProperty('--lc-reco-cols', countAttr);
        }
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


    function updateRecommendations($configurator) {
        if (typeof LightingConfiguratorData === 'undefined') {
            return;
        }

        var rooms = $configurator.find('[data-step="1"] .lc-card.is-selected').map(function () {
            return parseInt($(this).data('term-id'), 10);
        }).get();
        var styles = $configurator.find('[data-step="3"] .lc-card.is-selected').map(function () {
            return parseInt($(this).data('term-id'), 10);
        }).get();
        var categories = $configurator.find('[data-group="fixture-type"] .lc-style.is-selected').map(function () {
            return parseInt($(this).data('term-id'), 10);
        }).get();
        var materials = $configurator.find('[data-group="fixture-material"] .lc-style.is-selected').map(function () {
            return parseInt($(this).data('term-id'), 10);
        }).get();
        var sourceTypes = $configurator.find('[data-group="source-type"] .lc-style.is-selected').map(function () {
            return parseInt($(this).data('term-id'), 10);
        }).get();
        var temps = $configurator.find('[data-group="light-temp"] .lc-style.is-selected').map(function () {
            return $(this).text().trim().toLowerCase();
        }).get();

        $.ajax({
            url: LightingConfiguratorData.ajaxUrl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'lc_get_recommendations',
                nonce: LightingConfiguratorData.nonce,
                rooms: rooms,
                styles: styles,
                categories: categories,
                materials: materials,
                sources: sourceTypes,
                temps: temps,
            },
            success: function (response) {
                if (!response || !response.success) {
                    renderRecommendations($configurator, []);
                    renderComplementary($configurator, []);
                    return;
                }
                var data = response.data || {};
                renderRecommendations($configurator, data.recommended || []);
                renderComplementary($configurator, data.complementary || []);
            },
            error: function () {
                renderRecommendations($configurator, []);
                renderComplementary($configurator, []);
            },
        });
    }

    function renderRecommendations($configurator, items) {
        var $carousel = $configurator.find('.lc-reco-carousel');
        var $empty = $configurator.find('.lc-reco-empty');
        $carousel.empty();

        if (!items.length) {
            $empty.show();
            return;
        }

        $empty.hide();
        items.forEach(function (item) {
            var inCart = !!item.in_cart;
            var card = [
                '<article class="lc-reco-card', (inCart ? ' is-in-cart' : ''), '" data-product-id="', item.id, '" data-cart-key="', (item.cart_item_key || ''), '" data-add-to-cart="', item.add_to_cart, '">',
                (inCart ? '' : '<label class="lc-reco-check"><input type="checkbox" class="lc-reco-select" /><span></span></label>'),
                '<a class="lc-reco-thumb" href="', item.permalink, '">', item.thumbnail, '</a>',
                '<h5 class="lc-reco-name">', item.title, '</h5>',
                '<div class="lc-reco-actions"><a class="lc-reco-cart', (inCart ? ' lc-reco-remove' : ''), '" data-add-to-cart="', item.add_to_cart, '" href="', (inCart ? '#' : item.add_to_cart), '">', (inCart ? 'Remove from cart' : 'Add to cart'), '</a></div>',
                '</article>'
            ].join('');
            $carousel.append(card);
        });

        updateCarousel($carousel.closest('.lc-recommendations'), 0);
    }

    function renderComplementary($configurator, items) {
        var $carousel = $configurator.find('.lc-complementary-carousel');
        var $empty = $configurator.find('.lc-complementary-empty');
        if (!$carousel.length) {
            return;
        }
        $carousel.empty();

        if (!items.length) {
            $empty.show();
            return;
        }

        $empty.hide();
        items.forEach(function (item) {
            var inCart = !!item.in_cart;
            var card = [
                '<article class="lc-reco-card', (inCart ? ' is-in-cart' : ''), '" data-product-id="', item.id, '" data-cart-key="', (item.cart_item_key || ''), '" data-add-to-cart="', item.add_to_cart, '">',
                (inCart ? '' : '<label class="lc-reco-check"><input type="checkbox" class="lc-reco-select" /><span></span></label>'),
                '<a class="lc-reco-thumb" href="', item.permalink, '">', item.thumbnail, '</a>',
                '<h5 class="lc-reco-name">', item.title, '</h5>',
                '<div class="lc-reco-actions"><a class="lc-reco-cart', (inCart ? ' lc-reco-remove' : ''), '" data-add-to-cart="', item.add_to_cart, '" href="', (inCart ? '#' : item.add_to_cart), '">', (inCart ? 'Remove from cart' : 'Add to cart'), '</a></div>',
                '</article>'
            ].join('');
            $carousel.append(card);
        });

        updateCarousel($carousel.closest('.lc-recommendations'), 0);
    }

    function updateCarousel($scope, startIndex) {
        if (!$scope || !$scope.length) {
            return;
        }
        var $visibleCards = $scope.find('.lc-reco-card');
        var total = $visibleCards.length;
        var visibleCount = 4;
        var $configurator = $scope.closest('.lc-configurator');
        if ($configurator.length) {
            var countAttr = parseInt($configurator.data('visible-count'), 10);
            if (!isNaN(countAttr) && countAttr > 0) {
                visibleCount = countAttr;
            }
        }
        var index = Math.max(0, startIndex || 0);
        var maxStart = Math.max(0, total - visibleCount);
        index = Math.min(index, maxStart);

        $scope.data('carouselIndex', index);
        $visibleCards.removeClass('is-visible');
        $visibleCards.slice(index, index + visibleCount).addClass('is-visible');
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
        var $scope = $(this).closest('.lc-recommendations');
        var index = $scope.data('carouselIndex') || 0;
        updateCarousel($scope, index - 1);
    });

    $(document).on('click', '.lc-reco-next', function () {
        var $scope = $(this).closest('.lc-recommendations');
        var index = $scope.data('carouselIndex') || 0;
        updateCarousel($scope, index + 1);
    });

    $(document).on('click', '.lc-reco-bulk', function () {
        var $configurator = $(this).closest('.lc-configurator');
        var $selected = $configurator.find('.lc-reco-select:checked').closest('.lc-reco-card');
        $selected.each(function () {
            var productId = $(this).data('product-id');
            if (!productId) {
                return;
            }
            addToCartAjax(productId, $configurator, $(this));
        });
    });

    $(document).on('click', '.lc-reco-cart', function (e) {
        e.preventDefault();
        var $card = $(this).closest('.lc-reco-card');
        var productId = $card.data('product-id');
        if ($(this).hasClass('lc-reco-remove')) {
            var cartKey = $card.data('cart-key');
            if (!cartKey) {
                refreshCartState($card.closest('.lc-configurator'));
                return;
            }
            removeFromCartAjax(cartKey, $card.closest('.lc-configurator'), $card);
            return;
        }
        if (!productId) {
            return;
        }
        addToCartAjax(productId, $card.closest('.lc-configurator'), $card);
    });

    $(function () {
        $('.lc-configurator').each(function () {
            initConfigurator($(this));
            updateRecommendations($(this));
        });
    });

    function addToCartAjax(productId, $configurator, $card) {
        if (typeof wc_add_to_cart_params === 'undefined') {
            window.location.href = '/?add-to-cart=' + productId;
            return;
        }

        if ($card && $card.length) {
            setCardPendingInCart($card);
        }

        $.ajax({
            type: 'POST',
            url: wc_add_to_cart_params.wc_ajax_url.replace('%%endpoint%%', 'add_to_cart'),
            data: { product_id: productId, quantity: 1 },
            success: function (response) {
                if (!response) {
                    return;
                }
                if (response.error && response.product_url) {
                    window.location = response.product_url;
                    return;
                }
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);
                refreshCartState($configurator);
            },
        });
    }

    function removeFromCartAjax(cartKey, $configurator, $card) {
        if (typeof wc_add_to_cart_params === 'undefined') {
            return;
        }

        if ($card && $card.length) {
            setCardNotInCart($card);
        }

        $.ajax({
            type: 'POST',
            url: wc_add_to_cart_params.wc_ajax_url.replace('%%endpoint%%', 'remove_from_cart'),
            data: { cart_item_key: cartKey },
            success: function (response) {
                if (!response) {
                    return;
                }
                $(document.body).trigger('removed_from_cart', [response.fragments, response.cart_hash]);
                refreshCartState($configurator);
            },
        });
    }

    function refreshCartState($configurator) {
        if (!$configurator || !$configurator.length) {
            return;
        }
        $.ajax({
            type: 'POST',
            url: lightingConfigurator.ajaxUrl,
            data: {
                action: 'lc_get_cart_map',
                nonce: lightingConfigurator.nonce,
            },
            success: function (response) {
                if (!response || !response.success) {
                    return;
                }
                var map = (response.data && response.data.cart_map) ? response.data.cart_map : {};
                applyCartState($configurator, map);
            },
        });
    }

    function applyCartState($configurator, cartMap) {
        $configurator.find('.lc-reco-card').each(function () {
            var $card = $(this);
            var productId = $card.data('product-id');
            var cartKey = cartMap && cartMap[productId] ? cartMap[productId] : '';
            if (cartKey) {
                setCardInCart($card, cartKey);
            } else {
                setCardNotInCart($card);
            }
        });
    }

    function setCardInCart($card, cartKey) {
        $card.addClass('is-in-cart');
        $card.attr('data-cart-key', cartKey);
        $card.find('.lc-reco-check').remove();
        var $btn = $card.find('.lc-reco-cart');
        if (!$btn.length) {
            $btn = $('<a class="lc-reco-cart"></a>').appendTo($card.find('.lc-reco-actions'));
        }
        $btn.addClass('lc-reco-remove').attr('href', '#').text('Remove from cart');
    }

    function setCardPendingInCart($card) {
        setCardInCart($card, '');
        $card.addClass('is-pending-cart');
    }

    function setCardNotInCart($card) {
        $card.removeClass('is-in-cart');
        $card.removeClass('is-pending-cart');
        $card.attr('data-cart-key', '');
        if (!$card.find('.lc-reco-check').length) {
            $card.prepend('<label class="lc-reco-check"><input type="checkbox" class="lc-reco-select" /><span></span></label>');
        }
        var $btn = $card.find('.lc-reco-cart');
        if (!$btn.length) {
            $btn = $('<a class="lc-reco-cart"></a>').appendTo($card.find('.lc-reco-actions'));
        }
        var addToCartUrl = $card.data('add-to-cart') || $btn.data('add-to-cart') || '#';
        $btn.removeClass('lc-reco-remove').attr('href', addToCartUrl).text('Add to cart');
    }
})(jQuery);

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
    }

    function toggleSelection($group, $target) {
        $group.removeClass('is-selected');
        $target.addClass('is-selected');
    }

    function initConfigurator($configurator) {
        $configurator.data('totalSteps', $configurator.find('.lc-step').length);
        setActiveStep($configurator, 1);
    }

    $(document).on('click', '.lc-card', function () {
        toggleSelection($(this).closest('.lc-card-grid').find('.lc-card'), $(this));
    });

    $(document).on('click', '.lc-style', function () {
        toggleSelection($(this).closest('.lc-style-strip').find('.lc-style'), $(this));
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

    $(function () {
        $('.lc-configurator').each(function () {
            initConfigurator($(this));
        });
    });
})(jQuery);

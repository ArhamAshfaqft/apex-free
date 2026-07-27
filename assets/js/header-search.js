(function ($) {
    'use strict';

    var handleHeaderSearch = function ($scope, $) {
        var $expandable = $scope.find('.eas-search-expandable');
        var $overlay = $scope.find('.eas-search-overlay-container');
        
        if ($expandable.length > 0) {
            var $trigger = $expandable.find('.eas-search-trigger');
            var $input = $expandable.find('input.search-field');

            $trigger.on('click', function (e) {
                // If input is closed, open it and focus
                if (!$expandable.hasClass('eas-active')) {
                    e.preventDefault();
                    $expandable.addClass('eas-active');
                    $input.focus();
                } else {
                    // If input is open and empty, close it on click
                    if ($input.val().trim() === '') {
                        e.preventDefault();
                        $expandable.removeClass('eas-active');
                    }
                    // Otherwise let the form submit (or do nothing if it is normal button behavior)
                }
            });

            // Close on click outside
            $(document).on('click.easHeaderSearchExpand', function (e) {
                if (!$(e.target).closest($expandable).length) {
                    $expandable.removeClass('eas-active');
                }
            });
        }

        if ($overlay.length > 0) {
            var $triggerBtn = $scope.find('.eas-search-trigger');
            var $closeBtn = $overlay.find('.eas-search-overlay-close');
            var $overlayInput = $overlay.find('input.search-field');

            $triggerBtn.on('click', function (e) {
                e.preventDefault();
                $overlay.addClass('eas-open');
                $('body').addClass('eas-search-prevent-scroll');
                setTimeout(function () {
                    $overlayInput.focus();
                }, 100);
            });

            var closeOverlay = function () {
                $overlay.removeClass('eas-open');
                $('body').removeClass('eas-search-prevent-scroll');
            };

            $closeBtn.on('click', function (e) {
                e.preventDefault();
                closeOverlay();
            });

            $overlay.on('click', function (e) {
                if ($(e.target).is($overlay)) {
                    closeOverlay();
                }
            });

            $(document).on('keydown.easHeaderSearchOverlay', function (e) {
                if (e.key === 'Escape' && $overlay.hasClass('eas-open')) {
                    closeOverlay();
                }
            });
        }

        // Save cleanup references
        $scope.data('eas-cleanup-func', function () {
            $(document).off('click.easHeaderSearchExpand');
            $(document).off('keydown.easHeaderSearchOverlay');
            $('body').removeClass('eas-search-prevent-scroll');
        });
    };

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/eas-header-search.default', handleHeaderSearch);
    });

})(jQuery);

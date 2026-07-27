(function ($) {
    'use strict';

    var handleNavMenu = function ($scope, $) {
        var $wrapper = $scope.find('.eas-nav-menu-wrapper');
        var $menu = $scope.find('.eas-nav-menu');
        var $list = $scope.find('.eas-nav-menu-list');
        var $toggle = $scope.find('.eas-menu-toggle');
        var $overlay = $scope.find('.eas-nav-menu-overlay');
        var $mobileContainer = $scope.find('.eas-mobile-menu-container');
        var $closeBtn = $scope.find('.eas-menu-close');
        var isClickTrigger = $menu.hasClass('eas-trigger-click');

        // 1. Sliding Indicator Logic (for premium menu hover lines/backgrounds)
        var hasSlidingIndicator = $menu.hasClass('eas-pointer-slide-line') || $menu.hasClass('eas-pointer-slide-bg');
        if (hasSlidingIndicator && $list.length > 0) {
            // Append indicator dynamically inside the UL for perfect absolute positioning relative to LI items
            var $indicator = $list.find('.eas-menu-indicator');
            if ($indicator.length === 0) {
                $list.append('<span class="eas-menu-indicator"></span>');
                $indicator = $list.find('.eas-menu-indicator');
            }

            var $topLevelItems = $list.find('> li > a');
            var $activeItem = $list.find('> li.current-menu-item > a, > li.current-menu-parent > a, > li.current_page_item > a');

            var updateIndicator = function ($el) {
                if (!$el || $el.length === 0) {
                    $indicator.css({ opacity: 0 });
                    return;
                }
                var parentLi = $el.parent();
                var width = parentLi.outerWidth();
                var left = parentLi.position().left;
                var height = parentLi.outerHeight();
                var top = parentLi.position().top;

                $indicator.css({
                    opacity: 1,
                    width: width + 'px',
                    left: left + 'px',
                    top: top + 'px',
                    height: $menu.hasClass('eas-pointer-slide-bg') ? height + 'px' : ''
                });
            };

            // Set initial position after render / fonts loaded
            setTimeout(function() {
                if ($activeItem.length > 0) {
                    updateIndicator($activeItem);
                }
            }, 100);

            $topLevelItems.on('mouseenter focusin', function () {
                updateIndicator($(this));
            });

            $list.on('mouseleave focusout', function () {
                if ($activeItem.length > 0) {
                    updateIndicator($activeItem);
                } else {
                    $indicator.css({ opacity: 0 });
                }
            });

            // Re-update on window resize
            $(window).on('resize.easNavMenu', function () {
                if ($activeItem.length > 0) {
                    updateIndicator($activeItem);
                }
            });
        }

        // 2. Click Trigger for Desktop Submenus (if enabled)
        if (isClickTrigger) {
            $menu.find('.menu-item-has-children > a').on('click', function (e) {
                var $parent = $(this).parent();
                // Only prevent default and toggle if we have a submenu
                if ($parent.find('> ul.sub-menu').length > 0) {
                    e.preventDefault();
                    var isOpen = $parent.hasClass('eas-submenu-open');
                    // Close others
                    $parent.siblings('.menu-item-has-children').removeClass('eas-submenu-open');
                    if (isOpen) {
                        $parent.removeClass('eas-submenu-open');
                    } else {
                        $parent.addClass('eas-submenu-open');
                    }
                }
            });

            // Close on document click
            $(document).on('click.easNavMenuClose', function (e) {
                if (!$(e.target).closest('.eas-nav-menu').length) {
                    $menu.find('.menu-item-has-children').removeClass('eas-submenu-open');
                }
            });
        }

        // 3. Mobile Toggle / Drawer Logic
        var openMobileMenu = function () {
            $toggle.addClass('eas-active');
            if ($mobileContainer.hasClass('eas-layout-dropdown')) {
                $mobileContainer.stop(true, true).slideDown(350);
            } else {
                $mobileContainer.addClass('eas-open');
                $overlay.addClass('eas-open');
                $('body').addClass('eas-nav-menu-prevent-scroll');
            }
        };

        var closeMobileMenu = function () {
            $toggle.removeClass('eas-active');
            if ($mobileContainer.hasClass('eas-layout-dropdown')) {
                $mobileContainer.stop(true, true).slideUp(300);
            } else {
                $mobileContainer.removeClass('eas-open');
                $overlay.removeClass('eas-open');
                $('body').removeClass('eas-nav-menu-prevent-scroll');
            }
        };

        $toggle.on('click', function (e) {
            e.preventDefault();
            if ($toggle.hasClass('eas-active')) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });

        $closeBtn.on('click', function (e) {
            e.preventDefault();
            closeMobileMenu();
        });

        $overlay.on('click', function (e) {
            e.preventDefault();
            closeMobileMenu();
        });

        // 4. Mobile Submenu Accordion
        $scope.find('.eas-nav-menu-mobile .menu-item-has-children > a').on('click', function (e) {
            var $link = $(this);
            var $parent = $link.parent();
            var $submenu = $link.siblings('.sub-menu');

            if ($submenu.length > 0) {
                // If it contains a submenu, toggle it
                e.preventDefault();
                if ($parent.hasClass('eas-submenu-open')) {
                    $parent.removeClass('eas-submenu-open');
                    $submenu.slideUp(250);
                } else {
                    $parent.addClass('eas-submenu-open');
                    $submenu.slideDown(250);
                }
            }
        });

        // Save cleanup references
        $scope.data('eas-cleanup-func', function () {
            $(window).off('resize.easNavMenu');
            $(document).off('click.easNavMenuClose');
            $('body').removeClass('eas-nav-menu-prevent-scroll');
        });
    };

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/eas-nav-menu.default', handleNavMenu);
    });

})(jQuery);

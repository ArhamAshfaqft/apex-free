(function ($) {
	'use strict';

	/**
	 * Initialize Flex Accordion Widget
	 */
	function initFlexAccordion($scope) {
		var $wrap = $scope.find('.eas-flex-accordion');
		if (!$wrap.length) return;

		// Clean up previous event bindings and data to prevent editor memory leaks
		var existingCleanup = $wrap.data('eas-accordion-cleanup');
		if (typeof existingCleanup === 'function') {
			existingCleanup();
		}

		var $items = $wrap.find('.eas-flex-accordion-item');
		var triggerMode = $wrap.data('trigger-mode') || 'hover';

		/**
		 * Set active/expanded class on a card item
		 */
		function setActiveCard($activeItem) {
			$items.removeClass('eas-active');
			$activeItem.addClass('eas-active');
		}

		// ── Trigger Configuration ──────────────────────────────────────────
		if (triggerMode === 'click') {
			// Click Action Mode
			$items.on('click.easAccordion', function (e) {
				// Allow clicks on links/buttons to pass through without just toggling the card
				if ($(e.target).closest('.eas-flex-accordion-btn').length) {
					return;
				}
				e.preventDefault();
				setActiveCard($(this));
			});
		} else {
			// Hover Action Mode
			$items.on('mouseenter.easAccordion', function () {
				setActiveCard($(this));
			});

			// Mobile touch-click fallback (since hover events do not exist on screens)
			$items.on('click.easAccordion', function (e) {
				if ($(e.target).closest('.eas-flex-accordion-btn').length) {
					return;
				}
				
				// Only toggle on touch screens if not already active
				if (window.innerWidth <= 768 || window.matchMedia('(pointer: coarse)').matches) {
					if (!$(this).hasClass('eas-active')) {
						e.preventDefault();
						setActiveCard($(this));
					}
				}
			});
		}

		// Cleanup handler
		var cleanup = function () {
			$items.off('.easAccordion');
			$wrap.removeData('eas-accordion-cleanup');
		};

		$wrap.data('eas-accordion-cleanup', cleanup);
	}

	// ── Elementor Hook Initialization ─────────────────────────────────────
	$(window).on('elementor/frontend/init', function () {
		if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
			elementorFrontend.hooks.addAction(
				'frontend/element_ready/eas-flex-accordion.default',
				initFlexAccordion
			);
		}
	});

	// Standard document ready fallback
	$(document).ready(function () {
		$('.eas-flex-accordion').each(function () {
			var $widget = $(this).closest('.elementor-widget-eas-flex-accordion');
			if ($widget.length) {
				initFlexAccordion($widget);
			}
		});
	});

})(jQuery);

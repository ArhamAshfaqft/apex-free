/**
 * Interactive Image Hotspots Widget Script
 *
 * Handles hotspot click/hover triggers, ARIA keyboard accessibility,
 * auto-closing tooltips, smart viewport AND container overflow positioning, and complete event cleanup.
 *
 * @package ApexAddonsForElementor
 */

(function ($) {
	'use strict';

	/**
	 * Main Hotspots Handler
	 *
	 * @param {jQuery} $scope Widget scope
	 */
	var ApexadfoHotspotsHandler = function ($scope) {
		var $wrapper = $scope.find('.apexadfo-hotspots-wrapper');
		if (!$wrapper.length) {
			return;
		}

		var wrapper = $wrapper[0];
		var container = wrapper.querySelector('.apexadfo-hotspots-container');
		if (!container) {
			return;
		}

		var triggerMode = container.getAttribute('data-trigger') || 'click';
		var autoClose = container.getAttribute('data-auto-close') !== 'no';
		var items = container.querySelectorAll('.apexadfo-hotspot-item');

		var cleanupListeners = [];

		/**
		 * Close all open tooltips in this container
		 */
		function closeAllTooltips() {
			items.forEach(function (item) {
				item.classList.remove('apexadfo-is-active');
				var pin = item.querySelector('.apexadfo-hotspot-pin');
				if (pin) {
					pin.setAttribute('aria-expanded', 'false');
				}
			});
		}

		/**
		 * Adjust tooltip placement if it overflows screen boundaries or container boundaries
		 *
		 * @param {Element} item
		 */
		function adjustTooltipPlacement(item) {
			var tooltip = item.querySelector('.apexadfo-hotspot-tooltip');
			if (!tooltip) {
				return;
			}

			// Reset inline adjustments
			tooltip.style.left = '';
			tooltip.style.right = '';

			var rect = tooltip.getBoundingClientRect();
			var containerRect = container.getBoundingClientRect();
			var viewportWidth = window.innerWidth || document.documentElement.clientWidth;
			var viewportHeight = window.innerHeight || document.documentElement.clientHeight;

			// Top & Bottom boundary check: flip position if near screen edge OR container edge
			var minTopThreshold = Math.max(10, containerRect.top);
			var maxBottomThreshold = Math.min(viewportHeight - 10, containerRect.bottom);

			if (rect.top < minTopThreshold && tooltip.classList.contains('apexadfo-tooltip-top')) {
				tooltip.classList.remove('apexadfo-tooltip-top');
				tooltip.classList.add('apexadfo-tooltip-bottom');
				rect = tooltip.getBoundingClientRect();
			} else if (rect.bottom > maxBottomThreshold && tooltip.classList.contains('apexadfo-tooltip-bottom')) {
				tooltip.classList.remove('apexadfo-tooltip-bottom');
				tooltip.classList.add('apexadfo-tooltip-top');
				rect = tooltip.getBoundingClientRect();
			}

			// Right edge overflow check (viewport AND container boundary)
			var maxRight = Math.min(viewportWidth - 10, containerRect.right);
			if (rect.right > maxRight) {
				var overflowRight = rect.right - maxRight;
				tooltip.style.left = 'calc(50% - ' + overflowRight + 'px)';
			}

			// Left edge overflow check (viewport AND container boundary)
			var minLeft = Math.max(10, containerRect.left);
			if (rect.left < minLeft) {
				var overflowLeft = minLeft - rect.left;
				tooltip.style.left = 'calc(50% + ' + overflowLeft + 'px)';
			}
		}

		/**
		 * Toggle specific hotspot item
		 *
		 * @param {Element} item
		 */
		function toggleTooltip(item) {
			var isActive = item.classList.contains('apexadfo-is-active');
			var pin = item.querySelector('.apexadfo-hotspot-pin');

			if (isActive) {
				item.classList.remove('apexadfo-is-active');
				if (pin) {
					pin.setAttribute('aria-expanded', 'false');
				}
			} else {
				if (autoClose) {
					closeAllTooltips();
				}
				item.classList.add('apexadfo-is-active');
				if (pin) {
					pin.setAttribute('aria-expanded', 'true');
				}
				adjustTooltipPlacement(item);
			}
		}

		/**
		 * Open specific hotspot item
		 *
		 * @param {Element} item
		 */
		function openTooltip(item) {
			if (autoClose) {
				closeAllTooltips();
			}
			item.classList.add('apexadfo-is-active');
			var pin = item.querySelector('.apexadfo-hotspot-pin');
			if (pin) {
				pin.setAttribute('aria-expanded', 'true');
			}
			adjustTooltipPlacement(item);
		}

		// Attach listeners to items
		items.forEach(function (item) {
			var pin = item.querySelector('.apexadfo-hotspot-pin');
			var closeBtn = item.querySelector('.apexadfo-tooltip-close');
			if (!pin) {
				return;
			}

			if (triggerMode === 'hover') {
				var onMouseEnter = function () {
					openTooltip(item);
				};
				var onMouseLeave = function () {
					item.classList.remove('apexadfo-is-active');
					pin.setAttribute('aria-expanded', 'false');
				};

				item.addEventListener('mouseenter', onMouseEnter);
				item.addEventListener('mouseleave', onMouseLeave);

				cleanupListeners.push(function () {
					item.removeEventListener('mouseenter', onMouseEnter);
					item.removeEventListener('mouseleave', onMouseLeave);
				});
			} else {
				var onPinClick = function (e) {
					e.preventDefault();
					e.stopPropagation();
					toggleTooltip(item);
				};

				pin.addEventListener('click', onPinClick);

				cleanupListeners.push(function () {
					pin.removeEventListener('click', onPinClick);
				});
			}

			// Touch close button handler
			if (closeBtn) {
				var onCloseClick = function (e) {
					e.preventDefault();
					e.stopPropagation();
					item.classList.remove('apexadfo-is-active');
					pin.setAttribute('aria-expanded', 'false');
				};

				closeBtn.addEventListener('click', onCloseClick);

				cleanupListeners.push(function () {
					closeBtn.removeEventListener('click', onCloseClick);
				});
			}

			// ARIA Keyboard navigation on pin focus
			var onPinKeyDown = function (e) {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					toggleTooltip(item);
				} else if (e.key === 'Escape') {
					if (item.classList.contains('apexadfo-is-active')) {
						item.classList.remove('apexadfo-is-active');
						pin.setAttribute('aria-expanded', 'false');
						pin.focus();
					}
				}
			};

			pin.addEventListener('keydown', onPinKeyDown);

			cleanupListeners.push(function () {
				pin.removeEventListener('keydown', onPinKeyDown);
			});
		});

		// Outside click to close all active tooltips
		function onDocumentClick(e) {
			if (!container.contains(e.target)) {
				closeAllTooltips();
			}
		}

		if (triggerMode === 'click') {
			document.addEventListener('click', onDocumentClick);
			cleanupListeners.push(function () {
				document.removeEventListener('click', onDocumentClick);
			});
		}

		// Store complete cleanup handler for Elementor editor re-render
		$scope.data('apexadfoHotspotsDestroy', function () {
			cleanupListeners.forEach(function (cleanup) {
				if (typeof cleanup === 'function') {
					cleanup();
				}
			});
		});
	};

	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction(
			'frontend/element_ready/eas-interactive-image-hotspots.default',
			function ($scope) {
				var destroy = $scope.data('apexadfoHotspotsDestroy');
				if (typeof destroy === 'function') {
					destroy();
				}
				ApexadfoHotspotsHandler($scope);
			}
		);
	});
})(jQuery);

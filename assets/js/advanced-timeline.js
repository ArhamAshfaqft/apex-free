/**
 * Advanced Timeline Widget Script
 *
 * Scroll-driven connector progress line fill & IntersectionObserver node illumination.
 * Compatible with Elementor Editor & Frontend live instances.
 *
 * @package ApexAddonsForElementor
 */

(function ($) {
	'use strict';

	/**
	 * Main Timeline Handler
	 *
	 * @param {jQuery} $scope Widget scope
	 */
	var ApexadfoTimelineHandler = function ($scope) {
		var $wrapper = $scope.find('.apexadfo-timeline-wrapper');
		if (!$wrapper.length) {
			return;
		}

		var container = $wrapper.find('.apexadfo-timeline-container')[0];
		if (!container) {
			return;
		}

		var items = container.querySelectorAll('.apexadfo-timeline-item');
		var progressLine = container.querySelector('.apexadfo-timeline-line-progress');
		var isHorizontal = container.classList.contains('apexadfo-layout-horizontal');
		var observer = null;
		var animationFrameId = null;

		/**
		 * Calculate scroll progress and update connector line fill
		 */
		function updateProgress() {
			if (!progressLine) {
				return;
			}

			var rect = container.getBoundingClientRect();
			var windowHeight = window.innerHeight || document.documentElement.clientHeight;

			if (isHorizontal) {
				var scrollLeft = container.scrollLeft;
				var maxScrollLeft = container.scrollWidth - container.clientWidth;
				var pctH = maxScrollLeft > 0 ? (scrollLeft / maxScrollLeft) * 100 : 0;
				progressLine.style.width = Math.min(100, Math.max(0, pctH)) + '%';
			} else {
				// Vertical scroll progress calculation
				var scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
				var docHeight = document.documentElement.scrollHeight || document.body.scrollHeight;
				var containerTop = rect.top + scrollPosition;
				var containerHeight = rect.height;

				// Start line fill when top of container reaches 70% of screen height
				var startScroll = containerTop - (windowHeight * 0.7);
				// Reach 100% line fill when bottom of container reaches 75% of screen height
				var endScroll = (containerTop + containerHeight) - (windowHeight * 0.75);

				var pctV = 0;
				// Force 100% if user reaches bottom of page or past end of timeline container
				if (scrollPosition + windowHeight >= docHeight - 15 || scrollPosition >= endScroll) {
					pctV = 100;
				} else if (scrollPosition <= startScroll) {
					pctV = 0;
				} else if (endScroll > startScroll) {
					pctV = ((scrollPosition - startScroll) / (endScroll - startScroll)) * 100;
				}

				pctV = Math.min(100, Math.max(0, pctV));

				if (animationFrameId) {
					cancelAnimationFrame(animationFrameId);
				}

				animationFrameId = requestAnimationFrame(function () {
					progressLine.style.height = pctV + '%';
				});
			}
		}

		/**
		 * IntersectionObserver to highlight items as they enter viewport
		 */
		if (typeof IntersectionObserver !== 'undefined' && items.length) {
			observer = new IntersectionObserver(
				function (entries) {
					entries.forEach(function (entry) {
						if (entry.isIntersecting) {
							entry.target.classList.add('apexadfo-is-active');
						}
					});
				},
				{
					root: isHorizontal ? container : null,
					rootMargin: '0px 0px -15% 0px',
					threshold: 0.2,
				}
			);

			items.forEach(function (item) {
				observer.observe(item);
			});
		} else {
			// Fallback if IntersectionObserver is unsupported
			items.forEach(function (item) {
				item.classList.add('apexadfo-is-active');
			});
		}

		// Scroll listeners for progress line
		if (isHorizontal) {
			container.addEventListener('scroll', updateProgress, { passive: true });
		} else {
			window.addEventListener('scroll', updateProgress, { passive: true });
			window.addEventListener('resize', updateProgress, { passive: true });
		}

		// Initial progress update
		updateProgress();

		// Cleanup handler for Elementor editor re-render
		$scope.data('apexadfoTimelineDestroy', function () {
			if (observer) {
				observer.disconnect();
			}
			if (isHorizontal) {
				container.removeEventListener('scroll', updateProgress);
			} else {
				window.removeEventListener('scroll', updateProgress);
				window.removeEventListener('resize', updateProgress);
			}
			if (animationFrameId) {
				cancelAnimationFrame(animationFrameId);
			}
		});
	};

	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction(
			'frontend/element_ready/eas-advanced-timeline.default',
			function ($scope) {
				var destroy = $scope.data('apexadfoTimelineDestroy');
				if (typeof destroy === 'function') {
					destroy();
				}
				ApexadfoTimelineHandler($scope);
			}
		);
	});
})(jQuery);

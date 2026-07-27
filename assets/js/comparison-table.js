/**
 * Comparison Table Widget Script
 *
 * Interactive tooltip trigger handling for mouse & touch devices.
 * Compatible with Elementor Editor & Frontend live instances.
 *
 * @package ApexAddonsForElementor
 */

(function ($) {
	'use strict';

	/**
	 * Main Comparison Table Handler
	 *
	 * @param {jQuery} $scope Widget scope
	 */
	var ApexadfoComparisonTableHandler = function ($scope) {
		var $wrapper = $scope.find('.apexadfo-comparison-table-wrapper');
		if (!$wrapper.length) {
			return;
		}

		var container = $wrapper[0];
		var tooltips = container.querySelectorAll('.apexadfo-tooltip-wrap');

		/**
		 * Toggle tooltip on click / touch tap
		 */
		function onTooltipClick(e) {
			e.stopPropagation();
			var currentWrap = this;
			var isActive = currentWrap.classList.contains('apexadfo-is-active');

			// Close all other open tooltips
			tooltips.forEach(function (wrap) {
				if (wrap !== currentWrap) {
					wrap.classList.remove('apexadfo-is-active');
				}
			});

			if (isActive) {
				currentWrap.classList.remove('apexadfo-is-active');
			} else {
				currentWrap.classList.add('apexadfo-is-active');
			}
		}

		/**
		 * Close active tooltips when clicking outside
		 */
		function onDocumentClick(e) {
			tooltips.forEach(function (wrap) {
				if (!wrap.contains(e.target)) {
					wrap.classList.remove('apexadfo-is-active');
				}
			});
		}

		// Bind click events on tooltips
		tooltips.forEach(function (wrap) {
			wrap.addEventListener('click', onTooltipClick);
		});

		document.addEventListener('click', onDocumentClick);

		// Cleanup handler for Elementor editor re-render
		$scope.data('apexadfoComparisonTableDestroy', function () {
			tooltips.forEach(function (wrap) {
				wrap.removeEventListener('click', onTooltipClick);
			});
			document.removeEventListener('click', onDocumentClick);
		});
	};

	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction(
			'frontend/element_ready/eas-comparison-table.default',
			function ($scope) {
				var destroy = $scope.data('apexadfoComparisonTableDestroy');
				if (typeof destroy === 'function') {
					destroy();
				}
				ApexadfoComparisonTableHandler($scope);
			}
		);
	});
})(jQuery);

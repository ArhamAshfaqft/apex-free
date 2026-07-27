/**
 * APEX FREE - Container Carousel Editor Preview Handler
 *
 * Runs inside the Elementor Editor preview iframe.
 * Dynamically handles toggling the layout classes and vertical states on/off without DOM cloning or wrapping.
 */
(function ($) {
	'use strict';

	$(window).on('elementor/frontend/init', function () {

		function handleScope($scope) {
			var modelCID = $scope.data('model-cid');
			if (!modelCID) return;

			// Access the Backbone model for this element
			var model = null;
			try {
				var elementData = elementorFrontend.config.elements;
				if (elementData && elementData.data) {
					model = elementData.data[modelCID];
				}
			} catch (e) {}
			if (!model || !model.attributes) return;

			var settings = model.attributes.settings
				? model.attributes.settings.attributes
				: model.attributes;

			// Apply initial state
			applyCarouselState($scope, settings);

			// Watch for live changes
			var settingsModel = model.attributes.settings || model;

			// Unbind any previous listeners for these controls to prevent memory leaks and duplicates
			settingsModel.off('change:eas_container_carousel');
			settingsModel.off('change:eas_carousel_pause_on_hover');
			settingsModel.off('change:eas_carousel_direction');

			// Re-bind listeners
			settingsModel.on('change:eas_container_carousel change:eas_carousel_pause_on_hover change:eas_carousel_direction', function () {
				var s = settingsModel.attributes;
				applyCarouselState($scope, s);
			});
		}

		function applyCarouselState($scope, settings) {
			var carouselActive = settings['eas_container_carousel'] || 'no';

			if (carouselActive === 'yes') {
				$scope.addClass('eas-carousel-editor-preview');
				$scope.addClass('eas-container-carousel-active');
				
				// Set pause on hover attribute for preview
				var pauseOnHover = settings['eas_carousel_pause_on_hover'] || 'yes';
				$scope.attr('data-eas-pause-on-hover', pauseOnHover);

				var $contentWrapper = $scope.find('> .e-con-inner, > .elementor-container');
				if (!$contentWrapper.length) {
					$contentWrapper = $scope;
				}

				// Check direction for vertical preview
				var direction = settings['eas_carousel_direction'] || 'rtl';
				var isVertical = (direction === 'btt' || direction === 'ttb');

				if (isVertical) {
					$scope.addClass('eas-carousel-vertical');
					$scope[0].style.setProperty('--eas-carousel-slide-width', '100%');
				} else {
					$scope.removeClass('eas-carousel-vertical');
					// Calculate and set default slide width percentage based on child count
					var count = $contentWrapper.children('.elementor-element, .elementor-widget').length;
					if (count > 0) {
						$scope[0].style.setProperty('--eas-carousel-slide-width', (100 / count) + '%');
					}
				}
			} else {
				$scope.removeClass('eas-carousel-editor-preview');
				$scope.removeClass('eas-container-carousel-active');
				$scope.removeClass('eas-carousel-vertical');
				$scope.removeAttr('data-eas-pause-on-hover');
				if ($scope[0]) {
					$scope[0].style.removeProperty('--eas-carousel-slide-width');
				}
			}
		}

		elementorFrontend.hooks.addAction('frontend/element_ready/container', handleScope);
	});

})(jQuery);

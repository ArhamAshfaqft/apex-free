/**
 * APEX - Container Carousel Engine (Swiper Engine)
 *
 * Dynamically initializes Swiper carousel on the frontend, supporting linear autoplay,
 * instant hover-pause, and grab-to-drag swiping in both horizontal and vertical directions.
 */
(function ($) {
	'use strict';

	function initContainerCarousel($scope) {
		// Do not initialize Swiper in the editor!
		if ($('body').hasClass('elementor-editor-active') || (typeof elementorFrontend !== 'undefined' && elementorFrontend.isEditMode())) {
			return;
		}

		var scopeId = $scope.data('id') || Math.random().toString(36).substr(2, 9);
		var resizeEventName = 'resize.easCarousel' + scopeId;
		$(window).off(resizeEventName);

		// Responsive: skip on tablet/mobile if disabled
		var w = window.innerWidth;
		var disableMobile = $scope.attr('data-eas-carousel-disable-mobile') === 'yes';
		var disableTablet = $scope.attr('data-eas-carousel-disable-tablet') === 'yes';
		var shouldDisable = (disableMobile && w < 768) || (disableTablet && w < 1025);

		// Listen to resize to handle dynamic enable/disable (e.g. inside Elementor Editor device preview changes)
		$(window).on(resizeEventName, function () {
			var currentW = window.innerWidth;
			var nowDisable = (disableMobile && currentW < 768) || (disableTablet && currentW < 1025);
			var isCurrentlyInit = $scope.data('eas-swiper-instance') ? true : false;

			if (nowDisable && isCurrentlyInit) {
				destroyContainerCarousel($scope);
			} else if (!nowDisable && !isCurrentlyInit) {
				initContainerCarousel($scope);
			}
		});

		if (shouldDisable) {
			return;
		}

		// 1. Read config
		var configRaw = $scope.attr('data-eas-carousel-config');
		if (!configRaw) return;

		var config;
		try {
			config = JSON.parse(configRaw);
		} catch (e) {
			return;
		}

		// Safeguard: Do not run carousel on containers that are nested inside another active carousel
		if ($scope.parents('.eas-container-carousel-active').length || $scope.closest('.eas-carousel-swiper').length) {
			return;
		}

		// 2. Find content wrapper
		var $contentWrapper = $scope.find('> .e-con-inner, > .elementor-container');
		if (!$contentWrapper.length) {
			$contentWrapper = $scope;
		}

		// Clean up any previous carousel structure
		destroyContainerCarousel($scope);

		// 3. Gather original children
		var $children = $contentWrapper.children('.elementor-element, .elementor-widget');
		if (!$children.length) return;

		var direction = config.direction || 'rtl';
		var isVertical = (direction === 'btt' || direction === 'ttb');

		// Set default slide width percentage based on child count on the wrapper
		if (isVertical) {
			$scope[0].style.setProperty('--eas-carousel-slide-width', '100%');
		} else {
			var defaultSlideWidth = (100 / $children.length) + '%';
			$scope[0].style.setProperty('--eas-carousel-slide-width', defaultSlideWidth);

			// Map custom responsive slides widths if set
			var slidesDesktop = parseFloat(config.slidesDesktop) || 4;
			var slidesTablet  = parseFloat(config.slidesTablet) || slidesDesktop;
			var slidesMobile  = parseFloat(config.slidesMobile) || slidesTablet;

			// We use 15px spaceBetween slide margin
			$scope[0].style.setProperty('--eas-carousel-width-desktop', 'calc((100% - ' + (15 * (slidesDesktop - 1)) + 'px) / ' + slidesDesktop + ')');
			$scope[0].style.setProperty('--eas-carousel-width-tablet', 'calc((100% - ' + (15 * (slidesTablet - 1)) + 'px) / ' + slidesTablet + ')');
			$scope[0].style.setProperty('--eas-carousel-width-mobile', 'calc((100% - ' + (15 * (slidesMobile - 1)) + 'px) / ' + slidesMobile + ')');
		}

		// 4. Create Swiper DOM structure
		var $swiperContainer = $('<div class="swiper eas-carousel-swiper"></div>');
		var $swiperWrapper = $('<div class="swiper-wrapper eas-carousel-wrapper"></div>');
		$swiperContainer.append($swiperWrapper);
		$contentWrapper.append($swiperContainer);

		// Move children into swiper wrapper and add slide classes
		$children.each(function () {
			$(this).addClass('swiper-slide eas-carousel-slide');
			$swiperWrapper.append($(this));
		});

		// 5. If total children is less than 12, duplicate them to ensure smooth infinite loop
		if ($children.length < 12) {
			var repeatTimes = Math.ceil(12 / $children.length);
			for (var i = 1; i < repeatTimes; i++) {
				$children.each(function () {
					var $clone = $(this).clone();
					$clone.addClass('swiper-slide eas-carousel-slide eas-carousel-clone-temp');
					$clone.removeAttr('id').removeAttr('data-id');
					$clone.find('[id]').removeAttr('id');
					$swiperWrapper.append($clone);
				});
			}
		}

		// 6. Build Swiper instance
		var speed = parseInt(config.speed) || 5000;
		var pauseOnHover = config.pauseOnHover || 'yes';

		// Swiper configuration for smooth linear continuous flow
		var swiperOptions = {
			direction: isVertical ? 'vertical' : 'horizontal',
			slidesPerView: 'auto',
			loop: true,
			loopedSlides: $swiperWrapper.children().length, // Clone all slides for seamless looping!
			spaceBetween: 15, // Gap between slides!
			freeMode: {
				enabled: true,
				momentum: false,
			},
			speed: speed,
			autoplay: {
				delay: 0,
				disableOnInteraction: false,
				reverseDirection: (direction === 'ltr' || direction === 'ttb'),
			},
			allowTouchMove: true, // Allows grabbing and dragging with mouse/touch!
			grabCursor: true,     // Shows grab hand cursor!
		};

		var swiperInstance = new Swiper($swiperContainer[0], swiperOptions);

		// Store instance
		$scope.data('eas-swiper-instance', swiperInstance);
		$scope.data('eas-carousel-active', true);

		// 7. Handle hover-pause (instantly freeze transitions)
		if (pauseOnHover === 'yes') {
			$swiperContainer.on('mouseenter', function () {
				if (swiperInstance) {
					if (swiperInstance.autoplay) {
						swiperInstance.autoplay.stop();
					}
					var currentTranslate = swiperInstance.getTranslate();
					swiperInstance.setTransition(0);
					swiperInstance.setTranslate(currentTranslate);
				}
			});
			$swiperContainer.on('mouseleave', function () {
				if (swiperInstance && swiperInstance.autoplay) {
					swiperInstance.setTransition(speed);
					swiperInstance.autoplay.start();
				}
			});
		}
	}

	function destroyContainerCarousel($scope) {
		var scopeId = $scope.data('id');
		if (scopeId) {
			$(window).off('resize.easCarousel' + scopeId);
		}

		var swiperInstance = $scope.data('eas-swiper-instance');
		if (swiperInstance) {
			swiperInstance.destroy(true, true);
			$scope.removeData('eas-swiper-instance');
		}

		var $contentWrapper = $scope.find('> .e-con-inner, > .elementor-container');
		if (!$contentWrapper.length) {
			$contentWrapper = $scope;
		}

		var $swiperContainer = $contentWrapper.find('> .eas-carousel-swiper');
		if ($swiperContainer.length) {
			var $swiperWrapper = $swiperContainer.find('> .eas-carousel-wrapper');
			if ($swiperWrapper.length) {
				// Move original elements back to content wrapper and clean classes (ignore temp clones)
				$swiperWrapper.children().not('.eas-carousel-clone-temp').each(function () {
					$(this).removeClass('swiper-slide eas-carousel-slide');
					$contentWrapper.append($(this));
				});
			}
			$swiperContainer.remove();
		}
		$scope.removeData('eas-carousel-active');
		if ($scope[0]) {
			$scope[0].style.removeProperty('--eas-carousel-slide-width');
		}
	}

	// Expose globally
	window.EASInitContainerCarousel = initContainerCarousel;
	window.EASDestroyContainerCarousel = destroyContainerCarousel;

	// Hook into Elementor frontend
	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/container', initContainerCarousel);
	});

})(jQuery);

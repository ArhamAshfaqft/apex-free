(function ($) {
	'use strict';

	/**
	 * Initialize Team Member Carousel Widget
	 */
	function initTeamMemberShowcase($scope) {
		var $wrap = $scope.find('.eas-team-member-carousel-wrap');
		if (!$wrap.length) return; // Grid mode — no Swiper needed

		var $container = $wrap.find('.swiper-container');
		if (!$container.length) {
			$container = $wrap.find('.swiper');
			if (!$container.length) return;
		}

		// Clean up any existing swiper instances to prevent memory leaks in the editor
		var existingSwiper = $container.data('eas-swiper-instance');
		if (existingSwiper && typeof existingSwiper.destroy === 'function') {
			existingSwiper.destroy(true, true);
			$container.removeData('eas-swiper-instance');
		}

		var configRaw = $wrap.attr('data-eas-carousel-config');
		if (!configRaw) return;

		var config;
		try {
			config = JSON.parse(configRaw);
		} catch (e) {
			return;
		}

		var slidesDesktop = parseInt(config.slidesDesktop) || 3;
		var slidesTablet  = parseInt(config.slidesTablet) || 2;
		var slidesMobile  = parseInt(config.slidesMobile) || 1;
		var gap           = (config.gap !== undefined && config.gap !== '') ? parseInt(config.gap) : 30;
		var totalSlides   = $container.find('.swiper-slide').length;
		var shouldLoop    = config.loop === 'yes' && totalSlides > slidesDesktop;

		var swiperOptions = {
			slidesPerView: slidesDesktop,
			spaceBetween: gap,
			loop: shouldLoop,
			speed: 600,
			grabCursor: true,
			watchSlidesProgress: true,
			observer: true,
			observeParents: true,
			breakpoints: {
				320: {
					slidesPerView: slidesMobile,
					spaceBetween: Math.min(20, gap)
				},
				768: {
					slidesPerView: slidesTablet,
					spaceBetween: Math.min(24, gap)
				},
				1025: {
					slidesPerView: slidesDesktop,
					spaceBetween: gap
				}
			}
		};

		if (config.autoplay === 'yes') {
			swiperOptions.autoplay = {
				delay: parseInt(config.autoplaySpeed) || 3000,
				disableOnInteraction: false,
				pauseOnMouseEnter: true
			};
		}

		if (config.arrows === 'yes') {
			var nextEl = $wrap.find('.eas-tm-arrow-next')[0];
			var prevEl = $wrap.find('.eas-tm-arrow-prev')[0];
			if (nextEl && prevEl) {
				swiperOptions.navigation = {
					nextEl: nextEl,
					prevEl: prevEl
				};
			}
		}

		if (config.dots === 'yes') {
			var dotsEl = $wrap.find('.eas-tm-dots')[0];
			if (dotsEl) {
				swiperOptions.pagination = {
					el: dotsEl,
					clickable: true,
					bulletClass: 'swiper-pagination-bullet',
					bulletActiveClass: 'swiper-pagination-bullet-active'
				};
			}
		}

		// Use the DOM element directly for Swiper
		var containerEl = $container[0];

		// Initialize Swiper — use Elementor's async wrapper if available, otherwise global
		if (typeof elementorFrontend !== 'undefined' && elementorFrontend.utils && elementorFrontend.utils.swiper) {
			new elementorFrontend.utils.swiper(containerEl, swiperOptions).then(function (swiperInstance) {
				$container.data('eas-swiper-instance', swiperInstance);
				setupTouchHover($wrap);
			}).catch(function () {
				// Fallback: try global Swiper
				if (typeof Swiper !== 'undefined') {
					var swiperInstance = new Swiper(containerEl, swiperOptions);
					$container.data('eas-swiper-instance', swiperInstance);
					setupTouchHover($wrap);
				}
			});
		} else if (typeof Swiper !== 'undefined') {
			var swiperInstance = new Swiper(containerEl, swiperOptions);
			$container.data('eas-swiper-instance', swiperInstance);
			setupTouchHover($wrap);
		}
	}

	/**
	 * Touch-device hover toggle for social overlays
	 */
	function setupTouchHover($wrap) {
		var $cards = $wrap.find('.eas-team-member-card');
		$cards.off('click.easTeam').on('click.easTeam', function (e) {
			if ($(e.target).closest('.eas-team-member-social-link').length) return;
			if (window.innerWidth <= 1024 || window.matchMedia('(pointer: coarse)').matches) {
				var $card = $(this);
				if (!$card.hasClass('eas-touch-hover')) {
					e.preventDefault();
					$cards.removeClass('eas-touch-hover');
					$card.addClass('eas-touch-hover');
				} else {
					$card.removeClass('eas-touch-hover');
				}
			}
		});
	}

	// ── Hook into Elementor ────────────────────────────────────────────────
	$(window).on('elementor/frontend/init', function () {
		if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
			elementorFrontend.hooks.addAction(
				'frontend/element_ready/eas-team-member.default',
				initTeamMemberShowcase
			);
		}
	});

})(jQuery);

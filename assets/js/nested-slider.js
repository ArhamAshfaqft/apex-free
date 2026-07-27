(function ($) {
	'use strict';

	var instances = new WeakMap();
	var easingMap = {
		smooth: 'cubic-bezier(.22,1,.36,1)',
		gentle: 'cubic-bezier(.25,.46,.45,.94)',
		crisp: 'cubic-bezier(.4,0,.2,1)',
		linear: 'linear'
	};

	function isEditor() {
		return !!(window.elementorFrontend && elementorFrontend.isEditMode && elementorFrontend.isEditMode());
	}

	function parseConfig(root) {
		try {
			return JSON.parse(root.getAttribute('data-eas-carousel') || '{}');
		} catch (error) {
			return {};
		}
	}

	function positive(value, fallback) {
		value = parseFloat(value);
		return Number.isFinite(value) && value > 0 ? value : fallback;
	}

	function currentValue(config, key) {
		var width = window.innerWidth;
		if (width <= 767 && config[key + 'Mobile'] != null) return config[key + 'Mobile'];
		if (width <= 1024 && config[key + 'Tablet'] != null) return config[key + 'Tablet'];
		return config[key];
	}

	function prepareMarkup(root) {
		var viewport = root.querySelector('.eas-carousel-viewport');
		var wrapper = viewport && viewport.querySelector(':scope > .eas-slider-slides');
		if (!viewport || !wrapper) return null;
		viewport.classList.add('swiper');
		wrapper.classList.add('swiper-wrapper');
		Array.prototype.forEach.call(wrapper.children, function (slide) {
			slide.classList.add('swiper-slide');
		});
		return { viewport: viewport, wrapper: wrapper };
	}

	function buildOptions(root, config) {
		var slides = positive(currentValue(config, 'slidesPerView'), 1);
		var total = root.querySelectorAll('.eas-slider-slide-item:not([data-eas-runtime-clone])').length;
		var movable = total > slides;
		var paginationType = ['bullets', 'fraction', 'progressbar'].indexOf(config.pagination) !== -1 ? config.pagination : 'bullets';
		var options = {
			direction: 'horizontal',
			slidesPerView: slides,
			slidesPerGroup: Math.max(1, parseInt(currentValue(config, 'slidesToScroll'), 10) || 1),
			spaceBetween: Math.max(0, parseFloat(currentValue(config, 'spaceBetween')) || 0),
			speed: Math.max(100, parseInt(config.speed, 10) || 650),
			centeredSlides: !!config.centeredSlides,
			loop: !isEditor() && movable && !!config.loop,
			watchOverflow: true,
			allowTouchMove: movable,
			grabCursor: movable,
			observer: true,
			observeParents: true,
			navigation: config.arrows && movable ? {
				nextEl: root.querySelector('.eas-carousel-next'),
				prevEl: root.querySelector('.eas-carousel-prev')
			} : false,
			pagination: config.pagination && config.pagination !== 'none' ? {
				el: root.querySelector('.eas-carousel-pagination'),
				type: paginationType,
				clickable: paginationType === 'bullets'
			} : false,
			keyboard: config.keyboard ? { enabled: true, onlyInViewport: true } : false,
			a11y: { enabled: true }
		};
		if (!isEditor() && movable && config.autoplay) {
			options.autoplay = {
				delay: Math.max(500, parseInt(config.autoplayDelay, 10) || 3500),
				disableOnInteraction: !!config.pauseOnInteraction,
				pauseOnMouseEnter: !!config.pauseOnHover
			};
		}
		return options;
	}

	function finalize(root, config, swiper) {
		instances.set(root, swiper);
		root.classList.add('eas-carousel-initialized', 'eas-carousel-ready');
		root.classList.toggle('eas-carousel-equal-height', !!config.equalHeight);
		if (swiper && swiper.wrapperEl) {
			swiper.wrapperEl.style.transitionTimingFunction = easingMap[config.easing] || easingMap.smooth;
		}
	}

	function initRoot(root) {
		if (!root || instances.has(root) || window.apexadfoNestedSliderProRuntime) return;
		var markup = prepareMarkup(root);
		if (!markup) return;
		var config = parseConfig(root);
		var options = buildOptions(root, config);
		try {
			if (window.elementorFrontend && elementorFrontend.utils && elementorFrontend.utils.swiper) {
				var result = new elementorFrontend.utils.swiper(markup.viewport, options);
				if (result && typeof result.then === 'function') {
					result.then(function (swiper) { finalize(root, config, swiper); }).catch(function () { root.classList.add('eas-carousel-css-fallback'); });
				} else if (result) {
					finalize(root, config, result);
				}
			} else if (window.Swiper) {
				finalize(root, config, new window.Swiper(markup.viewport, options));
			}
		} catch (error) {
			root.classList.add('eas-carousel-css-fallback');
		}
	}

	function initScope($scope) {
		var scope = $scope && $scope[0] ? $scope[0] : document;
		if (scope.matches && scope.matches('.eas-nested-carousel')) initRoot(scope);
		Array.prototype.forEach.call(scope.querySelectorAll ? scope.querySelectorAll('.eas-nested-carousel') : [], initRoot);
	}

	$(window).on('elementor/frontend/init', function () {
		if (window.apexadfoNestedSliderProRuntime) return;
		elementorFrontend.hooks.addAction('frontend/element_ready/eas-nested-slider.default', initScope);
	});
})(jQuery);

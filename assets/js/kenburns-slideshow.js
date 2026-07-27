(function ($) {
	'use strict';

	/**
	 * Safe helper to read numeric control values from Elementor settings objects
	 */
	function getSize(settings, key, fallback) {
		var val = settings[key];
		if (val === undefined || val === null) {
			return fallback;
		}
		if (typeof val === 'object' && val.size !== undefined) {
			return val.size;
		}
		return val;
	}

	/**
	 * Cinematic Background Slideshow Engine
	 */
	class EASKenBurnsSlideshow {
		constructor($wrapper) {
			this.$wrapper = $wrapper;

			var configRaw = $wrapper.attr('data-eas-kb-config');
			if (!configRaw) return;

			try {
				this.config = JSON.parse(configRaw);
			} catch (e) {
				console.error('Apex Cinematic Slideshow: Failed to parse configuration JSON.', e);
				return;
			}

			this.images = this.config.images || [];
			if (!this.images.length) return;

			this.duration = parseInt(this.config.duration) || 5000;
			this.transition = parseInt(this.config.transition) || 1500;
			this.zoom = this.config.zoom || 'alternate';
			this.showDots = this.config.dots === 'yes';
			this.navStyle = this.config.navStyle || 'dots';

			this.currentIndex = 0;
			this.timer = null;
			this.slides = [];

			// Clean up previous elements
			this.$wrapper.find('> .eas-kb-slideshow-container').remove();
			this.$wrapper.find('> .eas-kb-dots').remove();

			this.buildDOM();
			this.bindEvents();

			// Play first slide and start auto loop
			this.playSlide(0);
			this.startTimer();
		}

		/**
		 * Inject Slideshow, Overlay, and Dots DOM markup dynamically
		 */
		buildDOM() {
			var self = this;

			// 1. Slideshow Container
			var $container = $('<div class="eas-kb-slideshow-container"></div>');

			this.images.forEach(function (url, index) {
				var $slide = $(
					'<div class="eas-kb-slide">' +
					'<img src="' + url + '" alt="Background Slide ' + (index + 1) + '" />' +
					'</div>'
				);

				// Apply custom transition speeds using CSS variables
				$slide.css({
					'--eas-kb-trans': self.transition + 'ms',
					'--eas-kb-dur': (self.duration + self.transition) + 'ms'
				});

				$container.append($slide);
				self.slides.push($slide);
			});

			// Append absolute overlay element
			var $overlay = $('<div class="eas-kb-slideshow-overlay"></div>');
			$container.append($overlay);

			this.$wrapper.prepend($container);
			this.$container = $container;

			// 2. Pagination Dots/Lines/Numbers
			if (this.showDots && this.images.length > 1) {
				var $dotsWrap = $('<div class="eas-kb-dots eas-kb-nav-' + this.navStyle + '"></div>');

				this.images.forEach(function (url, index) {
					var dotContent = '';
					if (self.navStyle === 'numbers') {
						var num = index + 1;
						dotContent = num < 10 ? '0' + num : num;
					}
					var $dot = $('<button class="eas-kb-dot" aria-label="Go to slide ' + (index + 1) + '">' + dotContent + '</button>');
					$dotsWrap.append($dot);
				});

				this.$wrapper.append($dotsWrap);
				this.$dotsWrap = $dotsWrap;
			}
		}

		/**
		 * Play specific slide index
		 */
		playSlide(index) {
			var self = this;
			var total = this.slides.length;
			if (!total) return;

			// Normalize index boundaries
			if (index < 0) index = total - 1;
			if (index >= total) index = 0;

			var oldIndex = this.currentIndex;

			// Handle slide states: target active, previous leaving, others reset
			this.slides.forEach(function ($slide, idx) {
				if (idx === index) {
					var animClass = 'eas-kb-zoom-in';
					if (self.zoom === 'in') {
						animClass = 'eas-kb-zoom-in';
					} else if (self.zoom === 'out') {
						animClass = 'eas-kb-zoom-out';
					} else {
						var cycle = index % 4;
						if (cycle === 0) animClass = 'eas-kb-zoom-in';
						else if (cycle === 1) animClass = 'eas-kb-zoom-out';
						else if (cycle === 2) animClass = 'eas-kb-pan-left';
						else animClass = 'eas-kb-pan-right';
					}
					$slide.removeClass('eas-kb-slide-leaving')
					      .addClass('eas-kb-slide-active ' + animClass);
				} else if (idx === oldIndex) {
					$slide.removeClass('eas-kb-slide-active')
					      .addClass('eas-kb-slide-leaving');
				} else {
					$slide.removeClass('eas-kb-slide-active eas-kb-slide-leaving eas-kb-zoom-in eas-kb-zoom-out eas-kb-pan-left eas-kb-pan-right');
				}
			});

			var $target = this.slides[index];

			// Sync dot active status
			if (this.showDots && this.$dotsWrap) {
				this.$dotsWrap.find('.eas-kb-dot').removeClass('eas-kb-dot-active');
				this.$dotsWrap.find('.eas-kb-dot').eq(index).addClass('eas-kb-dot-active');
			}

			this.currentIndex = index;
		}

		/**
		 * Move to next slide in loop
		 */
		nextSlide() {
			this.playSlide(this.currentIndex + 1);
		}

		/**
		 * Start auto loop timer
		 */
		startTimer() {
			var self = this;
			this.timer = setInterval(function () {
				self.nextSlide();
			}, this.duration);
		}

		/**
		 * Reset auto loop timer on manual action
		 */
		resetTimer() {
			clearInterval(this.timer);
			this.startTimer();
		}

		/**
		 * Bind event handlers
		 */
		bindEvents() {
			var self = this;

			if (this.showDots && this.$dotsWrap) {
				this.$dotsWrap.on('click', '.eas-kb-dot', function (e) {
					e.preventDefault();
					var idx = $(this).index();
					if (idx !== self.currentIndex) {
						self.playSlide(idx);
						self.resetTimer();
					}
				});
			}
		}

		/**
		 * Cleanup loop timers, elements and listeners on destroy
		 */
		destroy() {
			clearInterval(this.timer);
			if (this.$container) this.$container.remove();
			if (this.$dotsWrap) this.$dotsWrap.remove();
			this.$wrapper.removeClass('eas-has-kb-slideshow');
			this.$wrapper.removeData('eas-kb-engine');
		}
	}

	// Expose class globally
	window.EASKenBurnsSlideshow = EASKenBurnsSlideshow;

	/**
	 * Hook Slideshow Engine into Elementor ready actions
	 */
	function initSlideshowBg($scope) {
		// Destroy old engine if present
		var prev = $scope.data('eas-kb-engine');
		if (prev && typeof prev.destroy === 'function') {
			prev.destroy();
		}
		$scope.removeData('eas-kb-engine');

		if (!$scope.attr('data-eas-kb-config')) return;

		var engine = new EASKenBurnsSlideshow($scope);
		$scope.data('eas-kb-engine', engine);
	}

	/**
	 * Live Editor Preview handler that registers Backbone model change listeners
	 */
	function initEditorPreview($scope) {
		var modelCID = $scope.data('model-cid');
		if (!modelCID) return;

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

		// Initial apply state
		applySlideshowState($scope, settings);

		// Watch for changes on the settings model
		var settingsModel = model.attributes.settings || model;

		// Unbind previous listeners to avoid double triggers
		settingsModel.off('change:eas_kb_slideshow_enable');
		settingsModel.off('change:eas_kb_slideshow_gallery');
		settingsModel.off('change:eas_kb_slideshow_duration');
		settingsModel.off('change:eas_kb_slideshow_transition');
		settingsModel.off('change:eas_kb_slideshow_zoom');
		settingsModel.off('change:eas_kb_slideshow_dots');
		settingsModel.off('change:eas_kb_slideshow_nav_style');

		// Bind change listener
		settingsModel.on(
			'change:eas_kb_slideshow_enable change:eas_kb_slideshow_gallery change:eas_kb_slideshow_duration change:eas_kb_slideshow_transition change:eas_kb_slideshow_zoom change:eas_kb_slideshow_dots change:eas_kb_slideshow_nav_style',
			function () {
				var s = settingsModel.attributes;
				applySlideshowState($scope, s);
			}
		);
	}

	function applySlideshowState($scope, settings) {
		var prev = $scope.data('eas-kb-engine');
		if (prev && typeof prev.destroy === 'function') {
			prev.destroy();
		}
		$scope.removeData('eas-kb-engine');

		var enable = settings['eas_kb_slideshow_enable'] || 'no';
		var gallery = settings['eas_kb_slideshow_gallery'];

		// In the editor, gallery is a Backbone collection
		var imageUrls = [];
		if (gallery && gallery.models) {
			gallery.models.forEach(function (m) {
				var attrs = m.attributes;
				if (attrs && attrs.url) {
					imageUrls.push(attrs.url);
				}
			});
		} else if (Array.isArray(gallery)) {
			gallery.forEach(function (img) {
				if (img && img.url) {
					imageUrls.push(img.url);
				}
			});
		}

		if (enable === 'yes' && imageUrls.length > 0) {
			var duration = getSize(settings, 'eas_kb_slideshow_duration', 5000);
			var transition = getSize(settings, 'eas_kb_slideshow_transition', 1500);
			var zoom = settings['eas_kb_slideshow_zoom'] || 'alternate';
			var dots = settings['eas_kb_slideshow_dots'] || 'yes';
			var navStyle = settings['eas_kb_slideshow_nav_style'] || 'dots';

			var slideshow_config = {
				duration: parseInt(duration) || 5000,
				transition: parseInt(transition) || 1500,
				zoom: zoom,
				dots: dots,
				navStyle: navStyle,
				images: imageUrls
			};

			$scope.attr('data-eas-kb-config', JSON.stringify(slideshow_config));
			$scope.addClass('eas-has-kb-slideshow');

			var engine = new EASKenBurnsSlideshow($scope);
			$scope.data('eas-kb-engine', engine);
		} else {
			$scope.removeAttr('data-eas-kb-config');
			$scope.removeClass('eas-has-kb-slideshow');
			$scope.find('> .eas-kb-slideshow-container').remove();
			$scope.find('> .eas-kb-dots').remove();
		}
	}

	$(window).on('elementor/frontend/init', function () {
		if (elementorFrontend.isEditMode()) {
			elementorFrontend.hooks.addAction('frontend/element_ready/section',   initEditorPreview);
			elementorFrontend.hooks.addAction('frontend/element_ready/column',    initEditorPreview);
			elementorFrontend.hooks.addAction('frontend/element_ready/container', initEditorPreview);
		} else {
			elementorFrontend.hooks.addAction('frontend/element_ready/section',   initSlideshowBg);
			elementorFrontend.hooks.addAction('frontend/element_ready/column',    initSlideshowBg);
			elementorFrontend.hooks.addAction('frontend/element_ready/container', initSlideshowBg);
		}
	});

})(jQuery);

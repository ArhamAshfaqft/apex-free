(function (window, document) {
	'use strict';

	if (window.apexadfoHorizontalStoryProRuntime) return;

	var instances = new WeakMap();

	function clamp(value, min, max) {
		return Math.min(max, Math.max(min, value));
	}

	function parseConfig(element) {
		var defaults = {
			speed: '1', snap: 'yes', progress: 'bar', direction: 'auto',
			disableTablet: 'no', disableMobile: 'no', mobileFallback: 'vertical'
		};
		try {
			return Object.assign(defaults, JSON.parse(element.getAttribute('data-eas-hscroll-config') || '{}'));
		} catch (error) {
			return defaults;
		}
	}

	function isEditor() {
		return document.body.classList.contains('elementor-editor-active') ||
			(window.elementorFrontend && window.elementorFrontend.isEditMode && window.elementorFrontend.isEditMode());
	}

	function deviceMode() {
		var width = window.innerWidth;
		if (width <= 767) return 'mobile';
		if (width <= 1024) return 'tablet';
		return 'desktop';
	}

	function directChild(element, selector) {
		return Array.prototype.find.call(element.children, function (child) { return child.matches(selector); }) || null;
	}

	function Story(element) {
		this.element = element;
		this.config = parseConfig(element);
		this.track = null;
		this.inner = null;
		this.panels = [];
		this.generatedInner = false;
		this.progressBar = null;
		this.disabled = false;
		this.start = 0;
		this.range = 1;
		this.maxTravel = 0;
		this.progress = 0;
		this.raf = 0;
		this.snapTimer = 0;
		this.isSnapping = false;
		this.resizeObserver = null;
		this.onScroll = this.scheduleRender.bind(this);
		this.onResize = this.scheduleMeasure.bind(this);
		this.setup();
	}

	Story.prototype.direction = function () {
		if (this.config.direction === 'ltr' || this.config.direction === 'rtl') return this.config.direction;
		return getComputedStyle(document.documentElement).direction === 'rtl' ? 'rtl' : 'ltr';
	};

	Story.prototype.shouldDisable = function () {
		var mode = deviceMode();
		var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		return isEditor() || reduced ||
			(mode === 'mobile' && this.config.disableMobile === 'yes') ||
			(mode === 'tablet' && this.config.disableTablet === 'yes');
	};

	Story.prototype.setup = function () {
		var self = this;
		this.track = this.element.parentElement && this.element.parentElement.classList.contains('eas-hscroll-track')
			? this.element.parentElement : document.createElement('div');
		if (!this.track.parentNode) {
			this.track.className = 'eas-hscroll-track';
			this.element.parentNode.insertBefore(this.track, this.element);
			this.track.appendChild(this.element);
		}

		this.inner = directChild(this.element, '.e-con-inner, .elementor-container, .eas-hscroll-inner-wrap');
		if (!this.inner) {
			this.inner = document.createElement('div');
			this.inner.className = 'eas-hscroll-inner-wrap';
			this.generatedInner = true;
			Array.from(this.element.children).forEach(function (child) {
				if (!child.classList.contains('eas-hscroll-generated-ui')) self.inner.appendChild(child);
			});
			this.element.appendChild(this.inner);
		}

		this.panels = Array.from(this.inner.children).filter(function (child) {
			return child.nodeType === 1 && !child.classList.contains('eas-hscroll-generated-ui');
		});
		if (this.panels.length < 2) return;

		this.element.classList.add('eas-hscroll-ready');
		this.element.classList.toggle('eas-hscroll-is-rtl', this.direction() === 'rtl');
		this.element.setAttribute('role', this.element.getAttribute('role') || 'region');
		this.element.setAttribute('aria-roledescription', 'horizontal story');
		this.inner.classList.add('eas-hscroll-inner');
		this.panels.forEach(function (panel, index) {
			panel.classList.add('eas-hscroll-panel');
			panel.setAttribute('role', 'group');
			panel.setAttribute('aria-roledescription', 'story panel');
			panel.setAttribute('aria-label', 'Panel ' + (index + 1) + ' of ' + self.panels.length);
		});

		if (this.config.progress === 'bar') {
			var bar = document.createElement('div');
			bar.className = 'eas-hscroll-progress eas-hscroll-generated-ui';
			bar.setAttribute('role', 'progressbar');
			bar.setAttribute('aria-label', 'Story progress');
			bar.setAttribute('aria-valuemin', '0');
			bar.setAttribute('aria-valuemax', '100');
			bar.innerHTML = '<span></span>';
			this.element.appendChild(bar);
			this.progressBar = bar;
		}

		window.addEventListener('scroll', this.onScroll, { passive: true });
		window.addEventListener('resize', this.onResize, { passive: true });
		if ('ResizeObserver' in window) {
			this.resizeObserver = new ResizeObserver(function () { self.scheduleMeasure(); });
			this.resizeObserver.observe(this.inner);
		}
		this.measure();
	};

	Story.prototype.applyResponsiveMode = function () {
		this.disabled = this.shouldDisable();
		this.track.classList.toggle('eas-hscroll-fallback', this.disabled);
		this.element.classList.toggle('eas-hscroll-fallback-vertical', this.disabled && this.config.mobileFallback !== 'swipe');
		this.element.classList.toggle('eas-hscroll-fallback-swipe', this.disabled && this.config.mobileFallback === 'swipe');
		if (this.disabled) {
			this.track.style.height = 'auto';
			this.inner.style.transform = '';
		}
	};

	Story.prototype.speedFactor = function () {
		if (this.config.speed === 'snap_one') return 1;
		var speed = parseFloat(this.config.speed);
		return [0.5, 1, 1.5, 2].indexOf(speed) >= 0 ? speed : 1;
	};

	Story.prototype.measure = function () {
		if (this.panels.length < 2) return;
		this.applyResponsiveMode();
		if (this.disabled) return;
		var viewportHeight = Math.max(320, window.innerHeight);
		var rect = this.track.getBoundingClientRect();
		this.maxTravel = Math.max(0, this.inner.scrollWidth - window.innerWidth);
		this.range = Math.max(1, this.maxTravel * this.speedFactor());
		this.start = rect.top + window.pageYOffset;
		this.track.style.height = (viewportHeight + this.range) + 'px';
		this.element.style.setProperty('--eas-hscroll-height', viewportHeight + 'px');
		this.render();
	};

	Story.prototype.scheduleMeasure = function () {
		var self = this;
		clearTimeout(this.measureTimer);
		this.measureTimer = setTimeout(function () { self.measure(); }, 80);
	};

	Story.prototype.scheduleRender = function () {
		var self = this;
		if (!this.raf) this.raf = window.requestAnimationFrame(function () {
			self.raf = 0;
			self.render();
		});
		if (this.config.snap === 'yes' || this.config.speed === 'snap_one') {
			clearTimeout(this.snapTimer);
			this.snapTimer = setTimeout(function () { self.snap(); }, 140);
		}
	};

	Story.prototype.render = function () {
		if (this.disabled || this.panels.length < 2) return;
		this.progress = clamp((window.pageYOffset - this.start) / this.range, 0, 1);
		var direction = this.direction() === 'rtl' ? 1 : -1;
		this.inner.style.transform = 'translate3d(' + (direction * this.maxTravel * this.progress) + 'px,0,0)';
		if (this.progressBar) {
			this.progressBar.firstElementChild.style.transform = 'scaleX(' + this.progress + ')';
			this.progressBar.setAttribute('aria-valuenow', String(Math.round(this.progress * 100)));
		}
	};

	Story.prototype.snap = function () {
		if (this.disabled || this.isSnapping || window.pageYOffset <= this.start || window.pageYOffset >= this.start + this.range) return;
		var steps = Math.max(1, this.panels.length - 1);
		var targetProgress = Math.round(this.progress * steps) / steps;
		var target = this.start + targetProgress * this.range;
		if (Math.abs(window.pageYOffset - target) < 3) return;
		this.isSnapping = true;
		window.scrollTo({ top: target, behavior: 'smooth' });
		var self = this;
		setTimeout(function () { self.isSnapping = false; }, 450);
	};

	Story.prototype.destroy = function () {
		window.removeEventListener('scroll', this.onScroll);
		window.removeEventListener('resize', this.onResize);
		if (this.resizeObserver) this.resizeObserver.disconnect();
		window.cancelAnimationFrame(this.raf);
		clearTimeout(this.snapTimer);
		clearTimeout(this.measureTimer);
		if (this.progressBar) this.progressBar.remove();
		if (this.inner) this.inner.style.transform = '';
		if (this.track) this.track.style.height = '';
	};

	function init(scope) {
		if (window.apexadfoHorizontalStoryProRuntime) return;
		var root = scope && scope.nodeType ? scope : document;
		var elements = [];
		if (root.matches && root.matches('.eas-container-hscroll-active[data-eas-hscroll-config]')) elements.push(root);
		elements = elements.concat(Array.from(root.querySelectorAll ? root.querySelectorAll('.eas-container-hscroll-active[data-eas-hscroll-config]') : []));
		elements.forEach(function (element) {
			var previous = instances.get(element);
			if (previous) previous.destroy();
			instances.set(element, new Story(element));
		});
	}

	window.apexadfoInitHorizontalStory = init;
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { init(document); });
	else window.setTimeout(function () { init(document); }, 0);

	if (window.elementorFrontend && window.elementorFrontend.hooks) {
		window.elementorFrontend.hooks.addAction('frontend/element_ready/container', function ($scope) { init($scope && $scope[0] ? $scope[0] : $scope); });
	} else {
		window.addEventListener('elementor/frontend/init', function () {
			window.elementorFrontend.hooks.addAction('frontend/element_ready/container', function ($scope) { init($scope && $scope[0] ? $scope[0] : $scope); });
		});
	}
})(window, document);

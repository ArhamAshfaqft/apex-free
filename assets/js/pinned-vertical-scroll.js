(function (window, document) {
	'use strict';

	var instances = new WeakMap();

	function clamp(value, min, max) {
		return Math.min(max, Math.max(min, value));
	}

	function editorMode() {
		return document.body.classList.contains('elementor-editor-active') ||
			(window.elementorFrontend && window.elementorFrontend.isEditMode && window.elementorFrontend.isEditMode());
	}

	function deviceMode() {
		if (window.innerWidth <= 767) return 'mobile';
		if (window.innerWidth <= 1024) return 'tablet';
		return 'desktop';
	}

	function parseConfig(element) {
		var defaults = {
			layoutMode: 'direct',
			direction: 'up', distance: 1, smoothing: 18,
			stageHeight: { desktop: 100, tablet: 100, mobile: 100 },
			topOffset: { desktop: 0, tablet: 0, mobile: 0 },
			bottomOffset: { desktop: 0, tablet: 0, mobile: 0 },
			gap: { desktop: '24px', tablet: '24px', mobile: '24px' },
			disableTablet: 'no', disableMobile: 'no'
		};
		try {
			return Object.assign(defaults, JSON.parse(element.getAttribute('data-apexadfo-pvs-config') || '{}'));
		} catch (error) {
			return defaults;
		}
	}

	function responsive(config, key, fallback) {
		var mode = deviceMode();
		var values = config[key] || {};
		if (values[mode] !== undefined && values[mode] !== '') return values[mode];
		if (mode === 'mobile' && values.tablet !== undefined) return values.tablet;
		if (values.desktop !== undefined) return values.desktop;
		return fallback;
	}

	function isStructuralChild(child) {
		return child.classList.contains('elementor-shape') ||
			child.classList.contains('elementor-background-overlay') ||
			child.classList.contains('apexadfo-pvs-generated');
	}

	function PinnedVerticalScroll(element) {
		this.element = element;
		this.originalElementHeight = element.style.height;
		this.config = parseConfig(element);
		this.stage = null;
		this.track = null;
		this.trackParent = null;
		this.panels = [];
		this.panelAttributes = [];
		this.originalChildren = [];
		this.stageOriginalChildren = [];
		this.generatedStage = false;
		this.disabled = false;
		this.start = 0;
		this.range = 1;
		this.maxTravel = 0;
		this.progress = 0;
		this.targetProgress = 0;
		this.activeIndex = 0;
		this.raf = 0;
		this.measureTimer = 0;
		this.resizeObserver = null;
		this.mutationObserver = null;
		this.onScroll = this.scheduleRender.bind(this);
		this.onResize = this.scheduleMeasure.bind(this);
		this.onAssetLoad = this.scheduleMeasure.bind(this);
		this.setup();
	}

	PinnedVerticalScroll.prototype.shouldDisable = function () {
		var mode = deviceMode();
		var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var conflict = this.element.classList.contains('eas-container-hscroll-active') ||
			this.element.classList.contains('eas-container-stack-active') ||
			this.element.classList.contains('eas-section-transition');
		return reduced || conflict ||
			(mode === 'tablet' && this.config.disableTablet === 'yes') ||
			(mode === 'mobile' && this.config.disableMobile === 'yes');
	};

	PinnedVerticalScroll.prototype.findHost = function () {
		return Array.prototype.find.call(this.element.children, function (child) {
			return child.matches('.e-con-inner, .elementor-container');
		}) || null;
	};

	PinnedVerticalScroll.prototype.setup = function () {
		var self = this;
		if (editorMode()) {
			this.element.classList.add('apexadfo-pvs-editor-preview');
			return;
		}

		this.stage = this.findHost();
		if (!this.stage) {
			this.stage = document.createElement('div');
			this.stage.className = 'apexadfo-pvs-stage apexadfo-pvs-generated';
			this.generatedStage = true;
			this.stageOriginalChildren = Array.from(this.element.children).filter(function (child) {
				return !isStructuralChild(child);
			});
			this.stageOriginalChildren.forEach(function (child) { self.stage.appendChild(child); });
			this.element.appendChild(this.stage);
		} else {
			this.stage.classList.add('apexadfo-pvs-stage');
		}

		this.trackParent = this.stage;

		this.originalChildren = Array.from(this.trackParent.children).filter(function (child) {
			return child.nodeType === 1 && !isStructuralChild(child);
		});
		if (!this.originalChildren.length) {
			this.element.classList.add('apexadfo-pvs-fallback');
			return;
		}

		this.track = document.createElement('div');
		this.track.className = 'apexadfo-pvs-track apexadfo-pvs-generated';
		this.originalChildren.forEach(function (child) { self.track.appendChild(child); });
		this.trackParent.appendChild(this.track);
		this.panels = Array.from(this.track.children);
		this.panels.forEach(function (panel, index) {
			self.panelAttributes[index] = {
				role: panel.getAttribute('role'),
				roledescription: panel.getAttribute('aria-roledescription'),
				label: panel.getAttribute('aria-label')
			};
			panel.classList.add('apexadfo-pvs-panel');
			panel.setAttribute('role', panel.getAttribute('role') || 'group');
			panel.setAttribute('aria-roledescription', 'vertical story panel');
			panel.setAttribute('aria-label', panel.getAttribute('aria-label') || ('Panel ' + (index + 1) + ' of ' + self.panels.length));
		});

		window.addEventListener('scroll', this.onScroll, { passive: true });
		window.addEventListener('resize', this.onResize, { passive: true });
		this.element.addEventListener('load', this.onAssetLoad, true);
		if ('ResizeObserver' in window) {
			this.resizeObserver = new ResizeObserver(function () { self.scheduleMeasure(); });
			this.resizeObserver.observe(this.track);
		}
		if ('MutationObserver' in window) {
			this.mutationObserver = new MutationObserver(function () { self.scheduleMeasure(); });
			this.mutationObserver.observe(this.track, { childList: true, subtree: true });
		}
		if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { self.scheduleMeasure(); });
		this.measure();
		this.dispatch('init');
	};

	PinnedVerticalScroll.prototype.applyResponsiveMode = function () {
		var mode = deviceMode();
		var top = clamp(Number(responsive(this.config, 'topOffset', 0)) || 0, 0, 300);
		var bottom = clamp(Number(responsive(this.config, 'bottomOffset', 0)) || 0, 0, 300);
		var percent = clamp(Number(responsive(this.config, 'stageHeight', 100)) || 100, 35, 100);
		var gap = String(responsive(this.config, 'gap', '24px'));
		var stageHeight = Math.max(240, (window.innerHeight * percent / 100) - top - bottom);

		this.element.style.setProperty('--apexadfo-pvs-top', top + 'px');
		this.element.style.setProperty('--apexadfo-pvs-stage-height', stageHeight + 'px');
		this.element.style.setProperty('--apexadfo-pvs-gap', gap);
		this.disabled = this.shouldDisable();
		this.element.classList.toggle('apexadfo-pvs-fallback', this.disabled);
		if (this.disabled) {
			this.element.style.height = 'auto';
			this.track.style.transform = '';
			this.element.style.removeProperty('--apexadfo-pvs-translate');
		}
		return { mode: mode, top: top, stageHeight: stageHeight };
	};

	PinnedVerticalScroll.prototype.measure = function () {
		if (!this.track) return;
		var geometry = this.applyResponsiveMode();
		if (this.disabled) {
			this.dispatch('measure');
			return;
		}
		var rect = this.element.getBoundingClientRect();
		this.maxTravel = Math.max(0, this.track.scrollHeight - geometry.stageHeight);
		this.range = Math.max(1, this.maxTravel * clamp(Number(this.config.distance) || 1, 0.65, 2));
		this.start = rect.top + window.pageYOffset - geometry.top;
		this.element.style.height = (geometry.stageHeight + this.range) + 'px';
		this.render(true);
		this.dispatch('measure');
	};

	PinnedVerticalScroll.prototype.scheduleMeasure = function () {
		var self = this;
		window.clearTimeout(this.measureTimer);
		this.measureTimer = window.setTimeout(function () { self.measure(); }, 80);
	};

	PinnedVerticalScroll.prototype.scheduleRender = function () {
		var self = this;
		if (!this.raf) this.raf = window.requestAnimationFrame(function () {
			self.raf = 0;
			self.render(false);
		});
	};

	PinnedVerticalScroll.prototype.smoothingAlpha = function () {
		var smoothing = clamp(Number(this.config.smoothing) || 0, 0, 100);
		return smoothing <= 0 ? 1 : clamp(0.34 - (smoothing * 0.0028), 0.06, 0.34);
	};

	PinnedVerticalScroll.prototype.render = function (immediate) {
		var self = this;
		if (this.disabled || !this.track) return;
		this.targetProgress = this.maxTravel > 0 ? clamp((window.pageYOffset - this.start) / this.range, 0, 1) : 0;
		var alpha = immediate ? 1 : this.smoothingAlpha();
		this.progress += (this.targetProgress - this.progress) * alpha;
		if (Math.abs(this.targetProgress - this.progress) < 0.0005) this.progress = this.targetProgress;
		var travel = this.maxTravel * this.progress;
		var translate = this.config.direction === 'down' ? (-this.maxTravel + travel) : -travel;
		this.element.style.setProperty('--apexadfo-pvs-translate', translate + 'px');
		this.activeIndex = this.closestPanelIndex(translate);
		this.dispatch('update');
		if (this.progress !== this.targetProgress && !this.raf) {
			this.raf = window.requestAnimationFrame(function () {
				self.raf = 0;
				self.render(false);
			});
		}
	};

	PinnedVerticalScroll.prototype.closestPanelIndex = function (translate) {
		if (!this.panels.length) return 0;
		var center = (this.stage ? this.stage.clientHeight : window.innerHeight) / 2;
		var closest = 0;
		var distance = Infinity;
		this.panels.forEach(function (panel, index) {
			var panelCenter = panel.offsetTop + translate + panel.offsetHeight / 2;
			var current = Math.abs(center - panelCenter);
			if (current < distance) {
				distance = current;
				closest = index;
			}
		});
		return closest;
	};

	PinnedVerticalScroll.prototype.panelProgress = function (index) {
		if (!this.panels[index] || this.maxTravel <= 0) return 0;
		var raw = clamp(this.panels[index].offsetTop / this.maxTravel, 0, 1);
		return this.config.direction === 'down' ? 1 - raw : raw;
	};

	PinnedVerticalScroll.prototype.scrollToPanel = function (index, behavior) {
		var progress = this.panelProgress(clamp(index, 0, this.panels.length - 1));
		window.scrollTo({ top: this.start + progress * this.range, behavior: behavior || 'smooth' });
	};

	PinnedVerticalScroll.prototype.dispatch = function (name) {
		this.element.dispatchEvent(new CustomEvent('apexadfo:pvs:' + name, {
			detail: { instance: this, progress: this.progress, activeIndex: this.activeIndex }
		}));
	};

	PinnedVerticalScroll.prototype.destroy = function () {
		this.dispatch('destroy');
		window.removeEventListener('scroll', this.onScroll);
		window.removeEventListener('resize', this.onResize);
		this.element.removeEventListener('load', this.onAssetLoad, true);
		if (this.resizeObserver) this.resizeObserver.disconnect();
		if (this.mutationObserver) this.mutationObserver.disconnect();
		window.cancelAnimationFrame(this.raf);
		window.clearTimeout(this.measureTimer);
		if (this.track && this.trackParent) {
			this.originalChildren.forEach(function (child, index) {
				var attributes = this.panelAttributes[index] || {};
				child.classList.remove('apexadfo-pvs-panel');
				['role', 'aria-roledescription', 'aria-label'].forEach(function (name) {
					var key = name === 'aria-roledescription' ? 'roledescription' : (name === 'aria-label' ? 'label' : 'role');
					if (attributes[key] === null || attributes[key] === undefined) child.removeAttribute(name);
					else child.setAttribute(name, attributes[key]);
				});
				this.trackParent.insertBefore(child, this.track);
			}, this);
			this.track.remove();
		}
		if (this.generatedStage && this.stage) {
			this.stageOriginalChildren.forEach(function (child) { this.element.insertBefore(child, this.stage); }, this);
			this.stage.remove();
		} else if (this.stage) {
			this.stage.classList.remove('apexadfo-pvs-stage');
		}
		this.element.classList.remove('apexadfo-pvs-fallback', 'apexadfo-pvs-editor-preview');
		this.element.style.height = this.originalElementHeight;
		this.element.style.removeProperty('--apexadfo-pvs-top');
		this.element.style.removeProperty('--apexadfo-pvs-stage-height');
		this.element.style.removeProperty('--apexadfo-pvs-gap');
		this.element.style.removeProperty('--apexadfo-pvs-translate');
	};

	function init(scope) {
		var root = scope && scope.nodeType ? scope : document;
		var elements = [];
		if (root.matches && root.matches('.apexadfo-pvs-active[data-apexadfo-pvs-config]')) elements.push(root);
		elements = elements.concat(Array.from(root.querySelectorAll ? root.querySelectorAll('.apexadfo-pvs-active[data-apexadfo-pvs-config]') : []));
		elements.forEach(function (element) {
			if (parseConfig(element).layoutMode !== 'direct') return;
			var previous = instances.get(element);
			if (previous) previous.destroy();
			var instance = new PinnedVerticalScroll(element);
			instances.set(element, instance);
		});
	}

	window.apexadfoPinnedVerticalScroll = {
		init: init,
		refresh: function (element) {
			var instance = instances.get(element);
			if (instance) instance.scheduleMeasure();
			else init(element);
		},
		getInstance: function (element) { return instances.get(element) || null; },
		registerInstance: function (element, instance) {
			if (element && instance) instances.set(element, instance);
		}
	};

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

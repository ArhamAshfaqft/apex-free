(function () {
	'use strict';

	var globalConfig = window.apexadfoFunnelConfig || { ajaxurl: '', i18n: {} };

	function create(tag, className, text) {
		var node = document.createElement(tag);
		if (className) node.className = className;
		if (text != null) node.textContent = text;
		return node;
	}

	function Funnel(root) {
		this.root = root;
		this.panel = root.querySelector('.eas-funnel-panel');
		this.stage = root.querySelector('.eas-funnel-stage');
		this.error = root.querySelector('.eas-funnel-error');
		this.progress = root.querySelector('.eas-funnel-progress');
		this.count = root.querySelector('.eas-funnel-step-count');
		this.back = root.querySelector('.eas-funnel-back');
		this.restart = root.querySelector('.eas-funnel-restart');
		this.launcher = root.querySelector('.eas-funnel-launcher');
		this.close = root.querySelector('.eas-funnel-close');
		this.honeypot = root.querySelector('.eas-funnel-honeypot');
		this.config = JSON.parse(root.getAttribute('data-eas-funnel-config') || '{}');
		this.steps = Array.isArray(this.config.steps) ? this.config.steps : [];
		this.stepMap = {};
		this.steps.forEach(function (step, index) { step._index = index; this.stepMap[step.id] = step; }, this);
		this.isEditor = !!(window.elementorFrontend && typeof window.elementorFrontend.isEditMode === 'function' && window.elementorFrontend.isEditMode());
		window.easFunnelEditorState = window.easFunnelEditorState || {};
		this.stateKey = root.id || ('funnel-' + (this.config.widgetId || 'preview'));
		var saved = this.isEditor ? window.easFunnelEditorState[this.stateKey] : null;
		this.current = saved && this.stepMap[saved.current] ? this.stepMap[saved.current] : (this.steps[0] || null);
		this.history = saved && Array.isArray(saved.history) ? saved.history.filter(function (id) { return !!this.stepMap[id]; }, this) : [];
		this.answers = saved && saved.answers ? Object.assign({}, saved.answers) : {};
		this.submitted = false;
		this.busy = false;
		this.lastFocus = null;
		this.abortController = typeof AbortController !== 'undefined' ? new AbortController() : null;
		this.bind();
		this.render();
	}

	Funnel.prototype.bind = function () {
		var self = this;
		var options = this.abortController ? { signal: this.abortController.signal } : false;
		if (this.launcher) this.launcher.addEventListener('click', function () { self.open(); }, options);
		if (this.close) this.close.addEventListener('click', function () { self.closePanel(); }, options);
		this.back.addEventListener('click', function () { self.goBack(); }, options);
		this.restart.addEventListener('click', function () { self.reset(); }, options);
		this.panel.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && self.config.mode !== 'inline' && self.config.mode !== 'fullscreen') self.closePanel();
			if (event.key === 'Tab' && self.config.mode === 'modal') self.trapFocus(event);
		}, options);
	};

	Funnel.prototype.destroy = function () {
		if (this.abortController) this.abortController.abort();
	};

	Funnel.prototype.trapFocus = function (event) {
		var focusable = Array.prototype.slice.call(this.panel.querySelectorAll('button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [href], [tabindex]:not([tabindex="-1"])')).filter(function (node) { return !node.hidden && node.offsetParent !== null; });
		if (!focusable.length) return;
		var first = focusable[0];
		var last = focusable[focusable.length - 1];
		if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
		else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
	};

	Funnel.prototype.open = function () {
		this.lastFocus = document.activeElement;
		this.panel.hidden = false;
		this.root.classList.add('eas-funnel-open');
		if (this.config.mode === 'modal') document.body.classList.add('eas-funnel-dialog-open');
		if (this.launcher) this.launcher.setAttribute('aria-expanded', 'true');
		var focusable = this.panel.querySelector('button, input, textarea, select');
		if (focusable) window.setTimeout(function () { focusable.focus(); }, 40);
	};

	Funnel.prototype.closePanel = function () {
		this.panel.hidden = true;
		this.root.classList.remove('eas-funnel-open');
		if (this.config.mode === 'modal' && !document.querySelector('.eas-funnel-mode-modal.eas-funnel-open')) document.body.classList.remove('eas-funnel-dialog-open');
		if (this.launcher) this.launcher.setAttribute('aria-expanded', 'false');
		if (this.lastFocus && this.lastFocus.focus) this.lastFocus.focus();
	};

	Funnel.prototype.setError = function (message) {
		this.error.textContent = message || '';
		this.error.hidden = !message;
	};

	Funnel.prototype.render = function () {
		if (!this.current) return;
		if (this.isEditor) {
			window.easFunnelEditorState[this.stateKey] = {
				current: this.current.id,
				history: this.history.slice(),
				answers: Object.assign({}, this.answers)
			};
		}
		this.setError('');
		this.stage.replaceChildren();
		var step = this.current;
		var content = create('div', 'eas-funnel-step eas-funnel-step-' + step.type);
		var heading = null;
		if (step.title) {
			heading = create('h3', 'eas-funnel-question', step.title);
			heading.tabIndex = -1;
			content.appendChild(heading);
		}
		if (step.description) content.appendChild(create('p', 'eas-funnel-description', step.description));
		this.renderStepContent(content, step);
		if (!heading) heading = content.querySelector('.eas-funnel-question');
		if (heading) heading.tabIndex = -1;
		this.stage.appendChild(content);

		var visible = this.steps.filter(function (item) { return item.type === 'form'; });
		var activeNumber = Math.max(1, visible.indexOf(step) + 1);
		var percent = visible.length ? Math.min(100, Math.round((activeNumber / visible.length) * 100)) : 100;
		var showProgress = !!this.config.showProgress;
		this.progress.hidden = !showProgress;
		this.progress.style.display = showProgress ? '' : 'none';
		if (showProgress && this.progress.querySelector('span')) {
			this.progress.querySelector('span').style.width = percent + '%';
		}

		var showCount = !!(this.config.showStepCount && step.type === 'form');
		this.count.hidden = !showCount;
		this.count.style.display = showCount ? '' : 'none';
		this.count.textContent = showCount ? 'Step ' + activeNumber + ' of ' + visible.length : '';

		var showBack = !!(step.type !== 'success' && (this.history.length > 0 || (this.isEditor && this.config.allowBack !== false)));
		this.back.textContent = this.config.labels.back || 'Back';
		this.back.hidden = !showBack;
		this.back.style.display = showBack ? '' : 'none';
		this.back.disabled = !this.history.length;

		var showRestart = !!this.config.allowRestart;
		this.restart.textContent = this.config.labels.restart || 'Start again';
		this.restart.hidden = !showRestart;
		this.restart.style.display = showRestart ? '' : 'none';
		var isEditor = this.isEditor;
		window.requestAnimationFrame(function () {
			content.classList.add('is-active');
			if (!isEditor && heading) heading.focus({ preventScroll: true });
		});
	};

	Funnel.prototype.renderStepContent = function (content, step) {
		var self = this;
		var fields = step.fields || [];
		if (fields.length) {
			var grid = create('div', 'eas-funnel-field-grid');
			fields.forEach(function (field) { self.renderField(grid, field); });
			content.appendChild(grid);
		}
		if (step.type === 'success') return;
		// Navigation is intentionally rendered only by a placed Button item.
	};

	Funnel.prototype.renderField = function (grid, field) {
		var self = this;
		if (field.type === 'hidden') {
			this.answers[field.id] = field.default || '';
			return;
		}
		var wrap = create('div', 'eas-funnel-field-wrap eas-funnel-field-' + field.type);
		wrap.dataset.fieldId = field.id;
		wrap.style.setProperty('--eas-field-width', (field.width || '100') + '%');
		wrap.style.setProperty('--eas-field-width-tablet', (field.width_tablet || '100') + '%');
		wrap.style.setProperty('--eas-field-width-mobile', (field.width_mobile || '100') + '%');
		if (field.type === 'heading') {
			var tag = /^(h[1-6]|div)$/.test(field.tag || '') ? field.tag : 'h3';
			var fieldHeading = create(tag, 'eas-funnel-question eas-funnel-content-heading', field.content || '');
			wrap.appendChild(fieldHeading); grid.appendChild(wrap); return;
		}
		if (field.type === 'description') {
			wrap.appendChild(create('div', 'eas-funnel-description eas-funnel-content-description', field.content || ''));
			grid.appendChild(wrap); return;
		}
		if (field.type === 'result') {
			if (field.show_icon !== false) {
				var result = create('div', 'eas-funnel-success-message');
				result.innerHTML = '<span aria-hidden="true">&#10003;</span>';
				wrap.appendChild(result);
			}
			grid.appendChild(wrap); return;
		}
		if (field.type === 'button') {
			var action = create('div', 'eas-funnel-action-row');
			action.appendChild(this.actionButton(field.button_label || this.config.labels.continue, function () {
				self.collectCurrentStep(); self.advance();
			}));
			wrap.appendChild(action); grid.appendChild(wrap); return;
		}
		if (field.type === 'html') {
			var html = create('div', 'eas-funnel-html');
			html.innerHTML = field.content || '';
			wrap.appendChild(html);
			grid.appendChild(wrap);
			return;
		}
		if (field.label && field.type !== 'acceptance') {
			var label = create('label', 'eas-funnel-field-label', field.label);
			label.htmlFor = this.root.id + '-' + field.id;
			if (field.required) label.appendChild(create('span', 'eas-funnel-required-mark', ' *'));
			wrap.appendChild(label);
		}

		var existing = this.answers[field.id];
		if (field.type === 'radio' || field.type === 'checkbox') {
			var choices = create('div', 'eas-funnel-choices-grid eas-funnel-field-choices');
			choices.setAttribute('role', field.type === 'radio' ? 'radiogroup' : 'group');
			(field.options || []).forEach(function (option, optionIndex) {
				var selected = field.type === 'checkbox' ? Array.isArray(existing) && existing.indexOf(option.value) !== -1 : existing === option.value;
				var optionLabel = create('label', 'eas-funnel-choice eas-funnel-option eas-funnel-option-' + field.type);
				var control = create('input', 'eas-funnel-option-control');
				control.type = field.type;
				control.name = self.root.id + '-' + field.id;
				control.value = option.value;
				control.checked = selected;
				control.id = self.root.id + '-' + field.id + '-' + optionIndex;
				var indicator = create('span', 'eas-funnel-option-indicator');
				var text = create('span', 'eas-funnel-option-label', option.label);
				optionLabel.appendChild(control); optionLabel.appendChild(indicator); optionLabel.appendChild(text);
				optionLabel.classList.toggle('is-selected', selected);
				control.addEventListener('change', function () {
					if (field.type === 'radio') {
						self.answers[field.id] = option.value;
						choices.querySelectorAll('.eas-funnel-option').forEach(function (item) { item.classList.remove('is-selected'); });
						optionLabel.classList.add('is-selected');
					} else {
						var values = Array.isArray(self.answers[field.id]) ? self.answers[field.id].slice() : [];
						var position = values.indexOf(option.value);
						if (position === -1) values.push(option.value); else values.splice(position, 1);
						self.answers[field.id] = values;
						optionLabel.classList.toggle('is-selected', control.checked);
					}
				});
				choices.appendChild(optionLabel);
			});
			wrap.appendChild(choices);
		} else if (field.type === 'select') {
			var select = create('select', 'eas-funnel-input');
			select.id = this.root.id + '-' + field.id;
			select.dataset.easInput = field.id;
			select.appendChild(new Option(field.placeholder || this.config.labels.select, ''));
			(field.options || []).forEach(function (option) { select.appendChild(new Option(option.label, option.value)); });
			select.value = existing == null ? (field.default || '') : existing;
			wrap.appendChild(select);
		} else if (field.type === 'acceptance') {
			var acceptance = create('label', 'eas-funnel-acceptance');
			var checkbox = create('input'); checkbox.type = 'checkbox'; checkbox.id = this.root.id + '-' + field.id; checkbox.dataset.easInput = field.id; checkbox.checked = !!existing;
			acceptance.appendChild(checkbox);
			var acceptanceText = create('span', '', field.placeholder || field.label);
			if (field.required) acceptanceText.appendChild(create('span', 'eas-funnel-required-mark', ' *'));
			acceptance.appendChild(acceptanceText); wrap.appendChild(acceptance);
		} else {
			var input;
			if (field.type === 'textarea') { input = create('textarea', 'eas-funnel-input eas-funnel-textarea'); input.rows = 4; }
			else { input = create('input', 'eas-funnel-input'); input.type = field.type === 'tel' ? 'tel' : field.type; }
			input.id = this.root.id + '-' + field.id;
			input.dataset.easInput = field.id;
			input.value = existing == null ? (field.default || '') : existing;
			input.placeholder = field.placeholder || '';
			input.addEventListener('keydown', function (event) { if (event.key === 'Enter' && field.type !== 'textarea') { event.preventDefault(); self.collectCurrentStep(); self.advance(); } });
			wrap.appendChild(input);
		}
		grid.appendChild(wrap);
	};

	Funnel.prototype.collectCurrentStep = function () {
		var self = this;
		this.stage.querySelectorAll('[data-eas-input]').forEach(function (input) {
			self.answers[input.dataset.easInput] = input.type === 'checkbox' ? input.checked : input.value.trim();
		});
	};

	Funnel.prototype.valid = function () {
		var self = this;
		var valid = true;
		(this.current.fields || []).some(function (field) {
			if (['heading', 'description', 'button', 'result', 'html', 'hidden'].indexOf(field.type) !== -1) return false;
			var value = self.answers[field.id];
			var empty = value == null || value === '' || value === false || (Array.isArray(value) && !value.length);
			if (field.required && empty) { self.setError((field.label || 'This field') + ' is required.'); valid = false; return true; }
			if (!empty && field.type === 'email' && !/^\S+@\S+\.\S+$/.test(value)) { self.setError(globalConfig.i18n.email || 'Please enter a valid email.'); valid = false; return true; }
			if (!empty && field.type === 'number' && isNaN(Number(value))) { self.setError('Please enter a valid number.'); valid = false; return true; }
			return false;
		});
		return valid;
	};

	Funnel.prototype.nextStep = function () {
		var self = this;
		var route = (this.current.routes || []).find(function (item) {
			if (item.answer === '') return false;
			var value = self.answers[item.field];
			var values = Array.isArray(value) ? value.map(String) : [String(value == null ? '' : value)];
			return values.indexOf(String(item.answer)) !== -1;
		});
		if (route && this.stepMap[route.next]) return this.stepMap[route.next];
		if (this.current.next && this.stepMap[this.current.next]) return this.stepMap[this.current.next];
		return this.steps[this.current._index + 1] || null;
	};

	Funnel.prototype.advance = function () {
		if (this.busy || !this.valid()) return;
		var next = this.nextStep();
		if (!next) { this.submit(null); return; }
		if (next.type === 'success' && !this.submitted) { this.submit(next); return; }
		this.history.push(this.current.id); this.current = next; this.render();
	};

	Funnel.prototype.goBack = function () {
		if (!this.history.length || this.busy) return;
		var id = this.history.pop();
		if (this.stepMap[id]) { this.current = this.stepMap[id]; this.render(); }
	};

	Funnel.prototype.actionButton = function (label, handler, className) {
		var button = create('button', className || 'eas-funnel-button', label);
		button.type = 'button'; button.addEventListener('click', handler); return button;
	};

	Funnel.prototype.appendAction = function (content, button) {
		var row = create('div', 'eas-funnel-action-row'); row.appendChild(button); content.appendChild(row);
	};

	Funnel.prototype.submit = function (successStep) {
		var self = this;
		this.busy = true; this.setError('');
		var button = this.stage.querySelector('.eas-funnel-button');
		if (button) { button.disabled = true; button.dataset.label = button.textContent; button.textContent = globalConfig.i18n.sending || 'Sending...'; }
		var data = new FormData();
		data.append('action', 'apexadfo_funnel_submit'); data.append('nonce', this.config.nonce);
		data.append('widget_id', this.config.widgetId); data.append('page_id', this.config.pageId || 0); data.append('answers', JSON.stringify(this.answers));
		data.append('website', this.honeypot.value || ''); data.append('source_url', window.location.href); data.append('referrer', document.referrer || '');
		fetch(globalConfig.ajaxurl, { method: 'POST', credentials: 'same-origin', body: data }).then(function (response) { return response.json(); }).then(function (response) {
			if (!response.success) throw new Error((response.data && response.data.message) || globalConfig.i18n.error);
			self.submitted = true; self.history.push(self.current.id);
			if (successStep) { self.current = successStep; if (!successStep.description && response.data.message) successStep.description = response.data.message; self.render(); }
			else { self.stage.replaceChildren(create('p', 'eas-funnel-description', response.data.message || 'Thank you.')); }
		}).catch(function (error) {
			self.setError(error.message || globalConfig.i18n.error || 'Something went wrong.');
			if (button) { button.disabled = false; button.textContent = button.dataset.label || self.config.labels.submit; }
		}).finally(function () { self.busy = false; });
	};

	Funnel.prototype.reset = function () {
		if (this.busy) return;
		this.current = this.steps[0] || null; this.history = []; this.answers = {}; this.submitted = false; this.render();
	};

	function initialize(scope) {
		(scope || document).querySelectorAll('.eas-conversational-funnel').forEach(function (root) {
			var signature = root.getAttribute('data-eas-funnel-config') || '';
			var isEditor = !!(window.elementorFrontend && typeof window.elementorFrontend.isEditMode === 'function' && window.elementorFrontend.isEditMode());
			if (root.getAttribute('data-eas-ready') === 'true' && (!isEditor || root.getAttribute('data-eas-config-signature') === signature)) return;
			root.setAttribute('data-eas-ready', 'true');
			root.setAttribute('data-eas-config-signature', signature);
			try {
				if (root.easFunnelInstance && typeof root.easFunnelInstance.destroy === 'function') root.easFunnelInstance.destroy();
				root.easFunnelInstance = new Funnel(root);
			} catch (error) { root.removeAttribute('data-eas-ready'); if (window.console) console.error('Apex Funnel:', error); }
		});
	}

	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { initialize(document); }); else initialize(document);
	window.addEventListener('elementor/frontend/init', function () {
		if (window.elementorFrontend && elementorFrontend.hooks) elementorFrontend.hooks.addAction('frontend/element_ready/eas-conversational-funnel.default', function ($scope) { initialize($scope[0]); });
	});
})();

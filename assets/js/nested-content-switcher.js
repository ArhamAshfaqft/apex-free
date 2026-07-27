/**
 * Apex Nested Content Switcher.
 */
(function ($) {
	'use strict';

	function initSwitcher($scope) {
		var previousDestroy = $scope.data('apexadfoNestedSwitcherDestroy');
		if (typeof previousDestroy === 'function') {
			previousDestroy();
		}

		var root = $scope[0].querySelector('.apexadfo-nested-switcher');
		if (!root) {
			return;
		}

		var config = {};
		try {
			config = JSON.parse(root.getAttribute('data-apexadfo-switcher') || '{}');
		} catch (error) {
			config = {};
		}

		var tabs = Array.prototype.slice.call(root.querySelectorAll('.apexadfo-switcher-tab'));
		var panelsContainer = root.querySelector('.apexadfo-switcher-panels');
		var panels = panelsContainer ? Array.prototype.slice.call(panelsContainer.children).filter(function (child) {
			return child.classList.contains('e-con');
		}) : [];
		var viewport = root.querySelector('.apexadfo-switcher-viewport');
		var duration = Math.max(0, Math.min(3000, parseInt(config.duration, 10) || 0));
		var activeIndex = -1;
		var cleanup = [];
		var heightTimer = 0;
		var animationTimer = 0;

		if (!tabs.length || !panels.length || !viewport) {
			return;
		}
		var panelCount = Math.min(tabs.length, panels.length);
		tabs = tabs.slice(0, panelCount);
		panels = panels.slice(0, panelCount);
		panels.forEach(function (panel, index) {
			panel.classList.add('apexadfo-switcher-panel');
			panel.setAttribute('role', 'tabpanel');
			panel.setAttribute('tabindex', '0');
			panel.id = root.id + '-panel-' + index;
			panel.setAttribute('aria-labelledby', root.id + '-tab-' + index);
		});

		root.style.setProperty('--apexadfo-switcher-duration', duration + 'ms');
		root.classList.add('apexadfo-transition-' + (config.transition || 'fade'));

		function hashIndex() {
			if (!config.deepLinking || !window.location.hash) {
				return -1;
			}
			var hash = decodeURIComponent(window.location.hash.slice(1));
			return tabs.findIndex(function (tab) {
				return tab.getAttribute('data-slug') === hash || root.id + '-' + tab.getAttribute('data-slug') === hash;
			});
		}

		function updateHash(tab) {
			if (!config.deepLinking || !tab || window.elementorFrontend && window.elementorFrontend.isEditMode()) {
				return;
			}
			var slug = tab.getAttribute('data-slug');
			if (!slug || window.location.hash.slice(1) === slug) {
				return;
			}
			if (window.history && window.history.replaceState) {
				window.history.replaceState(null, '', window.location.pathname + window.location.search + '#' + encodeURIComponent(slug));
			} else {
				window.location.hash = slug;
			}
		}

		function animateViewport(oldHeight, newHeight) {
			window.clearTimeout(heightTimer);
			viewport.classList.remove('apexadfo-is-resizing');
			if (!config.animateHeight || !duration || oldHeight === newHeight) {
				viewport.style.height = '';
				return;
			}
			viewport.classList.add('apexadfo-is-resizing');
			viewport.style.height = oldHeight + 'px';
			void viewport.offsetHeight;
			viewport.style.height = newHeight + 'px';
			heightTimer = window.setTimeout(function () {
				viewport.style.height = '';
				viewport.classList.remove('apexadfo-is-resizing');
			}, duration + 40);
		}

		function activate(index, options) {
			options = options || {};
			index = Math.max(0, Math.min(panels.length - 1, parseInt(index, 10) || 0));
			if (index === activeIndex && !options.force) {
				return;
			}

			var oldHeight = viewport.getBoundingClientRect().height;
			window.clearTimeout(animationTimer);
			var allPanels = panelsContainer ? Array.prototype.slice.call(panelsContainer.children).filter(function (child) {
				return child.classList.contains('e-con');
			}) : [];

			allPanels.forEach(function (panel, panelIndex) {
				var active = panelIndex === index;
				panel.hidden = !active;
				panel.classList.toggle('apexadfo-is-active', active);
				panel.classList.remove('apexadfo-is-entering');
			});
			tabs.forEach(function (tab, tabIndex) {
				var active = tabIndex === index;
				tab.setAttribute('aria-selected', active ? 'true' : 'false');
				tab.setAttribute('tabindex', active ? '0' : '-1');
			});

			var activePanel = panels[index] || allPanels[index];
			if (activePanel) {
				var newHeight = activePanel.getBoundingClientRect().height;
				animateViewport(oldHeight, newHeight);
				if (!options.initial && duration && config.transition !== 'none') {
					void activePanel.offsetWidth;
					activePanel.classList.add('apexadfo-is-entering');
					animationTimer = window.setTimeout(function () {
						activePanel.classList.remove('apexadfo-is-entering');
					}, duration + 40);
				}
			}

			activeIndex = index;
			if (tabs[index]) {
				var navContainer = root.querySelector('.apexadfo-switcher-nav');
				if (navContainer && navContainer.scrollWidth > navContainer.clientWidth && typeof tabs[index].scrollIntoView === 'function') {
					tabs[index].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
				}
			}
			if (options.focus) {
				tabs[index].focus();
			}
			if (options.hash) {
				updateHash(tabs[index]);
			}
			root.dispatchEvent(new CustomEvent('apexadfo:switcher-change', { detail: { index: index, slug: tabs[index].getAttribute('data-slug') } }));
		}

		tabs.forEach(function (tab, index) {
			function onClick() {
				activate(index, { hash: true });
			}
			function onKeyDown(event) {
				var next = index;
				if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
					next = (index + 1) % tabs.length;
				} else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
					next = (index - 1 + tabs.length) % tabs.length;
				} else if (event.key === 'Home') {
					next = 0;
				} else if (event.key === 'End') {
					next = tabs.length - 1;
				} else {
					return;
				}
				event.preventDefault();
				activate(next, { focus: true, hash: true });
			}
			tab.addEventListener('click', onClick);
			tab.addEventListener('keydown', onKeyDown);
			cleanup.push(function () {
				tab.removeEventListener('click', onClick);
				tab.removeEventListener('keydown', onKeyDown);
			});
		});

		function onHashChange() {
			var index = hashIndex();
			if (index >= 0) {
				activate(index);
			}
		}
		if (config.deepLinking) {
			window.addEventListener('hashchange', onHashChange);
			cleanup.push(function () { window.removeEventListener('hashchange', onHashChange); });
		}

		if (window.elementorFrontend && window.elementorFrontend.isEditMode() && window.MutationObserver) {
			var observer = new MutationObserver(function (mutations) {
				var hasChildChange = false;
				mutations.forEach(function (m) {
					if (m.addedNodes.length || m.removedNodes.length) {
						hasChildChange = true;
					}
				});
				if (hasChildChange) {
					initSwitcher($scope);
				}
			});
			if (panelsContainer) {
				observer.observe(panelsContainer, { childList: true });
			}
			var navObsContainer = root.querySelector('.apexadfo-switcher-nav');
			if (navObsContainer) {
				observer.observe(navObsContainer, { childList: true });
			}
			cleanup.push(function () { observer.disconnect(); });
		}

		var initial = hashIndex();
		if (initial < 0) {
			initial = Math.max(0, Math.min(panels.length - 1, parseInt(config.initialPanel, 10) || 0));
		}
		activate(initial, { initial: true, force: true });

		$scope.data('apexadfoNestedSwitcherDestroy', function () {
			window.clearTimeout(heightTimer);
			window.clearTimeout(animationTimer);
			cleanup.forEach(function (destroy) { destroy(); });
			viewport.classList.remove('apexadfo-is-resizing');
			viewport.style.height = '';
		});
	}

	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/eas-nested-content-switcher.default', initSwitcher);
	});
})(jQuery);

(function () {
	'use strict';

	var localStore = getStorage('localStorage');
	var sessionStore = getStorage('sessionStorage');
	var localOK = storageAvailable(localStore);
	var sessionOK = storageAvailable(sessionStore);
	var pageViews = updateVisitCounters();

	function getStorage(name) {
		try { return window[name] || null; } catch (error) { return null; }
	}

	function storageAvailable(storage) {
		if (!storage) return false;
		try {
			var key = '__apexadfo_popup_test__';
			storage.setItem(key, key);
			storage.removeItem(key);
			return true;
		} catch (error) { return false; }
	}

	function readNumber(storage, key) {
		if (!storage) return 0;
		return Math.max(0, parseInt(storage.getItem(key), 10) || 0);
	}

	function updateVisitCounters() {
		var result = { views: 1, sessions: 1 };
		if (!localOK) return result;
		result.views = readNumber(localStore, 'apexadfo_popup_page_views') + 1;
		localStore.setItem('apexadfo_popup_page_views', String(result.views));
		result.sessions = readNumber(localStore, 'apexadfo_popup_sessions');
		if (sessionOK && sessionStore.getItem('apexadfo_popup_session_seen') !== 'yes') {
			result.sessions += 1;
			localStore.setItem('apexadfo_popup_sessions', String(result.sessions));
			sessionStore.setItem('apexadfo_popup_session_seen', 'yes');
		}
		result.sessions = Math.max(1, result.sessions);
		return result;
	}

	function safeClosest(target, selector) {
		if (!selector || !target || typeof target.closest !== 'function') return null;
		try { return target.closest(selector); } catch (error) { return null; }
	}

	function safeQuery(selector) {
		if (!selector) return null;
		try { return document.querySelector(selector); } catch (error) { return null; }
	}

	function getDevice() {
		var width = window.innerWidth || document.documentElement.clientWidth;
		if (width <= 767) return 'mobile';
		if (width <= 1024) return 'tablet';
		return 'desktop';
	}

	function getBrowser() {
		var agent = navigator.userAgent || '';
		if (/Edg\//.test(agent)) return 'edge';
		if (/Firefox\//.test(agent)) return 'firefox';
		if (/Chrome\//.test(agent) && !/Edg\//.test(agent)) return 'chrome';
		if (/Safari\//.test(agent) && !/Chrome\//.test(agent)) return 'safari';
		return 'other';
	}

	function initPopup(popup) {
		if (popup.dataset.apexadfoReady === 'yes') return;
		popup.dataset.apexadfoReady = 'yes';

		var settings;
		try { settings = JSON.parse(popup.dataset.settings || '{}'); } catch (error) { settings = {}; }
		var id = String(popup.dataset.popupId || '');
		var storageKey = 'apexadfo_popup_' + id;
		var countKey = storageKey + '_count';
		var dialog = popup.querySelector('.apexadfo-popup__dialog');
		var previousFocus = null;
		var opened = false;
		var transitioning = false;
		var closeTimer = 0;
		var inactivityTimer = 0;
		var observer = null;
		var cleanups = [];

		function addListener(target, name, callback, options) {
			target.addEventListener(name, callback, options);
			cleanups.push(function () { target.removeEventListener(name, callback, options); });
		}

		function shownCount() {
			return localOK ? readNumber(localStore, countKey) : 0;
		}

		function blockedByFrequency() {
			if (Number(settings.max_shows) > 0 && shownCount() >= Number(settings.max_shows)) return true;
			if (settings.frequency === 'session' && sessionOK) return sessionStore.getItem(storageKey) === 'shown';
			if (settings.frequency === 'once' && localOK) return localStore.getItem(storageKey) === 'shown';
			return false;
		}

		function matchesRules() {
			var devices = String(settings.devices || 'desktop,tablet,mobile').split(',');
			if (devices.indexOf(getDevice()) === -1) return false;
			if (settings.user_state === 'logged_in' && settings.is_logged_in !== 'yes') return false;
			if (settings.user_state === 'logged_out' && settings.is_logged_in === 'yes') return false;
			if (settings.browser && settings.browser !== 'all' && settings.browser !== getBrowser()) return false;
			if (settings.url_contains && window.location.href.indexOf(settings.url_contains) === -1) return false;
			if (settings.referrer_contains && document.referrer.indexOf(settings.referrer_contains) === -1) return false;
			if (Number(settings.min_page_views) > pageViews.views) return false;
			if (Number(settings.min_sessions) > pageViews.sessions) return false;
			var now = Date.now();
			var start = settings.schedule_start ? Date.parse(settings.schedule_start) : 0;
			var end = settings.schedule_end ? Date.parse(settings.schedule_end) : 0;
			if (start && now < start) return false;
			if (end && now > end) return false;
			return true;
		}

		function rememberShown() {
			if (settings.frequency === 'session' && sessionOK) sessionStore.setItem(storageKey, 'shown');
			if (settings.frequency === 'once' && localOK) localStore.setItem(storageKey, 'shown');
			if (localOK) localStore.setItem(countKey, String(shownCount() + 1));
		}

		function focusableElements() {
			return Array.prototype.slice.call(popup.querySelectorAll('button:not([disabled]), a[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])')).filter(function (element) {
				return element.offsetWidth > 0 || element.offsetHeight > 0;
			});
		}

		function updateBodyLock() {
			var needsLock = document.querySelector('.apexadfo-popup.is-open[data-prevent-scroll="yes"], .apexadfo-popup.is-closing[data-prevent-scroll="yes"]');
			document.body.classList.toggle('apexadfo-popup-open', Boolean(needsLock));
		}

		function openPopup(options) {
			options = options || {};
			if (opened || transitioning || !matchesRules()) return;
			if (!options.ignoreFrequency && blockedByFrequency()) return;
			window.clearTimeout(closeTimer);
			opened = true;
			transitioning = true;
			previousFocus = document.activeElement;
			popup.hidden = false;
			popup.setAttribute('aria-hidden', 'false');
			popup.dataset.preventScroll = settings.prevent_scroll === 'yes' ? 'yes' : 'no';
			popup.classList.remove('is-closing');
			popup.classList.add('is-opening');
			window.requestAnimationFrame(function () {
				window.requestAnimationFrame(function () {
					popup.classList.add('is-open');
					popup.classList.remove('is-opening');
					transitioning = false;
					updateBodyLock();
				});
			});
			rememberShown();
			var targets = focusableElements();
			window.setTimeout(function () { (targets[0] || dialog).focus(); }, 0);
			popup.dispatchEvent(new CustomEvent('apexadfo:popup:opened', { bubbles: true, detail: { id: id } }));
		}

		function closePopup() {
			if (!opened || transitioning) return;
			opened = false;
			transitioning = true;
			popup.classList.add('is-closing');
			popup.classList.remove('is-open');
			updateBodyLock();
			closeTimer = window.setTimeout(function () {
				popup.hidden = true;
				popup.setAttribute('aria-hidden', 'true');
				popup.classList.remove('is-closing', 'is-opening');
				transitioning = false;
				updateBodyLock();
				if (previousFocus && typeof previousFocus.focus === 'function') previousFocus.focus();
				popup.dispatchEvent(new CustomEvent('apexadfo:popup:closed', { bubbles: true, detail: { id: id } }));
			}, Math.max(0, Number(settings.animation_duration) || 0));
		}

		function scheduleOpen() {
			window.setTimeout(openPopup, Math.max(0, Number(settings.delay) || 0));
		}

		addListener(popup, 'click', function (event) {
			var closer = safeClosest(event.target, '[data-apexadfo-popup-close]');
			if (!closer) return;
			if (closer.classList.contains('apexadfo-popup__overlay') && settings.close_overlay !== 'yes') return;
			closePopup();
		});

		addListener(document, 'keydown', function (event) {
			if (!opened) return;
			if (event.key === 'Escape' && settings.close_escape === 'yes') {
				event.preventDefault();
				closePopup();
				return;
			}
			if (event.key !== 'Tab') return;
			var targets = focusableElements();
			if (!targets.length) { event.preventDefault(); dialog.focus(); return; }
			var first = targets[0];
			var last = targets[targets.length - 1];
			if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
			else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
		});

		addListener(document, 'apexadfo:popup:open', function (event) {
			if (String(event.detail && event.detail.id) === id) openPopup({ ignoreFrequency: Boolean(event.detail && event.detail.ignoreFrequency) });
		});
		addListener(document, 'apexadfo:popup:close', function (event) {
			if (String(event.detail && event.detail.id) === id) closePopup();
		});

		if (!matchesRules() || blockedByFrequency()) return;

		if (settings.trigger_load === 'yes') scheduleOpen();
		if (settings.trigger_scroll === 'yes') {
			var onScroll = function () {
				var scrollable = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
				if ((window.scrollY / scrollable) * 100 >= (Number(settings.scroll_percent) || 50)) {
					window.removeEventListener('scroll', onScroll);
					scheduleOpen();
				}
			};
			addListener(window, 'scroll', onScroll, { passive: true });
			onScroll();
		}
		if (settings.trigger_element === 'yes' && 'IntersectionObserver' in window) {
			var watched = safeQuery(settings.element_selector);
			if (watched) {
				observer = new IntersectionObserver(function (entries) {
					if (entries.some(function (entry) { return entry.isIntersecting; })) { observer.disconnect(); scheduleOpen(); }
				}, { threshold: 0.2 });
				observer.observe(watched);
			}
		}
		if (settings.trigger_click === 'yes' && settings.click_selector) {
			addListener(document, 'click', function (event) {
				if (!safeClosest(event.target, settings.click_selector)) return;
				event.preventDefault();
				scheduleOpen();
			});
		}
		if (settings.trigger_inactivity === 'yes') {
			var resetInactivity = function () {
				window.clearTimeout(inactivityTimer);
				inactivityTimer = window.setTimeout(openPopup, Math.max(1, Number(settings.inactivity_seconds) || 30) * 1000);
			};
			['pointerdown', 'pointermove', 'keydown', 'scroll', 'touchstart'].forEach(function (name) { addListener(document, name, resetInactivity, { passive: true }); });
			resetInactivity();
		}
		if (settings.trigger_exit === 'yes' && getDevice() === 'desktop') {
			var onExit = function (event) {
				if (event.clientY > 0 || event.relatedTarget) return;
				document.removeEventListener('mouseout', onExit);
				scheduleOpen();
			};
			addListener(document, 'mouseout', onExit);
		}
	}

	function init() {
		document.querySelectorAll('.apexadfo-popup').forEach(initPopup);
	}

	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
	else init();
}());

(function () {
	'use strict';
	var config = window.apexadfoPreloader || {};
	var key = 'apexadfo_preloader_' + (config.templateId || 'basic') + '_limit';
	var now = Date.now();
	var shouldShow = true;
	try {
		if (config.frequency === 'once_session') shouldShow = !window.sessionStorage.getItem(key);
		else if (config.frequency && config.frequency !== 'always') {
			var expiry = parseInt(window.localStorage.getItem(key), 10);
			shouldShow = !expiry || now >= expiry;
		}
	} catch (error) { shouldShow = true; }

	function recordShow() {
		try {
			if (config.frequency === 'once_session') window.sessionStorage.setItem(key, 'yes');
			else if (config.frequency && config.frequency !== 'always') {
				var hours = config.frequency === 'once_week' ? 168 : (config.frequency === 'once_day' ? 24 : (parseInt(config.frequencyHours, 10) || 24));
				window.localStorage.setItem(key, String(Date.now() + hours * 3600000));
			}
		} catch (error) {}
	}

	function ready() {
		var wrap = document.getElementById('eas-preloader-wrap');
		if (!wrap) return;
		if (!shouldShow) { wrap.remove(); return; }
		document.body.classList.add('eas-preloader-active');
		var start = Date.now();
		var completed = false;
		var percent = document.getElementById('eas-preloader-percent-num');
		var minimum = Math.max(0, Number(config.minDuration || 1) * 1000);
		var maximum = Math.max(minimum, Number(config.maxDuration || 5) * 1000);
		var transition = Math.max(0, Number(config.transitionSpeed || 0.6) * 1000);

		if (config.svgDraw && config.svgSelector) {
			document.querySelectorAll(config.svgSelector).forEach(function (node) {
				if (typeof node.getTotalLength !== 'function') return;
				var length = node.getTotalLength();
				node.style.strokeDasharray = length;
				node.style.strokeDashoffset = length;
				node.style.transition = 'stroke-dashoffset ' + Number(config.svgDuration || 2) + 's ease';
				window.requestAnimationFrame(function () { node.style.strokeDashoffset = '0'; });
			});
		}

		function finish() {
			if (completed) return;
			var remaining = minimum - (Date.now() - start);
			if (remaining > 0) { window.setTimeout(finish, remaining); return; }
			completed = true;
			recordShow();
			if (percent) percent.textContent = '100%';
			wrap.classList.add('eas-preloader-wrap-loaded');
			window.setTimeout(function () {
				wrap.remove();
				document.body.classList.remove('eas-preloader-active');
			}, transition + 100);
		}

		if (percent) {
			var value = 0;
			var timer = window.setInterval(function () {
				value = Math.min(99, value + 1);
				percent.textContent = value + '%';
				if (completed) window.clearInterval(timer);
			}, Math.max(15, minimum / 100));
		}

		if (config.exitTrigger === 'custom') document.addEventListener('eas_preloader_close', finish, { once: true });
		else window.addEventListener('load', finish, { once: true });
		window.setTimeout(finish, maximum);
	}

	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready, { once: true });
	else ready();
})();

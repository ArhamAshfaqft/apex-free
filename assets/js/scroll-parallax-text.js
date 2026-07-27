(function ($) {
	'use strict';

	function initScrollText($scope) {
		if (window.apexadfoScrollMarqueeProRuntime) return;
		var cleanups = [];
		$scope.find('.eas-scroll-parallax-text-wrap').each(function () {
			var wrap = this;
			var row = wrap.querySelector('.eas-scroll-parallax-text-inner');
			if (!row) return;
			var direction = wrap.getAttribute('data-direction') === 'ltr' ? 1 : -1;
			var speed = Math.max(0.1, parseFloat(wrap.getAttribute('data-speed')) || 1);
			var frame = 0;
			var update = function () {
				frame = 0;
				var rect = wrap.getBoundingClientRect();
				var progress = (window.innerHeight - rect.top) / Math.max(1, window.innerHeight + rect.height);
				var distance = (progress - 0.5) * 22 * speed * direction;
				row.style.transform = 'translate3d(' + distance.toFixed(3) + '%,0,0)';
			};
			var requestUpdate = function () {
				if (!frame) frame = window.requestAnimationFrame(update);
			};
			window.addEventListener('scroll', requestUpdate, { passive: true });
			window.addEventListener('resize', requestUpdate);
			requestUpdate();
			cleanups.push(function () {
				window.removeEventListener('scroll', requestUpdate);
				window.removeEventListener('resize', requestUpdate);
				if (frame) window.cancelAnimationFrame(frame);
				row.style.transform = '';
			});
		});
		$scope.data('apexadfo-scroll-text-cleanup', function () {
			cleanups.forEach(function (cleanup) { cleanup(); });
		});
	}

	$(window).on('elementor/frontend/init', function () {
		if (window.apexadfoScrollMarqueeProRuntime) return;
		elementorFrontend.hooks.addAction('frontend/element_ready/eas-scroll-parallax-text.default', initScrollText);
	});
})(jQuery);

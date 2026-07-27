(function ($) {
	'use strict';

	var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function destroy($scope) {
		$scope.find('.eas-premium-typography-morph').each(function () {
			var intervalId = $(this).data('eas-morph-interval');
			if (intervalId) window.clearInterval(intervalId);
			$(this).removeData('eas-morph-interval');
		});
		$scope.find('.eas-premium-typography-scramble').each(function () {
			var $element = $(this);
			var intervalId = $element.data('eas-scramble-interval');
			var observer = $element.data('eas-scramble-observer');
			if (intervalId) window.clearInterval(intervalId);
			if (observer) observer.disconnect();
			$element.removeData('eas-scramble-interval eas-scramble-observer').off('mouseenter.easScramble');
		});
	}

	function initMorph($scope) {
		$scope.find('.eas-premium-typography-morph').each(function () {
			var $wrap = $(this);
			var $words = $wrap.find('.eas-morph-word');
			if (reducedMotion || $words.length <= 1) return;
			var current = 0;
			var speed = Math.max(1000, parseInt($wrap.attr('data-speed'), 10) || 3000);
			$wrap.data('eas-morph-interval', window.setInterval(function () {
				$words.eq(current).removeClass('active');
				current = (current + 1) % $words.length;
				$words.eq(current).addClass('active');
			}, speed));
		});
	}

	function scramble($element) {
		var target = $element.attr('data-text') || $element.text();
		var characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+?';
		var progress = 0;
		var active = $element.data('eas-scramble-interval');
		if (active) window.clearInterval(active);
		var intervalId = window.setInterval(function () {
			$element.text(target.split('').map(function (character, index) {
				if (index < progress || character === ' ') return character;
				return characters[Math.floor(Math.random() * characters.length)];
			}).join(''));
			if (progress >= target.length) {
				window.clearInterval(intervalId);
				$element.text(target).removeData('eas-scramble-interval');
			}
			progress += 0.5;
		}, 40);
		$element.data('eas-scramble-interval', intervalId);
	}

	function initScramble($scope) {
		$scope.find('.eas-premium-typography-scramble').each(function () {
			var $element = $(this);
			var trigger = $element.attr('data-trigger') || 'scroll';
			if (reducedMotion) return;
			if (trigger === 'hover') {
				$element.on('mouseenter.easScramble', function () { scramble($element); });
				return;
			}
			if ('IntersectionObserver' in window) {
				var observer = new IntersectionObserver(function (entries) {
					entries.forEach(function (entry) {
						if (entry.isIntersecting) {
							scramble($element);
							observer.unobserve(entry.target);
						}
					});
				}, { threshold: 0.2 });
				observer.observe($element[0]);
				$element.data('eas-scramble-observer', observer);
			} else {
				scramble($element);
			}
		});
	}

	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/eas-premium-typography.default', function ($scope) {
			destroy($scope);
			initMorph($scope);
			initScramble($scope);
		});
	});
})(jQuery);

(function ($) {
	'use strict';

	var instanceCount = 0;

	function disabled(config) {
		var width = window.innerWidth;
		if (config.respectReducedMotion === 'yes' && window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return true;
		return (config.disableMobile === 'yes' && width < 768) || (config.disableTablet === 'yes' && width >= 768 && width < 1025);
	}

	function init($scope) {
		var oldCleanup = $scope.data('eas-stack-cleanup');
		if (typeof oldCleanup === 'function') oldCleanup();

		var raw = $scope.attr('data-eas-stack-config');
		if (!raw) return;
		var config;
		try { config = JSON.parse(raw); } catch (error) { return; }
		var $wrapper = $scope.children('.e-con-inner, .elementor-container').first();
		if (!$wrapper.length) $wrapper = $scope;
		var $cards = $wrapper.children().filter(function () {
			return !/^(script|style|link)$/i.test(this.tagName);
		});
		if ($cards.length < 2) return;

		var namespace = '.easStack' + (++instanceCount);
		var stickyTop = Number(config.stickyTop) || 80;
		var stackOffset = Number(config.stackOffset) || 0;
		var scaleUp = Number(config.scaleUp) || 1.08;
		var travel = isNaN(Number(config.travel)) ? 100 : Number(config.travel);
		var endOffset = isNaN(Number(config.endOffset)) ? 100 : Number(config.endOffset);
		var dimFactor = Number(config.dimFactor) || 0;
		var scrub = config.studio ? Math.max(0.04, Math.min(0.35, Number(config.scrub) || 0.12)) : 0.12;
		var frame = null;
		var firstRun = true;
		var viewportHeight = window.innerHeight;
		var parentTop = 0;
		var cards = [];
		var studio = config.studio && window.EASStackStudio ? window.EASStackStudio : null;
		var studioInstance = null;
		var snapTimer = null;

		$scope.addClass(config.studio ? 'eas-stack-studio-active eas-stack-mode-' + config.mode : 'eas-stack-classic');
		$scope.toggleClass('eas-stack-disable-tablet', config.disableTablet === 'yes');
		$scope.toggleClass('eas-stack-disable-mobile', config.disableMobile === 'yes');
		$scope.css('overflow', 'visible');
		$wrapper.css('overflow', 'visible');
		$cards.each(function (index) {
			var $card = $(this).addClass('eas-container-stack-card');
			var top = stickyTop + index * stackOffset;
			$card.css({ position: 'sticky', top: top + 'px', zIndex: 10 + index });
			if (!$card.children('.eas-stack-dim-overlay').length) $card.append('<div class="eas-stack-dim-overlay" aria-hidden="true"></div>');
			cards.push({ $el: $card, $overlay: $card.children('.eas-stack-dim-overlay'), stickyTop: top, relativeTop: 0, targetProgress: 0, easedProgress: 0 });
		});
		if (studio) studioInstance = studio.prepare($scope, $wrapper, cards, config);

		function measure() {
			if (!$scope.is(':visible')) return;
			parentTop = $scope.offset().top;
			cards.forEach(function (card) {
				var transform = card.$el[0].style.transform;
				card.$el[0].style.transform = 'none';
				card.relativeTop = card.$el.offset().top - parentTop;
				card.$el[0].style.transform = transform;
			});
		}

		function targets() {
			var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
			cards.forEach(function (card) {
				var currentTop = parentTop + card.relativeTop - scrollTop;
				var start = viewportHeight;
				var end = card.stickyTop + endOffset;
				card.targetProgress = Math.max(0, Math.min(1, 1 - ((currentTop - end) / Math.max(1, start - end))));
				if (firstRun) card.easedProgress = card.targetProgress;
			});
			firstRun = false;
		}

		function resetCards() {
			cards.forEach(function (card) {
				card.$el.css({ position: '', top: '', transform: '', opacity: '', filter: '', clipPath: '' });
				card.$overlay.css('opacity', 0);
			});
		}

		function render() {
			if (disabled(config)) { resetCards(); frame = null; return; }
			// Restore sticky layout after crossing back above a disabled responsive
			// breakpoint. Without this, a stack first loaded on mobile stayed static.
			cards.forEach(function (card, index) {
				card.$el.css({ position: 'sticky', top: card.stickyTop + 'px', zIndex: 10 + index });
			});
			var moving = false;
			cards.forEach(function (card, index) {
				var difference = card.targetProgress - card.easedProgress;
				if (Math.abs(difference) > 0.001) { card.easedProgress += difference * scrub; moving = true; } else card.easedProgress = card.targetProgress;
				var t = 1 - Math.pow(1 - card.easedProgress, 3);
				var state = { x: 0, y: travel * (1 - t), z: 0, scale: scaleUp - (scaleUp - 1) * t, rotateX: 0, rotateY: 0, rotateZ: 0, opacity: 1, blur: 0, clip: '' };
				if (studio) state = studio.cardState(state, card, index, cards.length, t, config);
				card.$el[0].style.transform = 'translate3d(' + state.x.toFixed(2) + 'px,' + state.y.toFixed(2) + 'px,' + state.z.toFixed(2) + 'px) rotateX(' + state.rotateX.toFixed(2) + 'deg) rotateY(' + state.rotateY.toFixed(2) + 'deg) rotateZ(' + state.rotateZ.toFixed(2) + 'deg) scale(' + state.scale.toFixed(4) + ')';
				card.$el[0].style.opacity = state.opacity.toFixed(3);
				card.$el[0].style.filter = state.blur > 0.01 ? 'blur(' + state.blur.toFixed(2) + 'px)' : '';
				card.$el[0].style.clipPath = state.clip || '';
				card.$overlay.css('opacity', index < cards.length - 1 ? (cards[index + 1].easedProgress * dimFactor).toFixed(3) : 0);
			});
			if (studio) studio.render(studioInstance, cards, config);
			frame = moving ? window.requestAnimationFrame(render) : null;
		}

		function onScroll() {
			targets();
			if (!frame) frame = window.requestAnimationFrame(render);
			if (studio && config.snap) {
				window.clearTimeout(snapTimer);
				snapTimer = window.setTimeout(function () { studio.snap(cards, parentTop, config); }, 180);
			}
		}
		$(window).on('scroll' + namespace, onScroll).on('resize' + namespace, function () {
			viewportHeight = window.innerHeight; measure(); onScroll();
		});
		measure(); targets(); render();

		$scope.data('eas-stack-cleanup', function () {
			if (frame) window.cancelAnimationFrame(frame);
			window.clearTimeout(snapTimer);
			$(window).off(namespace);
			if (studio) studio.destroy(studioInstance);
			resetCards();
			$cards.removeClass('eas-container-stack-card').css('z-index', '').children('.eas-stack-dim-overlay').remove();
			$scope.removeClass('eas-stack-classic eas-stack-studio-active eas-stack-mode-depth eas-stack-mode-peel eas-stack-mode-fan eas-stack-mode-reveal eas-stack-mode-classic_plus').css('overflow', '');
			$wrapper.css('overflow', '');
			$scope.removeData('eas-stack-cleanup');
		});
	}

	window.EASContainerStack = { init: init };
	function scan($context) { ($context || $(document)).find('.eas-container-stack-active[data-eas-stack-active="yes"]').addBack('.eas-container-stack-active[data-eas-stack-active="yes"]').each(function () { init($(this)); }); }
	$(window).on('elementor/frontend/init', function () {
		if (window.elementorFrontend && elementorFrontend.hooks) elementorFrontend.hooks.addAction('frontend/element_ready/container.default', function ($scope) { if ($scope.attr('data-eas-stack-active') === 'yes') init($scope); });
	});
	$(function () { scan($(document)); });
})(jQuery);

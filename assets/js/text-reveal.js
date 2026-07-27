(function ($) {
	'use strict';

	function deviceDisabled(config) {
		var width = window.innerWidth;
		return (width < 768 && config.disableMobile === 'yes') || (width >= 768 && width <= 1024 && config.disableTablet === 'yes');
	}

	function parseConfig(element) {
		try {
			return JSON.parse(element.getAttribute('data-eas-tr-config') || '{}');
		} catch (error) {
			return {};
		}
	}

	function tokenize(element, granularity) {
		if (element.dataset.apexadfoRevealPrepared === 'yes') {
			return Array.prototype.slice.call(element.querySelectorAll('.eas-tr-unit'));
		}
		var walker = document.createTreeWalker(element, NodeFilter.SHOW_TEXT);
		var textNodes = [];
		var node;
		while ((node = walker.nextNode())) {
			if (node.nodeValue && node.nodeValue.trim()) textNodes.push(node);
		}
		textNodes.forEach(function (textNode) {
			var parts = granularity === 'char' ? Array.from(textNode.nodeValue) : (granularity === 'word' ? textNode.nodeValue.split(/(\s+)/) : [textNode.nodeValue]);
			var fragment = document.createDocumentFragment();
			parts.forEach(function (part) {
				if (!part) return;
				if (/^\s+$/.test(part)) {
					fragment.appendChild(document.createTextNode(part));
					return;
				}
				var span = document.createElement('span');
				span.className = 'eas-tr-unit';
				span.textContent = part;
				fragment.appendChild(span);
			});
			textNode.parentNode.replaceChild(fragment, textNode);
		});
		element.dataset.apexadfoRevealPrepared = 'yes';
		return Array.prototype.slice.call(element.querySelectorAll('.eas-tr-unit'));
	}

	function initReveal($scope) {
		if (window.apexadfoTextRevealProRuntime) return;
		var element = $scope.find('.eas-text-reveal-active')[0];
		if (!element) return;
		var config = parseConfig(element);
		if (deviceDisabled(config)) return;
		var target = element.querySelector('.eas-tr-text') || element;
		var units = tokenize(target, config.granularity || 'line');
		if (!units.length) return;
		var frame = 0;
		var update = function () {
			frame = 0;
			var rect = element.getBoundingClientRect();
			var progress = Math.max(0, Math.min(1, (window.innerHeight * 0.85 - rect.top) / Math.max(1, window.innerHeight * 0.6 + rect.height)));
			var active = progress * units.length;
			units.forEach(function (unit, index) {
				var amount = Math.max(0, Math.min(1, active - index));
				unit.style.color = amount >= 0.5 ? (config.activeColor || '#ffffff') : (config.inactiveColor || 'rgba(255,255,255,.25)');
			});
		};
		var requestUpdate = function () {
			if (!frame) frame = window.requestAnimationFrame(update);
		};
		window.addEventListener('scroll', requestUpdate, { passive: true });
		window.addEventListener('resize', requestUpdate);
		requestUpdate();
	}

	$(window).on('elementor/frontend/init', function () {
		if (window.apexadfoTextRevealProRuntime) return;
		elementorFrontend.hooks.addAction('frontend/element_ready/eas-text-reveal.default', initReveal);
	});
})(jQuery);

(function ($) {
	'use strict';

	// Device check - disable custom cursor on touch devices to ensure high performance
	function isTouchDevice() {
		return ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
	}

	function isElementorEditor() {
		var environment = window.elementorFrontendConfig && window.elementorFrontendConfig.environmentMode;
		return document.body.classList.contains('elementor-editor-active')
			|| /(?:^|[?&])elementor-preview=/.test(window.location.search)
			|| !!(environment && environment.edit)
			|| !!(window.elementorFrontend && typeof window.elementorFrontend.isEditMode === 'function' && window.elementorFrontend.isEditMode());
	}

	$(window).on('load', function () {
		if (isTouchDevice() || isElementorEditor()) return;

		var config = window.apexadfoCustomCursorConfig || {};
		if (!config.style || config.style === 'none') return;

		var mouse = { x: 0, y: 0 };
		var isHovering = false;

		// Track mouse movement
		$(window).on('mousemove', function (e) {
			mouse.x = e.clientX;
			mouse.y = e.clientY;
		});

		// Interactive hover selectors
		var interactiveSelector = 'a, button, input[type="submit"], [role="button"], .elementor-clickable';

		// --------------------------------------------------------
		// SETUP DIFFERENT CURSOR DESIGNS
		// --------------------------------------------------------

		if (config.style === 'ring-dot') {
			// Design 1: Ring & Dot Follower
			var $dot = $('<div class="eas-cursor-dot"></div>');
			var $ring = $('<div class="eas-cursor-ring"></div>');

			if (config.color) {
				$dot.css('--eas-cursor-color', config.color);
				$ring.css('--eas-cursor-color', config.color);
			}

			$('body').append($dot).append($ring).addClass('eas-cursor-hide-native');

			var dotPos = { x: 0, y: 0 };
			var ringPos = { x: 0, y: 0 };

			// Fast dot follow, slower ring follow
			var dotSpeed = 0.35;
			var ringSpeed = 0.12;

			var updateRingDot = function () {
				dotPos.x += (mouse.x - dotPos.x) * dotSpeed;
				dotPos.y += (mouse.y - dotPos.y) * dotSpeed;

				ringPos.x += (mouse.x - ringPos.x) * ringSpeed;
				ringPos.y += (mouse.y - ringPos.y) * ringSpeed;

				$dot[0].style.transform = 'translate3d(' + dotPos.x + 'px, ' + dotPos.y + 'px, 0)';
				$ring[0].style.transform = 'translate3d(' + ringPos.x + 'px, ' + ringPos.y + 'px, 0)';

				requestAnimationFrame(updateRingDot);
			};
			requestAnimationFrame(updateRingDot);

			// Hover states
			$('body').on('mouseenter', interactiveSelector, function () {
				$dot.addClass('hovering');
				$ring.addClass('hovering');
			}).on('mouseleave', interactiveSelector, function () {
				$dot.removeClass('hovering');
				$ring.removeClass('hovering');
			});

		} else if (config.style === 'glow-blob') {
			// Design 2: Ambient Glow Blob
			var $blob = $('<div class="eas-cursor-glow-blob"></div>');
			if (config.color) {
				// Convert hex color to rgba with transparency for the soft gradient glow
				var hex = config.color.replace('#', '');
				var r = parseInt(hex.substring(0, 2), 16);
				var g = parseInt(hex.substring(2, 4), 16);
				var b = parseInt(hex.substring(4, 6), 16);
				$blob.css('--eas-cursor-color', 'rgba(' + r + ',' + g + ',' + b + ', 0.45)');
			}

			$('body').append($blob); // Do not hide native cursor for glow blob since it acts as ambient light

			var blobPos = { x: 0, y: 0 };
			var blobSpeed = 0.08; // Very soft delay

			var updateGlowBlob = function () {
				blobPos.x += (mouse.x - blobPos.x) * blobSpeed;
				blobPos.y += (mouse.y - blobPos.y) * blobSpeed;

				$blob[0].style.transform = 'translate3d(' + blobPos.x + 'px, ' + blobPos.y + 'px, 0)';

				requestAnimationFrame(updateGlowBlob);
			};
			requestAnimationFrame(updateGlowBlob);

			// Hover states
			$('body').on('mouseenter', interactiveSelector, function () {
				$blob.addClass('hovering');
			}).on('mouseleave', interactiveSelector, function () {
				$blob.removeClass('hovering');
			});

		} else {
			// Design 3: Default Dot or Inverse Color Ball
			var $cursor = $('<div class="eas-custom-cursor"></div>');
			if (config.style === 'difference') {
				$cursor.addClass('eas-custom-cursor--difference');
			}
			if (config.color) {
				$cursor.css('--eas-cursor-color', config.color);
			}

			$('body').append($cursor).addClass('eas-cursor-hide-native');

			var pos = { x: 0, y: 0 };
			var speed = 0.16;
			var targetScale = 1;
			var currentScale = 1;

			var updateCursor = function () {
				pos.x += (mouse.x - pos.x) * speed;
				pos.y += (mouse.y - pos.y) * speed;
				currentScale += (targetScale - currentScale) * 0.2;

				$cursor[0].style.transform = 'translate3d(' + pos.x + 'px, ' + pos.y + 'px, 0) scale(' + currentScale + ')';
				requestAnimationFrame(updateCursor);
			};
			requestAnimationFrame(updateCursor);

			// Handle active click compression
			$(window).on('mousedown', function () {
				targetScale = 0.85;
			});
			$(window).on('mouseup', function () {
				targetScale = 1;
			});

			// Hover states
			$('body').on('mouseenter', interactiveSelector, function () {
				$cursor.addClass('hovering');
			}).on('mouseleave', interactiveSelector, function () {
				$cursor.removeClass('hovering');
			});
		}
	});

})(jQuery);

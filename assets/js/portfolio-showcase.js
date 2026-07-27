(function ($) {
	'use strict';

	/**
	 * Initialize Portfolio Hover Showcase Widget
	 */
	function initPortfolioShowcase($scope) {
		var $wrap = $scope.find('.eas-portfolio-showcase-wrap');
		if (!$wrap.length) return;

		// Clean up any existing instance on this element first
		var existingCleanup = $wrap.data('eas-portfolio-showcase-cleanup');
		if (typeof existingCleanup === 'function') {
			existingCleanup();
		}

		var $list = $wrap.find('.eas-portfolio-showcase-list');
		var $items = $wrap.find('.eas-portfolio-showcase-item');
		var $preview = $wrap.find('.eas-portfolio-showcase-preview-container');
		
		if (!$preview.length) return;

		var $mediaItems = $preview.find('.eas-portfolio-showcase-media-item');

		// Damping & tilt parameters
		var damping = parseFloat($wrap.data('damping')) || 0.1;
		var tiltSensitivity = parseFloat($wrap.data('tilt-sensitivity')) || 0.05;
		var maxTilt = parseFloat($wrap.data('max-tilt')) || 15;

		// Animation tracking variables
		var targetX = 0, targetY = 0;
		var currentX = 0, currentY = 0;
		var targetRotation = 0, currentRotation = 0;
		var prevTargetX = 0;
		var isHovering = false;
		var animationFrameId = null;

		// Cache preview dimensions dynamically (zero reflow inside tick)
		var previewWidth = 320;
		var previewHeight = 200;

		function updateDimensions() {
			if ($preview.is(':visible')) {
				previewWidth = $preview.outerWidth() || 320;
				previewHeight = $preview.outerHeight() || 200;
			}
		}

		// Initial size reading
		updateDimensions();

		/**
		 * Main render loop for updating position & rotation
		 */
		function tick() {
			if (!isHovering) {
				animationFrameId = null;
				return;
			}

			// LERP position tracking
			var dx = targetX - currentX;
			var dy = targetY - currentY;

			currentX += dx * damping;
			currentY += dy * damping;

			// Calculate mouse velocity for dynamic tilt
			var velocityX = targetX - prevTargetX;
			prevTargetX = targetX;

			// Scale down the horizontal mouse velocity to get a rotation angle
			var tilt = velocityX * tiltSensitivity;
			targetRotation = Math.max(-maxTilt, Math.min(maxTilt, tilt));

			// LERP rotation tracking
			currentRotation += (targetRotation - currentRotation) * 0.1;

			// Apply transform using hardware-accelerated translate3d and 2D rotation
			$preview[0].style.transform = 'translate3d(' + 
				(currentX - previewWidth / 2).toFixed(1) + 'px, ' + 
				(currentY - previewHeight / 2).toFixed(1) + 'px, 0) ' +
				'rotate(' + currentRotation.toFixed(2) + 'deg)';

			animationFrameId = requestAnimationFrame(tick);
		}

		/**
		 * Track mouse coordinates relative to the screen (using clientX/clientY for fixed position)
		 */
		function onPointerMove(e) {
			targetX = e.clientX;
			targetY = e.clientY;
		}

		/**
		 * Handle Mouse Enter Row
		 */
		$items.on('mouseenter.easShowcase', function () {
			var index = $(this).data('index');

			// Swap active image wrapper class
			$mediaItems.removeClass('eas-active');
			$mediaItems.filter('[data-index="' + index + '"]').addClass('eas-active');

			// Show preview container
			if (!isHovering) {
				isHovering = true;
				updateDimensions(); // Update dimensions right before showing
				$preview.addClass('eas-active');
				if (!animationFrameId) {
					animationFrameId = requestAnimationFrame(tick);
				}
			}
		});

		$list.on('mouseenter.easShowcase', function (e) {
			// Prime initial position to prevent visual jump from (0,0)
			targetX = currentX = e.clientX;
			targetY = currentY = e.clientY;
			prevTargetX = targetX;
			updateDimensions();
		});

		$list.on('mouseleave.easShowcase', function () {
			isHovering = false;
			$preview.removeClass('eas-active');
			if (animationFrameId) {
				cancelAnimationFrame(animationFrameId);
				animationFrameId = null;
			}
		});

		// Listen to pointer events for responsive/stylus compatibility
		window.addEventListener('pointermove', onPointerMove, { passive: true });

		var handleResize = function () {
			updateDimensions();
		};
		window.addEventListener('resize', handleResize, { passive: true });

		// Register cleanup handler to destroy instance cleanly on widget re-load/delete
		var cleanup = function () {
			isHovering = false;
			$preview.removeClass('eas-active');
			if (animationFrameId) {
				cancelAnimationFrame(animationFrameId);
				animationFrameId = null;
			}
			
			// Remove events
			window.removeEventListener('pointermove', onPointerMove);
			window.removeEventListener('resize', handleResize);
			$items.off('.easShowcase');
			$list.off('.easShowcase');
		};

		$wrap.data('eas-portfolio-showcase-cleanup', cleanup);
	}

	// ── Initializer Scan ──────────────────────────────────────────────────
	$(window).on('elementor/frontend/init', function () {
		if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
			elementorFrontend.hooks.addAction(
				'frontend/element_ready/eas-portfolio-showcase.default',
				initPortfolioShowcase
			);
		}
	});
})(jQuery);

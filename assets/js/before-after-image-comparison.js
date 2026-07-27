/**
 * Before/After Image Comparison Widget Script
 *
 * Handles pointer/touch drag, click, hover, and ARIA keyboard interaction.
 * Includes complete event listener cleanup for Elementor editor re-renders.
 *
 * @package ApexAddonsForElementor
 */

(function ($) {
	'use strict';

	/**
	 * Main Before/After Handler
	 *
	 * @param {jQuery} $scope Widget container scope
	 */
	var ApexadfoBeforeAfterHandler = function ($scope) {
		var $wrapper = $scope.find('.apexadfo-before-after-container');
		if (!$wrapper.length) {
			return;
		}

		var container = $wrapper[0];
		var beforeWrap = container.querySelector('.apexadfo-before-after-before-wrap');
		var afterWrap = container.querySelector('.apexadfo-before-after-after-wrap');
		var beforeImg = beforeWrap ? beforeWrap.querySelector('.apexadfo-before-after-img') : null;
		var afterImg = afterWrap ? afterWrap.querySelector('.apexadfo-before-after-img') : null;
		var handle = container.querySelector('.apexadfo-before-after-handle');
		var line = container.querySelector('.apexadfo-before-after-line');

		if (!beforeWrap || !afterWrap || !beforeImg || !afterImg || !handle || !line) {
			return;
		}

		// Read configuration data attributes
		var orientation = container.getAttribute('data-orientation') || 'horizontal';
		var initialPos = parseFloat(container.getAttribute('data-starting-pos')) || 50;
		var moveOnHover = container.getAttribute('data-hover') === 'yes';
		var isVertical = orientation === 'vertical';

		var currentPos = initialPos;
		var isDragging = false;
		var animationFrameId = null;
		var resizeObserver = null;

		/**
		 * Pin BOTH before-image and after-image to the exact pixel width/height of the container.
		 */
		function syncImageBounds() {
			var width = container.offsetWidth;
			var height = container.offsetHeight;

			if (width > 0 && height > 0) {
				if (beforeImg) {
					beforeImg.style.width = width + 'px';
					beforeImg.style.height = height + 'px';
				}
				if (afterImg) {
					afterImg.style.width = width + 'px';
					afterImg.style.height = height + 'px';
				}
			}
		}

		/**
		 * Apply position percentage (0 - 100)
		 *
		 * @param {number} percentage
		 */
		function setPosition(percentage) {
			percentage = Math.max(0, Math.min(100, percentage));
			currentPos = percentage;

			if (animationFrameId) {
				cancelAnimationFrame(animationFrameId);
			}

			animationFrameId = requestAnimationFrame(function () {
				if (isVertical) {
					beforeWrap.style.height = percentage + '%';
					handle.style.top = percentage + '%';
					line.style.top = percentage + '%';
				} else {
					beforeWrap.style.width = percentage + '%';
					handle.style.left = percentage + '%';
					line.style.left = percentage + '%';
				}

				handle.setAttribute('aria-valuenow', Math.round(percentage));
			});
		}

		/**
		 * Calculate percentage from client coordinates
		 *
		 * @param {number} pageX
		 * @param {number} pageY
		 */
		function getPercentageFromCoords(pageX, pageY) {
			var rect = container.getBoundingClientRect();
			var scrollX = window.pageXOffset || document.documentElement.scrollLeft;
			var scrollY = window.pageYOffset || document.documentElement.scrollTop;

			var clientX = pageX - (rect.left + scrollX);
			var clientY = pageY - (rect.top + scrollY);

			var pct;
			if (isVertical) {
				pct = (clientY / rect.height) * 100;
			} else {
				pct = (clientX / rect.width) * 100;
			}

			return pct;
		}

		/**
		 * Pointer / Mouse / Touch move handler
		 */
		function onPointerMove(e) {
			if (!isDragging && !moveOnHover) {
				return;
			}

			var pageX = e.pageX;
			var pageY = e.pageY;

			if (e.touches && e.touches.length > 0) {
				pageX = e.touches[0].pageX;
				pageY = e.touches[0].pageY;
			}

			var pct = getPercentageFromCoords(pageX, pageY);
			setPosition(pct);
		}

		/**
		 * Drag start handler
		 */
		function onDragStart(e) {
			isDragging = true;
			container.classList.add('apexadfo-is-dragging');

			var pageX = e.pageX;
			var pageY = e.pageY;

			if (e.touches && e.touches.length > 0) {
				pageX = e.touches[0].pageX;
				pageY = e.touches[0].pageY;
			}

			var pct = getPercentageFromCoords(pageX, pageY);
			setPosition(pct);

			if (e.type === 'touchstart') {
				e.preventDefault();
			}
		}

		/**
		 * Drag end handler
		 */
		function onDragEnd() {
			if (isDragging) {
				isDragging = false;
				container.classList.remove('apexadfo-is-dragging');
			}
		}

		/**
		 * Container click handler
		 */
		function onContainerClick(e) {
			if (e.target === handle || handle.contains(e.target)) {
				return;
			}
			var pct = getPercentageFromCoords(e.pageX, e.pageY);
			setPosition(pct);
		}

		/**
		 * Keyboard navigation handler for accessibility
		 */
		function onKeyDown(e) {
			var step = e.shiftKey ? 5 : 1;
			var handled = false;

			switch (e.key) {
				case 'ArrowLeft':
				case 'ArrowUp':
					setPosition(currentPos - step);
					handled = true;
					break;

				case 'ArrowRight':
				case 'ArrowDown':
					setPosition(currentPos + step);
					handled = true;
					break;

				case 'Home':
					setPosition(0);
					handled = true;
					break;

				case 'End':
					setPosition(100);
					handled = true;
					break;

				case 'PageUp':
					setPosition(currentPos + 10);
					handled = true;
					break;

				case 'PageDown':
					setPosition(currentPos - 10);
					handled = true;
					break;
			}

			if (handled) {
				e.preventDefault();
			}
		}

		// Sync bounds initially and on image load for both images
		syncImageBounds();
		if (beforeImg) {
			if (beforeImg.complete) {
				syncImageBounds();
			} else {
				beforeImg.addEventListener('load', syncImageBounds);
			}
		}
		if (afterImg) {
			if (afterImg.complete) {
				syncImageBounds();
			} else {
				afterImg.addEventListener('load', syncImageBounds);
			}
		}

		// Responsive resize handling
		if (typeof ResizeObserver !== 'undefined') {
			resizeObserver = new ResizeObserver(function () {
				syncImageBounds();
			});
			resizeObserver.observe(container);
		} else {
			window.addEventListener('resize', syncImageBounds);
		}

		// Initialize starting position
		setPosition(initialPos);

		// Event Bindings
		handle.addEventListener('mousedown', onDragStart);
		handle.addEventListener('touchstart', onDragStart, { passive: false });

		if (moveOnHover) {
			container.addEventListener('mousemove', onPointerMove);
			container.addEventListener('touchmove', onPointerMove, { passive: true });
		} else {
			window.addEventListener('mousemove', onPointerMove);
			window.addEventListener('touchmove', onPointerMove, { passive: true });
			window.addEventListener('mouseup', onDragEnd);
			window.addEventListener('touchend', onDragEnd);
		}

		container.addEventListener('click', onContainerClick);
		handle.addEventListener('keydown', onKeyDown);

		// Complete cleanup function on instance element for Elementor editor refresh
		$scope.data('apexadfoBeforeAfterDestroy', function () {
			handle.removeEventListener('mousedown', onDragStart);
			handle.removeEventListener('touchstart', onDragStart);
			window.removeEventListener('mousemove', onPointerMove);
			window.removeEventListener('touchmove', onPointerMove);
			window.removeEventListener('mouseup', onDragEnd);
			window.removeEventListener('touchend', onDragEnd);
			container.removeEventListener('mousemove', onPointerMove);
			container.removeEventListener('touchmove', onPointerMove);
			container.removeEventListener('click', onContainerClick);
			handle.removeEventListener('keydown', onKeyDown);

			if (beforeImg) {
				beforeImg.removeEventListener('load', syncImageBounds);
			}
			if (afterImg) {
				afterImg.removeEventListener('load', syncImageBounds);
			}

			if (resizeObserver) {
				resizeObserver.disconnect();
			} else {
				window.removeEventListener('resize', syncImageBounds);
			}

			if (animationFrameId) {
				cancelAnimationFrame(animationFrameId);
			}
		});
	};

	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction(
			'frontend/element_ready/eas-before-after-image-comparison.default',
			function ($scope) {
				var destroy = $scope.data('apexadfoBeforeAfterDestroy');
				if (typeof destroy === 'function') {
					destroy();
				}
				ApexadfoBeforeAfterHandler($scope);
			}
		);
	});
})(jQuery);

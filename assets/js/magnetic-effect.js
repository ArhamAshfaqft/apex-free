(function ($) {
	'use strict';

	var activeElements = [];
	var isListenerActive = false;
	var animationFrameId = null;

	/**
	 * Calculate absolute center coordinates of an element relative to the viewport
	 * without triggering layout reflow inside animation loops.
	 */
	function cacheElementBounds(instance) {
		var $el = instance.$el;
		if (!$el.length || !$el.is(':visible')) {
			instance.active = false;
			return;
		}

		var rect = $el[0].getBoundingClientRect();
		var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
		var scrollTop = window.pageYOffset || document.documentElement.scrollTop;

		// Calculate center coordinates relative to document scroll position
		instance.centerX = rect.left + rect.width / 2 + scrollLeft;
		instance.centerY = rect.top + rect.height / 2 + scrollTop;
		instance.width = rect.width;
		instance.height = rect.height;
		instance.active = true;
	}

	/**
	 * Cache boundaries for all registered elements (called on load & resize)
	 */
	function recacheAllBounds() {
		activeElements.forEach(function (instance) {
			cacheElementBounds(instance);
		});
	}

	/**
	 * Main animation loop running on requestAnimationFrame.
	 * Only updates elements that need movement, optimizing GPU resources.
	 */
	function renderLoop() {
		var needsMoreFrames = false;

		activeElements.forEach(function (instance) {
			if (!instance.active) return;

			var dx = instance.targetX - instance.currentX;
			var dy = instance.targetY - instance.currentY;

			// Check if coordinates have settled to stop calling unnecessary transforms
			if (Math.abs(dx) > 0.1 || Math.abs(dy) > 0.1) {
				instance.currentX += dx * instance.strength;
				instance.currentY += dy * instance.strength;
				needsMoreFrames = true;
			} else {
				instance.currentX = instance.targetX;
				instance.currentY = instance.targetY;
			}

			// Apply transform on the main wrapper
			var transformStr = 'translate3d(' + instance.currentX.toFixed(1) + 'px, ' + instance.currentY.toFixed(1) + 'px, 0)';
			instance.$el[0].style.transform = transformStr;

			// Multi-layer nested parallax (pulls inner content further)
			if (instance.pullText && instance.$inner.length) {
				var innerX = instance.currentX * 0.4;
				var innerY = instance.currentY * 0.4;
				var innerTransform = 'translate3d(' + innerX.toFixed(1) + 'px, ' + innerY.toFixed(1) + 'px, 0)';
				instance.$inner.each(function () {
					this.style.transform = innerTransform;
				});
			}
		});

		if (needsMoreFrames) {
			animationFrameId = requestAnimationFrame(renderLoop);
		} else {
			animationFrameId = null;
		}
	}

	/**
	 * Handle global pointermove (passive mouse coordinate updates)
	 */
	function onPointerMove(e) {
		var mouseX = e.pageX;
		var mouseY = e.pageY;
		var w = window.innerWidth;

		activeElements.forEach(function (instance) {
			if (!instance.active) return;

			// Handle mobile responsive bypass
			if (instance.mobileBypass && w < 768) {
				instance.targetX = 0;
				instance.targetY = 0;
				return;
			}

			// Calculate distance to the element's center point
			var distance = Math.hypot(mouseX - instance.centerX, mouseY - instance.centerY);

			if (distance < instance.radius) {
				// Pull element relative to the distance (closer = pulls stronger)
				var pullX = (mouseX - instance.centerX) * instance.strength;
				var pullY = (mouseY - instance.centerY) * instance.strength;
				
				instance.targetX = pullX;
				instance.targetY = pullY;
			} else {
				// Snap back to original location
				instance.targetX = 0;
				instance.targetY = 0;
			}
		});

		// Trigger rendering loop if not already running
		if (!animationFrameId) {
			animationFrameId = requestAnimationFrame(renderLoop);
		}
	}

	/**
	 * Register element for magnetic attraction
	 */
	function registerMagneticElement($el) {
		var configRaw = $el.attr('data-eas-magnetic-config');
		if (!configRaw) return;

		var config;
		try {
			config = JSON.parse(configRaw);
		} catch (e) {
			console.error('Apex Magnetic Effect: Failed to parse config', e);
			return;
		}
		
		// Setup instance properties
		var instance = {
			$el: $el,
			radius: parseFloat(config.radius) || 80,
			strength: parseFloat(config.strength) || 0.2,
			pullText: config.pullText === 'yes',
			mobileBypass: config.mobile === 'yes',
			centerX: 0,
			centerY: 0,
			width: 0,
			height: 0,
			targetX: 0,
			targetY: 0,
			currentX: 0,
			currentY: 0,
			active: true,
			$inner: $el.find('.elementor-button-content, .elementor-button-text, .elementor-button-icon, .eas-magnetic-inner, span, i, h1, h2, h3, h4, h5, h6').first()
		};

		// Promote element to its own GPU composite layer
		$el.css({
			'will-change': 'transform',
			'transition': 'none'
		});

		if (instance.pullText && instance.$inner.length) {
			instance.$inner.css({
				'display': 'inline-block',
				'will-change': 'transform',
				'transition': 'none'
			});
		}

		// Cache bounds immediately
		cacheElementBounds(instance);

		// Store instance
		activeElements.push(instance);

		// Initialize global listeners on first registration
		if (!isListenerActive) {
			window.addEventListener('pointermove', onPointerMove, { passive: true });
			window.addEventListener('resize', recacheAllBounds, { passive: true });
			isListenerActive = true;
		}

		// Start loop to snap positions
		if (!animationFrameId) {
			animationFrameId = requestAnimationFrame(renderLoop);
		}
	}

	/**
	 * Unregister element for magnetic attraction (called during editor re-renders)
	 */
	function unregisterMagneticElement($el) {
		activeElements = activeElements.filter(function (instance) {
			if (instance.$el[0] === $el[0]) {
				// Reset element transforms back to standard layout
				$el.css({
					'transform': '',
					'will-change': ''
				});
				if (instance.$inner && instance.$inner.length) {
					instance.$inner.css({
						'transform': '',
						'will-change': ''
					});
				}
				return false;
			}
			return true;
		});

		// Turn off global listeners if no elements remain
		if (activeElements.length === 0 && isListenerActive) {
			window.removeEventListener('pointermove', onPointerMove);
			window.removeEventListener('resize', recacheAllBounds);
			isListenerActive = false;
			if (animationFrameId) {
				cancelAnimationFrame(animationFrameId);
				animationFrameId = null;
			}
		}
	}

	/**
	 * Elementor Ready Initialization Action
	 */
	function initMagneticEffect($scope) {
		// Widgets and containers are wrapped with this class by the PHP before_render hooks
		if ($scope.hasClass('eas-magnetic-active')) {
			// Unregister any previous instance to prevent leaks
			unregisterMagneticElement($scope);
			registerMagneticElement($scope);
		}
	}

	// ── Initializer Scan ──────────────────────────────────────────────────
	$(window).on('elementor/frontend/init', function () {
		if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
			// Hook into both widgets and containers
			elementorFrontend.hooks.addAction('frontend/element_ready/widget', initMagneticEffect);
			elementorFrontend.hooks.addAction('frontend/element_ready/container', initMagneticEffect);
		}
	});

	// ── Editor Live Preview Settings Watcher ──────────────────────────────
	$(window).on('load', function () {
		if (typeof window.elementor !== 'undefined' && elementor.channels && elementor.channels.editor) {
			elementor.channels.editor.on('change', function (model) {
				var controlKeys = [
					'eas_magnetic_enable',
					'eas_magnetic_radius',
					'eas_magnetic_strength',
					'eas_magnetic_text',
					'eas_magnetic_mobile'
				];

				var changedKey = '';
				if (model && model.changed) {
					for (var key in model.changed) {
						if (controlKeys.indexOf(key) !== -1) {
							changedKey = key;
							break;
						}
					}
				}

				if (!changedKey) return;

				// Short delay to let Elementor update preview iframe contents
				setTimeout(function () {
					var id = model.get('id') || model.id;
					// Locate elements within the Elementor preview wrapper frame
					var $previewContents = $(document.body);
					var $el = $previewContents.find('.elementor-element-' + id);
					
					if ($el.length) {
						var enable = model.get('eas_magnetic_enable');
						if (enable === 'yes') {
							var radius = model.get('eas_magnetic_radius');
							var strength = model.get('eas_magnetic_strength');
							var text = model.get('eas_magnetic_text');
							var mobile = model.get('eas_magnetic_mobile');
							
							var config = {
								radius: radius ? (radius.size || 80) : 80,
								strength: strength ? (strength.size || 0.2) : 0.2,
								pullText: text || 'no',
								mobile: mobile || 'yes'
							};
							
							$el.addClass('eas-magnetic-active');
							$el.attr('data-eas-magnetic-config', JSON.stringify(config));
							
							// Re-initialize on this element
							initMagneticEffect($el);
						} else {
							$el.removeClass('eas-magnetic-active');
							$el.removeAttr('data-eas-magnetic-config');
							unregisterMagneticElement($el);
						}
					}
				}, 100);
			});
		}
	});

	// Standard ready scan for frontend static pages
	$(document).ready(function () {
		$('.eas-magnetic-active').each(function () {
			registerMagneticElement($(this));
		});
	});

})(jQuery);

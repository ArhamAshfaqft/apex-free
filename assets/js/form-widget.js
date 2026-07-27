/**
 * APEX ADDONS - Advanced Form Widget Frontend Engine
 *
 * Implements:
 * 1. HTML5 Canvas Signature Pad (with mobile touch & HDPI Retina scaling)
 * 2. Country Flag Dial Code prefix selector (with search filter)
 * 3. Dynamic Conditional Logic (Show/Hide evaluator on value change)
 * 4. Multi-step navigation validation
 * 5. AJAX Form submitter & honeypot spam verification
 */
(function ($) {
	'use strict';

	// Main initialization router
	function initFormWidget($scope) {
		var $form = $scope.find('.eas-form-wrap form');
		if (!$form.length) return;

		// 1. Initialise Country Telephone dropdown selectors
		initTelPickers($form);

		// 2. Initialise Signature Pad canvases
		initSignaturePads($form);

		// 3. Initialise Multi-Step navigations
		initMultiSteps($form);

		// 4. Initialise Conditional Logic evaluator
		initConditionalLogic($form);

		// 5. Initialise AJAX form submit handler
		initAjaxSubmit($form);

		// 6. Initialise Range Sliders
		initRangeSliders($form);

		// 7. Initialise Rating Fields
		initRatingFields($form);

		// 8. Initialise Image Select Fields
		initImageSelectFields($form);
	}

	/* ====================================================================
	   1. TELEPHONE PICKERS & COUNTRY FLAGS
	   ==================================================================== */
	function initTelPickers($form) {
		$form.find('.eas-form-tel-wrap').each(function () {
			var $wrap = $(this);
			var $selector = $wrap.find('.eas-form-tel-prefix-selector');
			var $dropdown = $wrap.find('.eas-form-tel-dropdown');
			var $searchInput = $wrap.find('.eas-form-tel-search');
			var $hiddenInput = $wrap.find('.eas-form-tel-hidden-val');
			var $numberInput = $wrap.find('.eas-form-tel-input');
			var $badgeFlag = $selector.find('.eas-form-tel-badge-flag');
			var $badgeCode = $selector.find('.eas-form-tel-badge-code');

			// Toggle dropdown display
			$selector.on('click', function (e) {
				e.stopPropagation();
				$('.eas-form-tel-dropdown').not($dropdown).hide();
				$('.eas-form-tel-prefix-selector').not($selector).removeClass('active');

				$selector.toggleClass('active');
				$dropdown.toggle();
				if ($dropdown.is(':visible')) {
					$searchInput.val('').trigger('input').focus();
				}
			});

			// Filter country list on input
			$searchInput.on('input', function (e) {
				e.stopPropagation();
				var filter = $(this).val().toLowerCase().trim();
				$dropdown.find('.eas-form-tel-option').each(function () {
					var name = $(this).find('.eas-form-tel-name').text().toLowerCase();
					var code = $(this).find('.eas-form-tel-code').text().toLowerCase();
					if (name.indexOf(filter) !== -1 || code.indexOf(filter) !== -1) {
						$(this).show();
					} else {
						$(this).hide();
					}
				});
			});

			// Select prefix option
			$dropdown.on('click', '.eas-form-tel-option', function (e) {
				e.stopPropagation();
				var flag = $(this).attr('data-flag');
				var dialCode = $(this).attr('data-code');

				$badgeFlag.text(flag);
				$badgeCode.text(dialCode);

				$selector.removeClass('active');
				$dropdown.hide();

				updateHiddenValue();
				$numberInput.focus();
			});

			// Close dropdown on click outside
			$(document).on('click', function () {
				$selector.removeClass('active');
				$dropdown.hide();
			});

			// Update combined hidden prefix + number value
			$numberInput.on('input', function () {
				updateHiddenValue();
			});

			function updateHiddenValue() {
				var code = $badgeCode.text().trim();
				var num = $numberInput.val().replace(/[^0-9]/g, '');
				if (num !== '') {
					$hiddenInput.val(code + ' ' + num);
				} else {
					$hiddenInput.val('');
				}
				$hiddenInput.trigger('change'); // Trigger logic evaluator
			}
		});
	}

	/* ====================================================================
	   2. SIGNATURE PAD DRAWING ENGINE
	   ==================================================================== */
	function initSignaturePads($form) {
		$form.find('.eas-form-sig-pad-wrap').each(function () {
			var $wrap = $(this);
			var canvas = $wrap.find('canvas')[0];
			var $hiddenInput = $wrap.find('.eas-form-sig-value');
			var $clearBtn = $wrap.find('.eas-form-sig-clear');

			if (!canvas) return;

			var ctx = canvas.getContext('2d');
			var drawing = false;
			var lineCol = $wrap.attr('data-line-color') || '#0f172a';
			var lineW   = parseInt($wrap.attr('data-line-width')) || 2;

			// Handle HDPI / Retina display crisp scaling
			var devicePixelRatio = window.devicePixelRatio || 1;
			function resizeCanvas() {
				var rect = canvas.getBoundingClientRect();
				canvas.width = rect.width * devicePixelRatio;
				canvas.height = rect.height * devicePixelRatio;
				ctx.scale(devicePixelRatio, devicePixelRatio);
				
				// Reset line attributes after resizing
				ctx.lineCap = 'round';
				ctx.lineJoin = 'round';
				ctx.strokeStyle = lineCol;
				ctx.lineWidth = lineW;

				// Clear hidden input if resized
				$hiddenInput.val('').trigger('change');
			}

			// Run on boot
			resizeCanvas();

			// Listen to resize events safely
			$(window).on('resize', function () {
				// Don't resize canvas if it already has drawing to prevent wiping it,
				// but on start we scale it correctly.
			});

			// Draw coordinates helper
			function getPos(e) {
				var rect = canvas.getBoundingClientRect();
				var clientX = e.clientX || (e.touches && e.touches[0] && e.touches[0].clientX);
				var clientY = e.clientY || (e.touches && e.touches[0] && e.touches[0].clientY);
				return {
					x: clientX - rect.left,
					y: clientY - rect.top
				};
			}

			// Mouse events
			$(canvas).on('mousedown', function (e) {
				var rect = canvas.getBoundingClientRect();
				if (canvas.width <= devicePixelRatio || Math.abs(rect.width * devicePixelRatio - canvas.width) > 1) {
					resizeCanvas();
				}
				drawing = true;
				var pos = getPos(e);
				ctx.beginPath();
				ctx.moveTo(pos.x, pos.y);
				e.preventDefault();
			});

			$(canvas).on('mousemove', function (e) {
				if (!drawing) return;
				var pos = getPos(e);
				ctx.lineTo(pos.x, pos.y);
				ctx.stroke();
				e.preventDefault();
			});

			$(window).on('mouseup', function () {
				if (drawing) {
					drawing = false;
					saveData();
				}
			});

			// Touch events (mobile pointer events)
			$(canvas).on('touchstart', function (e) {
				var rect = canvas.getBoundingClientRect();
				if (canvas.width <= devicePixelRatio || Math.abs(rect.width * devicePixelRatio - canvas.width) > 1) {
					resizeCanvas();
				}
				drawing = true;
				var pos = getPos(e);
				ctx.beginPath();
				ctx.moveTo(pos.x, pos.y);
				e.preventDefault();
			});

			$(canvas).on('touchmove', function (e) {
				if (!drawing) return;
				var pos = getPos(e);
				ctx.lineTo(pos.x, pos.y);
				ctx.stroke();
				e.preventDefault();
			});

			$(canvas).on('touchend', function (e) {
				if (drawing) {
					drawing = false;
					saveData();
					e.preventDefault();
				}
			});

			// Clear button
			$clearBtn.on('click', function (e) {
				e.preventDefault();
				ctx.clearRect(0, 0, canvas.width, canvas.height);
				$hiddenInput.val('').trigger('change');
			});

			// Save base64 signature representation
			function saveData() {
				// Verify if canvas is blank
				var blank = document.createElement('canvas');
				blank.width = canvas.width;
				blank.height = canvas.height;
				if (canvas.toDataURL() === blank.toDataURL()) {
					$hiddenInput.val('').trigger('change');
				} else {
					var dataUrl = canvas.toDataURL('image/png');
					$hiddenInput.val(dataUrl).trigger('change');
				}
			}
		});
	}

	/* ====================================================================
	   3. MULTI-STEP NAVIGATION MANAGER
	   ==================================================================== */
	function initMultiSteps($form) {
		var $steps = $form.find('.eas-form-step');
		if (!$steps.length) return;

		// Set step 1 active
		$steps.removeClass('active').first().addClass('active');
		updateStepIndicator($form, 0);

		// Handle "Next" step validation & progress slide
		$form.on('click', '.eas-form-btn-next', function (e) {
			e.preventDefault();
			var currentStepIdx = $steps.filter('.active').index('.eas-form-step');
			var $currentStep = $steps.eq(currentStepIdx);

			// Run standard HTML5 field validation for fields inside the current step wrapper ONLY!
			var stepFieldsValid = true;
			$currentStep.find('input, textarea, select').each(function () {
				var $input = $(this);
				
				// Skip verifying fields that are hidden by conditional logic
				if ($input.closest('.eas-form-field-wrap').is(':hidden')) {
					return;
				}

				if (!this.checkValidity()) {
					this.reportValidity();
					stepFieldsValid = false;
					return false; // Break loop
				}
			});

			if (!stepFieldsValid) return;

			// Slide next
			if (currentStepIdx < $steps.length - 1) {
				$currentStep.removeClass('active');
				$steps.eq(currentStepIdx + 1).addClass('active');
				updateStepIndicator($form, currentStepIdx + 1);
			}
		});

		// Handle "Prev" step
		$form.on('click', '.eas-form-btn-prev', function (e) {
			e.preventDefault();
			var currentStepIdx = $steps.filter('.active').index('.eas-form-step');
			if (currentStepIdx > 0) {
				$steps.eq(currentStepIdx).removeClass('active');
				$steps.eq(currentStepIdx - 1).addClass('active');
				updateStepIndicator($form, currentStepIdx - 1);
			}
		});
	}

	function updateStepIndicator($form, activeIdx) {
		var $steps = $form.find('.eas-form-step');
		var totalSteps = $steps.length;

		// Fill progress bar (if indicator type is bar)
		var $barFill = $form.find('.eas-form-steps-progress-bar-fill');
		if ($barFill.length) {
			var pct = Math.round(((activeIdx + 1) / totalSteps) * 100);
			$barFill.css('width', pct + '%');
		}

		// Update circles (if indicator type is circles)
		var $circlesProgress = $form.find('.eas-form-steps-circles-progress');
		if ($circlesProgress.length) {
			var circlePct = Math.round((activeIdx / (totalSteps - 1)) * 100);
			$circlesProgress.css('width', circlePct + '%');
		}

		$form.find('.eas-form-steps-circle-item').each(function (idx) {
			var $circle = $(this);
			$circle.removeClass('active completed');
			if (idx === activeIdx) {
				$circle.addClass('active');
			} else if (idx < activeIdx) {
				$circle.addClass('completed');
			}
		});
	}

	/* ====================================================================
	   4. DYNAMIC CONDITIONAL LOGIC EVALUATOR
	   ==================================================================== */
	function initConditionalLogic($form) {
		var $logicFields = $form.find('[data-eas-logic]');
		if (!$logicFields.length) return;

		// Run logic on value change
		$form.on('change input', 'input, textarea, select', function () {
			evaluateFormLogic($form);
		});

		// Run initial boot evaluator
		evaluateFormLogic($form);
	}

	function evaluateFormLogic($form) {
		$form.find('[data-eas-logic]').each(function () {
			var $wrap = $(this);
			var logicConfigRaw = $wrap.attr('data-eas-logic');
			if (!logicConfigRaw) return;

			try {
				var config = JSON.parse(logicConfigRaw);
				if (!config.rules || !config.rules.length) return;

				var relation = config.relation || 'all'; // 'all' (AND) or 'any' (OR)
				var action = config.action || 'show';     // 'show' or 'hide'

				var ruleResults = [];
				config.rules.forEach(function (rule) {
					ruleResults.push(evaluateSingleRule($form, rule));
				});

				// Verify relations
				var match = false;
				if (relation === 'any') {
					match = ruleResults.indexOf(true) !== -1;
				} else {
					match = ruleResults.indexOf(false) === -1; // Match all
				}

				// Apply action
				var shouldShow = (action === 'show') ? match : !match;
				
				if (shouldShow) {
					if ($wrap.is(':hidden')) {
						$wrap.stop(true, true).slideDown(250);
						$wrap.find('input, select, textarea').prop('disabled', false);
					}
				} else {
					if ($wrap.is(':visible')) {
						$wrap.stop(true, true).slideUp(200);
						$wrap.find('input, select, textarea').prop('disabled', true);
					}
				}
			} catch (e) {
				// Fail silently
			}
		});
	}

	function evaluateSingleRule($form, rule) {
		var targetId = rule.field_id;
		var operator = rule.operator;
		var compareVal = rule.value !== undefined ? String(rule.value).trim().toLowerCase() : '';

		// Locate target field element
		var $targetInput = $form.find('[data-eas-field-id="' + targetId + '"]');
		if (!$targetInput.length) {
			// Fallback search inside inputs names or classes
			$targetInput = $form.find('[name="' + targetId + '"], [name="' + targetId + '[]"]');
		}

		if (!$targetInput.length) return false;

		// Get current input value safely (supporting radios & checkbox arrays)
		var currentVal = '';
		if ($targetInput.is('[type="checkbox"]')) {
			var checkedVals = [];
			$targetInput.filter(':checked').each(function () {
				checkedVals.push($(this).val());
			});
			currentVal = checkedVals.join(',');
		} else if ($targetInput.is('[type="radio"]')) {
			currentVal = $targetInput.filter(':checked').val() || '';
		} else {
			currentVal = $targetInput.val() || '';
		}

		currentVal = String(currentVal).trim().toLowerCase();

		switch (operator) {
			case 'equals':
				return currentVal === compareVal;
			case 'not_equals':
				return currentVal !== compareVal;
			case 'contains':
				return compareVal !== '' && currentVal.indexOf(compareVal) !== -1;
			case 'greater_than':
				return parseFloat(currentVal) > parseFloat(compareVal);
			case 'less_than':
				return parseFloat(currentVal) < parseFloat(compareVal);
			case 'empty':
				return currentVal === '';
			case 'not_empty':
				return currentVal !== '';
			default:
				return false;
		}
	}

	/* ====================================================================
	   5. AJAX SUBMIT WITH HONEYPOT BLOCKER
	   ==================================================================== */
	function initAjaxSubmit($form) {
		$form.on('submit', function (e) {
			e.preventDefault();

			var $formEl = $(this);
			var $msgBox = $formEl.find('.eas-form-message');
			var $submitBtn = $formEl.find('.eas-form-btn-submit');

			// Hide old message alerts
			$msgBox.hide().removeClass('eas-form-message-success eas-form-message-error').text('');

			// Verify honeypot anti-spam trap
			var honeypotVal = $formEl.find('.eas-form-hidden-hp').val();
			if (honeypotVal !== undefined && honeypotVal !== '') {
				// Spam bot trapped
				$msgBox.addClass('eas-form-message-error').text('Spam detection triggered. Submit blocked.').fadeIn(200);
				return;
			}

			// Validate final step before submission
			var formValid = true;
			$formEl.find('input, textarea, select').each(function () {
				var $input = $(this);
				if ($input.closest('.eas-form-field-wrap').is(':hidden')) return; // Skip hidden logic fields
				if (!this.checkValidity()) {
					this.reportValidity();
					formValid = false;
					return false;
				}
			});

			if (!formValid) return;

			// Add loading status to button
			var originalBtnText = $submitBtn.text();
			$submitBtn.prop('disabled', true).text('Sending...');

			// Prepare data
			var formData = new FormData($formEl[0]);
			formData.append('action', 'apexadfo_form_submit');
			formData.append('nonce', $formEl.find('[name="apexadfo_form_nonce"]').val());

			// Submit
			$.ajax({
				url: apexadfoFormConfig.ajaxurl,
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function (response) {
					$submitBtn.prop('disabled', false).text(originalBtnText);
					if (response.success) {
						$msgBox.addClass('eas-form-message-success').text(response.data.message || 'Form submitted successfully!').fadeIn(200);
						$formEl[0].reset();
						
						// Show animated success overlay
						var successMsg = response.data.message || 'Form submitted successfully!';
						var $overlay = $(
							'<div class="eas-form-success-overlay">' +
								'<svg class="eas-form-success-checkmark" viewBox="0 0 52 52">' +
									'<circle class="eas-form-success-circle" cx="26" cy="26" r="25"/>' +
									'<path class="eas-form-success-check" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>' +
								'</svg>' +
								'<span class="eas-form-success-text">' + successMsg + '</span>' +
								'</div>'
						);
						$formEl.closest('.eas-form-wrap').css('position', 'relative').append($overlay);
						setTimeout(function () {
							$overlay.fadeOut(400, function () { $overlay.remove(); });
						}, 4000);

						// Reset signature pads hidden values
						$formEl.find('.eas-form-sig-value').val('').trigger('change');
						$formEl.find('.eas-form-sig-canvas').each(function () {
							var ctx = this.getContext('2d');
							ctx.clearRect(0, 0, this.width, this.height);
						});

						// Reset range sliders to their initial default values
						$formEl.find('.eas-form-range-wrap').each(function () {
							var $wrap = $(this);
							var $slider = $wrap.find('.eas-form-range-slider');
							var min = $slider.attr('min') || 0;
							$slider.val(min).trigger('change');
						});

						// Reset rating fields
						$formEl.find('.eas-form-rating-wrap').each(function () {
							var $wrap = $(this);
							$wrap.find('.eas-form-rating-value-input').val('0').trigger('change');
							$wrap.find('.eas-form-rating-item').removeClass('active hovered');
						});

						// Reset image selectors
						$formEl.find('.eas-form-image-select-grid').each(function () {
							var $grid = $(this);
							$grid.find('.eas-form-image-select-hidden-input').val('').trigger('change');
							$grid.find('.eas-form-image-select-card').removeClass('active');
						});

						// Reset Multi-step navigation back to slide 1
						var $steps = $formEl.find('.eas-form-step');
						if ($steps.length) {
							$steps.removeClass('active').first().addClass('active');
							updateStepIndicator($formEl, 0);
						}

						// Fire redirect if configured
						if (response.data.redirect_url) {
							setTimeout(function () {
								window.location.href = response.data.redirect_url;
							}, 800);
						}
					} else {
						$msgBox.addClass('eas-form-message-error').text(response.data || 'Submission failed. Please check fields.').fadeIn(200);
					}
				},
				error: function () {
					$submitBtn.prop('disabled', false).text(originalBtnText);
					$msgBox.addClass('eas-form-message-error').text('An error occurred during sending. Please check your connection.').fadeIn(200);
				}
			});
		});
	}

	/* ====================================================================
	   6. RANGE SLIDER BUBBLE & VALUE ENGINE
	   ==================================================================== */
	function initRangeSliders($form) {
		$form.find('.eas-form-range-wrap').each(function () {
			var $wrap = $(this);
			var $slider = $wrap.find('.eas-form-range-slider');
			var $valLabel = $wrap.find('.eas-form-range-val');
			var prefix = $wrap.attr('data-prefix') || '';
			var suffix = $wrap.attr('data-suffix') || '';

			function updateBubble() {
				var val = $slider.val();
				$valLabel.text(prefix + val + suffix);
			}

			// Run on initial load
			updateBubble();

			$slider.on('input change', function () {
				updateBubble();
				$slider.trigger('change'); // ensure change event bubbles up for logic engine
			});
		});
	}

	/* ====================================================================
	   7. INTERACTIVE RATING FIELDS ENGINE
	   ==================================================================== */
	function initRatingFields($form) {
		$form.find('.eas-form-rating-wrap').each(function () {
			var $wrap = $(this);
			var $items = $wrap.find('.eas-form-rating-item');
			var $hiddenInput = $wrap.find('.eas-form-rating-value-input');

			// Hover preview state
			$items.on('mouseenter', function () {
				var val = parseInt($(this).attr('data-value'));
				$items.each(function () {
					var itemVal = parseInt($(this).attr('data-value'));
					if (itemVal <= val) {
						$(this).addClass('hovered');
					} else {
						$(this).removeClass('hovered');
					}
				});
			});

			$wrap.find('.eas-form-rating-stars').on('mouseleave', function () {
				$items.removeClass('hovered');
			});

			// Click Selection lock state
			$items.on('click', function () {
				var val = parseInt($(this).attr('data-value'));
				$hiddenInput.val(val).trigger('change');

				$items.each(function () {
					var itemVal = parseInt($(this).attr('data-value'));
					if (itemVal <= val) {
						$(this).addClass('active');
					} else {
						$(this).removeClass('active');
					}
				});
			});
		});
	}

	/* ====================================================================
	   8. IMAGE SELECT CHOICE CARDS ENGINE
	   ==================================================================== */
	function initImageSelectFields($form) {
		$form.find('.eas-form-image-select-grid').each(function () {
			var $grid = $(this);
			var $cards = $grid.find('.eas-form-image-select-card');
			var $hiddenInput = $grid.find('.eas-form-image-select-hidden-input');

			$cards.on('click', function () {
				var $card = $(this);
				var val = $card.attr('data-value');

				// Update select active status card class
				$cards.removeClass('active');
				$card.addClass('active');

				// Update hidden input element
				$hiddenInput.val(val).trigger('change');
			});
		});
	}

	// Elementor frontend register hook
	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/eas-form.default', initFormWidget);
	});

})(jQuery);

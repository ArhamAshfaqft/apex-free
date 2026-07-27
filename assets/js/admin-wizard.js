/**
 * Apex Addons - Setup Wizard Admin Script
 */
(function($) {
	'use strict';

	var currentStep = 1;

	// Preset element lists (matching PHP Setup_Wizard presets)
	var presetsMap = {
		basic: [
			'form_widget', 'comparison_table', 'nested_slider', 'nested_content_switcher',
			'glass_card', 'conversational_funnel', 'quiz_builder', 'team_member',
			'portfolio_showcase', 'flex_accordion', 'dual_heading', 'svg_icon',
			'scroll_parallax_text', 'nav_menu', 'interactive_image_hotspots',
			'singular_widgets', 'container_carousel', 'magnetic_effect'
		],
		performance: [
			'form_widget', 'comparison_table', 'nested_slider', 'nested_content_switcher',
			'dual_heading', 'svg_icon', 'nav_menu', 'flex_accordion'
		]
	};

	$(document).ready(function() {

		// Switch Steps
		function goToStep(step) {
			if (step < 1 || step > 4) return;
			currentStep = step;

			$('.apexadfo-wizard-step').removeClass('active');
			$('#apexadfo-step-' + step).addClass('active');

			$('.apexadfo-wizard-step-indicator').removeClass('active');
			$('.apexadfo-wizard-step-indicator[data-step="' + step + '"]').addClass('active');
		}

		// Next Step
		$(document).on('click', '.apexadfo-next-step', function(e) {
			e.preventDefault();
			goToStep(currentStep + 1);
		});

		// Previous Step
		$(document).on('click', '.apexadfo-prev-step', function(e) {
			e.preventDefault();
			goToStep(currentStep - 1);
		});

		// Direct Step Nav
		$(document).on('click', '.apexadfo-wizard-step-indicator', function() {
			var targetStep = parseInt($(this).data('step'), 10);
			if (targetStep < currentStep || targetStep === 1) {
				goToStep(targetStep);
			}
		});

		// Preset Selection Handler
		$(document).on('change', 'input[name="apexadfo_preset"]', function() {
			$('.apexadfo-preset-card').removeClass('selected');
			$(this).closest('.apexadfo-preset-card').addClass('selected');

			var selectedPreset = $(this).val();
			applyPresetToToggles(selectedPreset);
		});

		function applyPresetToToggles(preset) {
			if (preset === 'custom') return;

			var activeKeys = [];
			if (preset === 'complete') {
				$('.apexadfo-toggle-input').prop('checked', true);
				return;
			}

			if (presetsMap[preset]) {
				activeKeys = presetsMap[preset];
			}

			$('.apexadfo-toggle-input').each(function() {
				var val = $(this).val();
				$(this).prop('checked', activeKeys.indexOf(val) !== -1);
			});
		}

		// Select All / Deselect All
		$(document).on('click', '.apexadfo-select-all', function(e) {
			e.preventDefault();
			var group = $(this).data('group');
			$('[data-group-grid="' + group + '"] .apexadfo-toggle-input').prop('checked', true);
		});

		$(document).on('click', '.apexadfo-deselect-all', function(e) {
			e.preventDefault();
			var group = $(this).data('group');
			$('[data-group-grid="' + group + '"] .apexadfo-toggle-input').prop('checked', false);
		});

		// Save & Finish AJAX
		$(document).on('click', '.apexadfo-save-finish', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var preset = $('input[name="apexadfo_preset"]:checked').val();
			var selectedElements = [];

			$('.apexadfo-toggle-input:checked').each(function() {
				selectedElements.push($(this).val());
			});

			$btn.find('.apexadfo-btn-text').hide();
			$btn.find('.apexadfo-btn-spinner').show();
			$btn.prop('disabled', true);

			$.ajax({
				url: ApexAdfoWizard.ajax_url,
				type: 'POST',
				data: {
					action: 'apexadfo_save_wizard',
					security: ApexAdfoWizard.nonce,
					preset: preset,
					elements: selectedElements
				},
				success: function(response) {
					$btn.find('.apexadfo-btn-spinner').hide();
					$btn.find('.apexadfo-btn-text').show();
					$btn.prop('disabled', false);

					if (response.success) {
						goToStep(4);
					} else {
						alert(response.data.message || 'Error saving wizard settings.');
					}
				},
				error: function() {
					$btn.find('.apexadfo-btn-spinner').hide();
					$btn.find('.apexadfo-btn-text').show();
					$btn.prop('disabled', false);
					alert('Connection error. Please try again.');
				}
			});
		});

	});

})(jQuery);

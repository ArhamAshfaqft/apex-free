/**
 * APEX ADDONS - Admin Dashboard Engine
 *
 * Handles AJAX state saving, tabs routing, bulk toggling, and client-side real-time search.
 */
(function ($) {
	'use strict';

	$(document).ready(function () {

		// -----------------------------------------------
		// 1. TABS NAVIGATION
		// -----------------------------------------------
		$('.eas-admin-tab-trigger').on('click', function () {
			var targetTab = $(this).attr('data-tab');

			// Update tabs triggers
			$('.eas-admin-tab-trigger').removeClass('active');
			$(this).addClass('active');

			// Update tabs content panels (supports settings page)
			$('.eas-admin-tab-content').removeClass('active');
			$('#eas-tab-' + targetTab).addClass('active');

			// Update tabs content panels (supports theme builder page)
			$('.eas-admin-tab-panel').removeClass('active');
			$('#' + targetTab).addClass('active');

			// Run search check to make sure it filters correctly
			runSearch();
		});

		// -----------------------------------------------
		// 2. REAL-TIME SEARCH FILTER
		// -----------------------------------------------
		$('#eas-admin-search').on('input', function () {
			runSearch();
		});

		function runSearch() {
			var $search = $('#eas-admin-search');
			if (!$search.length) {
				return;
			}
			var query = $search.val().toLowerCase().trim();
			var activePanel = $('.eas-admin-tab-content.active, .eas-admin-tab-panel.active');
			var cards = activePanel.find('.eas-admin-card');
			var visibleCount = 0;

			cards.each(function () {
				var $card = $(this);
				var title = $card.find('.eas-admin-card-title').text().toLowerCase();
				var desc = $card.find('.eas-admin-card-desc').text().toLowerCase();

				if (title.indexOf(query) !== -1 || desc.indexOf(query) !== -1) {
					$card.show();
					visibleCount++;
				} else {
					$card.hide();
				}
			});

			// Show or hide "No results" box
			var $noResults = activePanel.find('.eas-admin-no-results');
			if (visibleCount === 0 && cards.length > 0) {
				$noResults.fadeIn(150);
			} else {
				$noResults.hide();
			}
		}

		// -----------------------------------------------
		// 3. BULK ACTIONS (ENABLE / DISABLE ALL)
		// -----------------------------------------------
		$('.eas-admin-bulk-trigger').on('click', function () {
			var action = $(this).attr('data-action'); // 'enable' or 'disable'
			var activePanel = $('.eas-admin-tab-content.active, .eas-admin-tab-panel.active');
			
			// Find all toggles in the active panel that are NOT disabled/locked
			var toggles = activePanel.find('.eas-addon-toggle:not(:disabled)');
			if (!toggles.length) return;

			var status = (action === 'enable');
			var listToSave = [];

			toggles.each(function () {
				var checkbox = $(this);
				if (checkbox.prop('checked') !== status) {
					checkbox.prop('checked', status);
					listToSave.push({
						id: checkbox.attr('data-addon-id'),
						status: status
					});
				}
			});

			if (listToSave.length > 0) {
				bulkSaveAddons(listToSave);
			}
		});

		// -----------------------------------------------
		// 4. INDIVIDUAL TOGGLE AJAX SAVE
		// -----------------------------------------------
		$('.eas-addon-toggle').on('change', function () {
			var checkbox = $(this);
			var addonId = checkbox.attr('data-addon-id');
			var status = checkbox.prop('checked');

			// Apply visual loading feedback if desired
			var card = checkbox.closest('.eas-admin-card');
			card.css('opacity', '0.7');

			$.ajax({
				url: apexadfoAdmin.ajaxurl,
				type: 'POST',
				data: {
					action: 'apexadfo_toggle_addon',
					addon_id: addonId,
					status: status,
					nonce: apexadfoAdmin.nonce
				},
				success: function (response) {
					card.css('opacity', '1');
				},
				error: function () {
					// Revert on error
					checkbox.prop('checked', !status);
					card.css('opacity', '1');
					alert('Error saving settings. Please try again.');
				}
			});
		});

		// Bulk save helper
		function bulkSaveAddons(list) {
			// Loop and save sequentially or pack them in a single batch
			// For simplicity and bulletproof performance, we run them as a batch AJAX
			$.ajax({
				url: apexadfoAdmin.ajaxurl,
				type: 'POST',
				data: {
					action: 'apexadfo_bulk_toggle_addons',
					addons_list: JSON.stringify(list),
					nonce: apexadfoAdmin.nonce
				},
				success: function (response) {
					// Success feedback
				},
				error: function () {
					alert('Error saving bulk settings. Please refresh the page.');
				}
			});
		}

		// -----------------------------------------------
		// 5. SIGNATURE PREVIEW MODAL
		// -----------------------------------------------
		$('.eas-view-sig-trigger').on('click', function (e) {
			e.preventDefault();
			var sigData = $(this).attr('data-sig');
			$('#eas-sig-modal-img').attr('src', sigData);
			$('#eas-sig-modal').fadeIn(200);
		});

		$('#eas-sig-modal-close, #eas-sig-modal').on('click', function (e) {
			if (e.target.id === 'eas-sig-modal' || e.target.id === 'eas-sig-modal-close') {
				$('#eas-sig-modal').fadeOut(150, function () {
					$('#eas-sig-modal-img').attr('src', '');
				});
			}
		});

		// -----------------------------------------------
		// 6. DELETE SUBMISSION AJAX
		// -----------------------------------------------
		$('.eas-delete-submission-btn').on('click', function () {
			if (!confirm('Are you sure you want to permanently delete this submission?')) {
				return;
			}

			var button = $(this);
			var submissionId = button.attr('data-id');
			var row = $('#eas-submission-row-' + submissionId);

			row.css('opacity', '0.5');

			$.ajax({
				url: apexadfoAdmin.ajaxurl,
				type: 'POST',
				data: {
					action: 'apexadfo_delete_submission',
					submission_id: submissionId,
					nonce: apexadfoAdmin.nonce
				},
				success: function (response) {
					if (response.success) {
						row.fadeOut(300, function () {
							row.remove();
							// Check if table is empty, show empty state
							if ($('.eas-submissions-table tbody tr').length === 0) {
								$('.eas-submissions-table-wrap').html(
									'<div class="eas-submissions-empty-state">' +
									'<span class="dashicons dashicons-database" style="font-size: 32px; width: 32px; height: 32px; color: #94a3b8; margin-bottom: 12px; display: inline-block;"></span>' +
									'<p>No form submissions logged yet.</p>' +
									'</div>'
								);
							}
						});
					} else {
						row.css('opacity', '1');
						alert('Error deleting submission: ' + (response.data || 'Unknown error'));
					}
				},
				error: function () {
					row.css('opacity', '1');
					alert('Connection error. Please try again.');
				}
			});
		});

	});

})(jQuery);

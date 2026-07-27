(function($) {
	'use strict';

	var initProgressTracker = function($scope) {
		var $tracker = $scope.find('.eas-progress-tracker-wrap');
		if (!$tracker.length) return;

		var $bar = $tracker.find('.eas-progress-tracker-bar');
		var targetSelector = $tracker.data('target');
		var position = $tracker.data('position') || 'inline';

		// Handle viewport position moves
		if (position === 'top' || position === 'bottom') {
			// Append to body to avoid clipping by container overflows
			var fixedClass = 'eas-progress-tracker-fixed-' + position;
			var $existing = $('body').children('.' + fixedClass);
			if (!$existing.length) {
				$tracker.addClass(fixedClass).appendTo('body');
			} else {
				$tracker = $existing;
				$bar = $tracker.find('.eas-progress-tracker-bar');
			}
		}

		var updateProgress = function() {
			var scrollTop = $(window).scrollTop();
			var docHeight = $(document).height();
			var winHeight = $(window).height();
			
			var progress = 0;

			if (targetSelector) {
				var $target = $(targetSelector).first();
				if ($target.length) {
					var targetTop = $target.offset().top;
					var targetHeight = $target.outerHeight();
					var scrollOffset = scrollTop - targetTop;
					var scrollableHeight = targetHeight - winHeight;

					if (scrollOffset > 0 && scrollableHeight > 0) {
						progress = (scrollOffset / scrollableHeight) * 100;
					} else if (scrollOffset >= scrollableHeight) {
						progress = 100;
					} else {
						progress = 0;
					}
				}
			} else {
				var totalScrollable = docHeight - winHeight;
				if (totalScrollable > 0) {
					progress = (scrollTop / totalScrollable) * 100;
				}
			}

			// Constrain progress between 0 and 100
			progress = Math.min(Math.max(progress, 0), 100);
			$bar.css('width', progress + '%');
		};

		// Run initially and bind to scroll
		updateProgress();
		$(window).on('scroll.easProgress resize.easProgress', updateProgress);

		// Clean up when destroyed/removed (for Elementor editing mode)
		$scope.on('destroy', function() {
			$(window).off('scroll.easProgress resize.easProgress');
			if (position === 'top' || position === 'bottom') {
				$('body').children('.eas-progress-tracker-fixed-' + position).remove();
			}
		});
	};

	$(window).on('elementor/frontend/init', function() {
		elementorFrontend.hooks.addAction('frontend/element_ready/eas-progress-tracker.default', initProgressTracker);
	});
})(jQuery);

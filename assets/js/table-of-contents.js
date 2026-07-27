(function($) {
	'use strict';

	var initTOC = function($scope) {
		var $toc = $scope.find('.eas-toc-box');
		if (!$toc.length) return;

		var $list = $toc.find('.eas-toc-list');
		var targetSelector = $toc.data('target') || '.eas-post-content, .entry-content, main';
		var headingsSelector = $toc.data('headings') || 'h2,h3,h4';
		
		// Find post content container
		var $content = $(targetSelector).first();
		if (!$content.length) {
			$content = $('body');
		}

		var $headings = $content.find(headingsSelector);
		if (!$headings.length) {
			$list.append('<li><span class="eas-toc-empty">No headings found.</span></li>');
			return;
		}

		$list.empty();

		// Populate headings and add IDs if missing
		$headings.each(function(index, el) {
			var $heading = $(el);
			var id = $heading.attr('id');
			if (!id) {
				id = 'eas-toc-heading-' + index;
				$heading.attr('id', id);
			}

			var text = $heading.text().trim();
			var tagName = el.tagName.toLowerCase();

			var $li = $('<li>').addClass('eas-toc-item');
			var $a = $('<a>')
				.attr('href', '#' + id)
				.addClass('eas-toc-depth-' + tagName)
				.text(text);

			$li.append($a);
			$list.append($li);
		});

		// Toggle minimize/maximize
		$toc.find('.eas-toc-toggle').on('click', function() {
			var $btn = $(this);
			var isMinimized = $btn.data('minimized');
			if (isMinimized) {
				$list.slideDown();
				$btn.text($btn.data('text-minimize') || 'Minimize').data('minimized', false);
			} else {
				$list.slideUp();
				$btn.text($btn.data('text-maximize') || 'Maximize').data('minimized', true);
			}
		});

		// Smooth scrolling
		$list.find('a').on('click', function(e) {
			e.preventDefault();
			var targetId = $(this).attr('href');
			var $target = $(targetId);
			if ($target.length) {
				$('html, body').animate({
					scrollTop: $target.offset().top - 80 // Offset for admin bar/fixed header
				}, 600);
			}
		});

		// IntersectionObserver to highlight active item on scroll
		if ('IntersectionObserver' in window) {
			var observerOptions = {
				root: null,
				rootMargin: '0px 0px -60% 0px',
				threshold: 1.0
			};

			var observer = new IntersectionObserver(function(entries) {
				entries.forEach(function(entry) {
					if (entry.isIntersecting) {
						var id = entry.target.id;
						$list.find('a').removeClass('active');
						$list.find('a[href="#' + id + '"]').addClass('active');
					}
				});
			}, observerOptions);

			$headings.each(function() {
				observer.observe(this);
			});
		}
	};

	$(window).on('elementor/frontend/init', function() {
		elementorFrontend.hooks.addAction('frontend/element_ready/eas-table-of-contents.default', initTOC);
	});
})(jQuery);

(function ($) {
	'use strict';

	function refresh(scope) {
		if (!scope || !scope.length || typeof window.easHScrollRefresh !== 'function') return;
		window.clearTimeout(scope.data('eas-hscroll-editor-timer'));
		scope.data('eas-hscroll-editor-timer', window.setTimeout(function () {
			window.easHScrollRefresh(scope[0]);
		}, 80));
	}

	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/container', function ($scope) {
			if ($scope.hasClass('eas-container-hscroll-active')) refresh($scope);
		});

		if (window.elementor && elementor.channels && elementor.channels.editor) {
			elementor.channels.editor.on('change', function (controlView, elementView) {
				var model = elementView && elementView.model;
				var id = model && model.get ? model.get('id') : '';
				if (!id) return;
				var scope = $('.elementor-element-' + id);
				if (scope.hasClass('eas-container-hscroll-active')) refresh(scope);
			});
		}
	});
})(jQuery);

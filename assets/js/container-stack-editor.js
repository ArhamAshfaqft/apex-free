(function ($) {
	'use strict';
	if (typeof elementor === 'undefined') return;
	elementor.channels.editor.on('change', function (model) {
		var changed = model && model.changed ? Object.keys(model.changed) : [];
		if (!changed.some(function (key) { return key.indexOf('eas_container_stack') === 0 || key.indexOf('eas_stack_studio') === 0; })) return;
		window.setTimeout(function () {
			var doc = elementor.$preview && elementor.$preview[0] ? elementor.$preview[0].contentDocument : null;
			if (!doc || !doc.defaultView.EASContainerStack) return;
			var previewJQuery = doc.defaultView.jQuery;
			if (!previewJQuery) return;
			previewJQuery(doc).find('.eas-container-stack-active[data-eas-stack-active="yes"]').each(function () { doc.defaultView.EASContainerStack.init(previewJQuery(this)); });
		}, 250);
	});
})(jQuery);

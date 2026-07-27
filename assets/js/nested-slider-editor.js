(function($) {
	'use strict';

	function registerNestedSlider() {
		try {
			if (window.elementor && 
				elementor.modules && 
				elementor.modules.elements && 
				elementor.modules.elements.types && 
				typeof elementor.modules.elements.types.NestedElementBase === 'function' &&
				window.$e && 
				$e.components && 
				$e.components.get('nested-elements') && 
				$e.components.get('nested-elements').exports &&
				$e.components.get('nested-elements').exports.NestedView
			) {
				if (elementor.elementsManager && elementor.elementsManager.elementTypes) {
					if (!elementor.elementsManager.elementTypes['eas-nested-slider']) {
						// Use ES6 class syntax because NestedElementBase is a native ES6 class and does not support .extend()
						class NestedSlider extends elementor.modules.elements.types.NestedElementBase {
							getType() {
								return 'eas-nested-slider';
							}
							getView() {
								return $e.components.get('nested-elements').exports.NestedView;
							}
						}
						elementor.elementsManager.registerElementType(new NestedSlider());
					}
					return true;
				}
			}
		} catch (err) {
			console.error('APEX Slider Editor Registration Error:', err);
		}
		return false;
	}

	// Try to register immediately
	if (!registerNestedSlider()) {
		if (window.elementorCommon && elementorCommon.elements && elementorCommon.elements.$window) {
			elementorCommon.elements.$window.on('elementor/nested-element-type-loaded', registerNestedSlider);
		} else {
			$(window).on('elementor/nested-element-type-loaded', registerNestedSlider);
		}
	}
})(jQuery);

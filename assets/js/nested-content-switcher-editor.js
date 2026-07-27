( function( $, window ) {
	'use strict';

	function apexadfoRegisterNestedContentSwitcher() {
		try {
			if (
				! window.elementor ||
				! elementor.modules ||
				! elementor.modules.elements ||
				! elementor.modules.elements.types ||
				'function' !== typeof elementor.modules.elements.types.NestedElementBase ||
				! window.$e ||
				! $e.components ||
				! $e.components.get( 'nested-elements' ) ||
				! $e.components.get( 'nested-elements' ).exports ||
				! $e.components.get( 'nested-elements' ).exports.NestedView ||
				! elementor.elementsManager ||
				! elementor.elementsManager.elementTypes
			) {
				return false;
			}

			if ( ! elementor.elementsManager.elementTypes[ 'eas-nested-content-switcher' ] ) {
				class ApexadfoNestedContentSwitcher extends elementor.modules.elements.types.NestedElementBase {
					getType() {
						return 'eas-nested-content-switcher';
					}

					getView() {
						return $e.components.get( 'nested-elements' ).exports.NestedView;
					}
				}

				elementor.elementsManager.registerElementType( new ApexadfoNestedContentSwitcher() );
			}

			return true;
		} catch ( error ) {
			window.console.error( 'Apex nested content switcher editor registration failed.', error );
			return false;
		}
	}

	if ( ! apexadfoRegisterNestedContentSwitcher() ) {
		if ( window.elementorCommon && elementorCommon.elements && elementorCommon.elements.$window ) {
			elementorCommon.elements.$window.on( 'elementor/nested-element-type-loaded', apexadfoRegisterNestedContentSwitcher );
		} else {
			$( window ).on( 'elementor/nested-element-type-loaded', apexadfoRegisterNestedContentSwitcher );
		}
	}
}( jQuery, window ) );

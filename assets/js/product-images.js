( function( $ ) {
	'use strict';

	function initProductGallery( $scope ) {
		var $root = $scope && $scope.length ? $scope : $( document );
		var $galleries = $root.is( '.woocommerce-product-gallery' )
			? $root
			: $root.find( '.eas-product-gallery .woocommerce-product-gallery' );

		$galleries.each( function() {
			var $gallery = $( this );
			if ( $.fn.wc_product_gallery && ! $gallery.data( 'apexadfoProductGalleryInitialized' ) ) {
				$gallery.wc_product_gallery();
				$gallery.data( 'apexadfoProductGalleryInitialized', true );
			}

			var $trigger = $gallery.find( '.woocommerce-product-gallery__trigger' );
			if ( $trigger.length && ! $trigger.find( 'svg' ).length ) {
				$trigger.empty().append( '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0f172a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>' );
			}
		} );
	}

	$( function() {
		initProductGallery( $( document ) );
	} );

	$( window ).on( 'elementor/frontend/init', function() {
		if ( window.elementorFrontend && elementorFrontend.hooks ) {
			elementorFrontend.hooks.addAction( 'frontend/element_ready/eas-product-images.default', initProductGallery );
		}
	} );
}( jQuery ) );

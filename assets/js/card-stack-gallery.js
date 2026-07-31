/**
 * 3D Stacked Card Image Gallery Widget Script
 * Apex Addons for Elementor
 */
( function( $ ) {
	'use strict';

	var EasCardStackGallery = function( $scope ) {
		var $wrapper = $scope.find( '.eas-card-stack-gallery' );
		if ( ! $wrapper.length ) return;

		var $list = $wrapper.find( '.eas-card-stack-list' );
		var $items = $list.children( '.eas-card-stack-item' );
		if ( $items.length <= 1 ) return;

		var configRaw = $wrapper.attr( 'data-eas-stack-config' ) || '{}';
		var config = {};
		try {
			config = JSON.parse( configRaw );
		} catch ( e ) {
			config = {};
		}

		var autoplay = config.autoplay === true;
		var autoplaySpeed = parseInt( config.autoplaySpeed, 10 ) || 3000;
		var offsetX = parseFloat( config.offsetX ) || 20;
		var offsetY = parseFloat( config.offsetY ) || -20;
		var tilt = parseFloat( config.tilt ) || 0;
		var scaleFactor = parseFloat( config.scaleFactor ) || 0.94;
		var maxVisible = parseInt( config.visibleCards, 10 ) || 4;

		var isAnimating = false;
		var autoplayTimer = null;
		var touchStartX = 0;
		var touchStartY = 0;

		function updateStackPositions() {
			var currentItems = $list.children( '.eas-card-stack-item' );
			var total = currentItems.length;

			currentItems.each( function( index ) {
				var $item = $( this );
				var revIndex = index; // 0 is top item

				if ( revIndex < maxVisible ) {
					var x = revIndex * offsetX;
					var y = revIndex * offsetY;
					var scale = Math.pow( scaleFactor, revIndex );
					var rot = revIndex * tilt;
					var zIndex = total - revIndex;
					var opacity = 1 - ( revIndex * 0.18 );

					$item.css( {
						'transform': 'translate3d(' + x + 'px, ' + y + 'px, ' + ( -revIndex * 40 ) + 'px) scale(' + scale + ') rotate(' + rot + 'deg)',
						'z-index': zIndex,
						'opacity': Math.max( 0.2, opacity ),
						'pointer-events': revIndex === 0 ? 'auto' : 'none'
					} );
				} else {
					$item.css( {
						'transform': 'translate3d(' + ( maxVisible * offsetX ) + 'px, ' + ( maxVisible * offsetY ) + 'px, -200px) scale(0.7) rotate(0deg)',
						'z-index': 0,
						'opacity': 0,
						'pointer-events': 'none'
					} );
				}
			} );
		}

		function cycleNext() {
			if ( isAnimating ) return;
			isAnimating = true;

			var $currentItems = $list.children( '.eas-card-stack-item' );
			var $topItem = $currentItems.first();

			// Fly out transition
			$topItem.css( {
				'transform': 'translate3d(-120%, 30px, 50px) rotate(-15deg) scale(0.9)',
				'opacity': 0,
				'transition': 'transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.35s ease'
			} );

			setTimeout( function() {
				// Move top item to bottom of stack list
				$topItem.detach().appendTo( $list );

				// Reset transition for instant repositioning to back
				$topItem.css( {
					'transition': 'none',
					'opacity': 0
				} );

				// Trigger reflow & update 3D positions
				updateStackPositions();

				setTimeout( function() {
					// Restore standard transitions
					$list.children( '.eas-card-stack-item' ).css( 'transition', 'transform 0.45s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.45s ease, box-shadow 0.3s ease' );
					isAnimating = false;
				}, 50 );
			}, 380 );
		}

		function cyclePrev() {
			if ( isAnimating ) return;
			isAnimating = true;

			var $currentItems = $list.children( '.eas-card-stack-item' );
			var $bottomItem = $currentItems.last();

			// Move bottom item to top
			$bottomItem.detach().prependTo( $list );

			// Start from fly-out position
			$bottomItem.css( {
				'transition': 'none',
				'transform': 'translate3d(120%, -30px, 50px) rotate(15deg) scale(0.9)',
				'opacity': 0,
				'z-index': $currentItems.length + 1
			} );

			setTimeout( function() {
				$bottomItem.css( 'transition', 'transform 0.45s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.45s ease, box-shadow 0.3s ease' );
				updateStackPositions();

				setTimeout( function() {
					isAnimating = false;
				}, 450 );
			}, 30 );
		}

		// Click card to cycle
		$list.on( 'click', '.eas-card-stack-item', function( e ) {
			var $clicked = $( this );
			if ( $clicked.index() === 0 ) {
				var link = $clicked.data( 'link' );
				if ( link && ! $( e.target ).closest( 'a' ).length ) {
					window.open( link, $clicked.data( 'target' ) || '_self' );
				} else {
					cycleNext();
				}
			}
		} );

		// Navigation buttons
		$wrapper.find( '.eas-card-stack-next' ).on( 'click', function( e ) {
			e.preventDefault();
			cycleNext();
		} );

		$wrapper.find( '.eas-card-stack-prev' ).on( 'click', function( e ) {
			e.preventDefault();
			cyclePrev();
		} );

		// Touch gestures
		$list.on( 'touchstart', function( e ) {
			var touch = e.originalEvent.touches[0];
			touchStartX = touch.clientX;
			touchStartY = touch.clientY;
		} );

		$list.on( 'touchend', function( e ) {
			var touch = e.originalEvent.changedTouches[0];
			var diffX = touch.clientX - touchStartX;
			var diffY = touch.clientY - touchStartY;

			if ( Math.abs( diffX ) > 40 && Math.abs( diffX ) > Math.abs( diffY ) ) {
				if ( diffX < 0 ) {
					cycleNext();
				} else {
					cyclePrev();
				}
			}
		} );

		// Autoplay
		if ( autoplay ) {
			function startAutoplay() {
				stopAutoplay();
				autoplayTimer = setInterval( cycleNext, autoplaySpeed );
			}

			function stopAutoplay() {
				if ( autoplayTimer ) clearInterval( autoplayTimer );
			}

			startAutoplay();

			$wrapper.on( 'mouseenter touchstart', stopAutoplay );
			$wrapper.on( 'mouseleave touchend', startAutoplay );
		}

		// Initial position setup
		updateStackPositions();
	};

	$( window ).on( 'elementor/frontend/init', function() {
		elementorFrontend.hooks.addAction( 'frontend/element_ready/apexadfo-card-stack-gallery.default', EasCardStackGallery );
	} );

} )( jQuery );

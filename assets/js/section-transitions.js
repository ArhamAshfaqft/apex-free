( function ( root, factory ) {
	'use strict';

	var api = factory( root, root && root.document );
	if ( typeof module === 'object' && module.exports ) {
		module.exports = api;
	} else if ( root ) {
		root.EASSectionTransitions = api;
	}
}( typeof window !== 'undefined' ? window : globalThis, function ( win, doc ) {
	'use strict';

	var instances = new Map();
	var sourceRefs = new WeakMap();
	var frame = 0;
	var resizeTimer = 0;
	var mutationTimer = 0;
	var initialized = false;

	function clamp( value, min, max ) {
		return Math.min( max, Math.max( min, value ) );
	}

	function number( value, fallback ) {
		value = parseFloat( value );
		return Number.isFinite( value ) ? value : fallback;
	}

	function parseConfig( element ) {
		try {
			var config = JSON.parse( element.getAttribute( 'data-eas-section-transition' ) || '{}' );
			config.start = number( config.start, 100 );
			config.end = number( config.end, 10 );
			config.entryOffset = Math.max( 0, number( config.entryOffset, 80 ) );
			config.entryRadius = Math.max( 0, number( config.entryRadius, 28 ) );
			config.smoothing = clamp( number( config.smoothing, 0.16 ), 0.04, 1 );
			config.pinTop = Math.max( 0, number( config.pinTop, 0 ) );
			return config;
		} catch ( error ) {
			return null;
		}
	}

	function computeProgress( top, viewportHeight, startPercent, endPercent ) {
		var start = viewportHeight * number( startPercent, 100 ) / 100;
		var end = viewportHeight * number( endPercent, 10 ) / 100;
		var distance = start - end;
		if ( Math.abs( distance ) < 1 ) {
			distance = distance < 0 ? -1 : 1;
		}
		return clamp( ( start - top ) / distance, 0, 1 );
	}

	function freeState( progress, config ) {
		var eased = 1 - Math.pow( 1 - clamp( progress, 0, 1 ), 3 );
		return {
			translateY: ( 1 - eased ) * number( config.entryOffset, 80 ),
			radius: ( 1 - eased ) * number( config.entryRadius, 28 ),
			opacity: 1
		};
	}

	function previousSection( target, config ) {
		if ( config && config.sourceSelector ) {
			try {
				var selected = doc.querySelector( config.sourceSelector );
				if ( selected && selected !== target ) {
					return selected;
				}
			} catch ( error ) {
				// Invalid custom selectors safely fall back to the previous section.
			}
		}

		var sibling = target.previousElementSibling;
		while ( sibling && ( sibling.matches( 'script, style, template' ) || sibling.classList.contains( 'elementor-add-section' ) ) ) {
			sibling = sibling.previousElementSibling;
		}
		return sibling;
	}

	function hasPinConflict( source, target ) {
		if ( ! source || source.parentElement !== target.parentElement ) {
			return true;
		}
		var selector = '[data-eas-stack-active="yes"], [data-eas-hscroll-config], [data-eas-carousel-config]';
		return source.matches( selector ) || target.matches( selector );
	}

	function retainSource( source, config ) {
		if ( ! source ) {
			return;
		}
		var count = sourceRefs.get( source ) || 0;
		sourceRefs.set( source, count + 1 );
		if ( count === 0 ) {
			var computed = win.getComputedStyle( source );
			source.style.setProperty( '--eas-transition-source-base-filter', computed.filter === 'none' ? '' : computed.filter );
		}
		source.classList.add( 'eas-section-transition-source' );
		source.style.setProperty( '--eas-transition-pin-top', number( config.pinTop, 0 ) + 'px' );
	}

	function releaseSource( source ) {
		if ( ! source ) {
			return;
		}
		var count = ( sourceRefs.get( source ) || 1 ) - 1;
		if ( count > 0 ) {
			sourceRefs.set( source, count );
			return;
		}
		sourceRefs.delete( source );
		source.classList.remove( 'eas-section-transition-source', 'eas-section-transition-source-active' );
		source.style.removeProperty( '--eas-transition-pin-top' );
		source.style.removeProperty( '--eas-transition-source-opacity' );
		source.style.removeProperty( '--eas-transition-source-scale' );
		source.style.removeProperty( '--eas-transition-source-blur' );
		source.style.removeProperty( '--eas-transition-source-x' );
		source.style.removeProperty( '--eas-transition-source-y' );
		source.style.removeProperty( '--eas-transition-source-base-filter' );
	}

	function isDisabled( config ) {
		var width = win.innerWidth || 1440;
		if ( config.disableMobile === 'yes' && width <= 767 ) {
			return true;
		}
		if ( config.disableTablet === 'yes' && width > 767 && width <= 1024 ) {
			return true;
		}
		return config.respectReducedMotion === 'yes' && win.matchMedia && win.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	}

	function resetVisuals( instance ) {
		var target = instance.target;
		target.classList.remove( 'eas-section-transition-running', 'eas-section-transition-complete' );
		[ '--eas-transition-x', '--eas-transition-y', '--eas-transition-scale', '--eas-transition-opacity', '--eas-transition-radius', '--eas-transition-clip' ].forEach( function ( property ) {
			target.style.removeProperty( property );
		} );
		if ( instance.source ) {
			instance.source.classList.remove( 'eas-section-transition-source-active' );
		}
		if ( instance.config.pro === true && win.EASSectionTransitionsPro && typeof win.EASSectionTransitionsPro.reset === 'function' ) {
			win.EASSectionTransitionsPro.reset( instance );
		}
	}

	function createInstance( target ) {
		var config = parseConfig( target );
		if ( ! config ) {
			return null;
		}
		var source = previousSection( target, config );
		var shouldPin = config.pinPrevious === 'yes' && ! hasPinConflict( source, target );
		var targetComputed = win.getComputedStyle( target );
		var sourceComputed = source ? win.getComputedStyle( source ) : null;
		var instance = {
			target: target,
			source: source,
			config: config,
			pin: shouldPin,
			progress: -1,
			targetProgress: 0,
			destroyed: false,
			proState: null,
			baseTargetOpacity: number( targetComputed.opacity, 1 ),
			baseSourceOpacity: sourceComputed ? number( sourceComputed.opacity, 1 ) : 1
		};

		target.style.setProperty( '--eas-transition-base-clip', targetComputed.clipPath === 'none' ? 'none' : targetComputed.clipPath );
		target.classList.add( 'eas-section-transition-ready' );
		if ( shouldPin ) {
			retainSource( source, config );
		}
		if ( config.pro === true && win.EASSectionTransitionsPro && typeof win.EASSectionTransitionsPro.prepare === 'function' ) {
			instance.proState = win.EASSectionTransitionsPro.prepare( instance ) || null;
		}
		return instance;
	}

	function destroyInstance( instance ) {
		if ( ! instance || instance.destroyed ) {
			return;
		}
		instance.destroyed = true;
		resetVisuals( instance );
		instance.target.classList.remove( 'eas-section-transition-ready' );
		instance.target.style.removeProperty( '--eas-transition-base-clip' );
		if ( instance.config.pro === true && win.EASSectionTransitionsPro && typeof win.EASSectionTransitionsPro.destroy === 'function' ) {
			win.EASSectionTransitionsPro.destroy( instance );
		}
		if ( instance.pin ) {
			releaseSource( instance.source );
		}
	}

	function render( instance, progress ) {
		var target = instance.target;
		var config = instance.config;
		if ( isDisabled( config ) ) {
			resetVisuals( instance );
			return;
		}

		target.classList.toggle( 'eas-section-transition-running', progress > 0 && progress < 1 );
		target.classList.toggle( 'eas-section-transition-complete', progress >= 0.999 );
		if ( instance.source ) {
			instance.source.classList.toggle( 'eas-section-transition-source-active', progress > 0 && progress < 1 );
		}

		if ( config.pro === true && win.EASSectionTransitionsPro && typeof win.EASSectionTransitionsPro.render === 'function' ) {
			win.EASSectionTransitionsPro.render( instance, progress );
			return;
		}

		var state = freeState( progress, config );
		target.style.setProperty( '--eas-transition-x', '0px' );
		target.style.setProperty( '--eas-transition-y', state.translateY.toFixed( 3 ) + 'px' );
		target.style.setProperty( '--eas-transition-scale', '1' );
		target.style.setProperty( '--eas-transition-opacity', instance.baseTargetOpacity.toFixed( 4 ) );
		target.style.setProperty( '--eas-transition-radius', state.radius.toFixed( 3 ) + 'px' );
	}

	function updateTargets() {
		var viewportHeight = Math.max( win.innerHeight || 0, 1 );
		instances.forEach( function ( instance, target ) {
			if ( ! doc.documentElement.contains( target ) ) {
				destroyInstance( instance );
				instances.delete( target );
				return;
			}
			instance.targetProgress = computeProgress( target.getBoundingClientRect().top, viewportHeight, instance.config.start, instance.config.end );
		} );
	}

	function tick() {
		frame = 0;
		var keepGoing = false;
		updateTargets();
		instances.forEach( function ( instance ) {
			var delta = instance.targetProgress - instance.progress;
			if ( instance.progress < 0 ) {
				instance.progress = instance.targetProgress;
			} else if ( Math.abs( delta ) > 0.0005 ) {
				instance.progress += delta * instance.config.smoothing;
				keepGoing = true;
			} else {
				instance.progress = instance.targetProgress;
			}
			render( instance, clamp( instance.progress, 0, 1 ) );
		} );
		if ( keepGoing ) {
			frame = win.requestAnimationFrame( tick );
		}
	}

	function requestRender() {
		if ( ! frame ) {
			frame = win.requestAnimationFrame( tick );
		}
	}

	function scan( scope ) {
		if ( ! doc ) {
			return;
		}
		var rootNode = scope && scope.querySelectorAll ? scope : doc;
		var nodes = [];
		if ( rootNode.matches && rootNode.matches( '[data-eas-section-transition]' ) ) {
			nodes.push( rootNode );
		}
		rootNode.querySelectorAll( '[data-eas-section-transition]' ).forEach( function ( node ) {
			nodes.push( node );
		} );
		nodes.forEach( function ( target ) {
			var existing = instances.get( target );
			if ( existing ) {
				destroyInstance( existing );
			}
			var instance = createInstance( target );
			if ( instance ) {
				instances.set( target, instance );
			}
		} );
		requestRender();
	}

	function destroyAll() {
		instances.forEach( destroyInstance );
		instances.clear();
		if ( frame ) {
			win.cancelAnimationFrame( frame );
			frame = 0;
		}
	}

	function init() {
		if ( initialized || ! doc || ! win.requestAnimationFrame ) {
			return;
		}
		initialized = true;
		scan( doc );
		win.addEventListener( 'scroll', requestRender, { passive: true } );
		win.addEventListener( 'resize', function () {
			win.clearTimeout( resizeTimer );
			resizeTimer = win.setTimeout( requestRender, 80 );
		}, { passive: true } );

		if ( win.MutationObserver && doc.body ) {
			new win.MutationObserver( function ( mutations ) {
				var relevant = mutations.some( function ( mutation ) {
					if ( mutation.attributeName === 'data-eas-section-transition' ) {
						return true;
					}
					if ( mutation.type !== 'childList' ) {
						return false;
					}
					if ( mutation.target.closest && mutation.target.closest( '[data-eas-section-transition]' ) ) {
						return true;
					}
					return Array.prototype.some.call( mutation.addedNodes, function ( node ) {
						return node.nodeType === 1 && ( node.matches( '[data-eas-section-transition]' ) || node.querySelector( '[data-eas-section-transition]' ) );
					} );
				} );
				if ( relevant ) {
					win.clearTimeout( mutationTimer );
					mutationTimer = win.setTimeout( function () { scan( doc ); }, 120 );
				}
			} ).observe( doc.body, { childList: true, subtree: true, attributes: true, attributeFilter: [ 'data-eas-section-transition' ] } );
		}

		if ( win.elementorFrontend && win.elementorFrontend.hooks ) {
			win.elementorFrontend.hooks.addAction( 'frontend/element_ready/container', function ( scope ) {
				scan( scope && scope[ 0 ] ? scope[ 0 ] : scope );
			} );
		}
	}

	if ( doc ) {
		if ( doc.readyState === 'loading' ) {
			doc.addEventListener( 'DOMContentLoaded', init, { once: true } );
		} else {
			init();
		}
		win.addEventListener( 'elementor/frontend/init', init, { once: true } );
	}

	return {
		init: init,
		scan: scan,
		destroy: destroyAll,
		computeProgress: computeProgress,
		freeState: freeState,
		clamp: clamp
	};
} ) );

/**
 * Storebox Core — unit gallery: thumbnails swap the large image.
 */
( function () {
	'use strict';

	var core = window.StoreboxCore;
	if ( ! core ) {
		return;
	}

	core.register( 'gallery', function ( root ) {
		var main = root.querySelector( '[data-sb-main]' );
		var buttons = Array.prototype.slice.call( root.querySelectorAll( 'button[data-full]' ) );
		if ( ! main || buttons.length < 2 ) {
			return;
		}

		var timer = null;

		function show( button ) {
			if ( button.getAttribute( 'aria-current' ) === 'true' ) {
				return;
			}

			buttons.forEach( function ( item ) {
				item.setAttribute( 'aria-current', item === button ? 'true' : 'false' );
			} );

			var swap = function () {
				var srcset = button.getAttribute( 'data-srcset' );
				if ( srcset ) {
					main.setAttribute( 'srcset', srcset );
				} else {
					main.removeAttribute( 'srcset' );
				}
				main.src = button.getAttribute( 'data-full' );
				main.alt = button.getAttribute( 'data-alt' ) || '';
			};

			clearTimeout( timer );
			if ( core.reduceMotion ) {
				swap();
				return;
			}

			main.classList.add( 'is-fading' );
			timer = setTimeout( function () {
				var done = function () {
					main.classList.remove( 'is-fading' );
					main.removeEventListener( 'load', done );
					main.removeEventListener( 'error', done );
				};
				main.addEventListener( 'load', done );
				main.addEventListener( 'error', done );
				swap();
				if ( main.complete ) {
					done();
				}
			}, 180 );
		}

		buttons.forEach( function ( button, index ) {
			button.addEventListener( 'click', function () {
				show( button );
			} );

			// Arrow keys move between thumbnails.
			button.addEventListener( 'keydown', function ( event ) {
				var target = null;
				if ( event.key === 'ArrowRight' || event.key === 'ArrowDown' ) {
					target = buttons[ ( index + 1 ) % buttons.length ];
				} else if ( event.key === 'ArrowLeft' || event.key === 'ArrowUp' ) {
					target = buttons[ ( index - 1 + buttons.length ) % buttons.length ];
				}
				if ( target ) {
					event.preventDefault();
					target.focus();
					show( target );
				}
			} );
		} );
	} );
}() );

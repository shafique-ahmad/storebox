/**
 * Storebox Core — size calculator.
 *
 * Needed floor area = total volume ÷ (stack height × usable share); the
 * recommendation is the smallest size that covers it (else the largest).
 */
( function () {
	'use strict';

	var core = window.StoreboxCore;
	if ( ! core ) {
		return;
	}

	function format( template, value ) {
		return String( template ).replace( /%(\d+\$)?[ds]/, value ).replace( /%%/g, '%' );
	}

	core.register( 'calculator', function ( root ) {
		var config;
		try {
			config = JSON.parse( root.getAttribute( 'data-sb-calc' ) || '{}' );
		} catch ( error ) {
			return;
		}

		var sizes = config.sizes || [];
		var rows = Array.prototype.slice.call( root.querySelectorAll( '.sb-calc__item' ) );
		if ( ! sizes.length || ! rows.length ) {
			return;
		}

		var stack = parseFloat( config.stack ) || 2.2;
		var fill = parseFloat( config.fill ) || 0.65;
		var metric = true;
		var qty = rows.map( function () {
			return 0;
		} );
		var el = function ( key ) {
			return root.querySelector( '[data-sb-' + key + ']' );
		};
		var empty = el( 'empty' );
		var result = el( 'result' );

		function render() {
			var volume = 0;
			rows.forEach( function ( row, index ) {
				volume += qty[ index ] * ( parseFloat( row.getAttribute( 'data-v' ) ) || 0 );
			} );

			if ( volume <= 0 ) {
				if ( empty ) {
					empty.hidden = false;
				}
				if ( result ) {
					result.hidden = true;
				}
				return;
			}

			if ( empty ) {
				empty.hidden = true;
			}
			if ( result ) {
				result.hidden = false;
			}

			var need = volume / ( stack * fill );
			var pick = sizes[ sizes.length - 1 ];
			for ( var i = 0; i < sizes.length; i++ ) {
				if ( sizes[ i ].m >= need ) {
					pick = sizes[ i ];
					break;
				}
			}

			var pct = Math.max( 1, Math.min( 100, Math.round( need / pick.m * 100 ) ) );
			var alt = metric ? pick.ftLabel : pick.mLabel;
			if ( pick.ref ) {
				alt += ' · ' + format( config.about || 'about %s', pick.ref );
			}

			var size = el( 'size' );
			var altEl = el( 'alt' );
			var price = el( 'price' );
			var bar = el( 'bar' );
			var fillEl = el( 'fill' );

			if ( size ) {
				size.textContent = metric ? pick.mLabel : pick.ftLabel;
			}
			if ( altEl ) {
				altEl.textContent = alt;
			}
			if ( price ) {
				price.textContent = pick.price || '';
			}
			if ( bar ) {
				bar.style.width = pct + '%';
			}
			if ( fillEl ) {
				fillEl.textContent = format( config.fillText || core.i18n.fillText || '%d%%', pct );
			}
		}

		rows.forEach( function ( row, index ) {
			var output = row.querySelector( '.sb-calc__qty' );
			Array.prototype.forEach.call( row.querySelectorAll( 'button[data-d]' ), function ( button ) {
				button.addEventListener( 'click', function () {
					qty[ index ] = Math.max( 0, Math.min( 999, qty[ index ] + ( parseInt( button.getAttribute( 'data-d' ), 10 ) || 0 ) ) );
					if ( output ) {
						output.textContent = qty[ index ];
					}
					row.classList.toggle( 'is-on', qty[ index ] > 0 );
					render();
				} );
			} );
		} );

		var toggles = Array.prototype.slice.call( root.querySelectorAll( 'button[data-unit]' ) );
		toggles.forEach( function ( toggle ) {
			toggle.addEventListener( 'click', function () {
				metric = toggle.getAttribute( 'data-unit' ) !== 'ft';
				toggles.forEach( function ( other ) {
					other.setAttribute( 'aria-pressed', other === toggle ? 'true' : 'false' );
				} );
				render();
			} );
		} );

		render();
	} );
}() );

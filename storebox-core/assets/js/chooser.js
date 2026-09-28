/**
 * Storebox Core — to-scale size chooser: selecting a box updates the details.
 */
( function () {
	'use strict';

	var core = window.StoreboxCore;
	if ( ! core ) {
		return;
	}

	var FIELDS = [ 'size', 'alt', 'dim', 'fits', 'price' ];

	core.register( 'chooser', function ( root ) {
		var items = Array.prototype.slice.call( root.querySelectorAll( '.sb-chooser__item' ) );
		if ( ! items.length ) {
			return;
		}

		function select( item ) {
			items.forEach( function ( other ) {
				var active = other === item;
				other.classList.toggle( 'is-active', active );
				other.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
			} );

			FIELDS.forEach( function ( key ) {
				Array.prototype.forEach.call( root.querySelectorAll( '[data-sb-d="' + key + '"]' ), function ( el ) {
					el.textContent = item.getAttribute( 'data-' + key ) || '';
				} );
			} );
		}

		items.forEach( function ( item ) {
			item.addEventListener( 'click', function () {
				select( item );
			} );
		} );
	} );
}() );

/**
 * Storebox Core — photo gallery field (media library picker, sortable).
 */
( function ( $ ) {
	'use strict';

	var l10n = window.storeboxCoreAdmin || {};

	$( '[data-sb-gallery]' ).each( function () {
		var $box = $( this );
		var $list = $box.find( '[data-sb-gallery-list]' );
		var $input = $box.find( '[data-sb-gallery-input]' );
		var frame = null;

		function sync() {
			var ids = $list.children( 'li' ).map( function () {
				return $( this ).data( 'id' );
			} ).get();
			$input.val( ids.join( ',' ) );
		}

		function addItem( attachment ) {
			var sizes = attachment.sizes || {};
			var url = sizes.thumbnail ? sizes.thumbnail.url : attachment.url;
			var $li = $( '<li />' ).attr( 'data-id', attachment.id );
			$( '<img />' ).attr( { src: url, alt: '' } ).appendTo( $li );
			$( '<button type="button" class="sb-admin-gallery__remove">&times;</button>' )
				.attr( 'aria-label', l10n.remove || 'Remove photo' )
				.appendTo( $li );
			$list.append( $li );
		}

		$list.sortable( { update: sync, tolerance: 'pointer' } );

		$list.on( 'click', '.sb-admin-gallery__remove', function () {
			$( this ).closest( 'li' ).remove();
			sync();
		} );

		$box.on( 'click', '[data-sb-gallery-add]', function ( event ) {
			event.preventDefault();

			if ( ! frame ) {
				frame = wp.media( {
					title: l10n.title || 'Choose photos',
					button: { text: l10n.button || 'Add to gallery' },
					library: { type: 'image' },
					multiple: 'add'
				} );

				frame.on( 'select', function () {
					var existing = $input.val() ? $input.val().split( ',' ).map( Number ) : [];
					frame.state().get( 'selection' ).each( function ( model ) {
						var attachment = model.toJSON();
						if ( existing.indexOf( attachment.id ) === -1 ) {
							addItem( attachment );
						}
					} );
					sync();
				} );
			}

			frame.open();
		} );
	} );
}( jQuery ) );

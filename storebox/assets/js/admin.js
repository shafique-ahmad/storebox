/**
 * Storebox setup screen: install / activate plugins and dismiss the notice.
 */
( function () {
	'use strict';

	var cfg = window.storeboxAdmin;
	if ( ! cfg ) {
		return;
	}

	function post( action, data ) {
		var body = new window.FormData();
		body.append( 'action', action );
		Object.keys( data ).forEach( function ( key ) {
			body.append( key, data[ key ] );
		} );

		return window.fetch( cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body } )
			.then( function ( response ) {
				return response.json();
			} );
	}

	function fail( button, message ) {
		button.disabled = false;
		button.textContent = button.getAttribute( 'data-label' );
		var note = document.createElement( 'p' );
		note.className = 'sb-admin__error';
		note.setAttribute( 'role', 'alert' );
		note.textContent = typeof message === 'string' && message ? message : cfg.i18n.failed;
		button.parentNode.appendChild( note );
	}

	function run( button, action ) {
		var slug = button.getAttribute( 'data-sb-plugin' );

		button.disabled = true;
		button.textContent = action === 'activate' ? cfg.i18n.activating : button.getAttribute( 'data-sb-busy' );

		return post( 'storebox_' + action + '_plugin', { slug: slug, nonce: cfg.nonce } )
			.then( function ( res ) {
				if ( ! res || ! res.success ) {
					throw new Error( res && res.data && res.data.message ? res.data.message : '' );
				}
				if ( action === 'install' && button.getAttribute( 'data-sb-activate-after' ) === '1' ) {
					return run( button, 'activate' );
				}
				button.textContent = cfg.i18n.done;
				window.location.reload();
			} )
			.catch( function ( err ) {
				fail( button, err && err.message );
			} );
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '[data-sb-plugin]' );
		if ( ! button || button.disabled ) {
			return;
		}
		event.preventDefault();
		if ( ! button.getAttribute( 'data-label' ) ) {
			button.setAttribute( 'data-label', button.textContent.trim() );
		}
		var old = button.parentNode.querySelector( '.sb-admin__error' );
		if ( old ) {
			old.remove();
		}
		run( button, button.getAttribute( 'data-sb-action' ) );
	} );

	// Persist dismissal of the setup notice.
	document.addEventListener( 'click', function ( event ) {
		if ( ! event.target.closest( '[data-sb-notice] .notice-dismiss' ) ) {
			return;
		}
		post( 'storebox_dismiss_notice', { nonce: cfg.noticeNonce } );
	} );
}() );

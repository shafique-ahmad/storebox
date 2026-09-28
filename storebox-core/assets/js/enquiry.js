/**
 * Storebox Core — enquiry form: submits with fetch and shows the result in
 * place. Without JavaScript the form posts to admin-post.php instead.
 */
( function () {
	'use strict';

	var core = window.StoreboxCore;
	if ( ! core ) {
		return;
	}

	function request( url, options ) {
		return window.fetch( url, options ).then( function ( response ) {
			return response.json().catch( function () {
				return { success: false };
			} );
		} );
	}

	core.register( 'enquiry', function ( form ) {
		if ( ! window.fetch || ! window.FormData || ! core.ajaxUrl ) {
			return;
		}

		var wrap = form.closest( '.sb-enquiry' ) || form;
		var submit = form.querySelector( '.sb-form__submit' );
		var ok = form.querySelector( '.sb-form__ok' );
		var fail = form.querySelector( '.sb-form__error' );
		var label = submit ? submit.textContent : '';
		var busy = false;

		function done( success, message ) {
			busy = false;
			if ( submit ) {
				submit.disabled = success;
				submit.removeAttribute( 'aria-busy' );
				submit.textContent = label;
			}

			if ( success ) {
				wrap.classList.add( 'is-sent' );
				if ( fail ) {
					fail.hidden = true;
				}
				if ( ok ) {
					ok.hidden = false;
					ok.focus();
				}
				form.reset();
				return;
			}

			if ( ok ) {
				ok.hidden = true;
			}
			if ( fail ) {
				fail.textContent = message || core.i18n.error || '';
				fail.hidden = false;
			}
		}

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();
			if ( busy ) {
				return;
			}
			busy = true;

			if ( submit ) {
				submit.disabled = true;
				submit.setAttribute( 'aria-busy', 'true' );
				if ( core.i18n.sending ) {
					submit.textContent = core.i18n.sending;
				}
			}
			if ( fail ) {
				fail.hidden = true;
			}

			var separator = core.ajaxUrl.indexOf( '?' ) > -1 ? '&' : '?';

			// Pages may be cached: fetch a fresh nonce first.
			request( core.ajaxUrl + separator + 'action=storebox_enquiry_nonce', {
				credentials: 'same-origin',
				cache: 'no-store'
			} ).then( function ( response ) {
				var nonce = form.querySelector( '[name="sb_nonce"]' );
				if ( response && response.success && response.data && response.data.nonce && nonce ) {
					nonce.value = response.data.nonce;
				}

				var data = new window.FormData( form );
				data.set( 'action', 'storebox_enquiry' );
				data.set( 'sb_page', window.location.href.split( '#' )[ 0 ] );

				return request( core.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					body: data
				} );
			} ).then( function ( response ) {
				if ( response && response.success ) {
					done( true );
				} else {
					done( false, response && response.data && response.data.message );
				}
			} ).catch( function () {
				done( false );
			} );
		} );
	} );
}() );

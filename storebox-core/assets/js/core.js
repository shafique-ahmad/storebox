/**
 * Storebox Core — component registry.
 *
 * Each component script calls StoreboxCore.register( name, init ). init runs
 * once per element with data-sb-component="name": on page load, and again for
 * elements Elementor renders or re-renders in the editor.
 */
( function () {
	'use strict';

	var registry = {};
	var cfg = window.storeboxCore || {};

	function ready( fn ) {
		if ( document.readyState !== 'loading' ) {
			fn();
		} else {
			document.addEventListener( 'DOMContentLoaded', fn );
		}
	}

	function initElement( el ) {
		var name = el.getAttribute( 'data-sb-component' );
		if ( ! name || ! registry[ name ] || el.sbInitialised ) {
			return;
		}
		el.sbInitialised = true;
		try {
			registry[ name ]( el );
		} catch ( error ) {
			if ( window.console ) {
				window.console.error( '[Storebox] ' + name, error );
			}
		}
	}

	function scan( root ) {
		root = root || document;
		if ( root.matches && root.matches( '[data-sb-component]' ) ) {
			initElement( root );
		}
		Array.prototype.forEach.call( root.querySelectorAll( '[data-sb-component]' ), initElement );
	}

	window.StoreboxCore = {
		i18n: cfg.i18n || {},
		ajaxUrl: cfg.ajaxUrl || '',
		timezone: cfg.timezone || '',
		assets: cfg.assets || {},
		reduceMotion: !! ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ),
		isEditor: function () {
			return document.body.classList.contains( 'elementor-editor-active' ) || !! ( window.elementorFrontend && window.elementorFrontend.isEditMode && window.elementorFrontend.isEditMode() );
		},
		register: function ( name, init ) {
			registry[ name ] = init;
			ready( function () {
				Array.prototype.forEach.call( document.querySelectorAll( '[data-sb-component="' + name + '"]' ), initElement );
			} );
		},
		scan: scan
	};

	// Elementor: initialise components inside elements it renders.
	function hookElementor() {
		if ( ! window.elementorFrontend || ! window.elementorFrontend.hooks ) {
			return false;
		}
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/global', function ( $scope ) {
			if ( $scope && $scope[ 0 ] ) {
				scan( $scope[ 0 ] );
			}
		} );
		return true;
	}

	if ( ! hookElementor() && window.jQuery ) {
		window.jQuery( window ).on( 'elementor/frontend/init', hookElementor );
	}
}() );

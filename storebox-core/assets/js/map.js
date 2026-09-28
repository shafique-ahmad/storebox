/**
 * Storebox Core — location map.
 *
 * The illustrative map is plain HTML/CSS. In "live" mode this script loads
 * Leaflet (bundled with the plugin) when the map scrolls into view — or, when
 * consent is required, after the visitor clicks "Load interactive map" — and
 * draws the same pins on a tile map.
 */
( function () {
	'use strict';

	var core = window.StoreboxCore;
	if ( ! core ) {
		return;
	}

	var CONSENT_KEY = 'storebox-map-consent';
	var loading = null;

	function loadLeaflet() {
		if ( window.L && window.L.map ) {
			return Promise.resolve( window.L );
		}
		if ( loading ) {
			return loading;
		}

		var assets = core.assets || {};
		if ( ! assets.leafletJs ) {
			return Promise.reject( new Error( 'Leaflet URL missing' ) );
		}

		var css = new Promise( function ( resolve ) {
			if ( ! assets.leafletCss || document.querySelector( 'link[data-sb-leaflet]' ) ) {
				resolve();
				return;
			}
			var link = document.createElement( 'link' );
			link.rel = 'stylesheet';
			link.href = assets.leafletCss;
			link.setAttribute( 'data-sb-leaflet', '' );
			link.onload = resolve;
			link.onerror = resolve;
			document.head.appendChild( link );
		} );

		var js = new Promise( function ( resolve, reject ) {
			var script = document.createElement( 'script' );
			script.src = assets.leafletJs;
			script.async = true;
			script.onload = function () {
				if ( window.L && window.L.map ) {
					resolve( window.L );
				} else {
					reject( new Error( 'Leaflet did not load' ) );
				}
			};
			script.onerror = reject;
			document.head.appendChild( script );
		} );

		loading = Promise.all( [ js, css ] ).then( function ( values ) {
			return values[ 0 ];
		} );
		loading.catch( function () {
			loading = null;
		} );

		return loading;
	}

	function pin( point ) {
		var link = document.createElement( 'a' );
		var label = document.createElement( 'span' );
		var tip = document.createElement( 'i' );

		link.className = 'sb-map__pin';
		link.href = point.url;
		label.textContent = point.name;
		tip.setAttribute( 'aria-hidden', 'true' );
		link.appendChild( label );
		link.appendChild( tip );

		return link;
	}

	function build( L, root, config ) {
		var canvas = root.querySelector( '.sb-map__canvas' );
		if ( ! canvas || root.classList.contains( 'is-live' ) ) {
			return;
		}

		var holder = document.createElement( 'div' );
		holder.className = 'sb-map__leaflet';
		canvas.insertBefore( holder, canvas.firstChild );

		var map = L.map( holder, {
			scrollWheelZoom: false,
			keyboard: false,
			dragging: ! L.Browser.mobile,
			zoomSnap: 0.5
		} );

		var layer = L.tileLayer( config.tiles, {
			maxZoom: 19,
			attribution: config.attribution || ''
		} ).addTo( map );

		// If no tile loads (blocked network, privacy tools), go back to the
		// illustrative map rather than leaving an empty grey box.
		var loaded = 0;
		var failed = 0;
		var alive = true;
		var observer = null;
		var revert = function () {
			if ( loaded || ! alive ) {
				return;
			}
			alive = false;
			if ( observer ) {
				observer.disconnect();
			}
			// Leave Leaflet's event handler before tearing the map down.
			window.setTimeout( function () {
				map.remove();
				if ( holder.parentNode ) {
					holder.parentNode.removeChild( holder );
				}
				root.classList.remove( 'is-live' );
			}, 0 );
		};
		layer.on( 'tileload', function () {
			loaded++;
		} );
		layer.on( 'tileerror', function () {
			failed++;
			if ( failed >= 4 ) {
				revert();
			}
		} );
		window.setTimeout( revert, 12000 );

		var bounds = [];
		config.points.forEach( function ( point ) {
			var lat = parseFloat( point.lat );
			var lng = parseFloat( point.lng );
			if ( isNaN( lat ) || isNaN( lng ) ) {
				return;
			}
			L.marker( [ lat, lng ], {
				icon: L.divIcon( { className: 'sb-map__marker', html: pin( point ), iconSize: [ 0, 0 ] } ),
				keyboard: false,
				interactive: false
			} ).addTo( map );
			bounds.push( [ lat, lng ] );
		} );

		var zoom = parseInt( config.zoom, 10 ) || 12;
		if ( bounds.length === 1 ) {
			map.setView( bounds[ 0 ], zoom );
		} else if ( bounds.length > 1 ) {
			map.fitBounds( bounds, { padding: [ 70, 70 ], maxZoom: zoom } );
		}

		// Wheel zoom only once the visitor has engaged with the map.
		map.once( 'click', function () {
			map.scrollWheelZoom.enable();
		} );

		root.classList.add( 'is-live' );

		var overlay = root.querySelector( '.sb-map__consent' );
		if ( overlay ) {
			overlay.parentNode.removeChild( overlay );
		}

		// Elementor may resize the column after the map is drawn.
		if ( typeof window.ResizeObserver === 'function' ) {
			observer = new window.ResizeObserver( function () {
				if ( alive ) {
					map.invalidateSize();
				}
			} );
			observer.observe( holder );
		}
	}

	function remembered() {
		try {
			return window.localStorage.getItem( CONSENT_KEY ) === '1';
		} catch ( error ) {
			return false;
		}
	}

	function remember() {
		try {
			window.localStorage.setItem( CONSENT_KEY, '1' );
		} catch ( error ) {
			// Storage unavailable: ask again next time.
		}
	}

	core.register( 'map', function ( root ) {
		var config;
		try {
			config = JSON.parse( root.getAttribute( 'data-sb-map' ) || '{}' );
		} catch ( error ) {
			return;
		}

		if ( config.mode !== 'live' || ! config.tiles || ! config.points || ! config.points.length ) {
			return;
		}

		var started = false;
		var start = function () {
			if ( started ) {
				return;
			}
			started = true;
			loadLeaflet().then( function ( L ) {
				build( L, root, config );
			} ).catch( function () {
				// Leaflet unavailable: the illustrative map stays in place.
				started = false;
			} );
		};

		var button = root.querySelector( '[data-sb-map-load]' );
		if ( config.consent && ! remembered() ) {
			if ( button ) {
				button.addEventListener( 'click', function () {
					button.disabled = true;
					remember();
					start();
				} );
			}
			return;
		}

		if ( typeof window.IntersectionObserver === 'function' ) {
			var observer = new window.IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						observer.disconnect();
						start();
					}
				} );
			}, { rootMargin: '200px' } );
			observer.observe( root );
		} else {
			start();
		}
	} );
}() );

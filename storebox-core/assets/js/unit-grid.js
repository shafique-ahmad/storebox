/**
 * Storebox Core — unit grid: live filters and sorting, and the rail slider.
 */
( function () {
	'use strict';

	var core = window.StoreboxCore;
	if ( ! core ) {
		return;
	}

	function initFilters( root ) {
		var form = root.querySelector( '[data-sb-filters]' );
		var items = root.querySelector( '[data-sb-items]' );
		if ( ! form || ! items ) {
			return;
		}

		var cards = Array.prototype.slice.call( items.querySelectorAll( '.sb-unit-card' ) );
		var count = root.querySelector( '[data-sb-count]' );
		var empty = root.querySelector( '[data-sb-empty]' );
		var field = function ( key ) {
			return form.querySelector( '[data-sb-filter="' + key + '"]' );
		};
		var params = { size: 'unit_size', loc: 'unit_location', type: 'unit_type', sort: 'unit_sort' };

		function apply( updateUrl ) {
			var size = field( 'size' );
			var loc = field( 'loc' );
			var type = field( 'type' );
			var sort = field( 'sort' );
			var avail = field( 'avail' );
			var shown = 0;

			cards.forEach( function ( card ) {
				var ok = ( ! size || ! size.value || card.getAttribute( 'data-size' ) === size.value ) &&
					( ! loc || ! loc.value || card.getAttribute( 'data-loc' ) === loc.value ) &&
					( ! type || ! type.value || card.getAttribute( 'data-type' ) === type.value ) &&
					( ! avail || ! avail.checked || parseInt( card.getAttribute( 'data-avail' ), 10 ) > 0 );
				card.hidden = ! ok;
				if ( ok ) {
					shown++;
				}
			} );

			if ( sort && sort.value ) {
				var key = sort.value.split( '-' );
				cards.slice().sort( function ( a, b ) {
					var va = parseFloat( a.getAttribute( 'data-' + key[ 0 ] ) ) || 0;
					var vb = parseFloat( b.getAttribute( 'data-' + key[ 0 ] ) ) || 0;
					return key[ 1 ] === 'desc' ? vb - va : va - vb;
				} ).forEach( function ( card ) {
					items.appendChild( card );
				} );
			}

			if ( count ) {
				count.textContent = shown;
			}
			if ( empty ) {
				empty.hidden = shown !== 0;
			}

			if ( updateUrl && window.history && window.history.replaceState && window.URL && ! core.isEditor() ) {
				var url = new window.URL( window.location.href );
				Object.keys( params ).forEach( function ( k ) {
					var el = field( k );
					if ( el && el.value && ! ( k === 'sort' && el.value === 'area-asc' ) ) {
						url.searchParams.set( params[ k ], el.value );
					} else {
						url.searchParams.delete( params[ k ] );
					}
				} );
				if ( avail && avail.checked ) {
					url.searchParams.set( 'unit_available', '1' );
				} else {
					url.searchParams.delete( 'unit_available' );
				}
				window.history.replaceState( null, '', url.toString() );
			}
		}

		form.addEventListener( 'change', function () {
			apply( true );
		} );
		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();
			apply( true );
		} );

		Array.prototype.forEach.call( root.querySelectorAll( '[data-sb-reset]' ), function ( button ) {
			button.addEventListener( 'click', function () {
				[ 'size', 'loc', 'type' ].forEach( function ( k ) {
					var el = field( k );
					if ( el ) {
						el.value = '';
					}
				} );
				var sort = field( 'sort' );
				if ( sort ) {
					sort.value = 'area-asc';
				}
				var avail = field( 'avail' );
				if ( avail ) {
					avail.checked = false;
				}
				apply( true );
			} );
		} );
	}

	function initRail( root ) {
		var rail = root.querySelector( '.sb-rail' );
		if ( ! rail ) {
			return;
		}

		var prev = root.querySelector( '[data-sb-rail-prev]' );
		var next = root.querySelector( '[data-sb-rail-next]' );
		var bar = root.querySelector( '[data-sb-rail-bar]' );
		var delay = parseInt( root.getAttribute( 'data-autoplay' ), 10 ) || 0;
		var timer = null;
		var paused = false;
		var resume = null;
		var gap = function () {
			return parseFloat( window.getComputedStyle( rail ).columnGap ) || 22;
		};

		function step() {
			var card = rail.querySelector( '.sb-unit-card' );
			return card ? card.getBoundingClientRect().width + gap() : rail.clientWidth * 0.8;
		}

		function max() {
			return rail.scrollWidth - rail.clientWidth;
		}

		function sync() {
			var m = max();
			var visible = Math.min( 1, rail.clientWidth / ( rail.scrollWidth || 1 ) );
			var progress = m > 0 ? rail.scrollLeft / m : 0;
			if ( bar ) {
				bar.style.width = ( visible * 100 ) + '%';
				bar.style.transform = 'translateX(' + ( progress * ( 100 / visible - 100 ) ) + '%)';
			}
			if ( prev && next ) {
				var scrollable = m > 4;
				prev.disabled = ! scrollable;
				next.disabled = ! scrollable;
			}
		}

		function go( direction ) {
			var m = max();
			if ( direction > 0 && rail.scrollLeft >= m - 8 ) {
				rail.scrollTo( { left: 0, behavior: 'smooth' } );
			} else if ( direction < 0 && rail.scrollLeft <= 8 ) {
				rail.scrollTo( { left: m, behavior: 'smooth' } );
			} else {
				rail.scrollBy( { left: direction * step(), behavior: 'smooth' } );
			}
		}

		function nudge() {
			paused = true;
			clearTimeout( resume );
			resume = setTimeout( function () {
				paused = false;
			}, 7000 );
		}

		function stop() {
			if ( timer ) {
				clearInterval( timer );
				timer = null;
			}
		}

		function start() {
			if ( ! delay || core.reduceMotion || core.isEditor() ) {
				return;
			}
			stop();
			timer = setInterval( function () {
				if ( ! paused && ! document.hidden ) {
					go( 1 );
				}
			}, delay );
		}

		if ( prev ) {
			prev.addEventListener( 'click', function () {
				nudge();
				go( -1 );
			} );
		}
		if ( next ) {
			next.addEventListener( 'click', function () {
				nudge();
				go( 1 );
			} );
		}

		rail.addEventListener( 'scroll', sync, { passive: true } );
		window.addEventListener( 'resize', sync );
		rail.addEventListener( 'mouseenter', function () {
			paused = true;
		} );
		rail.addEventListener( 'mouseleave', function () {
			paused = false;
		} );
		root.addEventListener( 'focusin', function () {
			paused = true;
		} );
		root.addEventListener( 'focusout', function () {
			paused = false;
		} );
		rail.addEventListener( 'pointerdown', nudge );
		rail.addEventListener( 'wheel', nudge, { passive: true } );
		rail.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'ArrowRight' ) {
				event.preventDefault();
				nudge();
				go( 1 );
			} else if ( event.key === 'ArrowLeft' ) {
				event.preventDefault();
				nudge();
				go( -1 );
			}
		} );

		if ( typeof window.IntersectionObserver === 'function' ) {
			new window.IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						start();
					} else {
						stop();
					}
				} );
			}, { threshold: 0.2 } ).observe( rail );
		} else {
			start();
		}

		sync();
	}

	core.register( 'unit-grid', function ( root ) {
		initFilters( root );
		initRail( root );
	} );
}() );

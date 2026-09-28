/**
 * Storebox theme script: header state, mobile menu, submenus and reveal.
 *
 * Vanilla JavaScript, no dependencies. Every part checks for its markup first.
 */
( function () {
	'use strict';

	var doc = document;
	var l10n = window.storeboxL10n || {};
	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/* ------------------------------------------------------------------
	 * Header: the overlay layout turns solid after 40px of scrolling.
	 * ------------------------------------------------------------------ */
	var header = doc.getElementById( 'sb-header' );
	var body = doc.body;

	if ( header && header.getAttribute( 'data-sb-header' ) === 'overlay' && body.classList.contains( 'sb-header-transparent' ) ) {
		var ticking = false;
		var update = function () {
			header.classList.toggle( 'is-solid', window.scrollY > 40 || header.classList.contains( 'is-menu-open' ) );
			ticking = false;
		};
		window.addEventListener( 'scroll', function () {
			if ( ! ticking ) {
				ticking = true;
				window.requestAnimationFrame( update );
			}
		}, { passive: true } );
		update();
	}

	/* ------------------------------------------------------------------
	 * Elementor Pro header with the sb-e-header-overlay class: solid after
	 * 40px, or always on pages set to a solid header.
	 * ------------------------------------------------------------------ */
	var overlays = doc.querySelectorAll( '.sb-e-header-overlay' );

	if ( overlays.length ) {
		var alwaysSolid = body.classList.contains( 'sb-header-solid' );
		var overlayTicking = false;
		var overlayUpdate = function () {
			Array.prototype.forEach.call( overlays, function ( el ) {
				el.classList.toggle( 'is-solid', alwaysSolid || window.scrollY > 40 || el.classList.contains( 'elementor-nav-menu--toggle-open' ) );
			} );
			overlayTicking = false;
		};
		window.addEventListener( 'scroll', function () {
			if ( ! overlayTicking ) {
				overlayTicking = true;
				window.requestAnimationFrame( overlayUpdate );
			}
		}, { passive: true } );
		overlayUpdate();
	}

	/* ------------------------------------------------------------------
	 * Mobile menu.
	 * ------------------------------------------------------------------ */
	var burger = header ? header.querySelector( '.sb-burger' ) : null;
	var nav = doc.getElementById( 'sb-nav' );

	if ( burger && nav ) {
		var label = burger.querySelector( '.screen-reader-text' );
		var breakpoint = header.getAttribute( 'data-sb-header' ) === 'classic' ? 1080 : 1000;
		var desktop = window.matchMedia( '(min-width: ' + ( breakpoint + 1 ) + 'px)' );

		var setOpen = function ( open, returnFocus ) {
			nav.classList.toggle( 'is-open', open );
			header.classList.toggle( 'is-menu-open', open );
			burger.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			if ( label ) {
				label.textContent = open ? ( l10n.closeMenu || 'Close menu' ) : ( l10n.openMenu || 'Open menu' );
			}
			if ( header.getAttribute( 'data-sb-header' ) === 'overlay' ) {
				header.classList.toggle( 'is-solid', open || window.scrollY > 40 || body.classList.contains( 'sb-header-solid' ) );
			}
			if ( ! open && returnFocus ) {
				burger.focus();
			}
		};

		burger.addEventListener( 'click', function () {
			setOpen( ! nav.classList.contains( 'is-open' ), false );
		} );

		nav.addEventListener( 'click', function ( event ) {
			var link = event.target.closest( 'a' );
			if ( link && nav.classList.contains( 'is-open' ) && link.getAttribute( 'href' ) && link.getAttribute( 'href' ).charAt( 0 ) === '#' ) {
				setOpen( false, false );
			}
		} );

		doc.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Escape' && nav.classList.contains( 'is-open' ) ) {
				setOpen( false, true );
			}
		} );

		doc.addEventListener( 'click', function ( event ) {
			if ( nav.classList.contains( 'is-open' ) && ! header.contains( event.target ) ) {
				setOpen( false, false );
			}
		} );

		var onBreakpoint = function ( mq ) {
			if ( mq.matches && nav.classList.contains( 'is-open' ) ) {
				setOpen( false, false );
			}
		};
		if ( desktop.addEventListener ) {
			desktop.addEventListener( 'change', onBreakpoint );
		} else if ( desktop.addListener ) {
			desktop.addListener( onBreakpoint );
		}
	}

	/* ------------------------------------------------------------------
	 * Submenu toggles.
	 * ------------------------------------------------------------------ */
	var toggles = doc.querySelectorAll( '.sb-submenu-toggle' );

	Array.prototype.forEach.call( toggles, function ( toggle ) {
		var item = toggle.parentNode;

		toggle.addEventListener( 'click', function () {
			var open = ! item.classList.contains( 'is-open' );

			// Close sibling submenus at the same level.
			Array.prototype.forEach.call( item.parentNode.children, function ( sibling ) {
				if ( sibling !== item && sibling.classList.contains( 'is-open' ) ) {
					sibling.classList.remove( 'is-open' );
					var t = sibling.querySelector( ':scope > .sb-submenu-toggle' );
					if ( t ) {
						t.setAttribute( 'aria-expanded', 'false' );
					}
				}
			} );

			item.classList.toggle( 'is-open', open );
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );

		item.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Escape' && item.classList.contains( 'is-open' ) ) {
				event.stopPropagation();
				item.classList.remove( 'is-open' );
				toggle.setAttribute( 'aria-expanded', 'false' );
				toggle.focus();
			}
		} );
	} );

	// Close open desktop submenus when clicking elsewhere.
	doc.addEventListener( 'click', function ( event ) {
		Array.prototype.forEach.call( doc.querySelectorAll( '.sb-menu li.is-open' ), function ( item ) {
			if ( ! item.contains( event.target ) ) {
				item.classList.remove( 'is-open' );
				var t = item.querySelector( ':scope > .sb-submenu-toggle' );
				if ( t ) {
					t.setAttribute( 'aria-expanded', 'false' );
				}
			}
		} );
	} );

	/* ------------------------------------------------------------------
	 * Reveal on scroll (theme templates).
	 * ------------------------------------------------------------------ */
	var revealables = doc.querySelectorAll( '.sb-rv' );

	if ( revealables.length ) {
		var show = function ( el ) {
			el.classList.add( 'is-in' );
		};

		if ( reduceMotion || ! body.classList.contains( 'sb-reveal' ) || typeof window.IntersectionObserver !== 'function' ) {
			Array.prototype.forEach.call( revealables, show );
		} else {
			var observer = new window.IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( ! entry.isIntersecting ) {
						return;
					}
					var siblings = Array.prototype.filter.call( entry.target.parentNode.children, function ( child ) {
						return child.classList.contains( 'sb-rv' );
					} );
					entry.target.style.transitionDelay = ( Math.max( 0, siblings.indexOf( entry.target ) ) * 0.08 ) + 's';
					show( entry.target );
					observer.unobserve( entry.target );
				} );
			}, { rootMargin: '0px 0px -8% 0px', threshold: 0.06 } );

			Array.prototype.forEach.call( revealables, function ( el ) {
				observer.observe( el );
			} );
		}
	}
}() );

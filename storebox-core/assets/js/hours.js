/**
 * Storebox Core — opening hours: highlights today's row.
 *
 * Runs in the browser so cached pages stay correct; "today" is taken in the
 * site's time zone (Settings → General), falling back to the visitor's.
 */
( function () {
	'use strict';

	var core = window.StoreboxCore;
	if ( ! core ) {
		return;
	}

	var DAYS = [ 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' ];

	function siteDay() {
		var now = new Date();
		var zone = core.timezone || '';
		var offset = /^([+-])(\d{2}):(\d{2})$/.exec( zone );

		if ( offset ) {
			var minutes = ( parseInt( offset[ 2 ], 10 ) * 60 + parseInt( offset[ 3 ], 10 ) ) * ( offset[ 1 ] === '-' ? -1 : 1 );
			return new Date( now.getTime() + minutes * 60000 ).getUTCDay();
		}

		if ( zone && window.Intl && window.Intl.DateTimeFormat ) {
			try {
				var name = new window.Intl.DateTimeFormat( 'en-US', { weekday: 'short', timeZone: zone } ).format( now );
				var index = DAYS.indexOf( name );
				if ( index > -1 ) {
					return index;
				}
			} catch ( error ) {
				// Unknown zone: use the visitor's day.
			}
		}

		return now.getDay();
	}

	core.register( 'hours', function ( root ) {
		if ( ! root.hasAttribute( 'data-today' ) ) {
			return;
		}

		var row = root.querySelector( 'tr[data-day="' + siteDay() + '"]' );
		if ( row ) {
			row.classList.add( 'is-today' );
			row.setAttribute( 'aria-current', 'date' );
		}
	} );
}() );

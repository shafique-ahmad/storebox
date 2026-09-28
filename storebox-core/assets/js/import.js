/**
 * Storebox Core — demo importer screen.
 *
 * Runs the import as a series of short AJAX requests (one per step or batch),
 * so it works on hosts with low time limits; "Retry" resumes a failed request.
 */
( function () {
	'use strict';

	var cfg = window.storeboxImport;
	if ( ! cfg || ! window.fetch || ! window.FormData ) {
		return;
	}

	var i18n = cfg.i18n || {};
	var form = document.getElementById( 'sb-import-form' );
	var panel = document.getElementById( 'sb-import-progress' );

	function post( data ) {
		var body = new window.FormData();
		body.append( 'nonce', cfg.nonce );
		Object.keys( data ).forEach( function ( key ) {
			body.append( key, data[ key ] );
		} );

		return window.fetch( cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: body
		} ).then( function ( response ) {
			return response.text().then( function ( text ) {
				var json = null;
				try {
					json = JSON.parse( text );
				} catch ( error ) {
					json = null;
				}
				if ( ! json ) {
					throw new Error( ( i18n.serverError || 'HTTP %s' ).replace( '%s', response.status ) );
				}
				if ( ! json.success ) {
					throw new Error( ( json.data && json.data.message ) || ( i18n.serverError || 'HTTP %s' ).replace( '%s', response.status ) );
				}
				return json.data;
			} );
		}, function () {
			throw new Error( i18n.networkError || 'Network error' );
		} );
	}

	if ( form && panel ) {
		var status = panel.querySelector( '.sb-import__status' );
		var bar = panel.querySelector( '.sb-import__bar span' );
		var log = panel.querySelector( '.sb-import__log' );
		var retry = panel.querySelector( '.sb-import__retry' );
		var done = panel.querySelector( '.sb-import__done' );
		var job = null;

		var say = function ( text, isError ) {
			status.textContent = text;
			status.classList.toggle( 'is-error', !! isError );
		};

		var note = function ( text ) {
			var item = document.createElement( 'li' );
			item.textContent = text;
			log.appendChild( item );
			log.scrollTop = log.scrollHeight;
		};

		var progress = function () {
			var total = 0;
			var complete = 0;
			job.steps.forEach( function ( step, index ) {
				total += step.chunks;
				if ( index < job.index ) {
					complete += step.chunks;
				} else if ( index === job.index ) {
					complete += Math.min( job.chunk, step.chunks );
				}
			} );
			bar.style.width = Math.round( ( total ? complete / total : 0 ) * 100 ) + '%';
		};

		var fields = function () {
			var data = { demo: job.demo };
			Object.keys( job.options ).forEach( function ( key ) {
				data[ 'options[' + key + ']' ] = job.options[ key ] ? '1' : '';
			} );
			return data;
		};

		var next = function () {
			if ( job.index >= job.steps.length ) {
				panel.classList.add( 'is-done' );
				bar.style.width = '100%';
				say( i18n.done || 'Done' );
				done.hidden = false;
				return;
			}

			var step = job.steps[ job.index ];
			say( step.label + '…' );
			progress();

			var data = fields();
			data.action = 'storebox_demo_step';
			data.step = step.id;
			data.chunk = job.chunk;

			post( data ).then( function ( result ) {
				( result.log || [] ).forEach( note );
				if ( result.links && result.links.edit ) {
					var edit = done.querySelector( '[data-link="edit"]' );
					edit.href = result.links.edit;
					edit.hidden = false;
				}
				if ( result.done ) {
					job.index++;
					job.chunk = 0;
				} else {
					job.chunk++;
				}
				next();
			} ).catch( fail );
		};

		var fail = function ( error ) {
			say( ( i18n.failed || 'Error:' ) + ' ' + error.message, true );
			note( error.message );
			retry.hidden = false;
		};

		retry.querySelector( 'button' ).addEventListener( 'click', function () {
			retry.hidden = true;
			if ( job && job.steps ) {
				next();
			} else {
				start();
			}
		} );

		var start = function () {
			var data = fields();
			data.action = 'storebox_demo_plan';
			say( i18n.starting || '…' );
			post( data ).then( function ( result ) {
				job.steps = result.steps || [];
				job.index = 0;
				job.chunk = 0;
				next();
			} ).catch( fail );
		};

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();

			var choice = form.querySelector( 'input[name="demo"]:checked' );
			if ( ! choice ) {
				window.alert( i18n.chooseDemo || '' );
				return;
			}

			var options = {};
			[ 'content', 'templates', 'menus', 'settings', 'replace' ].forEach( function ( key ) {
				var box = form.querySelector( 'input[name="options[' + key + ']"]' );
				options[ key ] = !! ( box && box.checked && ! box.disabled );
			} );

			if ( options.replace && ! window.confirm( i18n.confirmReplace || '' ) ) {
				return;
			}

			job = { demo: choice.value, options: options, steps: null, index: 0, chunk: 0 };

			Array.prototype.forEach.call( form.elements, function ( el ) {
				el.disabled = true;
			} );
			panel.hidden = false;
			panel.classList.remove( 'is-done' );
			log.innerHTML = '';
			done.hidden = true;
			retry.hidden = true;
			bar.style.width = '0';

			start();
		} );
	}

	// Removal.
	var remove = document.getElementById( 'sb-remove-demo' );
	if ( remove ) {
		var media = document.getElementById( 'sb-remove-media' );
		var removeStatus = document.querySelector( '.sb-import__remove-status' );

		var round = function () {
			post( { action: 'storebox_demo_remove', media: media && media.checked ? '1' : '' } ).then( function ( result ) {
				if ( result.log && result.log.length ) {
					removeStatus.textContent = result.log[ result.log.length - 1 ];
				}
				if ( result.done ) {
					removeStatus.textContent = i18n.removed || '';
					window.setTimeout( function () {
						window.location.reload();
					}, 900 );
				} else {
					round();
				}
			} ).catch( function ( error ) {
				removeStatus.textContent = error.message;
				remove.disabled = false;
			} );
		};

		remove.addEventListener( 'click', function () {
			if ( ! window.confirm( i18n.confirmRemove || '' ) ) {
				return;
			}
			remove.disabled = true;
			removeStatus.textContent = i18n.removing || '';
			round();
		} );
	}
}() );

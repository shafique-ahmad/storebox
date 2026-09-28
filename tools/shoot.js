#!/usr/bin/env node
/**
 * Screenshot helper for visual checks.
 *
 * Usage: node tools/shoot.js <out-dir> <width> <name=url> [<name=url> ...]
 * Env: FULL=0 for viewport-only shots, WAIT=ms extra wait, SCROLL=1 to scroll
 * through the page first (triggers lazy images and reveal animations).
 * Reports console errors, page errors and horizontal overflow.
 */
const { chromium } = require( process.env.PW_PATH || '/opt/node22/lib/node_modules/playwright' );
const fs = require( 'fs' );
const path = require( 'path' );

( async () => {
	const [ out, width, ...pairs ] = process.argv.slice( 2 );
	fs.mkdirSync( out, { recursive: true } );
	const browser = await chromium.launch();
	const context = await browser.newContext( { viewport: { width: parseInt( width, 10 ), height: 900 }, deviceScaleFactor: 1 } );

	await require( './pw-routes' )( context );
	const results = [];
	for ( const pair of pairs ) {
		const idx = pair.indexOf( '=' );
		const name = pair.slice( 0, idx );
		const url = pair.slice( idx + 1 );
		const page = await context.newPage();
		const errors = [];
		page.on( 'console', ( msg ) => { if ( msg.type() === 'error' ) errors.push( 'console: ' + msg.text() ); } );
		page.on( 'pageerror', ( err ) => errors.push( 'pageerror: ' + err.message ) );
		page.on( 'requestfailed', ( req ) => { const u = req.url(); if ( ! /pexels|gstatic|googleapis|tile\.openstreetmap|unpkg/.test( u ) ) errors.push( 'requestfailed: ' + u ); } );
		page.on( 'console', () => {} );
		await page.goto( url, { waitUntil: 'networkidle', timeout: 60000 } ).catch( ( e ) => errors.push( 'goto: ' + e.message ) );
		if ( process.env.SCROLL !== '0' ) {
			await page.evaluate( async () => {
				const h = document.documentElement.scrollHeight;
				for ( let y = 0; y < h; y += 400 ) { window.scrollTo( { top: y, behavior: 'instant' } ); await new Promise( ( r ) => setTimeout( r, 80 ) ); }
				window.scrollTo( { top: 0, behavior: 'instant' } );
			} );
		}
		await page.waitForTimeout( parseInt( process.env.WAIT || '1200', 10 ) );
		const overflow = await page.evaluate( () => {
			const docW = document.documentElement.clientWidth;
			const offenders = [];
			document.querySelectorAll( 'body *' ).forEach( ( el ) => {
				const r = el.getBoundingClientRect();
				if ( r.right > docW + 1 && r.width > 0 && getComputedStyle( el ).position !== 'fixed' ) {
					offenders.push( ( el.className && el.className.toString ? el.className.toString().slice( 0, 60 ) : el.tagName ) + ' right=' + Math.round( r.right ) );
				}
			} );
			return { scrollW: document.documentElement.scrollWidth, docW, offenders: offenders.slice( 0, 5 ) };
		} );
		const file = path.join( out, `${ name }-${ width }.png` );
		await page.screenshot( { path: file, fullPage: process.env.FULL !== '0' } );
		results.push( { name, width, file, errors, overflow: overflow.scrollW > overflow.docW ? overflow : null } );
		await page.close();
	}
	await browser.close();
	for ( const r of results ) {
		console.log( `${ r.name }@${ r.width }: ${ r.errors.length ? r.errors.join( ' | ' ) : 'no errors' }${ r.overflow ? ' | OVERFLOW ' + JSON.stringify( r.overflow ) : '' }` );
	}
} )();

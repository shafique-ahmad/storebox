#!/usr/bin/env node
/**
 * Renders the Storebox demo artwork to JPEG.
 *
 * Usage: node tools/demo-images/render.js <out-dir> [scene ...]
 * Without scene names every scene is rendered.
 */
'use strict';

const fs = require( 'fs' );
const path = require( 'path' );
const { chromium } = require( process.env.PW_PATH || '/opt/node22/lib/node_modules/playwright' );
const scenes = require( './scenes' );

( async () => {
	const [ out, ...only ] = process.argv.slice( 2 );
	if ( ! out ) {
		console.error( 'Usage: render.js <out-dir> [scene ...]' );
		process.exit( 1 );
	}
	fs.mkdirSync( out, { recursive: true } );

	const browser = await chromium.launch();
	const page = await browser.newPage( { viewport: { width: 1920, height: 1280 }, deviceScaleFactor: 1 } );

	for ( const [ name, scene ] of Object.entries( scenes ) ) {
		if ( only.length && ! only.includes( name ) ) {
			continue;
		}
		const markup = scene.draw();
		if ( process.env.SVG ) {
			fs.writeFileSync( path.join( out, name + '.svg' ), markup );
		}
		await page.setContent( `<!doctype html><html><body style="margin:0;background:#fff">${ markup }</body></html>` );
		await page.screenshot( { path: path.join( out, name + '.jpg' ), type: 'jpeg', quality: 84, clip: { x: 0, y: 0, width: 1920, height: 1280 } } );
		const size = fs.statSync( path.join( out, name + '.jpg' ) ).size;
		console.log( `${ name }.jpg  ${ Math.round( size / 1024 ) } KB` );
	}

	await browser.close();
} )();

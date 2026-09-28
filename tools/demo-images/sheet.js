#!/usr/bin/env node
/**
 * Contact sheet of rendered images: node sheet.js <out.jpg> <img> [img ...]
 */
'use strict';

const fs = require( 'fs' );
const path = require( 'path' );
const { chromium } = require( process.env.PW_PATH || '/opt/node22/lib/node_modules/playwright' );

( async () => {
	const [ out, ...images ] = process.argv.slice( 2 );
	const cols = 2;
	const w = 960;
	const h = 640;
	const rows = Math.ceil( images.length / cols );
	const tags = images.map( ( img ) => `<figure style="margin:0;position:relative"><img src="data:image/jpeg;base64,${ fs.readFileSync( img ).toString( 'base64' ) }" style="width:${ w }px;height:${ h }px;display:block"><figcaption style="position:absolute;left:8px;top:8px;background:#000a;color:#fff;font:14px sans-serif;padding:2px 6px">${ path.basename( img ) }</figcaption></figure>` ).join( '' );
	const browser = await chromium.launch();
	const page = await browser.newPage( { viewport: { width: w * cols, height: h * rows } } );
	await page.setContent( `<body style="margin:0;display:grid;grid-template-columns:repeat(${ cols },${ w }px)">${ tags }</body>` );
	await page.screenshot( { path: out, type: 'jpeg', quality: 80 } );
	await browser.close();
} )();

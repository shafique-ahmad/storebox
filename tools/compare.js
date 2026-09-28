#!/usr/bin/env node
/**
 * Side-by-side comparison of two screenshots (for visual checks).
 *
 * Usage: node tools/compare.js <left.png> <right.png> <out.png> [labelLeft] [labelRight] [sliceHeight]
 *
 * With sliceHeight, the pair is cut into horizontal bands written as
 * out-1.png, out-2.png… so tall pages stay readable.
 */
const { chromium } = require( process.env.PW_PATH || '/opt/node22/lib/node_modules/playwright' );
const fs = require( 'fs' );

( async () => {
	const [ left, right, out, labelLeft = 'left', labelRight = 'right', slice = '0' ] = process.argv.slice( 2 );
	const b64 = ( f ) => 'data:image/png;base64,' + fs.readFileSync( f ).toString( 'base64' );
	const browser = await chromium.launch();
	const page = await browser.newPage( { viewport: { width: 1600, height: 900 } } );
	await page.setContent( `<!doctype html><style>
		body{margin:0;background:#888;font:600 14px sans-serif}
		.w{display:flex;gap:8px;align-items:flex-start}
		figure{margin:0;width:796px;background:#fff}
		figcaption{padding:6px 8px;background:#222;color:#fff}
		img{display:block;width:100%}
	</style><div class="w"><figure><figcaption>${ labelLeft }</figcaption><img src="${ b64( left ) }"></figure><figure><figcaption>${ labelRight }</figcaption><img src="${ b64( right ) }"></figure></div>` );
	await page.waitForTimeout( 300 );
	const height = await page.evaluate( () => document.querySelector( '.w' ).getBoundingClientRect().height );
	const band = parseInt( slice, 10 );
	if ( ! band ) {
		await page.screenshot( { path: out, fullPage: true } );
	} else {
		let i = 1;
		for ( let y = 0; y < height; y += band, i++ ) {
			await page.screenshot( { path: out.replace( /\.png$/, '-' + i + '.png' ), clip: { x: 0, y, width: 1600, height: Math.min( band, height - y ) }, fullPage: true } );
		}
	}
	await browser.close();
} )();

/**
 * Playwright routes for screenshots of the source designs: their Google Font
 * comes from the theme's bundled copy and their Pexels photos from the
 * original demo artwork, so they are comparable with the theme.
 *
 * Usage: await require( './pw-routes' )( browserContext );
 */
const fs = require( 'fs' );
const path = require( 'path' );

module.exports = async function ( context ) {
	const root = path.resolve( __dirname, '..' );
	const photos = {
		5759145: 'storage-corridor-trolley', 5759037: 'storage-corridor-wide', 5759147: 'unit-open-door',
		6169022: 'unit-shelving', 5759123: 'unit-small-yellow', 851305: 'storage-lockers', 38573375: 'door-padlock',
		20491127: 'vehicle-bay', 32011828: 'shutters-row', 32151280: 'drive-up-units', 4246123: 'packing-table',
		7464683: 'moving-boxes-room',
	};
	await context.route( /fonts\.googleapis\.com\/css2/, ( route ) =>
		route.fulfill( {
			contentType: 'text/css',
			body: fs.readFileSync( path.join( root, 'storebox/assets/css/fonts.css' ), 'utf8' ).replace( /url\(\.\.\/fonts\//g, 'url(https://sb-fonts.local/' ),
		} )
	);
	await context.route( /sb-fonts\.local\//, ( route ) =>
		route.fulfill( { contentType: 'font/woff2', body: fs.readFileSync( path.join( root, 'storebox/assets/fonts', route.request().url().split( '/' ).pop() ) ) } )
	);
	await context.route( /images\.pexels\.com/, ( route ) => {
		const m = route.request().url().match( /photos\/(\d+)\// );
		const file = m && photos[ m[ 1 ] ] ? path.join( root, 'storebox-core/demo/images', photos[ m[ 1 ] ] + '.jpg' ) : null;
		return file ? route.fulfill( { contentType: 'image/jpeg', body: fs.readFileSync( file ) } ) : route.abort();
	} );
};

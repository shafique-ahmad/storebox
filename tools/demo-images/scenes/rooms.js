/**
 * Home scenes: packing on a table, and a bright room with moving boxes.
 */
'use strict';

const L = require( '../lib' );

/**
 * Vertical cylinder (axis Y): visible side facets, then the top disc.
 */
function cylinderY( pr, eye, cx, cz, r, y0, y1, colors, segments = 36 ) {
	const pts = [];
	for ( let i = 0; i < segments; i++ ) {
		const a = ( i / segments ) * Math.PI * 2;
		pts.push( [ cx + Math.cos( a ) * r, cz + Math.sin( a ) * r, a ] );
	}
	let s = '';
	const faces = [];
	for ( let i = 0; i < segments; i++ ) {
		const p = pts[ i ];
		const q = pts[ ( i + 1 ) % segments ];
		const mx = ( p[ 0 ] + q[ 0 ] ) / 2;
		const mz = ( p[ 1 ] + q[ 1 ] ) / 2;
		const nx = mx - cx;
		const nz = mz - cz;
		if ( nx * ( eye.x - mx ) + nz * ( eye.z - mz ) <= 0 ) {
			continue;
		}
		// Light from the upper left.
		const lit = Math.max( 0, ( -nx * 0.7 - nz * 0.3 ) / r );
		faces.push( { d: Math.hypot( mx - eye.x, mz - eye.z ), svg: L.quad( pr, [ p[ 0 ], y0, p[ 1 ] ], [ q[ 0 ], y0, q[ 1 ] ], [ q[ 0 ], y1, q[ 1 ] ], [ p[ 0 ], y1, p[ 1 ] ], L.mix( colors.dark, colors.side, 0.35 + lit * 0.65 ), `stroke="${ L.mix( colors.dark, colors.side, 0.35 + lit * 0.65 ) }" stroke-width="1"` ) } );
	}
	faces.sort( ( a, b ) => b.d - a.d ).forEach( ( f ) => {
		s += f.svg;
	} );
	if ( eye.y > y1 ) {
		s += L.poly( pts.map( ( p ) => pr( p[ 0 ], y1, p[ 1 ] ) ), colors.top );
		if ( colors.hole ) {
			s += L.poly( pts.map( ( p ) => pr( cx + ( p[ 0 ] - cx ) * colors.holeRatio, y1, cz + ( p[ 1 ] - cz ) * colors.holeRatio ) ), colors.hole );
		}
	}
	return s;
}

/**
 * Packing on a table: an open box, tape, a marker and flat-packed boxes.
 */
function packing() {
	const eye = { x: 0.05, y: 1.75, z: -0.55 };
	const pr = L.camera( { f: 1250, cx: L.W * 0.5, cy: L.H * 0.47, pitch: 0.78, x: eye.x, y: eye.y, z: eye.z } );
	const ty = 0.76;
	const defs = [
		L.linear( 'wood', [ [ 0, '#D9B98E' ], [ 1, '#C39D6D' ] ], 0, 0, 1, 1 ),
		L.linear( 'floorP', [ [ 0, '#E7E2DA' ], [ 1, '#CFC7BB' ] ] ),
		L.radial( 'sun', [ [ 0, '#FFF6DF', 0.55 ], [ 1, '#FFF6DF', 0 ] ] ),
	].join( '' );

	let s = L.rect( 0, 0, L.W, L.H, 'url(#floorP)' );

	// Table top with wood grain.
	s += L.quad( pr, [ -1.3, ty, -0.1 ], [ 1.3, ty, -0.1 ], [ 1.3, ty, 1.6 ], [ -1.3, ty, 1.6 ], 'url(#wood)' );
	for ( let x = -1.25; x < 1.3; x += 0.21 ) {
		s += L.seg( pr, [ x, ty + 0.001, -0.1 ], [ x, ty + 0.001, 1.6 ], '#A98457', 1.4, 'opacity=".35"' );
	}
	s += L.quad( pr, [ -1.3, ty, -0.1 ], [ 1.3, ty, -0.1 ], [ 1.3, ty - 0.05, -0.1 ], [ -1.3, ty - 0.05, -0.1 ], '#A3804F' );
	const sp = pr( 0.5, ty, 0.9 );
	s += L.ellipse( sp[ 0 ], sp[ 1 ], 700, 360, 'url(#sun)' );

	// Flat-packed boxes, right.
	for ( let i = 0; i < 4; i++ ) {
		const y0 = ty + i * 0.012;
		s += L.cuboid( pr, [ 0.55 + i * 0.01, y0, 0.3 - i * 0.015 ], [ 1.15 + i * 0.01, y0 + 0.012, 1.05 - i * 0.015 ], { front: '#B98349', top: i % 2 ? '#C8965C' : '#C2905A', side: '#A87640' }, eye );
	}

	// Open box, centre.
	const b = { x0: -0.42, x1: 0.2, z0: 0.35, z1: 0.8, y0: ty, y1: ty + 0.32 };
	const inside = '#8F6435';
	const inWall = '#A57443';
	s += L.quad( pr, [ b.x0, b.y0 + 0.01, b.z0 ], [ b.x1, b.y0 + 0.01, b.z0 ], [ b.x1, b.y0 + 0.01, b.z1 ], [ b.x0, b.y0 + 0.01, b.z1 ], inside );
	s += L.quad( pr, [ b.x0, b.y0, b.z1 ], [ b.x1, b.y0, b.z1 ], [ b.x1, b.y1, b.z1 ], [ b.x0, b.y1, b.z1 ], inWall );
	s += L.quad( pr, [ b.x0, b.y0, b.z0 ], [ b.x0, b.y1, b.z0 ], [ b.x0, b.y1, b.z1 ], [ b.x0, b.y0, b.z1 ], L.shade( inWall, -0.1 ) );
	s += L.quad( pr, [ b.x1, b.y0, b.z0 ], [ b.x1, b.y1, b.z0 ], [ b.x1, b.y1, b.z1 ], [ b.x1, b.y0, b.z1 ], L.shade( inWall, 0.05 ) );
	// Folded items inside (a blanket and a small box).
	s += L.quad( pr, [ b.x0 + 0.03, b.y0 + 0.14, b.z0 + 0.03 ], [ b.x1 - 0.25, b.y0 + 0.14, b.z0 + 0.03 ], [ b.x1 - 0.25, b.y0 + 0.14, b.z1 - 0.03 ], [ b.x0 + 0.03, b.y0 + 0.14, b.z1 - 0.03 ], '#6E8FA3' );
	s += L.cuboid( pr, [ b.x1 - 0.24, b.y0, b.z0 + 0.05 ], [ b.x1 - 0.03, b.y0 + 0.2, b.z0 + 0.25 ], { front: '#E9E4DA', top: '#F5F1EA', side: '#D5CFC3' }, eye );
	// Outer walls (front and right are visible).
	s += L.quad( pr, [ b.x0, b.y0, b.z0 ], [ b.x1, b.y0, b.z0 ], [ b.x1, b.y1, b.z0 ], [ b.x0, b.y1, b.z0 ], '#C8965C' );
	// Flaps folded outwards.
	const flap = 0.2;
	s += L.quad( pr, [ b.x0, b.y1, b.z0 ], [ b.x1, b.y1, b.z0 ], [ b.x1, b.y1 - 0.07, b.z0 - flap ], [ b.x0, b.y1 - 0.07, b.z0 - flap ], '#D6A871' );
	s += L.quad( pr, [ b.x0, b.y1, b.z1 ], [ b.x1, b.y1, b.z1 ], [ b.x1, b.y1 + 0.13, b.z1 + flap * 0.8 ], [ b.x0, b.y1 + 0.13, b.z1 + flap * 0.8 ], '#B9874F' );
	s += L.quad( pr, [ b.x0, b.y1, b.z0 ], [ b.x0, b.y1, b.z1 ], [ b.x0 - flap * 0.9, b.y1 - 0.05, b.z1 ], [ b.x0 - flap * 0.9, b.y1 - 0.05, b.z0 ], '#CB9A61' );
	s += L.quad( pr, [ b.x1, b.y1, b.z0 ], [ b.x1, b.y1, b.z1 ], [ b.x1 + flap * 0.9, b.y1 - 0.05, b.z1 ], [ b.x1 + flap * 0.9, b.y1 - 0.05, b.z0 ], '#D3A56D' );
	// Label on the front.
	s += L.quad( pr, [ b.x0 + 0.07, b.y0 + 0.08, b.z0 - 0.002 ], [ b.x0 + 0.27, b.y0 + 0.08, b.z0 - 0.002 ], [ b.x0 + 0.27, b.y0 + 0.19, b.z0 - 0.002 ], [ b.x0 + 0.07, b.y0 + 0.19, b.z0 - 0.002 ], '#F7F4EE' );
	s += L.seg( pr, [ b.x0 + 0.1, b.y0 + 0.15, b.z0 - 0.003 ], [ b.x0 + 0.23, b.y0 + 0.15, b.z0 - 0.003 ], '#16303F', 3, 'stroke-linecap="round"' );
	s += L.seg( pr, [ b.x0 + 0.1, b.y0 + 0.115, b.z0 - 0.003 ], [ b.x0 + 0.19, b.y0 + 0.115, b.z0 - 0.003 ], '#16303F', 3, 'stroke-linecap="round" opacity=".6"' );

	// Roll of tape, left front.
	s += cylinderY( pr, eye, -0.75, 0.25, 0.085, ty, ty + 0.05, { side: '#E8C98F', dark: '#B48C4F', top: '#EFD7A8', hole: '#C9A77A', holeRatio: 0.62 } );
	// Marker.
	s += L.cuboid( pr, [ -1.02, ty, 0.78 ], [ -0.87, ty + 0.022, 0.805 ], { front: '#16303F', top: '#24475B', side: '#10242F' }, eye );
	s += L.cuboid( pr, [ -0.87, ty, 0.78 ], [ -0.82, ty + 0.022, 0.805 ], { front: '#F2B705', top: '#F5C733', side: '#C99700' }, eye );
	// Scissors (simplified).
	const a = pr( 0.34, ty, 0.2 );
	s += L.ellipse( a[ 0 ], a[ 1 ], 26, 17, 'none', 'stroke="#2C5871" stroke-width="9"' );
	s += L.ellipse( a[ 0 ] + 60, a[ 1 ] + 6, 26, 17, 'none', 'stroke="#2C5871" stroke-width="9"' );
	const blade = pr( 0.46, ty, 0.36 );
	s += L.line( [ a[ 0 ] + 20, a[ 1 ] - 10 ], blade, '#AEB8BE', 8, 'stroke-linecap="round"' );
	s += L.line( [ a[ 0 ] + 44, a[ 1 ] - 8 ], blade, '#C7CFD4', 8, 'stroke-linecap="round"' );

	return L.svg( s, defs );
}

/**
 * A bright, empty room with moving boxes by the window.
 */
function moving() {
	const eye = { x: 0.3, y: 1.45, z: -1.2 };
	const pr = L.camera( { f: 950, cx: L.W * 0.5, cy: L.H * 0.5, yaw: -0.18, x: eye.x, y: eye.y, z: eye.z } );
	const wd = 3.2;
	const depth = 5.2;
	const hgt = 2.8;
	const defs = [
		L.linear( 'mwall', [ [ 0, '#F6F3EE' ], [ 1, '#E7E1D8' ] ] ),
		L.linear( 'mside', [ [ 0, '#E9E3DA' ], [ 1, '#F4F0EA' ] ], 0, 0, 1, 0 ),
		L.linear( 'mfloor', [ [ 0, '#C9A57A' ], [ 1, '#B08A5E' ] ] ),
		L.linear( 'outside', [ [ 0, '#CFE3EC' ], [ 0.6, '#EAF3F6' ], [ 1, '#B9D1BF' ] ] ),
		L.radial( 'daylight', [ [ 0, '#FFF7E3', 0.75 ], [ 1, '#FFF7E3', 0 ] ] ),
	].join( '' );

	let s = L.rect( 0, 0, L.W, L.H, '#F2EEE8' );
	// Shell.
	s += L.quad( pr, [ -wd, 0, -2 ], [ wd, 0, -2 ], [ wd, 0, depth ], [ -wd, 0, depth ], 'url(#mfloor)' );
	for ( let x = -wd + 0.18; x < wd; x += 0.18 ) {
		s += L.seg( pr, [ x, 0.001, -2 ], [ x, 0.001, depth ], '#9C7850', 1.3, 'opacity=".35"' );
	}
	s += L.quad( pr, [ -wd, hgt, -2 ], [ wd, hgt, -2 ], [ wd, hgt, depth ], [ -wd, hgt, depth ], '#FBFAF8' );
	s += L.quad( pr, [ -wd, 0, -2 ], [ -wd, hgt, -2 ], [ -wd, hgt, depth ], [ -wd, 0, depth ], 'url(#mside)' );
	s += L.quad( pr, [ -wd, 0, depth ], [ wd, 0, depth ], [ wd, hgt, depth ], [ -wd, hgt, depth ], 'url(#mwall)' );
	s += L.quad( pr, [ -wd, 0, depth - 0.001 ], [ wd, 0, depth - 0.001 ], [ wd, 0.1, depth - 0.001 ], [ -wd, 0.1, depth - 0.001 ], '#FFFFFF' );

	// Large window in the back wall.
	const wx0 = -1.9;
	const wx1 = 1.4;
	s += L.quad( pr, [ wx0 - 0.08, 0.35, depth - 0.01 ], [ wx1 + 0.08, 0.35, depth - 0.01 ], [ wx1 + 0.08, 2.45, depth - 0.01 ], [ wx0 - 0.08, 2.45, depth - 0.01 ], '#FFFFFF' );
	s += L.quad( pr, [ wx0, 0.42, depth - 0.02 ], [ wx1, 0.42, depth - 0.02 ], [ wx1, 2.38, depth - 0.02 ], [ wx0, 2.38, depth - 0.02 ], 'url(#outside)' );
	// Trees outside, soft.
	[ [ -1.5, 1.0, 0.5 ], [ -0.8, 0.9, 0.42 ], [ 0.3, 1.05, 0.55 ], [ 1.0, 0.95, 0.4 ] ].forEach( ( t ) => {
		const c = pr( t[ 0 ], t[ 1 ], depth - 0.03 );
		s += L.ellipse( c[ 0 ], c[ 1 ], t[ 2 ] * 190, t[ 2 ] * 170, '#8FB29A', 'opacity=".75" filter="url(#blur10)"' );
	} );
	// Window bars.
	[ ( wx0 + wx1 ) / 2 ].forEach( ( x ) => {
		s += L.quad( pr, [ x - 0.04, 0.42, depth - 0.025 ], [ x + 0.04, 0.42, depth - 0.025 ], [ x + 0.04, 2.38, depth - 0.025 ], [ x - 0.04, 2.38, depth - 0.025 ], '#FFFFFF' );
	} );
	s += L.quad( pr, [ wx0, 1.5, depth - 0.025 ], [ wx1, 1.5, depth - 0.025 ], [ wx1, 1.56, depth - 0.025 ], [ wx0, 1.56, depth - 0.025 ], '#FFFFFF' );

	// Daylight falling on the floor.
	s += L.quad( pr, [ wx0 + 0.2, 0.002, depth - 0.2 ], [ wx1, 0.002, depth - 0.2 ], [ wx1 - 0.6, 0.002, depth - 2.6 ], [ wx0 - 0.4, 0.002, depth - 2.6 ], '#FFF6DE', 'opacity=".45" filter="url(#blur10)"' );

	// Boxes, stacked on the right.
	const items = [
		{ min: [ 1.1, 0, 2.6 ], max: [ 1.8, 0.55, 3.2 ] },
		{ min: [ 1.9, 0, 2.5 ], max: [ 2.55, 0.5, 3.1 ] },
		{ min: [ 1.2, 0.55, 2.66 ], max: [ 1.75, 0.98, 3.14 ] },
		{ min: [ 1.95, 0.5, 2.6 ], max: [ 2.45, 0.86, 3.0 ] },
		{ min: [ 1.3, 0.98, 2.72 ], max: [ 1.7, 1.28, 3.08 ] },
		{ min: [ 0.3, 0, 1.4 ], max: [ 0.95, 0.48, 1.95 ] },
		{ min: [ 0.38, 0.48, 1.46 ], max: [ 0.85, 0.82, 1.86 ] },
	];
	items.sort( ( a, b ) => b.min[ 2 ] - a.min[ 2 ] ).forEach( ( b, i ) => {
		s += L.box( pr, b.min, b.max, eye, { color: [ '#C8965C', '#BF8C52', '#D1A36D' ][ i % 3 ], label: '#F7F4EE', arrows: i % 2 === 0 } );
	} );

	// Potted plant, left.
	s += cylinderY( pr, eye, -2.3, 3.4, 0.24, 0, 0.5, { side: '#E6DED2', dark: '#BDB2A2', top: '#8C6B4A' } );
	const lc = pr( -2.3, 0.5, 3.4 );
	[ [ -40, -150, 60, 170, -18 ], [ 40, -170, 60, 190, 16 ], [ 0, -210, 55, 200, 2 ], [ -70, -90, 50, 120, -40 ], [ 75, -100, 50, 120, 40 ] ].forEach( ( l ) => {
		s += `<ellipse cx="${ L.fmt( lc[ 0 ] + l[ 0 ] ) }" cy="${ L.fmt( lc[ 1 ] + l[ 1 ] ) }" rx="${ l[ 2 ] }" ry="${ l[ 3 ] }" fill="${ l[ 4 ] > 0 ? '#4F7C5E' : '#5E8C6B' }" transform="rotate(${ l[ 4 ] } ${ L.fmt( lc[ 0 ] + l[ 0 ] ) } ${ L.fmt( lc[ 1 ] + l[ 1 ] ) })"/>`;
	} );

	// Rolled rug leaning on the side wall.
	const r0 = pr( -3.05, 0, 2.2 );
	const r1 = pr( -2.75, 1.7, 2.35 );
	s += L.line( r0, r1, '#2C5871', 64, 'stroke-linecap="round"' );
	s += L.line( r0, r1, '#3E6F8B', 22, 'stroke-linecap="round" opacity=".6"' );
	void hgt;
	return L.svg( s, defs );
}

module.exports = { packing, moving };

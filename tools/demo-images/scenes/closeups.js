/**
 * Close-ups and small scenes: padlocked door, lockers, vehicle bay.
 */
'use strict';

const L = require( '../lib' );

/**
 * A red roller door, close up, secured with a padlock.
 */
function padlock( opt = {} ) {
	const o = Object.assign( { door: '#B8412F', frame: '#3B4449' }, opt );
	const defs = [
		L.linear( 'slat', [ [ 0, L.shade( o.door, 0.2 ) ], [ 0.35, o.door ], [ 0.8, L.shade( o.door, -0.12 ) ], [ 1, L.shade( o.door, -0.35 ) ] ] ),
		L.linear( 'light', [ [ 0, '#ffffff', 0.2 ], [ 0.6, '#ffffff', 0 ], [ 1, '#000000', 0.25 ] ], 0, 0, 1, 0 ),
		L.linear( 'post', [ [ 0, '#2B3237' ], [ 0.5, '#58646B' ], [ 1, '#2B3237' ] ], 0, 0, 1, 0 ),
		L.linear( 'steel', [ [ 0, '#EEF1F3' ], [ 0.45, '#AEB8BE' ], [ 1, '#6F7B82' ] ], 0, 0, 1, 1 ),
		L.linear( 'brass', [ [ 0, '#F6D887' ], [ 0.5, '#D3A43A' ], [ 1, '#8C6A1D' ] ], 0, 0, 1, 1 ),
		L.linear( 'ground', [ [ 0, '#9FA8AD' ], [ 1, '#6F787D' ] ] ),
	].join( '' );

	let s = '';
	const groundY = 1080;
	s += L.rect( 0, 0, L.W, groundY, o.frame );
	// Slats.
	const slat = 58;
	for ( let y = -20; y < groundY - 30; y += slat ) {
		s += L.rect( 150, y, L.W - 300, slat, 'url(#slat)' );
		s += L.rect( 150, y + slat - 5, L.W - 300, 5, L.shade( o.door, -0.5 ), 'opacity=".6"' );
		s += L.rect( 150, y + 8, L.W - 300, 3, '#ffffff', 'opacity=".18"' );
	}
	s += L.rect( 150, 0, L.W - 300, groundY, 'url(#light)' );
	// Bottom rail with handles.
	s += L.rect( 150, groundY - 70, L.W - 300, 70, L.shade( o.door, -0.28 ) );
	s += L.rect( 150, groundY - 70, L.W - 300, 8, '#ffffff', 'opacity=".2"' );
	// Side posts.
	s += L.rect( 60, 0, 110, groundY, 'url(#post)' );
	s += L.rect( L.W - 170, 0, 110, groundY, 'url(#post)' );
	// Ground.
	s += L.rect( 0, groundY, L.W, L.H - groundY, 'url(#ground)' );
	s += L.rect( 0, groundY, L.W, 14, '#000000', 'opacity=".25"' );
	s += L.ellipse( L.W / 2, groundY + 30, 900, 40, '#000000', 'opacity=".18" filter="url(#blur24)"' );

	// Hasp plate and padlock, bottom centre.
	const cx = L.W * 0.5;
	const hy = groundY - 118;
	s += L.rect( cx - 170, hy - 40, 340, 110, 'url(#steel)', 'rx="10"' );
	s += L.ellipse( cx - 130, hy + 15, 11, 11, '#6F7B82' );
	s += L.ellipse( cx + 130, hy + 15, 11, 11, '#6F7B82' );
	s += L.rect( cx - 46, hy - 16, 92, 30, '#5b666d', 'rx="6"' );
	// Shackle.
	s += `<path d="M${ cx - 58 } ${ hy + 120 } L${ cx - 58 } ${ hy + 20 } A58 58 0 0 1 ${ cx + 58 } ${ hy + 20 } L${ cx + 58 } ${ hy + 120 }" fill="none" stroke="url(#steel)" stroke-width="26" stroke-linecap="round"/>`;
	// Lock body.
	s += L.ellipse( cx, hy + 330, 150, 26, '#000', 'opacity=".25" filter="url(#blur10)"' );
	s += L.rect( cx - 110, hy + 95, 220, 190, 'url(#brass)', 'rx="26"' );
	s += L.rect( cx - 110, hy + 95, 220, 30, '#ffffff', 'rx="16" opacity=".25"' );
	s += L.ellipse( cx, hy + 185, 22, 22, '#5d4613' );
	s += L.rect( cx - 7, hy + 190, 14, 48, '#5d4613', 'rx="7"' );

	return L.svg( s, defs );
}

/**
 * A wall of small lockers seen at an angle.
 */
function lockers( opt = {} ) {
	const o = Object.assign( { colors: [ '#2C5871', '#2C5871', '#16303F', '#2C5871', '#F2B705' ], seed: 4 }, opt );
	const eye = { x: 0, y: 1.5, z: 0 };
	const pr = L.camera( { f: 1150, cx: L.W * 0.42, cy: L.H * 0.5, yaw: 0.42, x: 0, y: 1.5, z: 0 } );
	const fz = 4.2;
	let rnd = o.seed;
	const random = () => {
		rnd = ( rnd * 9301 + 49297 ) % 233280;
		return rnd / 233280;
	};
	const defs = [
		L.linear( 'lwall', [ [ 0, '#F1F4F5' ], [ 1, '#CBD3D7' ] ], 0, 0, 1, 0 ),
		L.linear( 'lfloor', [ [ 0, '#B8C1C6' ], [ 1, '#8E999F' ] ] ),
		L.linear( 'lceil', [ [ 0, '#E9EDEF' ], [ 1, '#F7F9F9' ] ] ),
		L.linear( 'doorSheen', [ [ 0, '#ffffff', 0.22 ], [ 0.5, '#ffffff', 0 ], [ 1, '#000000', 0.12 ] ], 0, 0, 1, 1 ),
	].join( '' );

	let s = '';
	s += L.rect( 0, 0, L.W, L.H, 'url(#lceil)' );
	s += L.quad( pr, [ -3, 0, 0.6 ], [ 40, 0, 0.6 ], [ 40, 0, fz ], [ -3, 0, fz ], 'url(#lfloor)' );
	s += L.quad( pr, [ -3, 0, fz ], [ 40, 0, fz ], [ 40, 3.0, fz ], [ -3, 3.0, fz ], 'url(#lwall)' );
	s += L.quad( pr, [ -3, 3.0, 0.6 ], [ 40, 3.0, 0.6 ], [ 40, 3.0, fz ], [ -3, 3.0, fz ], '#EEF1F2' );

	// Locker grid: 3 rows.
	const cols = 16;
	const cw = 0.95;
	const rows = [ [ 0.12, 0.92 ], [ 0.98, 1.78 ], [ 1.84, 2.64 ] ];
	s += L.quad( pr, [ -1.5, 0.06, fz - 0.02 ], [ -1.5 + cols * cw + 0.1, 0.06, fz - 0.02 ], [ -1.5 + cols * cw + 0.1, 2.7, fz - 0.02 ], [ -1.5, 2.7, fz - 0.02 ], '#3B4449' );
	for ( let c = 0; c < cols; c++ ) {
		rows.forEach( ( r ) => {
			const x0 = -1.45 + c * cw;
			const x1 = x0 + cw - 0.06;
			const color = o.colors[ Math.floor( random() * o.colors.length ) ];
			s += L.quad( pr, [ x0, r[ 0 ], fz - 0.03 ], [ x1, r[ 0 ], fz - 0.03 ], [ x1, r[ 1 ], fz - 0.03 ], [ x0, r[ 1 ], fz - 0.03 ], color );
			s += L.quad( pr, [ x0, r[ 0 ], fz - 0.031 ], [ x1, r[ 0 ], fz - 0.031 ], [ x1, r[ 1 ], fz - 0.031 ], [ x0, r[ 1 ], fz - 0.031 ], 'url(#doorSheen)' );
			// Vents.
			for ( let v = 0; v < 3; v++ ) {
				const vy = r[ 1 ] - 0.12 - v * 0.05;
				s += L.seg( pr, [ x0 + 0.25, vy, fz - 0.032 ], [ x1 - 0.25, vy, fz - 0.032 ], L.shade( color, -0.35 ), 2, 'stroke-linecap="round" opacity=".7"' );
			}
			// Handle and number plate.
			s += L.quad( pr, [ x1 - 0.14, r[ 0 ] + 0.3, fz - 0.035 ], [ x1 - 0.1, r[ 0 ] + 0.3, fz - 0.035 ], [ x1 - 0.1, r[ 0 ] + 0.46, fz - 0.035 ], [ x1 - 0.14, r[ 0 ] + 0.46, fz - 0.035 ], '#DCE2E5' );
			s += L.quad( pr, [ x0 + 0.1, r[ 0 ] + 0.1, fz - 0.035 ], [ x0 + 0.3, r[ 0 ] + 0.1, fz - 0.035 ], [ x0 + 0.3, r[ 0 ] + 0.18, fz - 0.035 ], [ x0 + 0.1, r[ 0 ] + 0.18, fz - 0.035 ], '#ffffff', 'opacity=".9"' );
		} );
	}
	// Floor reflection of the lockers.
	s += L.quad( pr, [ -1.5, 0, fz - 0.05 ], [ -1.5 + cols * cw, 0, fz - 0.05 ], [ -1.5 + cols * cw, 0, fz - 1.4 ], [ -1.5, 0, fz - 1.4 ], '#2C5871', 'opacity=".12" filter="url(#blur10)"' );
	// Ceiling light strip.
	s += L.quad( pr, [ -2, 2.99, 2.2 ], [ 30, 2.99, 2.2 ], [ 30, 2.99, 2.45 ], [ -2, 2.99, 2.45 ], '#ffffff' );
	void eye;
	return L.svg( s, defs );
}

/**
 * Vehicle bay: a wide open door with a covered car inside.
 */
function bay( opt = {} ) {
	const o = Object.assign( { cover: '#9FB2BD', door: '#2C5871' }, opt );
	const pr = L.camera( { f: 900, cx: L.W * 0.5, cy: L.H * 0.52, x: 0, y: 1.55, z: -6.5 } );
	const eye = { x: 0, y: 1.55, z: -6.5 };
	const defs = [
		L.linear( 'bsky', [ [ 0, '#BFD6E3' ], [ 1, '#EAF1F4' ] ] ),
		L.linear( 'bwall', [ [ 0, '#E6E9EA' ], [ 1, '#D2D8DB' ] ] ),
		L.linear( 'bin', [ [ 0, '#C9D0D4' ], [ 1, '#A9B3B8' ] ] ),
		L.linear( 'bfloor', [ [ 0, '#9AA4A9' ], [ 1, '#79838A' ] ] ),
		L.linear( 'apron2', [ [ 0, '#B9C0C4' ], [ 1, '#8C959A' ] ] ),
		L.linear( 'coverShade', [ [ 0, L.shade( o.cover, 0.25 ) ], [ 0.55, o.cover ], [ 1, L.shade( o.cover, -0.3 ) ] ] ),
		L.radial( 'bayLight', [ [ 0, '#ffffff', 0.6 ], [ 1, '#ffffff', 0 ] ] ),
	].join( '' );

	const W2 = 2.9;
	const H2 = 3.2;
	const depth = 7;
	let s = '';
	s += L.rect( 0, 0, L.W, L.H, 'url(#bsky)' );
	// Apron in front of the building.
	s += L.quad( pr, [ -30, 0, -6 ], [ 30, 0, -6 ], [ 30, 0, 0 ], [ -30, 0, 0 ], 'url(#apron2)' );
	// Facade.
	s += L.quad( pr, [ -30, 0, 0 ], [ 30, 0, 0 ], [ 30, 4.6, 0 ], [ -30, 4.6, 0 ], 'url(#bwall)' );
	s += L.quad( pr, [ -30, 4.1, -0.2 ], [ 30, 4.1, -0.2 ], [ 30, 4.7, -0.2 ], [ -30, 4.7, -0.2 ], '#16303F' );
	// Interior of the bay.
	s += L.quad( pr, [ -W2, 0, 0 ], [ W2, 0, 0 ], [ W2, 0, depth ], [ -W2, 0, depth ], 'url(#bfloor)' );
	s += L.quad( pr, [ -W2, 0, 0 ], [ -W2, H2, 0 ], [ -W2, H2, depth ], [ -W2, 0, depth ], L.shade( '#C3CBCF', -0.08 ) );
	s += L.quad( pr, [ W2, 0, 0 ], [ W2, H2, 0 ], [ W2, H2, depth ], [ W2, 0, depth ], L.shade( '#C3CBCF', -0.12 ) );
	s += L.quad( pr, [ -W2, H2, 0 ], [ W2, H2, 0 ], [ W2, H2, depth ], [ -W2, H2, depth ], '#B7C0C5' );
	s += L.quad( pr, [ -W2, 0, depth ], [ W2, 0, depth ], [ W2, H2, depth ], [ -W2, H2, depth ], 'url(#bin)' );
	const lp = pr( 0, H2 - 0.05, depth * 0.5 );
	s += L.quad( pr, [ -0.6, H2 - 0.01, 3.2 ], [ 0.6, H2 - 0.01, 3.2 ], [ 0.6, H2 - 0.01, 3.6 ], [ -0.6, H2 - 0.01, 3.6 ], '#ffffff' );
	s += L.ellipse( lp[ 0 ], lp[ 1 ] + 120, 420, 240, 'url(#bayLight)', 'opacity=".5"' );

	// Covered car: a soft silhouette on the floor, seen from the front-left.
	const base = pr( 0, 0, 2.3 );
	const k = 900 / ( 2.3 + 6.5 );
	const X = ( v ) => base[ 0 ] + v * k;
	const Y = ( v ) => base[ 1 ] - v * k;
	s += L.ellipse( X( 0 ), Y( -0.02 ), 2.4 * k, 0.28 * k, '#0d1d26', 'opacity=".35" filter="url(#blur10)"' );
	s += `<path d="M${ X( -2.2 ) } ${ Y( 0.28 ) } C${ X( -2.25 ) } ${ Y( 0.85 ) } ${ X( -1.9 ) } ${ Y( 0.95 ) } ${ X( -1.2 ) } ${ Y( 1.0 ) } C${ X( -0.8 ) } ${ Y( 1.45 ) } ${ X( 0.7 ) } ${ Y( 1.5 ) } ${ X( 1.1 ) } ${ Y( 1.0 ) } C${ X( 1.9 ) } ${ Y( 0.98 ) } ${ X( 2.25 ) } ${ Y( 0.85 ) } ${ X( 2.2 ) } ${ Y( 0.28 ) } Z" fill="url(#coverShade)"/>`;
	s += `<path d="M${ X( -1.1 ) } ${ Y( 1.02 ) } C${ X( -0.7 ) } ${ Y( 1.38 ) } ${ X( 0.6 ) } ${ Y( 1.42 ) } ${ X( 1.0 ) } ${ Y( 1.02 ) }" fill="none" stroke="#ffffff" stroke-width="${ 0.05 * k }" opacity=".35" stroke-linecap="round"/>`;
	[ -1.45, 1.45 ].forEach( ( wx ) => {
		s += L.ellipse( X( wx ), Y( 0.28 ), 0.36 * k, 0.3 * k, '#1b2024' );
		s += L.ellipse( X( wx ), Y( 0.28 ), 0.16 * k, 0.14 * k, '#6f7a80' );
	} );
	s += `<path d="M${ X( -2.2 ) } ${ Y( 0.45 ) } Q${ X( 0 ) } ${ Y( 0.55 ) } ${ X( 2.2 ) } ${ Y( 0.45 ) }" fill="none" stroke="#2C5871" stroke-width="${ 0.06 * k }" opacity=".6"/>`;

	// Door frame and the rolled-up door.
	s += L.quad( pr, [ -W2 - 0.18, 0, -0.01 ], [ -W2, 0, -0.01 ], [ -W2, H2 + 0.3, -0.01 ], [ -W2 - 0.18, H2 + 0.3, -0.01 ], '#3B4449' );
	s += L.quad( pr, [ W2, 0, -0.01 ], [ W2 + 0.18, 0, -0.01 ], [ W2 + 0.18, H2 + 0.3, -0.01 ], [ W2, H2 + 0.3, -0.01 ], '#3B4449' );
	s += L.quad( pr, [ -W2 - 0.18, H2 - 0.2, -0.02 ], [ W2 + 0.18, H2 - 0.2, -0.02 ], [ W2 + 0.18, H2 + 0.3, -0.02 ], [ -W2 - 0.18, H2 + 0.3, -0.02 ], o.door );
	for ( let y = H2 - 0.15; y < H2 + 0.28; y += 0.1 ) {
		s += L.seg( pr, [ -W2, y, -0.025 ], [ W2, y, -0.025 ], '#000', 1.5, 'opacity=".25"' );
	}
	// Neighbouring closed doors.
	[ -1, 1 ].forEach( ( side ) => {
		const x0 = side > 0 ? W2 + 1.2 : -W2 - 1.2 - 5.2;
		s += L.quad( pr, [ x0 - 0.15, 0, -0.01 ], [ x0 + 5.35, 0, -0.01 ], [ x0 + 5.35, H2 + 0.3, -0.01 ], [ x0 - 0.15, H2 + 0.3, -0.01 ], '#3B4449' );
		s += L.quad( pr, [ x0, 0, -0.02 ], [ x0 + 5.2, 0, -0.02 ], [ x0 + 5.2, H2 + 0.15, -0.02 ], [ x0, H2 + 0.15, -0.02 ], o.door );
		for ( let y = 0.16; y < H2 + 0.1; y += 0.16 ) {
			s += L.seg( pr, [ x0, y, -0.025 ], [ x0 + 5.2, y, -0.025 ], '#000', 1.3, 'opacity=".22"' );
		}
	} );
	// Floor markings.
	s += L.quad( pr, [ -W2 + 0.1, 0.001, -4 ], [ -W2 + 0.22, 0.001, -4 ], [ -W2 + 0.22, 0.001, 0 ], [ -W2 + 0.1, 0.001, 0 ], '#F2B705' );
	s += L.quad( pr, [ W2 - 0.22, 0.001, -4 ], [ W2 - 0.1, 0.001, -4 ], [ W2 - 0.1, 0.001, 0 ], [ W2 - 0.22, 0.001, 0 ], '#F2B705' );
	void eye;
	return L.svg( s, defs );
}

module.exports = { padlock, lockers, bay };

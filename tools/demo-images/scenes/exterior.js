/**
 * Outside a storage building: a row of roller doors receding to the right.
 * Options add a parked van or change the door colours.
 */
'use strict';

const L = require( '../lib' );

module.exports = function exterior( opt = {} ) {
	const o = Object.assign(
		{
			doors: [ '#2C5871', '#F2B705', '#C8553D', '#2C5871', '#3F8F7B', '#F2B705' ],
			wall: '#E4E7E8',
			fascia: '#16303F',
			yaw: 0.66,
			f: 1050,
			cx: L.W * 0.36,
			cy: L.H * 0.56,
			van: false,
			doorW: 2.5,
			pitch: 3.2,
			start: -3,
			count: 16,
			sky: [ '#A9CBDD', '#E6F0F4' ],
			seed: 2,
		},
		opt
	);

	const eye = { x: 0, y: 1.55, z: 0 };
	const pr = L.camera( { f: o.f, cx: o.cx, cy: o.cy, yaw: o.yaw, x: eye.x, y: eye.y, z: eye.z } );
	const fz = 9; // Facade plane.
	const top = 3.7;
	const doorH = 2.7;
	const end = o.start + o.count * o.pitch + 1;
	let rnd = o.seed;
	const random = () => {
		rnd = ( rnd * 9301 + 49297 ) % 233280;
		return rnd / 233280;
	};

	const defs = [
		L.linear( 'sky', [ [ 0, o.sky[ 0 ] ], [ 1, o.sky[ 1 ] ] ] ),
		L.linear( 'asphalt', [ [ 0, '#8E979C' ], [ 1, '#5E676C' ] ] ),
		L.linear( 'apron', [ [ 0, '#C9CFD2' ], [ 1, '#AEB6BA' ] ] ),
		L.linear( 'facade', [ [ 0, L.shade( o.wall, 0.05 ) ], [ 1, L.shade( o.wall, -0.12 ) ] ], 0, 0, 1, 0 ),
		L.linear( 'doorShade', [ [ 0, '#ffffff', 0.18 ], [ 0.5, '#ffffff', 0 ], [ 1, '#000000', 0.12 ] ] ),
	].join( '' );

	let s = '';

	// Sky, soft clouds and distant trees.
	s += L.rect( 0, 0, L.W, L.H, 'url(#sky)' );
	[ [ 380, 180, 260, 60 ], [ 820, 120, 200, 44 ], [ 1500, 210, 300, 70 ], [ 1260, 90, 160, 36 ] ].forEach( ( c ) => {
		s += L.ellipse( c[ 0 ], c[ 1 ], c[ 2 ], c[ 3 ], '#ffffff', 'opacity=".75" filter="url(#blur24)"' );
	} );
	const horizon = pr( 200, 0, 400 )[ 1 ];
	for ( let i = 0; i < 26; i++ ) {
		const x = 700 + i * 52 + random() * 30;
		const r = 40 + random() * 50;
		s += L.ellipse( x, horizon - r * 0.7, r, r * 0.9, i % 2 ? '#6E8F7D' : '#5F8270', 'opacity=".85"' );
	}

	// Ground.
	s += L.rect( 0, horizon, L.W, L.H - horizon, 'url(#asphalt)' );
	s += L.quad( pr, [ o.start - 6, 0, fz - 2.6 ], [ end + 40, 0, fz - 2.6 ], [ end + 40, 0, fz ], [ o.start - 6, 0, fz ], 'url(#apron)' );

	// Parking lines perpendicular to the building.
	for ( let x = o.start - 1; x < end; x += o.pitch ) {
		s += L.quad( pr, [ x, 0, fz - 7.5 ], [ x + 0.1, 0, fz - 7.5 ], [ x + 0.1, 0, fz - 2.8 ], [ x, 0, fz - 2.8 ], '#F4F6F7', 'opacity=".75"' );
	}

	// Facade and fascia.
	s += L.quad( pr, [ o.start - 6, 0, fz ], [ end + 40, 0, fz ], [ end + 40, top, fz ], [ o.start - 6, top, fz ], 'url(#facade)' );
	s += L.quad( pr, [ o.start - 6, top - 0.55, fz - 0.25 ], [ end + 40, top - 0.55, fz - 0.25 ], [ end + 40, top + 0.15, fz - 0.25 ], [ o.start - 6, top + 0.15, fz - 0.25 ], o.fascia );
	s += L.quad( pr, [ o.start - 6, top - 0.55, fz - 0.25 ], [ end + 40, top - 0.55, fz - 0.25 ], [ end + 40, top - 0.55, fz ], [ o.start - 6, top - 0.55, fz ], L.shade( o.fascia, -0.3 ) );

	// Sign with the three-bar mark.
	const sx = o.start + 3.4;
	s += L.quad( pr, [ sx, top - 0.45, fz - 0.26 ], [ sx + 3.2, top - 0.45, fz - 0.26 ], [ sx + 3.2, top + 0.05, fz - 0.26 ], [ sx, top + 0.05, fz - 0.26 ], L.shade( o.fascia, 0.12 ) );
	[ 0, 1, 2 ].forEach( ( i ) => {
		const bx = sx + 0.25 + i * 0.16;
		s += L.quad( pr, [ bx, top - 0.35, fz - 0.27 ], [ bx + 0.1, top - 0.35, fz - 0.27 ], [ bx + 0.1, top - 0.05, fz - 0.27 ], [ bx, top - 0.05, fz - 0.27 ], '#F2B705' );
	} );
	s += L.quad( pr, [ sx + 0.85, top - 0.26, fz - 0.27 ], [ sx + 2.9, top - 0.26, fz - 0.27 ], [ sx + 2.9, top - 0.14, fz - 0.27 ], [ sx + 0.85, top - 0.14, fz - 0.27 ], '#ffffff', 'opacity=".9"' );

	// Doors.
	for ( let i = 0; i < o.count; i++ ) {
		const x0 = o.start + i * o.pitch;
		const x1 = x0 + o.doorW;
		const color = o.doors[ i % o.doors.length ];
		s += L.quad( pr, [ x0 - 0.12, 0, fz - 0.01 ], [ x1 + 0.12, 0, fz - 0.01 ], [ x1 + 0.12, doorH + 0.25, fz - 0.01 ], [ x0 - 0.12, doorH + 0.25, fz - 0.01 ], '#3B4449' );
		s += L.quad( pr, [ x0, 0, fz - 0.02 ], [ x1, 0, fz - 0.02 ], [ x1, doorH, fz - 0.02 ], [ x0, doorH, fz - 0.02 ], color );
		s += L.quad( pr, [ x0, 0, fz - 0.03 ], [ x1, 0, fz - 0.03 ], [ x1, doorH, fz - 0.03 ], [ x0, doorH, fz - 0.03 ], 'url(#doorShade)' );
		for ( let y = 0.14; y < doorH - 0.04; y += 0.15 ) {
			s += L.seg( pr, [ x0, y, fz - 0.035 ], [ x1, y, fz - 0.035 ], L.shade( color, -0.3 ), 1.3, 'opacity=".55"' );
		}
		s += L.quad( pr, [ x0 + o.doorW * 0.42, 0.16, fz - 0.04 ], [ x0 + o.doorW * 0.58, 0.16, fz - 0.04 ], [ x0 + o.doorW * 0.58, 0.24, fz - 0.04 ], [ x0 + o.doorW * 0.42, 0.24, fz - 0.04 ], '#2A3136' );
		// Number plate.
		s += L.quad( pr, [ x0 + 0.2, doorH + 0.32, fz - 0.02 ], [ x0 + 0.75, doorH + 0.32, fz - 0.02 ], [ x0 + 0.75, doorH + 0.52, fz - 0.02 ], [ x0 + 0.2, doorH + 0.52, fz - 0.02 ], '#ffffff' );
		// Light above every second door.
		if ( i % 2 === 0 ) {
			s += L.quad( pr, [ x0 + 1.1, doorH + 0.35, fz - 0.05 ], [ x0 + 1.4, doorH + 0.35, fz - 0.05 ], [ x0 + 1.4, doorH + 0.5, fz - 0.05 ], [ x0 + 1.1, doorH + 0.5, fz - 0.05 ], '#2A3136' );
		}
		// Bollard between doors.
		const b = x1 + ( o.pitch - o.doorW ) / 2;
		if ( i % 2 === 1 && b < end ) {
			s += L.cuboid( pr, [ b - 0.08, 0, fz - 0.9 ], [ b + 0.08, 0.9, fz - 0.74 ], { front: '#F2B705', top: '#F7CF4D', side: '#C99700' }, eye );
			s += L.quad( pr, [ b - 0.08, 0.62, fz - 0.905 ], [ b + 0.08, 0.62, fz - 0.905 ], [ b + 0.08, 0.72, fz - 0.905 ], [ b - 0.08, 0.72, fz - 0.905 ], '#1f272b' );
		}
	}

	// Shadow along the base of the facade.
	s += L.quad( pr, [ o.start - 6, 0, fz - 0.6 ], [ end + 40, 0, fz - 0.6 ], [ end + 40, 0, fz ], [ o.start - 6, 0, fz ], '#0d1d26', 'opacity=".12"' );

	if ( o.van ) {
		s += van( pr, eye, o.vanX || 4.2, fz - 4.9 );
	}

	return L.svg( s, defs );
};

/**
 * A small delivery van parked parallel to the building, cab to the left.
 */
function van( pr, eye, x0, z0 ) {
	const len = 5.2;
	const wid = 2.0;
	const x1 = x0 + len;
	const z1 = z0 + wid;
	const body = '#F2F4F5';
	const side = '#D3D9DC';
	let s = '';
	// Shadow.
	s += L.quad( pr, [ x0 - 0.3, 0, z0 - 0.3 ], [ x1 + 0.5, 0, z0 - 0.3 ], [ x1 + 0.5, 0, z1 + 0.4 ], [ x0 - 0.3, 0, z1 + 0.4 ], '#0d1d26', 'opacity=".38" filter="url(#blur10)"' );
	// Cargo box.
	s += L.cuboid( pr, [ x0 + 1.55, 0.42, z0 ], [ x1, 2.6, z1 ], { front: body, top: '#FFFFFF', side }, eye );
	// Bonnet and cab.
	s += L.cuboid( pr, [ x0, 0.42, z0 + 0.04 ], [ x0 + 1.55, 1.18, z1 - 0.04 ], { front: body, top: '#FAFBFB', side }, eye );
	s += L.cuboid( pr, [ x0 + 0.62, 1.18, z0 + 0.04 ], [ x0 + 1.55, 2.12, z1 - 0.04 ], { front: body, top: '#FFFFFF', side }, eye );
	// Sloped windscreen and the side panel below it.
	s += L.quad( pr, [ x0 + 0.02, 1.18, z0 + 0.1 ], [ x0 + 0.62, 2.1, z0 + 0.1 ], [ x0 + 0.62, 2.1, z1 - 0.1 ], [ x0 + 0.02, 1.18, z1 - 0.1 ], '#233B49' );
	s += L.poly( [ pr( x0, 1.18, z0 + 0.04 ), pr( x0 + 0.62, 2.12, z0 + 0.04 ), pr( x0 + 0.62, 1.18, z0 + 0.04 ) ], body );
	s += L.poly( [ pr( x0 + 0.12, 1.28, z0 + 0.035 ), pr( x0 + 0.6, 2.0, z0 + 0.035 ), pr( x0 + 1.4, 2.0, z0 + 0.035 ), pr( x0 + 1.4, 1.28, z0 + 0.035 ) ], '#2E4A59' );
	// Headlight and bumper.
	s += L.quad( pr, [ x0 - 0.005, 0.85, z0 + 0.12 ], [ x0 - 0.005, 1.02, z0 + 0.12 ], [ x0 - 0.005, 1.02, z0 + 0.42 ], [ x0 - 0.005, 0.85, z0 + 0.42 ], '#FFF6D8' );
	s += L.cuboid( pr, [ x0 - 0.08, 0.36, z0 ], [ x0 + 0.1, 0.6, z1 ], { front: '#2A3136', top: '#3a444a', side: '#20272b' }, eye );
	// Brand stripe on the cargo box.
	s += L.quad( pr, [ x0 + 1.7, 1.05, z0 - 0.003 ], [ x1 - 0.15, 1.05, z0 - 0.003 ], [ x1 - 0.15, 1.34, z0 - 0.003 ], [ x0 + 1.7, 1.34, z0 - 0.003 ], '#16303F' );
	s += L.quad( pr, [ x0 + 1.7, 1.34, z0 - 0.003 ], [ x1 - 0.15, 1.34, z0 - 0.003 ], [ x1 - 0.15, 1.41, z0 - 0.003 ], [ x0 + 1.7, 1.41, z0 - 0.003 ], '#F2B705' );
	// Wheels with arches.
	[ x0 + 0.95, x1 - 1.0 ].forEach( ( wx ) => {
		const c = pr( wx, 0.38, z0 - 0.01 );
		const e = pr( wx + 0.38, 0.38, z0 - 0.01 );
		const r = Math.abs( e[ 0 ] - c[ 0 ] );
		s += L.ellipse( c[ 0 ], c[ 1 ] - r * 0.05, r * 1.18, r * 1.2, '#2A3136' );
		s += L.ellipse( c[ 0 ], c[ 1 ], r * 0.95, r, '#15191c' );
		s += L.ellipse( c[ 0 ], c[ 1 ], r * 0.5, r * 0.52, '#A7B1B6' );
		s += L.ellipse( c[ 0 ], c[ 1 ], r * 0.16, r * 0.17, '#4b555b' );
	} );
	return s;
}

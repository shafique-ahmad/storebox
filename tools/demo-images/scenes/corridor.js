/**
 * Interior corridor of roller-door units, one-point perspective.
 */
'use strict';

const L = require( '../lib' );

/**
 * @param {Object} opt Scene options.
 * @return {string} SVG.
 */
module.exports = function corridor( opt = {} ) {
	const o = Object.assign(
		{
			w: 2.1, // Half width.
			eye: 1.5,
			ceil: 3.2,
			zn: 0.6,
			zf: 36,
			doorW: 1.9,
			gap: 0.55,
			doorH: 2.4,
			doors: [ '#2C5871', '#2C5871', '#2C5871', '#F2B705' ],
			frame: '#3B4449',
			wall: '#E6EAEC',
			floor: '#A9B4BA',
			ceiling: '#F3F5F6',
			fog: '#E3E9EC',
			end: '#FFFFFF',
			f: 980,
			cx: L.W * 0.56,
			cy: L.H * 0.47,
			trolley: true,
			lines: '#F2B705',
			start: 1.1,
			seed: 1,
		},
		opt
	);

	const pr = L.camera( { f: o.f, cx: o.cx, cy: o.cy } );
	const yF = -o.eye;
	const yC = o.ceil - o.eye;
	const fog = ( z, strength = 0.8 ) => Math.pow( Math.min( 1, Math.max( 0, ( z - o.zn ) / ( o.zf - o.zn ) ) ), 0.7 ) * strength;
	let rnd = o.seed;
	const random = () => {
		rnd = ( rnd * 9301 + 49297 ) % 233280;
		return rnd / 233280;
	};

	const defs = [
		L.linear( 'ceil', [ [ 0, L.shade( o.ceiling, -0.06 ) ], [ 1, o.fog ] ] ),
		L.linear( 'floor', [ [ 0, L.shade( o.floor, -0.18 ) ], [ 0.55, o.floor ], [ 1, L.mix( o.floor, o.fog, 0.6 ) ] ], 0, 1, 0, 0 ),
		L.linear( 'wallL', [ [ 0, L.shade( o.wall, -0.1 ) ], [ 1, o.fog ] ], 0, 0, 1, 0 ),
		L.linear( 'wallR', [ [ 0, o.fog ], [ 1, L.shade( o.wall, -0.14 ) ] ], 0, 0, 1, 0 ),
		L.radial( 'endGlow', [ [ 0, '#ffffff', 1 ], [ 1, '#ffffff', 0 ] ] ),
		L.linear( 'reflFade', [ [ 0, '#fff', 0.34 ], [ 1, '#fff', 0 ] ] ),
		'<mask id="floorMask"><rect width="100%" height="100%" fill="url(#reflMaskGrad)"/></mask>',
		L.linear( 'reflMaskGrad', [ [ 0, '#000' ], [ 0.52, '#000' ], [ 0.6, '#555' ], [ 1, '#111' ] ] ),
		L.radial( 'pool', [ [ 0, '#ffffff', 0.55 ], [ 1, '#ffffff', 0 ] ] ),
	].join( '' );

	let s = '';

	// Shell.
	s += L.quad( pr, [ -o.w, yC, o.zn ], [ o.w, yC, o.zn ], [ o.w, yC, o.zf ], [ -o.w, yC, o.zf ], 'url(#ceil)' );
	s += L.quad( pr, [ -o.w, yF, o.zn ], [ o.w, yF, o.zn ], [ o.w, yF, o.zf ], [ -o.w, yF, o.zf ], 'url(#floor)' );
	s += L.quad( pr, [ -o.w, yF, o.zn ], [ -o.w, yC, o.zn ], [ -o.w, yC, o.zf ], [ -o.w, yF, o.zf ], 'url(#wallL)' );
	s += L.quad( pr, [ o.w, yF, o.zn ], [ o.w, yC, o.zn ], [ o.w, yC, o.zf ], [ o.w, yF, o.zf ], 'url(#wallR)' );

	// End wall with a bright doorway.
	s += L.quad( pr, [ -o.w, yF, o.zf ], [ o.w, yF, o.zf ], [ o.w, yC, o.zf ], [ -o.w, yC, o.zf ], L.mix( o.wall, o.fog, 0.8 ) );
	s += L.quad( pr, [ -0.9, yF, o.zf - 0.01 ], [ 0.9, yF, o.zf - 0.01 ], [ 0.9, yF + 2.5, o.zf - 0.01 ], [ -0.9, yF + 2.5, o.zf - 0.01 ], o.end );
	const glowA = pr( 0, yF + 1.2, o.zf );
	s += L.ellipse( glowA[ 0 ], glowA[ 1 ], 170, 150, 'url(#endGlow)', 'opacity=".9"' );

	// Doors, far to near, with reflections on the floor.
	const doorsSvg = [];
	const reflSvg = [];
	[ -1, 1 ].forEach( ( side ) => {
		const x = side * ( o.w - 0.005 );
		const list = [];
		for ( let z = o.start; z + o.doorW < o.zf - 1; z += o.doorW + o.gap ) {
			list.push( z );
		}
		list.reverse().forEach( ( z0, index ) => {
			const z1 = z0 + o.doorW;
			const zm = ( z0 + z1 ) / 2;
			const t = fog( zm );
			const pick = o.doors[ Math.floor( random() * o.doors.length ) ];
			const color = L.mix( pick, o.fog, t );
			const frame = L.mix( o.frame, o.fog, t );
			const top = yF + o.doorH;
			let d = '';
			// Frame and housing.
			d += L.quad( pr, [ x, yF, z0 - 0.08 ], [ x, top + 0.2, z0 - 0.08 ], [ x, top + 0.2, z1 + 0.08 ], [ x, yF, z1 + 0.08 ], frame );
			d += L.quad( pr, [ x, yF, z0 ], [ x, top, z0 ], [ x, top, z1 ], [ x, yF, z1 ], color );
			// Slats.
			const scale = o.f / zm;
			for ( let y = yF + 0.14; y < top - 0.05; y += 0.15 ) {
				d += L.line( pr( x, y, z0 ), pr( x, y, z1 ), L.shade( color, -0.22 ), Math.max( 0.6, scale * 0.012 ), 'opacity=".55"' );
				d += L.line( pr( x, y + 0.02, z0 ), pr( x, y + 0.02, z1 ), L.shade( color, 0.18 ), Math.max( 0.4, scale * 0.006 ), 'opacity=".45"' );
			}
			// Handle and number plate.
			d += L.quad( pr, [ x, yF + 0.16, zm - 0.18 ], [ x, yF + 0.24, zm - 0.18 ], [ x, yF + 0.24, zm + 0.18 ], [ x, yF + 0.16, zm + 0.18 ], L.mix( '#2A3136', o.fog, t ) );
			d += L.quad( pr, [ x, top + 0.26, z0 + 0.25 ], [ x, top + 0.46, z0 + 0.25 ], [ x, top + 0.46, z0 + 0.85 ], [ x, top + 0.26, z0 + 0.85 ], L.mix( '#ffffff', o.fog, t * 0.6 ) );
			d += L.quad( pr, [ x, top + 0.33, z0 + 0.35 ], [ x, top + 0.39, z0 + 0.35 ], [ x, top + 0.39, z0 + 0.7 ], [ x, top + 0.33, z0 + 0.7 ], L.mix( '#16303F', o.fog, t ) );
			doorsSvg.push( d );

			// Reflection: the door mirrored in the polished floor.
			const ry = ( y ) => yF - ( y - yF ) * 0.55;
			reflSvg.push( L.quad( pr, [ x, yF, z0 ], [ x, ry( top ), z0 ], [ x, ry( top ), z1 ], [ x, yF, z1 ], color, 'opacity=".22"' ) );
			void index;
		} );
	} );

	s += `<g mask="url(#floorMask)" filter="url(#blur4)">${ reflSvg.join( '' ) }</g>`;

	// Safety lines on the floor.
	[ -1, 1 ].forEach( ( side ) => {
		const x0 = side * ( o.w - 0.32 );
		const x1 = side * ( o.w - 0.26 );
		s += L.quad( pr, [ x0, yF, o.zn ], [ x1, yF, o.zn ], [ x1, yF, o.zf ], [ x0, yF, o.zf ], o.lines, 'opacity=".85"' );
	} );

	s += doorsSvg.join( '' );

	// Ceiling lights and the pools of light below them.
	for ( let z = 2.5; z < o.zf - 1; z += 3.4 ) {
		const t = fog( z, 0.5 );
		s += L.quad( pr, [ -0.22, yC - 0.02, z ], [ 0.22, yC - 0.02, z ], [ 0.22, yC - 0.02, z + 1.5 ], [ -0.22, yC - 0.02, z + 1.5 ], L.mix( '#ffffff', o.fog, t ) );
		const a = pr( 0, yC - 0.02, z + 0.75 );
		s += L.ellipse( a[ 0 ], a[ 1 ], ( 0.9 * o.f ) / z, ( 0.22 * o.f ) / z, '#ffffff', 'opacity=".5" filter="url(#blur10)"' );
		const p = pr( 0, yF, z + 0.75 );
		s += L.ellipse( p[ 0 ], p[ 1 ], ( 1.4 * o.f ) / ( z + 0.75 ), ( 0.5 * o.f ) / ( z + 0.75 ), 'url(#pool)', 'opacity=".55"' );
	}

	// Hand truck with boxes, right foreground.
	if ( o.trolley ) {
		s += trolley( o );
	}

	return L.svg( s, defs );
};

/**
 * A hand truck loaded with three boxes (drawn in screen space).
 */
function trolley( o ) {
	const bx = o.trolleyX || L.W * 0.74;
	const by = o.trolleyY || L.H * 0.97;
	const k = o.trolleyScale || 1;
	const X = ( v ) => bx + v * k;
	const Y = ( v ) => by - v * k;
	let s = '';

	s += L.ellipse( X( 60 ), Y( -6 ), 250 * k, 34 * k, '#0d1d26', 'opacity=".35" filter="url(#blur24)"' );

	const boxes = [
		{ x: -64, y: 36, w: 230, h: 170, d: 58, c: '#C8965C' },
		{ x: -60, y: 206, w: 200, h: 150, d: 52, c: '#BF8C52' },
		{ x: -56, y: 356, w: 160, h: 118, d: 46, c: '#CFA06A' },
	];

	// Rails behind the boxes.
	s += `<path d="M${ X( -70 ) } ${ Y( 10 ) } L${ X( -58 ) } ${ Y( 560 ) } Q${ X( -54 ) } ${ Y( 610 ) } ${ X( -10 ) } ${ Y( 612 ) } L${ X( 40 ) } ${ Y( 610 ) }" fill="none" stroke="#1f272b" stroke-width="${ 18 * k }" stroke-linecap="round" stroke-linejoin="round"/>`;
	s += `<path d="M${ X( -66 ) } ${ Y( 10 ) } L${ X( -55 ) } ${ Y( 556 ) }" fill="none" stroke="#56626a" stroke-width="${ 5 * k }" stroke-linecap="round"/>`;

	boxes.forEach( ( b ) => {
		const x0 = X( b.x );
		const y0 = Y( b.y + b.h );
		const w = b.w * k;
		const h = b.h * k;
		const d = b.d * k;
		s += `<polygon points="${ x0 },${ y0 } ${ x0 + d },${ y0 - d * 0.55 } ${ x0 + w + d },${ y0 - d * 0.55 } ${ x0 + w },${ y0 }" fill="${ L.shade( b.c, 0.16 ) }"/>`;
		s += `<polygon points="${ x0 + w },${ y0 } ${ x0 + w + d },${ y0 - d * 0.55 } ${ x0 + w + d },${ y0 + h - d * 0.55 } ${ x0 + w },${ y0 + h }" fill="${ L.shade( b.c, -0.2 ) }"/>`;
		s += L.rect( x0, y0, w, h, b.c );
		s += L.rect( x0 + w * 0.44, y0, w * 0.12, h * 0.34, L.shade( b.c, 0.3 ) );
		s += `<polygon points="${ x0 + w * 0.44 },${ y0 } ${ x0 + w * 0.44 + d },${ y0 - d * 0.55 } ${ x0 + w * 0.56 + d },${ y0 - d * 0.55 } ${ x0 + w * 0.56 },${ y0 }" fill="${ L.shade( b.c, 0.38 ) }"/>`;
		s += L.rect( x0 + w * 0.1, y0 + h * 0.52, w * 0.26, h * 0.2, '#f7f4ee', 'rx="2"' );
		s += L.rect( x0 + w * 0.13, y0 + h * 0.6, w * 0.18, h * 0.035, '#9aa3a8' );
	} );

	// Toe plate and wheels.
	s += `<path d="M${ X( -76 ) } ${ Y( 16 ) } L${ X( 200 ) } ${ Y( 16 ) }" stroke="#1f272b" stroke-width="${ 14 * k }" stroke-linecap="round"/>`;
	[ -60, 150 ].forEach( ( wx ) => {
		s += L.ellipse( X( wx ), Y( -8 ), 40 * k, 40 * k, '#15191c' );
		s += L.ellipse( X( wx ), Y( -8 ), 17 * k, 17 * k, '#8b979e' );
		s += L.ellipse( X( wx ), Y( -8 ), 6 * k, 6 * k, '#2b3237' );
	} );

	return s;
}

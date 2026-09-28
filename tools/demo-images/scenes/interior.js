/**
 * Inside a storage unit: shelving with boxes and bins. With `outside`, the
 * camera stands in the corridor and looks through the open roller door.
 */
'use strict';

const L = require( '../lib' );

module.exports = function interior( opt = {} ) {
	const o = Object.assign(
		{
			outside: false,
			w: 2.3,
			eye: 1.5,
			ceil: 2.9,
			depth: 4.6,
			f: 900,
			cx: L.W * 0.5,
			cy: L.H * 0.5,
			wall: '#DCE2E5',
			floor: '#B7C0C5',
			door: '#2C5871',
			corridorWall: '#E8ECEE',
			seed: 3,
		},
		opt
	);

	const camZ = o.outside ? -3.4 : -1.25;
	const eye = { x: o.outside ? 0.35 : 0.2, y: 0, z: camZ };
	const pr = L.camera( { f: o.f, cx: o.cx, cy: o.cy, x: eye.x, z: camZ } );
	const yF = -o.eye;
	const yC = o.ceil - o.eye;
	const zb = o.depth;
	let rnd = o.seed;
	const random = () => {
		rnd = ( rnd * 9301 + 49297 ) % 233280;
		return rnd / 233280;
	};

	const defs = [
		L.linear( 'iwallL', [ [ 0, L.shade( o.wall, -0.2 ) ], [ 1, L.shade( o.wall, -0.04 ) ] ], 0, 0, 1, 0 ),
		L.linear( 'iwallR', [ [ 0, L.shade( o.wall, -0.02 ) ], [ 1, L.shade( o.wall, -0.22 ) ] ], 0, 0, 1, 0 ),
		L.linear( 'iback', [ [ 0, L.shade( o.wall, 0.06 ) ], [ 1, L.shade( o.wall, -0.06 ) ] ] ),
		L.linear( 'ifloor', [ [ 0, L.shade( o.floor, -0.05 ) ], [ 1, L.shade( o.floor, -0.25 ) ] ] ),
		L.linear( 'iceil', [ [ 0, L.shade( o.wall, -0.18 ) ], [ 1, L.shade( o.wall, -0.02 ) ] ] ),
		L.linear( 'cfloor', [ [ 0, '#C4CCD0' ], [ 1, '#9FAAB0' ] ] ),
		L.radial( 'ilight', [ [ 0, '#ffffff', 0.7 ], [ 1, '#ffffff', 0 ] ] ),
		L.linear( 'binBlue', [ [ 0, '#3E6F8B' ], [ 1, '#2C5871' ] ] ),
	].join( '' );

	let s = '';

	// Room shell.
	s += L.quad( pr, [ -o.w, yC, 0 ], [ o.w, yC, 0 ], [ o.w, yC, zb ], [ -o.w, yC, zb ], 'url(#iceil)' );
	s += L.quad( pr, [ -o.w, yF, 0 ], [ o.w, yF, 0 ], [ o.w, yF, zb ], [ -o.w, yF, zb ], 'url(#ifloor)' );
	s += L.quad( pr, [ -o.w, yF, 0 ], [ -o.w, yC, 0 ], [ -o.w, yC, zb ], [ -o.w, yF, zb ], 'url(#iwallL)' );
	s += L.quad( pr, [ o.w, yF, 0 ], [ o.w, yC, 0 ], [ o.w, yC, zb ], [ o.w, yF, zb ], 'url(#iwallR)' );
	s += L.quad( pr, [ -o.w, yF, zb ], [ o.w, yF, zb ], [ o.w, yC, zb ], [ -o.w, yC, zb ], 'url(#iback)' );

	// Corrugated wall ribs.
	for ( let z = 0.3; z < zb; z += 0.32 ) {
		[ -1, 1 ].forEach( ( side ) => {
			s += L.line( pr( side * o.w, yF, z ), pr( side * o.w, yC, z ), L.shade( o.wall, -0.24 ), 1.2, 'opacity=".5"' );
		} );
	}
	for ( let x = -o.w + 0.3; x < o.w; x += 0.32 ) {
		s += L.line( pr( x, yF, zb ), pr( x, yC, zb ), L.shade( o.wall, -0.16 ), 1, 'opacity=".45"' );
	}

	// Ceiling light.
	s += L.quad( pr, [ -0.5, yC - 0.01, 1.6 ], [ 0.5, yC - 0.01, 1.6 ], [ 0.5, yC - 0.01, 2.0 ], [ -0.5, yC - 0.01, 2.0 ], '#ffffff' );
	const lp = pr( 0, yC - 0.05, 1.8 );
	s += L.ellipse( lp[ 0 ], lp[ 1 ] + 40, 520, 260, 'url(#ilight)', 'opacity=".6"' );

	// Shelving along the back wall: two bays.
	const shelfZ0 = zb - 0.55;
	const shelfZ1 = zb - 0.05;
	const edge = o.w - 0.2;
	const posts = [ -edge, -0.15, 0.15, edge ];
	const levels = [ yF + 0.12, yF + 0.95, yF + 1.8 ];
	const items = [];

	posts.forEach( ( x ) => {
		items.push( { z: shelfZ0, draw: () => L.cuboid( pr, [ x - 0.03, yF, shelfZ0 ], [ x + 0.03, yF + 2.4, shelfZ0 + 0.04 ], { front: '#5d6a72', top: '#7c8a92', side: '#4a555c' }, eye ) } );
	} );

	levels.forEach( ( y, li ) => {
		[ [ -edge, -0.15 ], [ 0.15, edge ] ].forEach( ( bay ) => {
			items.push( { z: shelfZ0 + 0.001, draw: () => L.cuboid( pr, [ bay[ 0 ], y - 0.05, shelfZ0 ], [ bay[ 1 ], y, shelfZ1 ], { front: '#6f7c84', top: '#a4b0b6', side: '#56626a' }, eye ) } );
			// Boxes and bins on the shelf.
			let x = bay[ 0 ] + 0.06;
			while ( x < bay[ 1 ] - 0.3 ) {
				const bw = 0.38 + random() * 0.32;
				if ( x + bw > bay[ 1 ] - 0.04 ) {
					break;
				}
				const bh = li === 2 ? 0.28 + random() * 0.2 : 0.34 + random() * 0.34;
				const bd = 0.34 + random() * 0.1;
				const isBin = random() < 0.28;
				const x0 = x;
				const z0 = shelfZ1 - bd;
				items.push( {
					z: z0,
					draw: () => ( isBin ? bin( pr, [ x0, y, z0 ], [ x0 + bw, y + bh * 0.8, z0 + bd ], eye ) : L.box( pr, [ x0, y, z0 ], [ x0 + bw, y + bh, z0 + bd ], eye, { color: pickBox( random() ), label: random() < 0.5 ? '#f7f4ee' : '' } ) ),
				} );
				x += bw + 0.05 + random() * 0.06;
			}
		} );
	} );

	// Floor: boxes, a rolled rug and a wrapped chair shape.
	const floorBoxes = [
		[ -2.05, 2.55, 0.7, 0.55, 0.55 ],
		[ -2.0, 3.2, 0.62, 0.5, 0.5 ],
		[ -1.98, 2.55, 0.5, 0.42, 0.45, 0.55 ],
		[ 1.35, 2.7, 0.66, 0.56, 0.56 ],
		[ 1.45, 2.7, 0.46, 0.36, 0.42, 0.56 ],
	];
	floorBoxes.forEach( ( b ) => {
		const [ x0, z0, bw, bh, bd, yOff = 0 ] = b;
		items.push( { z: z0, draw: () => L.box( pr, [ x0, yF + yOff, z0 ], [ x0 + bw, yF + yOff + bh, z0 + bd ], eye, { color: pickBox( random() ), label: '#f7f4ee', arrows: true } ) } );
	} );

	items.sort( ( a, b ) => b.z - a.z ).forEach( ( item ) => {
		s += item.draw();
	} );

	// Rolled rug lying on the floor.
	s += rug( pr, eye, yF );

	// Open door frame and the rolled-up door housing.
	s += L.quad( pr, [ -o.w - 0.12, yF, 0 ], [ -o.w, yF, 0 ], [ -o.w, yC, 0 ], [ -o.w - 0.12, yC, 0 ], '#3B4449' );
	s += L.quad( pr, [ o.w, yF, 0 ], [ o.w + 0.12, yF, 0 ], [ o.w + 0.12, yC, 0 ], [ o.w, yC, 0 ], '#3B4449' );
	s += L.quad( pr, [ -o.w - 0.12, yC - 0.34, 0 ], [ o.w + 0.12, yC - 0.34, 0 ], [ o.w + 0.12, yC + 0.05, 0 ], [ -o.w - 0.12, yC + 0.05, 0 ], L.shade( o.door, -0.1 ) );
	for ( let y = yC - 0.3; y < yC; y += 0.08 ) {
		s += L.line( pr( -o.w, y, -0.001 ), pr( o.w, y, -0.001 ), L.shade( o.door, -0.35 ), 1.4, 'opacity=".6"' );
	}

	if ( o.outside ) {
		s = corridorAround( pr, o, yF, yC ) + s;
	}

	return L.svg( s, defs );
};

function pickBox( r ) {
	const tones = [ '#C8965C', '#BF8C52', '#D1A36D', '#B98349' ];
	return tones[ Math.floor( r * tones.length ) ];
}

/**
 * Plastic storage bin with a lid.
 */
function bin( pr, min, max, eye ) {
	const [ x0, y0, z0 ] = min;
	const [ x1, y1, z1 ] = max;
	let s = L.cuboid( pr, [ x0 + 0.02, y0, z0 + 0.02 ], [ x1 - 0.02, y1, z1 - 0.02 ], { front: 'url(#binBlue)', top: '#4A7D99', side: '#24495D' }, eye );
	s += L.cuboid( pr, [ x0, y1, z0 ], [ x1, y1 + 0.05, z1 ], { front: '#DDE4E8', top: '#F1F4F5', side: '#C3CCD1' }, eye );
	return s;
}

/**
 * A rolled rug lying across the floor, drawn as a cylinder.
 */
function rug( pr, eye, yF ) {
	const r = 0.14;
	const x0 = -0.95;
	const x1 = 0.75;
	const z = 2.45;
	const a = pr( x0, yF + r, z );
	const b = pr( x1, yF + r, z );
	const top = pr( x0, yF + 2 * r, z );
	const ry = Math.abs( a[ 1 ] - top[ 1 ] );
	let s = L.ellipse( ( a[ 0 ] + b[ 0 ] ) / 2, pr( 0, yF, z )[ 1 ] + 6, ( b[ 0 ] - a[ 0 ] ) / 2 + 30, 14, '#0d1d26', 'opacity=".25" filter="url(#blur10)"' );
	s += `<rect x="${ L.fmt( a[ 0 ] ) }" y="${ L.fmt( a[ 1 ] - ry ) }" width="${ L.fmt( b[ 0 ] - a[ 0 ] ) }" height="${ L.fmt( ry * 2 ) }" fill="#9E4B3C"/>`;
	for ( let i = 1; i < 6; i++ ) {
		const x = a[ 0 ] + ( ( b[ 0 ] - a[ 0 ] ) * i ) / 6;
		s += L.line( [ x, a[ 1 ] - ry ], [ x, a[ 1 ] + ry ], '#C9A15A', 5, 'opacity=".6"' );
	}
	s += L.rect( a[ 0 ], a[ 1 ] - ry, b[ 0 ] - a[ 0 ], ry * 0.5, '#ffffff', 'opacity=".12"' );
	s += L.ellipse( b[ 0 ], b[ 1 ], ry * 0.45, ry, '#7E3A2E' );
	s += L.ellipse( b[ 0 ], b[ 1 ], ry * 0.25, ry * 0.55, '#5E2A21' );
	void eye;
	return s;
}

/**
 * The corridor wall around the open unit, with closed neighbours.
 */
function corridorAround( pr, o, yF, yC ) {
	let s = '';
	const wall = o.corridorWall;
	const far = 40;
	// Corridor floor, walls and ceiling, seen from the corridor.
	s += L.quad( pr, [ -12, yF, -6 ], [ 12, yF, -6 ], [ 12, yF, 0 ], [ -12, yF, 0 ], 'url(#cfloor)' );
	s += L.quad( pr, [ -12, yF, 0 ], [ 12, yF, 0 ], [ 12, yC + 0.8, 0 ], [ -12, yC + 0.8, 0 ], wall );
	s += L.quad( pr, [ -12, yC + 0.8, -6 ], [ 12, yC + 0.8, -6 ], [ 12, yC + 0.8, 0 ], [ -12, yC + 0.8, 0 ], L.shade( wall, 0.04 ) );
	// Neighbouring closed doors.
	[ -1, 1 ].forEach( ( side ) => {
		for ( let i = 1; i <= 2; i++ ) {
			const x0 = side > 0 ? o.w + 0.5 + ( i - 1 ) * 5 : -o.w - 0.5 - ( i - 1 ) * 5 - 4.4;
			const x1 = x0 + 4.4;
			s += L.quad( pr, [ x0 - 0.12, yF, -0.001 ], [ x1 + 0.12, yF, -0.001 ], [ x1 + 0.12, yC + 0.05, -0.001 ], [ x0 - 0.12, yC + 0.05, -0.001 ], '#3B4449' );
			s += L.quad( pr, [ x0, yF, -0.002 ], [ x1, yF, -0.002 ], [ x1, yC - 0.05, -0.002 ], [ x0, yC - 0.05, -0.002 ], i === 1 && side > 0 ? '#F2B705' : o.door );
			for ( let y = yF + 0.15; y < yC - 0.08; y += 0.15 ) {
				s += L.line( pr( x0, y, -0.003 ), pr( x1, y, -0.003 ), '#000', 1.2, 'opacity=".18"' );
			}
		}
	} );
	// Floor reflection strip and a safety line.
	s += L.quad( pr, [ -12, yF, -0.45 ], [ 12, yF, -0.45 ], [ 12, yF, -0.39 ], [ -12, yF, -0.39 ], '#F2B705', 'opacity=".85"' );
	void far;
	return s;
}

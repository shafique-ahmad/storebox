/**
 * Drawing helpers for the Storebox demo artwork: colors, a perspective camera
 * and primitives (quads, cuboids, cylinders) that output SVG markup.
 */
'use strict';

const W = 1920;
const H = 1280;

/* ---------------------------------------------------------------- colors */

function toRgb( hex ) {
	const h = hex.replace( '#', '' );
	const n = parseInt( h.length === 3 ? h.split( '' ).map( ( c ) => c + c ).join( '' ) : h, 16 );
	return [ ( n >> 16 ) & 255, ( n >> 8 ) & 255, n & 255 ];
}

function toHex( rgb ) {
	return '#' + rgb.map( ( v ) => Math.max( 0, Math.min( 255, Math.round( v ) ) ).toString( 16 ).padStart( 2, '0' ) ).join( '' );
}

function mix( a, b, t ) {
	const x = toRgb( a );
	const y = toRgb( b );
	return toHex( x.map( ( v, i ) => v + ( y[ i ] - v ) * t ) );
}

function shade( c, amount ) {
	return amount >= 0 ? mix( c, '#ffffff', amount ) : mix( c, '#000000', -amount );
}

/* ---------------------------------------------------------------- camera */

/**
 * Perspective camera. World: X right, Y up, Z forward (metres).
 *
 * @param {Object} o { f, cx, cy, yaw (rad), pitch (rad), x, y, z (position) }
 * @return {Function} ( x, y, z ) => [ sx, sy, depth ]
 */
function camera( o ) {
	const c = Object.assign( { f: 1000, cx: W / 2, cy: H / 2, yaw: 0, pitch: 0, x: 0, y: 0, z: 0 }, o );
	const cy = Math.cos( c.yaw );
	const sy = Math.sin( c.yaw );
	const cp = Math.cos( c.pitch );
	const sp = Math.sin( c.pitch );

	// World point to camera space (no clamping).
	const view = ( x, y, z ) => {
		let px = x - c.x;
		let py = y - c.y;
		let pz = z - c.z;
		// Yaw (turn right is positive).
		const rx = px * cy - pz * sy;
		const rz = px * sy + pz * cy;
		px = rx;
		pz = rz;
		// Pitch (look down is positive).
		const ry = py * cp + pz * sp;
		const rz2 = -py * sp + pz * cp;
		return [ px, ry, rz2 ];
	};
	const project = ( v ) => [ c.cx + ( v[ 0 ] * c.f ) / v[ 2 ], c.cy - ( v[ 1 ] * c.f ) / v[ 2 ], v[ 2 ] ];
	const pr = ( x, y, z ) => {
		const v = view( x, y, z );
		v[ 2 ] = Math.max( 0.05, v[ 2 ] );
		return project( v );
	};
	pr.view = view;
	pr.project = project;
	pr.near = 0.15;
	return pr;
}

/**
 * Clips a camera-space polygon against the near plane.
 */
function clipNear( views, near ) {
	const out = [];
	for ( let i = 0; i < views.length; i++ ) {
		const a = views[ i ];
		const b = views[ ( i + 1 ) % views.length ];
		const ain = a[ 2 ] >= near;
		const bin = b[ 2 ] >= near;
		if ( ain ) {
			out.push( a );
		}
		if ( ain !== bin ) {
			const t = ( near - a[ 2 ] ) / ( b[ 2 ] - a[ 2 ] );
			out.push( [ a[ 0 ] + ( b[ 0 ] - a[ 0 ] ) * t, a[ 1 ] + ( b[ 1 ] - a[ 1 ] ) * t, near ] );
		}
	}
	return out;
}

/* ------------------------------------------------------------ primitives */

const fmt = ( n ) => ( Math.round( n * 10 ) / 10 ).toString();

function pts( list ) {
	return list.map( ( p ) => fmt( p[ 0 ] ) + ',' + fmt( p[ 1 ] ) ).join( ' ' );
}

function poly( list, fill, extra = '' ) {
	return `<polygon points="${ pts( list ) }" fill="${ fill }"${ extra ? ' ' + extra : '' }/>`;
}

function line( a, b, stroke, width, extra = '' ) {
	return `<line x1="${ fmt( a[ 0 ] ) }" y1="${ fmt( a[ 1 ] ) }" x2="${ fmt( b[ 0 ] ) }" y2="${ fmt( b[ 1 ] ) }" stroke="${ stroke }" stroke-width="${ fmt( width ) }"${ extra ? ' ' + extra : '' }/>`;
}

function rect( x, y, w, h, fill, extra = '' ) {
	return `<rect x="${ fmt( x ) }" y="${ fmt( y ) }" width="${ fmt( w ) }" height="${ fmt( h ) }" fill="${ fill }"${ extra ? ' ' + extra : '' }/>`;
}

function ellipse( cx, cy, rx, ry, fill, extra = '' ) {
	return `<ellipse cx="${ fmt( cx ) }" cy="${ fmt( cy ) }" rx="${ fmt( rx ) }" ry="${ fmt( ry ) }" fill="${ fill }"${ extra ? ' ' + extra : '' }/>`;
}

/**
 * Quad from four world points.
 */
function quad( pr, a, b, c, d, fill, extra = '' ) {
	if ( pr.view ) {
		const v = clipNear( [ a, b, c, d ].map( ( p ) => pr.view( ...p ) ), pr.near );
		return v.length < 3 ? '' : poly( v.map( pr.project ), fill, extra );
	}
	return poly( [ pr( ...a ), pr( ...b ), pr( ...c ), pr( ...d ) ], fill, extra );
}

/**
 * Line between two world points, clipped at the near plane.
 */
function seg( pr, a, b, stroke, width, extra = '' ) {
	let va = pr.view( ...a );
	let vb = pr.view( ...b );
	if ( va[ 2 ] < pr.near && vb[ 2 ] < pr.near ) {
		return '';
	}
	if ( va[ 2 ] < pr.near || vb[ 2 ] < pr.near ) {
		const t = ( pr.near - va[ 2 ] ) / ( vb[ 2 ] - va[ 2 ] );
		const cut = [ va[ 0 ] + ( vb[ 0 ] - va[ 0 ] ) * t, va[ 1 ] + ( vb[ 1 ] - va[ 1 ] ) * t, pr.near ];
		if ( va[ 2 ] < pr.near ) {
			va = cut;
		} else {
			vb = cut;
		}
	}
	return line( pr.project( va ), pr.project( vb ), stroke, width, extra );
}

/**
 * Axis-aligned cuboid; draws the faces the camera can see.
 *
 * @param {Function} pr    Camera.
 * @param {number[]} min   [x0, y0, z0].
 * @param {number[]} max   [x1, y1, z1].
 * @param {Object}   c     { front, top, side, bottom } colors.
 * @param {Object}   eye   Camera position { x, y, z }.
 * @return {string}
 */
function cuboid( pr, min, max, c, eye = { x: 0, y: 0, z: 0 } ) {
	const [ x0, y0, z0 ] = min;
	const [ x1, y1, z1 ] = max;
	let s = '';
	if ( eye.y > y1 ) {
		s += quad( pr, [ x0, y1, z0 ], [ x1, y1, z0 ], [ x1, y1, z1 ], [ x0, y1, z1 ], c.top );
	}
	if ( eye.y < y0 && c.bottom ) {
		s += quad( pr, [ x0, y0, z0 ], [ x1, y0, z0 ], [ x1, y0, z1 ], [ x0, y0, z1 ], c.bottom );
	}
	if ( eye.x < x0 ) {
		s += quad( pr, [ x0, y0, z0 ], [ x0, y1, z0 ], [ x0, y1, z1 ], [ x0, y0, z1 ], c.side );
	}
	if ( eye.x > x1 ) {
		s += quad( pr, [ x1, y0, z0 ], [ x1, y1, z0 ], [ x1, y1, z1 ], [ x1, y0, z1 ], c.side );
	}
	if ( eye.z < z0 ) {
		s += quad( pr, [ x0, y0, z0 ], [ x1, y0, z0 ], [ x1, y1, z0 ], [ x0, y1, z0 ], c.front );
	}
	return s;
}

/**
 * Cardboard box: cuboid plus tape and an optional label on the front.
 */
function box( pr, min, max, eye, o = {} ) {
	const base = o.color || '#C8965C';
	const c = {
		front: base,
		top: shade( base, 0.14 ),
		side: shade( base, -0.16 ),
	};
	let s = cuboid( pr, min, max, c, eye );
	const [ x0, y0, z0 ] = min;
	const [ x1, y1, z1 ] = max;
	const mx = ( x0 + x1 ) / 2;
	const tw = Math.min( 0.07, ( x1 - x0 ) * 0.12 );
	const tape = o.tape || shade( base, 0.28 );
	if ( eye.y > y1 ) {
		s += quad( pr, [ mx - tw, y1, z0 ], [ mx + tw, y1, z0 ], [ mx + tw, y1, z1 ], [ mx - tw, y1, z1 ], tape );
	}
	if ( eye.z < z0 ) {
		const th = Math.min( 0.16, ( y1 - y0 ) * 0.3 );
		s += quad( pr, [ mx - tw, y1 - th, z0 ], [ mx + tw, y1 - th, z0 ], [ mx + tw, y1, z0 ], [ mx - tw, y1, z0 ], tape );
		if ( o.label ) {
			const lw = Math.min( 0.22, ( x1 - x0 ) * 0.3 );
			const lh = Math.min( 0.12, ( y1 - y0 ) * 0.22 );
			const lx = x0 + ( x1 - x0 ) * 0.18;
			const ly = y0 + ( y1 - y0 ) * 0.28;
			s += quad( pr, [ lx, ly, z0 - 0.001 ], [ lx + lw, ly, z0 - 0.001 ], [ lx + lw, ly + lh, z0 - 0.001 ], [ lx, ly + lh, z0 - 0.001 ], o.label );
			s += quad( pr, [ lx + lw * 0.15, ly + lh * 0.55, z0 - 0.002 ], [ lx + lw * 0.8, ly + lh * 0.55, z0 - 0.002 ], [ lx + lw * 0.8, ly + lh * 0.68, z0 - 0.002 ], [ lx + lw * 0.15, ly + lh * 0.68, z0 - 0.002 ], '#9aa3a8' );
		}
		if ( o.arrows ) {
			const ax = x1 - ( x1 - x0 ) * 0.2;
			const ay = y0 + ( y1 - y0 ) * 0.62;
			const a = pr( ax, ay, z0 - 0.001 );
			const b = pr( ax, ay + ( y1 - y0 ) * 0.2, z0 - 0.001 );
			s += line( a, b, shade( base, -0.4 ), Math.max( 1.5, ( b[ 1 ] - a[ 1 ] ) * -0.08 ), 'stroke-linecap="round" opacity=".55"' );
		}
	}
	return s;
}

/* ------------------------------------------------------------ document */

function defs( extra = '' ) {
	return `<defs>
		<filter id="grain" x="0" y="0" width="100%" height="100%">
			<feTurbulence type="fractalNoise" baseFrequency=".85" numOctaves="2" seed="7" stitchTiles="stitch" result="n"/>
			<feColorMatrix type="saturate" values="0"/>
			<feComponentTransfer><feFuncA type="table" tableValues="0 .07"/></feComponentTransfer>
		</filter>
		<filter id="blur4"><feGaussianBlur stdDeviation="4"/></filter>
		<filter id="blur10"><feGaussianBlur stdDeviation="10"/></filter>
		<filter id="blur24"><feGaussianBlur stdDeviation="24"/></filter>
		<filter id="blur60"><feGaussianBlur stdDeviation="60"/></filter>
		<radialGradient id="vignette" cx="50%" cy="48%" r="75%">
			<stop offset="55%" stop-color="#000" stop-opacity="0"/>
			<stop offset="100%" stop-color="#000" stop-opacity=".32"/>
		</radialGradient>
		${ extra }
	</defs>`;
}

function svg( body, extraDefs = '', o = {} ) {
	const vignette = o.vignette === false ? '' : `<rect width="${ W }" height="${ H }" fill="url(#vignette)"/>`;
	return `<svg xmlns="http://www.w3.org/2000/svg" width="${ W }" height="${ H }" viewBox="0 0 ${ W } ${ H }">${ defs( extraDefs ) }${ body }${ vignette }<rect width="${ W }" height="${ H }" filter="url(#grain)" style="mix-blend-mode:multiply"/></svg>`;
}

function linear( id, stops, x1 = 0, y1 = 0, x2 = 0, y2 = 1, units = 'objectBoundingBox' ) {
	return `<linearGradient id="${ id }" x1="${ x1 }" y1="${ y1 }" x2="${ x2 }" y2="${ y2 }" gradientUnits="${ units }">${ stops.map( ( s ) => `<stop offset="${ s[ 0 ] }" stop-color="${ s[ 1 ] }"${ s[ 2 ] !== undefined ? ` stop-opacity="${ s[ 2 ] }"` : '' }/>` ).join( '' ) }</linearGradient>`;
}

function radial( id, stops, cx = 0.5, cy = 0.5, r = 0.5 ) {
	return `<radialGradient id="${ id }" cx="${ cx }" cy="${ cy }" r="${ r }">${ stops.map( ( s ) => `<stop offset="${ s[ 0 ] }" stop-color="${ s[ 1 ] }"${ s[ 2 ] !== undefined ? ` stop-opacity="${ s[ 2 ] }"` : '' }/>` ).join( '' ) }</radialGradient>`;
}

module.exports = { W, H, toRgb, toHex, mix, shade, camera, clipNear, pts, poly, line, seg, rect, ellipse, quad, cuboid, box, defs, svg, linear, radial, fmt };

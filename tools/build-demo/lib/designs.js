/**
 * Design tokens of the two demos, and the Elementor Site Settings (kit)
 * built from them. Page builders reference colors and fonts through the kit
 * (globals), so changing a global color or font updates every page.
 */
'use strict';

const E = require( './elementor' );

const palette = {
	primary: '#16303F',
	secondary: '#F2B705',
	accent: '#2C5871',
	text: '#1B2429',
	heading: '#16303F',
	dark: '#0D1D26',
	muted: '#5F6E77',
	success: '#187A4F',
	warning: '#AD601E',
	// The yellow darkened until small text passes WCAG AA on white (5.5:1)
	// and on the surface colours (4.8:1).
	gold: '#866405',
	white: '#FFFFFF',
};

const designs = {
	soft: {
		key: 'soft',
		colors: Object.assign( {}, palette, { bg: '#FFFFFF', surface: '#EEF1F2', border: '#DFE4E6' } ),
		contentWidth: 1184,
		gutter: 28,
		sec: 96,
		secHome: 104,
		radius: 14,
		radiusLg: 22,
		btnRadius: 99,
		btnPad: [ 17, 30 ],
		cardPad: 26,
		shadow: { horizontal: 0, vertical: 20, blur: 50, spread: 0, color: 'rgba(13,29,38,0.12)' },
		fonts: {
			primary: { weight: '800', lh: 1.04, ls: -0.035 },
			sbh1: 'clamp(2.6rem, 6.4vw, 5.2rem)',
			sbpage: 'clamp(2.3rem, 5vw, 3.9rem)',
			sbh2: 'clamp(2rem, 4.2vw, 3.4rem)',
			secondary: { size: 1.15, weight: '700', lh: 1.25, ls: -0.02 },
			text: { size: 1.0625, lh: 1.6 },
			accent: { size: 0.74, weight: '700', ls: 0.14 },
			sblede: { size: 1.14, lh: 1.65 },
			sbledel: { size: 1.22, lh: 1.6 },
			sbnumber: { size: 'clamp(1.9rem, 3.4vw, 2.7rem)', weight: '900', ls: -0.045, lh: 1 },
			sbbig: { size: 1.7, weight: '900', ls: -0.04, lh: 1 },
			sbquote: { size: 'clamp(1.7rem, 3.4vw, 2.7rem)', weight: '800', ls: -0.03, lh: 1.22 },
			sbbutton: { size: 1, weight: '700', lh: 1.2 },
			sbsmall: { size: 0.87, lh: 1.45 },
			sbfaq: { size: 1.06, weight: '700', lh: 1.4 },
			sbnum: { size: 0.8, weight: '800', ls: 0.1 },
			sbnav: { size: 0.95, weight: '500' },
		},
	},
	editorial: {
		key: 'editorial',
		colors: Object.assign( {}, palette, { bg: '#FBFBFA', surface: '#F1EFEA', border: '#E3E0D9' } ),
		contentWidth: 1220,
		gutter: 30,
		sec: 96,
		secHome: 106,
		radius: 6,
		radiusLg: 6,
		btnRadius: 4,
		btnPad: [ 16, 28 ],
		cardPad: 24,
		shadow: { horizontal: 0, vertical: 22, blur: 50, spread: 0, color: 'rgba(13,29,38,0.22)' },
		fonts: {
			primary: { weight: '900', lh: 1.02, ls: -0.04 },
			sbh1: 'clamp(2.7rem, 5.6vw, 4.6rem)',
			sbpage: 'clamp(2.7rem, 5.6vw, 4.6rem)',
			sbh2: 'clamp(2rem, 3.9vw, 3.2rem)',
			secondary: { size: 1.1, weight: '700', lh: 1.28, ls: -0.02 },
			text: { size: 1.0625, lh: 1.62 },
			accent: { size: 0.72, weight: '800', ls: 0.2 },
			sblede: { size: 1.1, lh: 1.62 },
			sbledel: { size: 1.1, lh: 1.62 },
			sbnumber: { size: 1.9, weight: '900', ls: -0.045, lh: 1 },
			sbbig: { size: 1.5, weight: '900', ls: -0.04, lh: 1.1 },
			sbquote: { size: 'clamp(1.5rem, 2.7vw, 2.15rem)', weight: '800', ls: -0.03, lh: 1.28 },
			sbbutton: { size: 0.99, weight: '700', lh: 1.2 },
			sbsmall: { size: 0.84, lh: 1.45 },
			sbfaq: { size: 1.03, weight: '700', lh: 1.4 },
			sbnum: { size: 0.82, weight: '900', ls: 0.08 },
			sbnav: { size: 0.95, weight: '600' },
		},
	},
};

/* ----------------------------------------------------------------- buttons */

/**
 * Button styles per design. Every button gets explicit settings, so a
 * template imported on its own still looks right without the demo kit.
 */
function buttons( d ) {
	const base = {
		background_background: 'classic',
		button_background_hover_background: 'classic',
		border_radius: E.dims( d.btnRadius ),
		text_padding: E.dims( d.btnPad[ 0 ], d.btnPad[ 1 ] ),
	};
	const g = ( o ) => ( { __globals__: Object.assign( { typography_typography: E.typo( 'sbbutton' ) }, o ) } );

	const yellow = Object.assign( {}, base, g( { background_color: E.color( 'secondary' ), button_text_color: E.color( 'sbdark' ), hover_color: E.color( 'sbdark' ) } ), {
		button_background_hover_color: '#FFC422',
	} );
	const dark = Object.assign( {}, base, g( { background_color: E.color( 'primary' ), button_text_color: E.color( 'sbwhite' ), hover_color: E.color( 'sbwhite' ) } ), {
		button_background_hover_color: 'soft' === d.key ? palette.accent : palette.dark,
	} );
	const ghost = Object.assign( {}, base, g( { button_text_color: E.color( 'sbwhite' ), hover_color: E.color( 'sbwhite' ) } ), {
		background_color: 'rgba(255,255,255,0)',
		button_background_hover_color: 'rgba(255,255,255,0.14)',
		border_border: 'solid',
		border_width: E.dims( 1.5 ),
		border_color: 'rgba(255,255,255,0.4)',
		button_hover_border_color: 'rgba(255,255,255,0.6)',
		text_padding: E.dims( d.btnPad[ 0 ] - 1.5, d.btnPad[ 1 ] - 1.5 ),
	} );
	const outline = Object.assign( {}, base, g( { button_text_color: E.color( 'primary' ), hover_color: E.color( 'sbwhite' ), button_hover_border_color: E.color( 'primary' ), button_background_hover_color: E.color( 'primary' ), border_color: E.color( 'primary' ) } ), {
		background_color: 'rgba(255,255,255,0)',
		border_border: 'solid',
		border_width: E.dims( 1.5 ),
		text_padding: E.dims( d.btnPad[ 0 ] - 1.5, d.btnPad[ 1 ] - 1.5 ),
	} );
	// Compact buttons (cards): local typography instead of the global Button font.
	const small = ( style ) => {
		const globals = Object.assign( {}, style.__globals__ );
		delete globals.typography_typography;
		return Object.assign( {}, style, {
			__globals__: globals,
			typography_typography: 'custom',
			typography_font_family: 'Archivo',
			typography_font_weight: '700',
			typography_font_size: E.rem( 0.9 ),
			text_padding: E.dims( 11, 22 ),
		} );
	};

	return { yellow, dark, ghost, outline, small };
}

/* --------------------------------------------------------------------- kit */

function typoEntry( id, title, f, fallbackSize ) {
	const entry = {
		_id: id,
		title,
		typography_typography: 'custom',
		typography_font_family: 'Archivo',
	};
	const size = 'string' === typeof f ? f : f.size;
	if ( size !== undefined ) {
		entry.typography_font_size = 'string' === typeof size ? E.custom( size ) : E.rem( size );
	} else if ( fallbackSize ) {
		entry.typography_font_size = E.custom( fallbackSize );
	}
	if ( f.weight ) {
		entry.typography_font_weight = f.weight;
	}
	if ( f.lh !== undefined ) {
		entry.typography_line_height = E.em( f.lh );
	}
	if ( f.ls !== undefined ) {
		entry.typography_letter_spacing = E.em( f.ls );
	}
	if ( 'accent' === id || 'sbnum' === id ) {
		entry.typography_text_transform = 'uppercase';
	}
	return entry;
}

/**
 * Elementor Site Settings for a design.
 */
function kit( d ) {
	const c = d.colors;
	const f = d.fonts;
	const heading = ( size ) => Object.assign( {}, f.primary, { size } );

	return {
		system_colors: [
			{ _id: 'primary', title: 'Primary', color: c.primary },
			{ _id: 'secondary', title: 'Secondary', color: c.secondary },
			{ _id: 'text', title: 'Text', color: c.text },
			{ _id: 'accent', title: 'Accent', color: c.accent },
		],
		custom_colors: [
			{ _id: 'sbheading', title: 'Heading', color: c.heading },
			{ _id: 'sbbg', title: 'Background', color: c.bg },
			{ _id: 'sbsurface', title: 'Surface', color: c.surface },
			{ _id: 'sbborder', title: 'Border', color: c.border },
			{ _id: 'sbdark', title: 'Dark', color: c.dark },
			{ _id: 'sbmuted', title: 'Muted text', color: c.muted },
			{ _id: 'sbsuccess', title: 'Success', color: c.success },
			{ _id: 'sbwarning', title: 'Warning', color: c.warning },
			{ _id: 'sbgold', title: 'Gold (small text on light)', color: c.gold },
			{ _id: 'sbwhite', title: 'White', color: c.white },
		],
		system_typography: [
			typoEntry( 'primary', 'Primary Heading', f.primary ),
			typoEntry( 'secondary', 'Secondary Heading', f.secondary ),
			typoEntry( 'text', 'Body', f.text ),
			typoEntry( 'accent', 'Caption / Eyebrow', f.accent ),
		],
		custom_typography: [
			typoEntry( 'sbh1', 'Hero Title', heading( f.sbh1 ) ),
			typoEntry( 'sbpage', 'Page Title', heading( f.sbpage ) ),
			typoEntry( 'sbh2', 'Section Title', heading( f.sbh2 ) ),
			typoEntry( 'sblede', 'Lead Paragraph', f.sblede ),
			typoEntry( 'sbledel', 'Hero Lead', f.sbledel ),
			typoEntry( 'sbnumber', 'Numbers', f.sbnumber ),
			typoEntry( 'sbbig', 'Callout Number', f.sbbig ),
			typoEntry( 'sbquote', 'Quote', f.sbquote ),
			typoEntry( 'sbbutton', 'Button', f.sbbutton ),
			typoEntry( 'sbnav', 'Navigation', f.sbnav ),
			typoEntry( 'sbsmall', 'Small Text', f.sbsmall ),
			typoEntry( 'sbfaq', 'Question', f.sbfaq ),
			typoEntry( 'sbnum', 'Step Number', f.sbnum ),
		],
		default_generic_fonts: 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
		container_width: E.px( d.contentWidth ),
		container_padding: E.dims( 0 ),
		space_between_widgets: { column: '20', row: '20', isLinked: true, unit: 'px', size: 20 },
		body_background_background: 'classic',
		body_background_color: c.bg,
		body_color: c.text,
		body_typography_typography: 'custom',
		body_typography_font_family: 'Archivo',
		body_typography_font_size: E.rem( f.text.size ),
		body_typography_line_height: E.em( f.text.lh ),
		// No site-wide Theme Style for links, heading colours, buttons or form
		// fields: Elementor applies those to every link, heading, button and
		// field on the site (menus, filters, forms), overriding their own
		// styles. The theme styles them from the same tokens instead, and gives
		// Elementor's Button widget and form fields matching defaults.
	};
}

module.exports = { designs, palette, buttons, kit };

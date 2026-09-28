/**
 * Elementor data factories.
 *
 * Builds the element tree Elementor stores in _elementor_data: flexbox
 * containers and widgets with deterministic IDs (so rebuilding the demo does
 * not churn every file). Only documented, stable control keys are used.
 */
'use strict';

const crypto = require( 'crypto' );

let scope = 'doc';
let seq = 0;

/** Starts a new document: IDs restart from a seed derived from its key. */
function begin( key ) {
	scope = key;
	seq = 0;
}

function id() {
	seq++;
	return crypto.createHash( 'sha1' ).update( scope + ':' + seq ).digest( 'hex' ).slice( 0, 7 );
}

/* ------------------------------------------------------------------ units */

const px = ( n ) => ( { unit: 'px', size: n, sizes: [] } );
const pct = ( n ) => ( { unit: '%', size: n, sizes: [] } );
const em = ( n ) => ( { unit: 'em', size: n, sizes: [] } );
const rem = ( n ) => ( { unit: 'rem', size: n, sizes: [] } );
const vh = ( n ) => ( { unit: 'vh', size: n, sizes: [] } );
const custom = ( v ) => ( { unit: 'custom', size: v, sizes: [] } );

/** Four-sided dimensions; one value = all sides, two = vertical/horizontal. */
function dims( t, r = t, b = t, l = r, unit = 'px' ) {
	return {
		unit,
		top: String( t ),
		right: String( r ),
		bottom: String( b ),
		left: String( l ),
		isLinked: t === r && r === b && b === l,
	};
}

function gap( column, row = column ) {
	return { column: String( column ), row: String( row ), isLinked: column === row, unit: 'px', size: column };
}

const color = ( key ) => 'globals/colors?id=' + key;
const typo = ( key ) => 'globals/typography?id=' + key;

/* -------------------------------------------------------------- elements */

/**
 * Container. Children that are containers are marked as inner by finalize().
 *
 * @param {Object} s        Settings.
 * @param {Array}  elements Children.
 */
function container( s = {}, elements = [] ) {
	const settings = Object.assign(
		{
			content_width: 'full',
			flex_direction: 'column',
			padding: dims( 0 ),
		},
		s
	);
	return { id: id(), elType: 'container', settings, elements: elements.filter( Boolean ), isInner: false };
}

function widget( type, settings = {} ) {
	return { id: id(), elType: 'widget', widgetType: type, settings, elements: [], isInner: false };
}

/**
 * Marks nested containers as inner, as Elementor does, and sizes widgets that
 * sit directly in a row: in a row container Elementor lets widgets share the
 * row (and start at zero width when "Align items" is set), so they get an
 * inline (auto) width, and buttons and icons never shrink.
 */
function finalize( elements, nested = false, parent = null ) {
	elements.forEach( ( el ) => {
		if ( 'container' === el.elType ) {
			el.isInner = nested;
			finalize( el.elements, true, el );
		} else if ( parent && 'row' === parent.settings.flex_direction ) {
			const s = el.settings;
			if ( undefined === s._element_width ) {
				s._element_width = 'auto';
			}
			if ( undefined === s._flex_size ) {
				s._flex_size = [ 'button', 'icon', 'image' ].includes( el.widgetType ) ? 'none' : 'shrink';
			}
		}
	} );
	return elements;
}

/* ---------------------------------------------------------------- layout */

/**
 * Full-width section with a boxed inner container.
 *
 * @param {Object} o { bg, pad:[top,bottom], padMobile:[t,b], gutter, gutterMobile, id, classes, width, extra }
 */
function section( o, children ) {
	const pad = o.pad || [ 96, 96 ];
	const padTablet = o.padTablet || [ Math.round( pad[ 0 ] * 0.85 ), Math.round( pad[ 1 ] * 0.85 ) ];
	const padMobile = o.padMobile || [ Math.round( pad[ 0 ] * 0.72 ), Math.round( pad[ 1 ] * 0.72 ) ];
	const gut = o.gutter === undefined ? 28 : o.gutter;
	const gutMobile = o.gutterMobile === undefined ? 20 : o.gutterMobile;
	const s = Object.assign(
		{
			content_width: 'boxed',
			flex_direction: 'column',
			flex_gap: gap( o.gap === undefined ? 0 : o.gap ),
			padding: dims( pad[ 0 ], gut, pad[ 1 ], gut ),
			padding_tablet: dims( padTablet[ 0 ], gut, padTablet[ 1 ], gut ),
			padding_mobile: dims( padMobile[ 0 ], gutMobile, padMobile[ 1 ], gutMobile ),
			html_tag: o.tag || 'section',
		},
		o.width ? { boxed_width: px( o.width ) } : {},
		o.bg ? background( o.bg ) : {},
		o.id ? { _element_id: o.id } : {},
		o.classes ? { css_classes: o.classes } : {},
		o.extra || {}
	);
	return container( s, children );
}

/** Background settings: '#hex', { global }, { image, position, overlay... }. */
function background( bg ) {
	if ( 'string' === typeof bg ) {
		return { background_background: 'classic', background_color: bg };
	}
	const s = { background_background: 'classic' };
	if ( bg.global ) {
		s.__globals__ = { background_color: color( bg.global ) };
	}
	if ( bg.color ) {
		s.background_color = bg.color;
	}
	if ( bg.image ) {
		s.background_image = image( bg.image );
		s.background_position = bg.position || 'center center';
		s.background_size = 'cover';
		s.background_repeat = 'no-repeat';
	}
	if ( bg.overlay ) {
		const o = bg.overlay;
		if ( o.gradient ) {
			Object.assign( s, {
				background_overlay_background: 'gradient',
				background_overlay_color: o.gradient[ 0 ],
				background_overlay_color_stop: pct( o.stops ? o.stops[ 0 ] : 0 ),
				background_overlay_color_b: o.gradient[ 1 ],
				background_overlay_color_b_stop: pct( o.stops ? o.stops[ 1 ] : 100 ),
				background_overlay_gradient_type: 'linear',
				background_overlay_gradient_angle: { unit: 'deg', size: o.angle === undefined ? 180 : o.angle, sizes: [] },
			} );
		} else {
			Object.assign( s, {
				background_overlay_background: 'classic',
				background_overlay_color: o.color,
				background_overlay_opacity: { unit: 'px', size: o.opacity === undefined ? 0.6 : o.opacity, sizes: [] },
			} );
		}
	}
	return s;
}

/** Media control value with import placeholders. */
function image( key, alt = '' ) {
	return { url: '{{image_url:' + key + '}}', id: '{{image:' + key + '}}', size: '', alt, source: 'library' };
}

/**
 * Row of columns that stacks on smaller screens.
 *
 * @param {Object} o { gap, align, justify, stack: 'tablet'|'mobile', wrap, extra }
 */
function row( o, children ) {
	const stack = o.stack === undefined ? 'tablet' : o.stack;
	const s = Object.assign(
		{
			content_width: 'full',
			flex_direction: 'row',
			flex_gap: gap( o.gap === undefined ? 24 : o.gap, o.rowGap === undefined ? ( o.gap === undefined ? 24 : o.gap ) : o.rowGap ),
			flex_align_items: o.align || 'stretch',
			flex_justify_content: o.justify || 'flex-start',
			flex_wrap: o.wrap ? 'wrap' : 'nowrap',
			padding: dims( 0 ),
		},
		stack ? { [ 'flex_direction_' + stack ]: 'column' } : {},
		'tablet' === stack ? { flex_direction_mobile: 'column' } : {},
		o.gapTablet !== undefined ? { flex_gap_tablet: gap( o.gapTablet ) } : {},
		o.gapMobile !== undefined ? { flex_gap_mobile: gap( o.gapMobile ) } : {},
		o.extra || {}
	);
	return container( s, children );
}

/**
 * Column inside a row.
 *
 * @param {Object} o { w (percent), gap, align, justify, pad, bg, radius, extra, stackWidth }
 */
function col( o, children ) {
	const s = Object.assign(
		{
			content_width: 'full',
			flex_direction: 'column',
			flex_gap: gap( o.gap === undefined ? 0 : o.gap ),
			padding: o.pad ? dims( ...o.pad ) : dims( 0 ),
		},
		o.w ? { width: pct( o.w ), width_tablet: pct( 100 ), width_mobile: pct( 100 ) } : {},
		o.align ? { flex_align_items: o.align } : {},
		o.justify ? { flex_justify_content: o.justify } : {},
		o.bg ? background( o.bg ) : {},
		o.radius !== undefined ? { border_radius: dims( o.radius ) } : {},
		o.extra || {}
	);
	return container( s, children );
}

/* --------------------------------------------------------------- widgets */

/**
 * Heading.
 *
 * @param {string} title HTML allowed (em, mark, br).
 * @param {Object} o     { tag, typo, color, colorHex, align, margin, classes, width, extra }
 */
function heading( title, o = {} ) {
	const s = Object.assign(
		{
			title,
			header_size: o.tag || 'h2',
		},
		o.align ? { align: o.align } : {},
		o.alignMobile ? { align_mobile: o.alignMobile } : {},
		o.colorHex ? { title_color: o.colorHex } : {},
		o.margin ? { _margin: dims( ...o.margin ) } : {},
		o.classes ? { _css_classes: o.classes } : {},
		o.width ? measure( o.width ) : {},
		o.extra || {}
	);
	const globals = {};
	if ( o.typo ) {
		globals.typography_typography = typo( o.typo );
	}
	if ( o.color ) {
		globals.title_color = color( o.color );
	}
	if ( Object.keys( globals ).length ) {
		s.__globals__ = Object.assign( globals, s.__globals__ || {} );
	}
	return widget( 'heading', s );
}

/**
 * Text editor.
 *
 * @param {string} html Paragraph HTML.
 * @param {Object} o    { typo, color, colorHex, align, margin, width, classes, extra }
 */
function text( html, o = {} ) {
	const s = Object.assign(
		{ editor: html.startsWith( '<' ) ? html : '<p>' + html + '</p>' },
		o.align ? { align: o.align } : {},
		o.colorHex ? { text_color: o.colorHex } : {},
		o.margin ? { _margin: dims( ...o.margin ) } : {},
		o.width ? measure( o.width ) : {},
		o.classes ? { _css_classes: o.classes } : {},
		o.extra || {}
	);
	const globals = {};
	if ( o.typo ) {
		globals.typography_typography = typo( o.typo );
	}
	if ( o.color ) {
		globals.text_color = color( o.color );
	}
	if ( Object.keys( globals ).length ) {
		s.__globals__ = globals;
	}
	return widget( 'text-editor', s );
}

/** Max width on desktop and tablet, full width on phones. */
function measure( width ) {
	return {
		_element_width: 'initial',
		_element_custom_width: px( width ),
		_element_width_mobile: 'inherit',
	};
}

/**
 * Button. Variants are defined by the design (see designs.js).
 *
 * @param {string} label  Text.
 * @param {string} url    Link (placeholders allowed).
 * @param {Object} style  Button style settings from the design.
 * @param {Object} o      { icon, align, alignMobile, width: 'full', margin, extra }
 */
function button( label, url, style = {}, o = {} ) {
	const s = Object.assign(
		{
			text: label,
			link: { url, is_external: '', nofollow: '', custom_attributes: '' },
			size: 'md',
		},
		style,
		o.icon ? { selected_icon: { value: o.icon, library: 'fa-solid' }, icon_align: 'right', icon_indent: px( 10 ) } : {},
		o.align ? { align: o.align } : {},
		o.alignMobile ? { align_mobile: o.alignMobile } : {},
		o.margin ? { _margin: dims( ...o.margin ) } : {},
		o.extra || {}
	);
	if ( 'full' === o.width ) {
		s.align = 'justify';
	}
	return widget( 'button', s );
}

/**
 * Image with a fixed, responsive height (object-fit: cover).
 *
 * @param {string} key Image key.
 * @param {Object} o   { alt, size, height:[d,t,m], radius, extra, link }
 */
function img( key, o = {} ) {
	const h = o.height;
	const s = Object.assign(
		{
			image: image( key, o.alt || '' ),
			image_size: o.size || 'large',
			width: pct( 100 ),
			_css_classes: 'sb-e-cover',
		},
		h ? { height: px( h[ 0 ] ), height_tablet: px( h[ 1 ] || h[ 0 ] ), height_mobile: px( h[ 2 ] || h[ 1 ] || h[ 0 ] ), 'object-fit': 'cover' } : {},
		o.radius !== undefined ? { image_border_radius: dims( o.radius ) } : {},
		o.link ? { link_to: 'custom', link: { url: o.link, is_external: '', nofollow: '' } } : {},
		o.extra || {}
	);
	return widget( 'image', s );
}

/**
 * Icon list.
 *
 * @param {Array}  items [{ text, icon, url }].
 * @param {Object} o     { inline, iconColor, textColor, textColorHex, iconColorHex, typo, space, iconSize, extra }
 */
function iconList( items, o = {} ) {
	const s = Object.assign(
		{
			view: o.inline ? 'inline' : 'traditional',
			icon_list: items.map( ( item ) => Object.assign(
				{ _id: id(), text: item.text, selected_icon: { value: item.icon || 'fas fa-check', library: 'fa-solid' } },
				item.url ? { link: { url: item.url, is_external: '', nofollow: '' } } : {}
			) ),
			space_between: px( o.space === undefined ? 10 : o.space ),
			icon_size: px( o.iconSize || 15 ),
			text_indent: px( o.indent === undefined ? 9 : o.indent ),
		},
		o.iconColorHex ? { icon_color: o.iconColorHex } : {},
		o.textColorHex ? { text_color: o.textColorHex } : {},
		o.extra || {}
	);
	const globals = {};
	if ( o.iconColor ) {
		globals.icon_color = color( o.iconColor );
	}
	if ( o.textColor ) {
		globals.text_color = color( o.textColor );
	}
	if ( o.typo ) {
		globals.icon_typography_typography = typo( o.typo );
	}
	if ( Object.keys( globals ).length ) {
		s.__globals__ = globals;
	}
	return widget( 'icon-list', s );
}

/** Font Awesome icon in a tile. */
function icon( value, o = {} ) {
	const s = Object.assign(
		{
			selected_icon: { value, library: value.startsWith( 'far' ) ? 'fa-regular' : 'fa-solid' },
			view: o.view || 'default',
			align: o.align || 'left',
			size: px( o.size || 22 ),
		},
		o.shape ? { shape: o.shape } : {},
		o.primaryHex ? { primary_color: o.primaryHex } : {},
		o.secondaryHex ? { secondary_color: o.secondaryHex } : {},
		o.padding ? { icon_padding: px( o.padding ) } : {},
		o.radius !== undefined ? { border_radius: dims( o.radius ) } : {},
		o.margin ? { _margin: dims( ...o.margin ) } : {},
		o.extra || {}
	);
	const globals = {};
	if ( o.primary ) {
		globals.primary_color = color( o.primary );
	}
	if ( o.secondary ) {
		globals.secondary_color = color( o.secondary );
	}
	if ( Object.keys( globals ).length ) {
		s.__globals__ = globals;
	}
	return widget( 'icon', s );
}

/** Animated number with a caption. */
function counter( value, title, o = {} ) {
	const decimals = String( value ).includes( '.' );
	return widget(
		'counter',
		Object.assign(
			{
				starting_number: 0,
				ending_number: value,
				suffix: o.suffix || '',
				prefix: o.prefix || '',
				duration: 1800,
				thousand_separator: decimals ? '' : 'yes',
				thousand_separator_char: ',',
				title,
				_css_classes: 'sb-e-counter-left' + ( o.classes ? ' ' + o.classes : '' ),
				__globals__: {
					number_color: color( o.numberColor || 'primary' ),
					typography_number_typography: typo( o.numberTypo || 'sbnumber' ),
					title_color: color( o.titleColor || 'sbmuted' ),
					typography_title_typography: typo( o.titleTypo || 'sbsmall' ),
				},
			},
			o.extra || {}
		)
	);
}

/**
 * Accordion (FAQ) with FAQ schema.
 *
 * @param {Array}  items [[question, answer]].
 * @param {Object} o     Style settings.
 */
function accordion( items, o = {} ) {
	return widget(
		'accordion',
		Object.assign(
			{
				tabs: items.map( ( item ) => ( { _id: id(), tab_title: item[ 0 ], tab_content: '<p>' + item[ 1 ] + '</p>' } ) ),
				selected_icon: { value: o.icon || 'fas fa-chevron-down', library: 'fa-solid' },
				selected_active_icon: { value: o.activeIcon || 'fas fa-chevron-up', library: 'fa-solid' },
				// Elementor gives the title role="button", which a heading tag may not carry.
				title_html_tag: 'div',
				faq_schema: 'yes',
				icon_align: 'right',
				border_width: px( 0 ),
				title_padding: dims( o.titlePad || 26, 0, o.titlePad || 26, 0 ),
				content_padding: dims( 0, 0, o.contentPad || 26, 0 ),
				_css_classes: 'sb-e-faq' + ( o.classes ? ' ' + o.classes : '' ),
				__globals__: {
					title_color: color( 'primary' ),
					tab_active_color: color( 'primary' ),
					icon_color: color( 'sbmuted' ),
					icon_active_color: color( 'primary' ),
					title_typography_typography: typo( o.titleTypo || 'sbfaq' ),
					content_color: color( 'sbmuted' ),
					content_typography_typography: typo( 'text' ),
				},
			},
			o.extra || {}
		)
	);
}

/**
 * Dynamic tag reference, as stored in __dynamic__.
 *
 * @param {string} name     Tag name.
 * @param {Object} settings Tag settings (before/after/fallback included).
 */
function tag( name, settings = {} ) {
	return '[elementor-tag id="' + id() + '" name="' + name + '" settings="' + encodeURIComponent( JSON.stringify( settings ) ) + '"]';
}

/** Makes a control of an element dynamic. */
function dyn( el, key, tagValue ) {
	el.settings.__dynamic__ = Object.assign( el.settings.__dynamic__ || {}, { [ key ]: tagValue } );
	return el;
}

/** Hides an element on the given devices. */
function hide( el, devices ) {
	devices.forEach( ( device ) => {
		el.settings[ 'hide_' + device ] = 'hidden-' + device;
	} );
	return el;
}

function spacer( height ) {
	return widget( 'spacer', { space: px( height ) } );
}

function divider( o = {} ) {
	return widget( 'divider', Object.assign( { style: 'solid', weight: px( o.weight || 1 ), gap: px( o.gap || 0 ), __globals__: { color: color( o.color || 'sbborder' ) } }, o.extra || {} ) );
}

module.exports = {
	begin,
	id,
	px,
	pct,
	em,
	rem,
	vh,
	custom,
	dims,
	gap,
	color,
	typo,
	container,
	widget,
	finalize,
	section,
	background,
	image,
	row,
	col,
	heading,
	text,
	measure,
	button,
	img,
	iconList,
	icon,
	counter,
	accordion,
	tag,
	dyn,
	hide,
	spacer,
	divider,
};

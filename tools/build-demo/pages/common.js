/**
 * Building blocks shared by the page layouts of both demos.
 */
'use strict';

const E = require( '../lib/elementor' );

const PHONE = '020 000 0000';
const PHONE_URL = 'tel:+31200000000';

/** Link placeholders resolved on import. */
const url = {
	home: '{{home}}',
	units: '{{url:page-units}}',
	sizes: '{{url:page-sizes}}',
	locations: '{{url:page-locations}}',
	about: '{{url:page-about}}',
	contact: '{{url:page-contact}}',
	blog: '{{url:page-blog}}',
	calc: '{{url:page-sizes#calc}}',
	faq: '{{url:page-contact#faq}}',
	unit: ( slug ) => '{{url:unit-' + slug + '}}',
	location: ( slug ) => '{{url:location-' + slug + '}}',
};

/** Select-control value (string ID) of an imported post. */
const sid = ( key ) => '{{sid:' + key + '}}';

/** Eyebrow (soft: rule before the text) or kicker (editorial). */
function eyebrow( D, label, o = {} ) {
	const soft = 'soft' === D.key;
	let colorKey;
	if ( o.color ) {
		colorKey = o.color;
	} else if ( o.onDark ) {
		colorKey = 'secondary';
	} else {
		colorKey = soft ? 'accent' : 'sbmuted';
	}
	return E.heading( label, {
		tag: 'p',
		typo: 'accent',
		color: colorKey,
		colorHex: o.colorHex,
		align: o.align,
		classes: soft ? 'sb-e-eyebrow' : '',
		margin: o.margin,
		extra: o.extra,
	} );
}

/**
 * Section heading block: eyebrow, title and lead paragraph.
 *
 * @param {Object} o { eyebrow, title, lede, onDark, align, tag, typo, width, gapTitle, gapLede, margin }
 */
function head( D, o ) {
	const onDark = !! o.onDark;
	const soft = 'soft' === D.key;
	const widgets = [];
	if ( o.eyebrow ) {
		widgets.push( eyebrow( D, o.eyebrow, { onDark, align: o.align, color: o.eyebrowColor } ) );
	}
	if ( o.title ) {
		widgets.push(
			E.heading( o.title, {
				tag: o.tag || 'h2',
				typo: o.typo || 'sbh2',
				color: onDark ? 'sbwhite' : 'primary',
				align: o.align,
				margin: [ o.eyebrow ? ( o.gapTitle === undefined ? ( soft ? 18 : 16 ) : o.gapTitle ) : 0, 0, 0, 0 ],
				classes: o.titleClasses,
				width: o.titleWidth,
			} )
		);
	}
	if ( o.lede ) {
		widgets.push(
			E.text( o.lede, {
				typo: o.ledeTypo || 'sblede',
				colorHex: onDark ? 'rgba(255,255,255,0.72)' : undefined,
				color: onDark ? undefined : 'sbmuted',
				align: o.align,
				margin: [ o.gapLede === undefined ? 20 : o.gapLede, 0, 0, 0 ],
				width: o.ledeWidth === undefined ? 560 : o.ledeWidth,
				extra: o.ledeExtra,
			} )
		);
	}
	return E.col(
		{
			extra: Object.assign(
				{ margin: E.dims( 0, 0, o.margin === undefined ? ( soft ? 52 : 56 ) : o.margin, 0 ) },
				o.width ? { width: E.px( o.width ), width_mobile: E.pct( 100 ) } : {},
				'center' === o.align ? { flex_align_items: 'center' } : {}
			),
		},
		widgets
	);
}

/** Heading block with a button on the right (stacks on phones). */
function headSplit( D, o, buttonWidget ) {
	const soft = 'soft' === D.key;
	return E.row(
		{
			gap: 40,
			rowGap: 24,
			align: 'flex-end',
			justify: 'space-between',
			stack: 'mobile',
			extra: { margin: E.dims( 0, 0, o.margin === undefined ? ( soft ? 52 : 56 ) : o.margin, 0 ), flex_align_items_mobile: 'flex-start' },
		},
		[
			E.col( { extra: { _flex_size: 'grow' } }, [
				eyebrow( D, o.eyebrow, { onDark: o.onDark } ),
				E.heading( o.title, { tag: 'h2', typo: 'sbh2', color: o.onDark ? 'sbwhite' : 'primary', margin: [ soft ? 18 : 16, 0, 0, 0 ] } ),
			] ),
			buttonWidget,
		]
	);
}

/** Numbered item (steps, tips, values). */
function numbered( D, n, title, body, o = {} ) {
	return [
		E.heading( n, { tag: 'p', typo: 'sbnum', color: 'secondary', margin: [ 0, 0, o.numGap === undefined ? 16 : o.numGap, 0 ] } ),
		E.heading( title, { tag: 'h3', typo: 'secondary', color: o.onDark ? 'sbwhite' : 'primary', margin: [ 0, 0, o.titleGap === undefined ? 10 : o.titleGap, 0 ] } ),
		small( body, { size: o.bodySize || 0.97, lh: 1.6, color: o.onDark ? null : 'sbmuted', colorHex: o.onDark ? 'rgba(255,255,255,0.66)' : undefined } ),
	];
}

/** "How reserving works" copy of each design (unit pages and plugin settings). */
function reservingHtml( D ) {
	const soft = 'soft' === ( D.key || D );
	return '<h2>How reserving works</h2>\n<ul>\n<li>Reserve online with no deposit and nothing to sign. We hold it for seven days.</li>\n' +
		( soft
			? '<li>Sign on your phone or at reception on move-in day, fit your own padlock, and collect your PIN.</li>\n<li>Pay monthly. Give two weeks\' notice to leave, and unused days are refunded.</li>\n'
			: '<li>Sign on your phone or at reception on move-in day, fit your own padlock, collect your PIN.</li>\n<li>Pay monthly. Two weeks\' notice to leave, unused days refunded.</li>\n' ) +
		'</ul>';
}

/** Small body text widget with a local size. */
function small( html, o = {} ) {
	const w = E.text( html, {
		color: o.color === undefined ? 'sbmuted' : o.color,
		colorHex: o.colorHex,
		margin: o.margin,
		align: o.align,
		extra: Object.assign( { typography_typography: 'custom', typography_font_size: E.rem( o.size || 0.92 ), typography_line_height: E.em( o.lh || 1.5 ) }, o.extra || {} ),
	} );
	if ( w.settings.__globals__ ) {
		delete w.settings.__globals__.typography_typography;
		if ( ! Object.keys( w.settings.__globals__ ).length ) {
			delete w.settings.__globals__;
		}
	}
	return w;
}

/** The FAQ used on the home and contact pages. */
function faqItems( D, withExtra ) {
	const soft = 'soft' === D.key;
	const items = [
		[ 'How long do I have to commit for?', soft ? 'There\'s no minimum term. You pay per month and give two weeks\' notice when you want to leave. Move out early and we refund the unused days.' : 'There is no minimum term. You pay per month and give two weeks\' notice when you want to leave. Move out early and we refund the unused days.' ],
		[ 'Can I change to a bigger or smaller unit later?', 'Yes, and there is no fee. Tell the facility manager and we move your contract across — you only pay the difference from that date.' ],
		[ 'Who has a key to my unit?', 'Only you. Bring your own padlock or buy one at reception. Staff cannot open your unit without you present.' ],
		[ 'What can\'t I store?', 'Anything perishable, living, flammable or illegal. Food, plants, animals, fuel, gas bottles, fireworks and chemicals are all out. Everything else is fine.' ],
		[ 'Is my stuff insured?', 'Not automatically. Some home contents policies cover items in storage, so check yours first. If it does not, we can arrange cover from €4 per month.' ],
	];
	if ( withExtra ) {
		items.push( [ 'Can someone else access my unit?', 'Yes. Add them as a named person on your account and they get their own PIN. You can remove them at any time.' ] );
	}
	return items;
}

/** FAQ accordion styled for the design. */
function faq( D, items ) {
	const soft = 'soft' === D.key;
	return E.accordion( items, {
		icon: soft ? 'fas fa-chevron-down' : 'fas fa-plus',
		activeIcon: soft ? 'fas fa-chevron-up' : 'fas fa-times',
		titlePad: soft ? 26 : 24,
		contentPad: soft ? 26 : 24,
		classes: soft ? '' : 'sb-e-faq--ruled',
		extra: { _element_width: 'initial', _element_custom_width: E.px( soft ? 860 : 760 ), _element_width_mobile: 'inherit', _element_width_tablet: 'inherit' },
	} );
}

module.exports = { E, PHONE, PHONE_URL, url, sid, eyebrow, head, headSplit, numbered, small, reservingHtml, faqItems, faq };

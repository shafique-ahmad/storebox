/**
 * Demo 2 — Business Storage (editorial design): page layouts.
 */
'use strict';

const C = require( './common' );

const { E, url, sid, PHONE, PHONE_URL } = C;

module.exports = ( D, B ) => {
	const S = {};
	const rule = { border_border: 'solid', border_width: E.dims( 2, 0, 0, 0 ), __globals__: { border_color: E.color( 'primary' ) } };
	const hairline = ( sides = [ 0, 0, 1, 0 ] ) => ( { border_border: 'solid', border_width: E.dims( ...sides ), __globals__: { border_color: E.color( 'sbborder' ) } } );

	/* ------------------------------------------------------------ sections */

	/** Page header on paper, optionally split with an image. */
	S.pageHero = ( o ) => {
		const text = [
			C.small( '<a href="' + url.home + '">Home</a>' + ( o.parent ? ' <span aria-hidden="true">/</span> <a href="' + o.parent[ 1 ] + '">' + o.parent[ 0 ] + '</a>' : '' ) + ' <span aria-hidden="true">/</span> <span aria-current="page">' + o.crumb + '</span>', { size: 0.82, margin: [ 0, 0, 26, 0 ], extra: { _css_classes: 'sb-e-crumbs' } } ),
			C.eyebrow( D, o.eyebrow ),
			E.heading( o.title, { tag: 'h1', typo: 'sbpage', color: 'primary', margin: [ 16, 0, 0, 0 ], width: 720 } ),
			E.text( o.lede, { typo: 'sblede', color: 'sbmuted', margin: [ 22, 0, 0, 0 ], width: 560 } ),
		];
		return E.section(
			{ pad: [ 64, 60 ], padMobile: [ 44, 44 ], gutter: D.gutter, bg: { global: 'sbbg' }, extra: hairline() },
			o.image
				? [
					E.row( { gap: 60, gapTablet: 36, align: 'flex-end' }, [
						E.col( { w: 55 }, text ),
						E.col( { w: 45 }, [ E.img( o.image, { alt: '', height: [ 400, 420, 260 ], radius: D.radius } ) ] ),
					] ),
				]
				: text
		);
	};

	/** Yellow call to action with an image. */
	S.cta = ( o = {} ) =>
		E.section( { pad: [ D.secHome, D.secHome ], gutter: D.gutter, bg: { global: 'secondary' } }, [
			E.row( { gap: 60, gapTablet: 44, align: 'center' }, [
				E.col( { w: 55 }, [
					C.eyebrow( D, o.eyebrow || 'Ready when you are', { colorHex: 'rgba(13,29,38,0.6)', color: null } ),
					E.heading( o.title || 'Reserve a unit in under two minutes.', { typo: 'sbh2', color: 'primary', margin: [ 16, 0, 0, 0 ], width: 600 } ),
					E.text( o.lede || 'No deposit, nothing to sign today, and we hold it free for seven days while you sort the van.', { colorHex: 'rgba(13,29,38,0.74)', margin: [ 18, 0, 0, 0 ], width: 480, extra: { typography_typography: 'custom', typography_font_size: E.rem( 1.06 ) } } ),
					E.row( { gap: 13, stack: 'mobile', extra: { margin: E.dims( 32, 0, 0, 0 ), flex_align_items_mobile: 'flex-start' } }, [
						E.button( 'Find your size', url.sizes, B.dark ),
						E.button( PHONE, PHONE_URL, B.outline ),
					] ),
				] ),
				E.col( { w: 45 }, [ E.img( 'storage-corridor-trolley', { alt: 'Storage corridor with a hand truck loaded with boxes', height: [ 380, 420, 260 ], radius: D.radius } ) ] ),
			] ),
		] );

	/** FAQ: heading column and questions column. */
	S.faq = ( o = {} ) =>
		E.section( { pad: [ o.pad || D.secHome, o.pad || D.secHome ], gutter: D.gutter, bg: '#FFFFFF', id: 'faq', extra: hairline( [ 1, 0, 0, 0 ] ) }, [
			E.row( { gap: 70, gapTablet: 44, align: 'flex-start' }, [
				E.col( { w: 41 }, [
					C.eyebrow( D, 'Questions' ),
					E.heading( 'The things people actually ask.', { typo: 'sbh2', color: 'primary', margin: [ 16, 0, 0, 0 ] } ),
					o.lede ? E.text( o.lede, { typo: 'sblede', color: 'sbmuted', margin: [ 18, 0, 0, 0 ] } ) : null,
				] ),
				E.col( { w: 59 }, [ C.faq( D, C.faqItems( D, o.extra ) ) ] ),
			] ),
		] );

	/** Numbered editorial list: number, title, text. */
	S.numlist = ( items, o = {} ) =>
		E.col(
			{ extra: Object.assign( { css_classes: 'sb-e-rows' }, o.onDark ? { border_border: 'solid', border_width: E.dims( 1, 0, 0, 0 ), border_color: 'rgba(255,255,255,0.18)' } : rule ) },
			items.map( ( item ) =>
				E.row( { gap: 24, rowGap: 8, align: 'baseline', stack: 'mobile', extra: { padding: E.dims( o.pad || 30, 0, o.pad || 30, 0 ) } }, [
					E.col( { extra: { width: E.px( o.numWidth || 70 ), width_tablet: E.px( o.numWidth || 70 ), width_mobile: E.pct( 100 ), _flex_size: 'none' } }, [
						o.year
							? E.heading( item[ 0 ], { tag: 'p', color: 'secondary', extra: { typography_typography: 'custom', typography_font_size: E.rem( 1.4 ), typography_font_weight: '900', typography_letter_spacing: E.em( -0.03 ) } } )
							: E.heading( item[ 0 ], { tag: 'p', typo: 'sbnum', color: 'secondary' } ),
					] ),
					E.col( { extra: { width: E.pct( 38 ), width_mobile: E.pct( 100 ) } }, [ E.heading( item[ 1 ], { tag: 'h3', typo: 'secondary', color: o.onDark ? 'sbwhite' : 'primary' } ) ] ),
					E.col( { extra: { width: E.pct( 52 ), width_mobile: E.pct( 100 ) } }, [ C.small( item[ 2 ], { size: 0.96, lh: 1.6, color: o.onDark ? null : 'sbmuted', colorHex: o.onDark ? 'rgba(255,255,255,0.64)' : undefined } ) ] ),
				] )
			)
		);

	/** Size chooser on navy with an image. */
	S.chooser = ( o ) =>
		E.section( { pad: [ D.secHome, D.secHome ], gutter: D.gutter, bg: { global: 'primary' }, id: o.id || 'sizes' }, [
			E.row( { gap: 70, gapTablet: 44, align: 'center' }, [
				E.col( { w: 50 }, [
					C.eyebrow( D, o.eyebrow, { onDark: true } ),
					E.heading( o.title, { typo: 'sbh2', color: 'sbwhite', margin: [ 16, 0, 20, 0 ] } ),
					E.text( o.lede, { typo: 'sblede', colorHex: 'rgba(255,255,255,0.66)', width: 520 } ),
					E.widget( 'storebox-size-chooser', { design: '', selected: 3, button_text: o.button, button_url: { url: url.units, is_external: '', nofollow: '' }, _margin: E.dims( 30, 0, 0, 0 ) } ),
				] ),
				E.col( { w: 50 }, [ E.img( o.image, { alt: o.alt, height: [ 720, 520, 320 ], radius: D.radius } ) ] ),
			] ),
		] );

	/* ---------------------------------------------------------------- home */

	S.hero = () => {
		// One Counter widget per fact, so each takes its content width in the row.
		const fact = ( value, label ) => {
			const prefix = value.startsWith( '€' ) ? '€' : '';
			return E.counter( prefix ? value.slice( 1 ) : value, label, { prefix, classes: 'sb-e-counter-tight' } );
		};
		return E.container(
			{
				content_width: 'full',
				flex_direction: 'row',
				flex_direction_tablet: 'column',
				flex_direction_mobile: 'column',
				flex_gap: E.gap( 0 ),
				flex_align_items: 'stretch',
				padding: E.dims( 0 ),
				html_tag: 'section',
				extra: undefined,
				border_border: 'solid',
				border_width: E.dims( 0, 0, 1, 0 ),
				__globals__: { border_color: E.color( 'sbborder' ) },
			},
			[
				E.col(
					{
						w: 53,
						extra: {
							padding: { unit: 'custom', top: '84px', right: '30px', bottom: '84px', left: 'max(30px, calc(50vw - 610px))', isLinked: false },
							padding_tablet: E.dims( 70, 30, 64, 30 ),
							padding_mobile: E.dims( 56, 20, 56, 20 ),
							flex_justify_content: 'center',
						},
					},
					[
						C.eyebrow( D, 'Self storage · from €35 a month', { margin: [ 0, 0, 22, 0 ] } ),
						E.heading( 'A tidy home starts with <em>somewhere else</em> to put things.', { tag: 'h1', typo: 'sbh1', color: 'primary', classes: 'sb-e-marker', width: 640 } ),
						E.text( 'Dry, alarmed units from 2 to 30 m² in Amsterdam and Haarlem. Reserve online, move in today, cancel whenever you like.', { typo: 'sblede', color: 'sbmuted', margin: [ 26, 0, 0, 0 ], width: 520 } ),
						E.row( { gap: 13, stack: 'mobile', extra: { margin: E.dims( 36, 0, 0, 0 ), flex_align_items_mobile: 'flex-start' } }, [
							E.button( 'Find your size', url.sizes, B.yellow, { icon: 'fas fa-arrow-right' } ),
							E.button( 'Units &amp; prices', url.units, B.outline ),
						] ),
						E.row(
							{ gap: 46, rowGap: 18, wrap: true, stack: false, extra: Object.assign( { margin: E.dims( 52, 0, 0, 0 ), padding: E.dims( 30, 0, 0, 0 ), flex_gap_mobile: E.gap( 30, 18 ) }, hairline( [ 1, 0, 0, 0 ] ) ) },
							[ fact( '3', 'Facilities' ), fact( '740', 'Units available' ), fact( '4.8', 'Average rating' ), fact( '€0', 'Deposit' ) ]
						),
					]
				),
				E.col(
					{
						w: 47,
						bg: { image: 'storage-lockers', global: 'sbsurface' },
						extra: {
							min_height: E.px( 560 ),
							min_height_tablet: E.px( 420 ),
							min_height_mobile: E.px( 410 ),
							flex_justify_content: 'flex-end',
							padding: E.dims( 0, 0, 56, 0 ),
							padding_tablet: E.dims( 0 ),
							_flex_order_tablet: 'start',
							_flex_order_mobile: 'start',
						},
					},
					[
						E.col(
							{
								bg: { global: 'secondary' },
								radius: D.radius,
								pad: [ 24, 28, 24, 28 ],
								extra: {
									width: E.px( 260 ),
									width_tablet: E.px( 260 ),
									width_mobile: E.px( 240 ),
									margin: E.dims( 0, 0, 0, -46 ),
									margin_tablet: E.dims( 0, 30, -28, 0 ),
									margin_mobile: E.dims( 0, 20, -28, 0 ),
									_flex_align_self_tablet: 'flex-end',
									z_index: 2,
									box_shadow_box_shadow_type: 'yes',
									box_shadow_box_shadow: D.shadow,
								},
							},
							[
								E.heading( 'Move in today', { tag: 'p', typo: 'sbbig', color: 'sbdark' } ),
								C.small( 'Reserve before 2pm and the unit is ready this afternoon.', { color: null, colorHex: 'rgba(13,29,38,0.76)', size: 0.86, lh: 1.45, margin: [ 7, 0, 0, 0 ] } ),
							]
						),
					]
				),
			]
		);
	};

	S.rail = () =>
		E.section( { pad: [ D.secHome, D.secHome ], gutter: D.gutter, id: 'units' }, [
			E.widget( 'storebox-unit-grid', {
				design: '',
				layout: 'rail',
				source: 'manual',
				ids: [ sid( 'unit-locker-2' ), sid( 'unit-small-5' ), sid( 'unit-medium-10' ), sid( 'unit-medium-15' ), sid( 'unit-large-20' ), sid( 'unit-large-30' ) ],
				header: 'yes',
				header_eyebrow: 'Units &amp; prices',
				header_title: 'Six sizes. One honest price list.',
				header_button_text: 'See all availability',
				header_button_url: { url: url.units, is_external: '', nofollow: '' },
				description: 'excerpt',
				subline: 'none',
				cta_text: 'Reserve',
				waitlist_text: 'Join list',
				full_label: 'Waitlist',
				hint: 'Drag, scroll or use the arrows',
				autoplay: 4000,
				show_arrows: 'yes',
				show_progress: 'yes',
			} ),
		] );

	S.features = () =>
		E.section( { pad: [ D.secHome, D.secHome ], gutter: D.gutter, id: 'why' }, [
			C.head( D, { eyebrow: 'Why StoreBox', title: 'Four things we refuse to cut corners on.', titleWidth: 640 } ),
			E.row( { gap: 70, gapTablet: 44, align: 'flex-start' }, [
				E.col(
					{ w: 55, extra: Object.assign( { css_classes: 'sb-e-rows' }, rule ) },
					[
						[ '01', 'Every door individually alarmed', 'Not just the building. Each unit has its own contact alarm, cameras cover the corridors around the clock, and footage is kept for thirty days.' ],
						[ '02', 'Held between 10 and 20°C', 'Every indoor unit is heated and ventilated, so documents, electronics and upholstery come out the way they went in — even after a Dutch winter.' ],
						[ '03', 'The price you were quoted', 'No admin fee, no deposit, and no increase in your first twelve months. If you move to a different size, you just pay the difference.' ],
						[ '04', 'Park at the door', 'Drive-up bays at all three facilities, with free trolleys and a covered loading area so you are never carrying boxes across an open yard in the rain.' ],
					].map( ( item ) =>
						E.row( { gap: 20, stack: false, extra: { padding: E.dims( 32, 0, 32, 0 ) } }, [
							E.col( { extra: { width: E.px( 58 ), width_tablet: E.px( 58 ), width_mobile: E.px( 44 ), _flex_size: 'none', padding: E.dims( 4, 0, 0, 0 ) } }, [ E.heading( item[ 0 ], { tag: 'p', typo: 'sbnum', color: 'secondary' } ) ] ),
							E.col( { extra: { width: E.pct( 100 ) } }, [
								E.heading( item[ 1 ], { tag: 'h3', typo: 'secondary', color: 'primary' } ),
								C.small( item[ 2 ], { size: 0.96, lh: 1.6, margin: [ 9, 0, 0, 0 ] } ),
							] ),
						] )
					)
				),
				E.col( { w: 45 }, [ E.img( 'storage-corridor-wide', { alt: 'Wide, clean corridor of self storage units', height: [ 620, 520, 320 ], radius: D.radius } ) ] ),
			] ),
		] );

	S.homeLocations = () =>
		E.section( { pad: [ D.secHome, D.secHome ], gutter: D.gutter, bg: { global: 'sbsurface' }, id: 'locations' }, [
			C.headSplit( D, { eyebrow: 'Locations', title: 'Three facilities, one price list.' }, E.button( 'Get directions', url.locations, B.outline ) ),
			E.widget( 'storebox-location-grid', { design: '', skin: 'row', tag_limit: 1, show_access: 'yes', show_free: 'yes' } ),
		] );

	S.quote = () =>
		E.section( { pad: [ D.secHome, D.secHome ], gutter: D.gutter }, [
			E.row( { gap: 70, gapTablet: 44, align: 'center' }, [
				E.col( { w: 50 }, [
					C.eyebrow( D, 'What people say' ),
					E.heading( '"I booked at nine in the evening and had my things in by lunchtime the next day. No one asked me to come in and sign anything."', { tag: 'p', typo: 'sbquote', color: 'primary', margin: [ 24, 0, 0, 0 ] } ),
					E.row( { gap: 14, align: 'center', stack: false, extra: { margin: E.dims( 30, 0, 0, 0 ) } }, [
						E.col( { bg: { global: 'primary' }, radius: 999, align: 'center', justify: 'center', extra: { width: E.px( 46 ), width_tablet: E.px( 46 ), width_mobile: E.px( 46 ), min_height: E.px( 46 ), _flex_size: 'none' } }, [
							E.heading( 'MK', { tag: 'span', color: 'sbwhite', align: 'center', extra: { typography_typography: 'custom', typography_font_weight: '800', typography_font_size: E.rem( 1 ) } } ),
						] ),
						E.col( {}, [
							E.heading( 'Marieke K.', { tag: 'p', color: 'primary', extra: { typography_typography: 'custom', typography_font_weight: '700', typography_font_size: E.rem( 0.96 ) } } ),
							C.small( 'Moved from Amsterdam Oost · stored 6 months', { size: 0.85 } ),
						] ),
					] ),
				] ),
				E.col( { w: 50 }, [ E.img( 'unit-small-yellow', { alt: 'Small storage unit behind an open yellow roller door', height: [ 470, 440, 300 ], radius: D.radius } ) ] ),
			] ),
		] );

	/* --------------------------------------------------------------- pages */

	const pages = {};

	pages.home = {
		title: 'Home',
		settings: { hide_title: 'yes', storebox_transparent_header: 'off' },
		content: () => [
			S.hero(),
			S.rail(),
			S.chooser( { eyebrow: 'Find your size', title: 'Drawn to scale, so you can actually picture it.', lede: 'Every size compared side by side at true proportion. Tap one to see what fits and what it costs.', button: 'Reserve this size', image: 'packing-table', alt: 'Open cardboard box on a table with packing tape and a marker' } ),
			S.features(),
			S.homeLocations(),
			S.quote(),
			S.faq( { lede: 'Can\'t see yours? Call us on ' + PHONE + ' — a person answers.' } ),
			S.cta(),
		],
	};

	pages.units = {
		title: 'Units',
		settings: { hide_title: 'yes', storebox_transparent_header: 'off' },
		content: () => [
			S.pageHero( { crumb: 'Units', eyebrow: 'Units &amp; prices', title: 'Eight sizes. One honest price list.', lede: 'From a 2 m² locker to a 30 m² stockroom, across three facilities. Every price includes 24/7 security and no deposit.' } ),
			E.section( { pad: [ 56, D.sec ], gutter: D.gutter }, [
				E.widget( 'storebox-unit-grid', {
					design: '',
					source: 'all',
					columns: '3',
					columns_tablet: '2',
					columns_mobile: '1',
					filters: 'yes',
					description: 'fits_location',
					subline: 'name',
					waitlist_text: 'Join list',
					full_label: 'Waitlist',
					label_empty_text: 'Try another location, or call us — units come free every week that are not listed yet.',
				} ),
			] ),
			E.section( { pad: [ 64, 64 ], gutter: D.gutter, bg: { global: 'sbsurface' } }, [
				E.row( { gap: 70, gapTablet: 40, align: 'center' }, [
					E.col( { w: 50 }, [
						C.eyebrow( D, 'Not sure which?' ),
						E.heading( 'Tell us what you are storing instead.', { typo: 'sbh2', color: 'primary', margin: [ 16, 0, 0, 0 ] } ),
						E.text( 'The calculator turns a list of furniture into a size in about thirty seconds, and it errs small.', { typo: 'sblede', color: 'sbmuted', margin: [ 18, 0, 0, 0 ], width: 520 } ),
						E.button( 'Open the calculator', url.sizes, B.dark, { icon: 'fas fa-arrow-right', margin: [ 26, 0, 0, 0 ] } ),
					] ),
					E.col( { w: 50 }, [ E.img( 'unit-shelving', { alt: 'Inside a storage unit: shelving with boxes and bins', height: [ 360, 380, 240 ], radius: D.radius } ) ] ),
				] ),
			] ),
			S.cta(),
		],
	};

	pages.sizes = {
		title: 'Find your size',
		slug: 'sizes',
		settings: { hide_title: 'yes', storebox_transparent_header: 'off' },
		content: () => [
			S.pageHero( { crumb: 'Find your size', eyebrow: 'Find your size', title: 'Stop guessing how much space you need.', lede: 'A calculator, a to-scale comparison and a room-by-room guide. Most people end up a size smaller than they expected.', image: 'packing-table' } ),
			E.section( { pad: [ D.sec, D.sec ], gutter: D.gutter, id: 'calc' }, [
				E.row( { gap: 70, gapTablet: 44, align: 'flex-start' }, [
					E.col( { w: 50 }, [
						C.eyebrow( D, '01 · The calculator' ),
						E.heading( 'Add what you are storing.', { typo: 'sbh2', color: 'primary', margin: [ 16, 0, 18, 0 ] } ),
						E.text( 'Rough numbers are fine. It assumes you stack to 2.2 m and leave an aisle to reach the back — the two things people forget.', { typo: 'sblede', color: 'sbmuted', width: 520 } ),
						E.spacer( 34 ),
						S.numlist( [
							[ '01', 'Pick your items', 'Tap plus for each piece of furniture and every ten boxes.' ],
							[ '02', 'Read the size', 'It updates as you go, with the price and how full the unit would be.' ],
							[ '03', 'Check it is free', 'The button takes you straight to that size at every location.' ],
						], { pad: 26 } ),
					] ),
					E.col( { w: 50 }, [ E.widget( 'storebox-size-calculator', { design: '', button_url: { url: url.units, is_external: '', nofollow: '' } } ) ] ),
				] ),
			] ),
			S.chooser( { id: 'scale', eyebrow: '02 · Drawn to scale', title: 'Compare them the way you would compare rooms.', lede: 'Every size at true proportion by floor area. Tap one to see what fits and what it costs.', button: 'See available units', image: 'storage-corridor-wide', alt: 'Wide, clean corridor of storage units' } ),
			E.section( { pad: [ D.sec, D.sec ], gutter: D.gutter }, [
				C.head( D, { eyebrow: '03 · Room by room', title: 'The size guide.', lede: 'What each size holds in practice, based on what our customers actually store.' } ),
				E.widget( 'storebox-size-guide', { design: '' } ),
			] ),
			S.cta(),
		],
	};

	pages.locations = {
		title: 'Locations',
		settings: { hide_title: 'yes', storebox_transparent_header: 'off' },
		content: () => [
			S.pageHero( { crumb: 'Locations', eyebrow: 'Locations', title: 'Three facilities, one price list.', lede: 'Two in Amsterdam, one in Haarlem. The same prices, the same security and the same contract at all three.' } ),
			E.section( { pad: [ 56, D.sec ], gutter: D.gutter }, [
				E.widget( 'storebox-location-map', { design: '', source: 'all', ratio: '16 / 7' } ),
				E.spacer( 56 ),
				E.widget( 'storebox-location-grid', { design: '', skin: 'row', address: 'full', tag_limit: 1 } ),
			] ),
			E.section( { pad: [ 64, 64 ], gutter: D.gutter, bg: { global: 'sbsurface' } }, [
				E.row( { gap: 70, gapTablet: 32, align: 'center' }, [
					E.col( { w: 50 }, [ C.eyebrow( D, 'At every facility' ), E.heading( 'The same standard, whichever you pick.', { typo: 'sbh2', color: 'primary', margin: [ 16, 0, 0, 0 ] } ) ] ),
					E.col(
						{ w: 50, extra: Object.assign( { css_classes: 'sb-e-rows' }, hairline( [ 1, 0, 0, 0 ] ) ) },
						[ 'Individually alarmed doors', 'CCTV kept for 30 days', 'Free trolleys and loading bay', 'Your own padlock and PIN', 'Monthly rolling contract', 'Swap sizes without a fee' ].map( ( item ) =>
							E.row( { gap: 16, justify: 'space-between', align: 'center', stack: false, extra: { padding: E.dims( 13, 0, 13, 0 ) } }, [
								C.small( item, { color: 'text', size: 0.96 } ),
								E.heading( 'Yes', { tag: 'span', color: 'sbsuccess', extra: { typography_typography: 'custom', typography_font_weight: '800', typography_font_size: E.rem( 0.96 ) } } ),
							] )
						)
					),
				] ),
			] ),
			S.cta(),
		],
	};

	pages.about = {
		title: 'About',
		settings: { hide_title: 'yes', storebox_transparent_header: 'off' },
		content: () => [
			S.pageHero( { crumb: 'About', eyebrow: 'About', title: 'We started StoreBox because we needed a unit ourselves.', lede: 'In 2014 the options were a damp garage or a warehouse with a four-page contract. So we built the thing we wanted to rent.', image: 'moving-boxes-room' } ),
			E.section( { pad: [ D.sec, D.sec ], gutter: D.gutter }, [
				E.heading( 'It should be as easy to rent a storage unit as it is to book a hotel room — <mark>no deposit, no minimum term</mark>, and a price you can see before you call.', { tag: 'p', typo: 'sbquote', color: 'primary', classes: 'sb-e-highlight', width: 728, extra: { typography_typography: 'custom', typography_font_size: E.custom( 'clamp(1.7rem, 3.2vw, 2.6rem)' ), typography_font_weight: '800', typography_line_height: E.em( 1.22 ), typography_letter_spacing: E.em( -0.035 ), __globals__: { title_color: E.color( 'primary' ) } } } ),
				E.row( { gap: 70, gapTablet: 24, align: 'flex-start', extra: { margin: E.dims( 60, 0, 0, 0 ) } }, [
					E.col( { w: 50 }, [ E.text( '<p>Our founders were between flats with a houseful of furniture and nowhere to put it. The places they found wanted a deposit, a twelve-month contract and a viewing appointment three days away.</p><p>So in 2014 we opened our first facility in Amsterdam Noord with one rule, the one above. Twelve years and three facilities later, it has not changed.</p>', { typo: 'text', color: 'text', classes: 'sb-e-prose' } ) ] ),
					E.col( { w: 50 }, [ E.text( '<p>We have grown entirely by word of mouth, which is why we still answer the phone ourselves and why the person who picks up can usually tell you which units are free before you finish asking.</p><p><a class="sb-link" href="' + url.locations + '">Visit a facility</a> — walk-rounds are welcome during office hours, no appointment needed.</p>', { typo: 'text', color: 'text', classes: 'sb-e-prose' } ) ] ),
				] ),
			] ),
			E.section( { pad: [ D.sec, D.sec ], gutter: D.gutter, bg: { global: 'sbsurface' } }, [
				C.head( D, { eyebrow: 'What we stand for', title: 'Three promises we have never broken.' } ),
				S.numlist( [
					[ '01', 'The price you see is the price you pay', 'No admin fee, no deposit, and no increase in your first twelve months. If a competitor is cheaper for the same size, tell us.' ],
					[ '02', 'Your things, your key', 'Only you can open your unit. Staff cannot get in without you, and we never cut a lock without a court order.' ],
					[ '03', 'Leave whenever you like', 'Two weeks\' notice, unused days refunded. We would rather you came back than felt trapped.' ],
				] ),
			] ),
			E.section( { pad: [ D.sec, D.sec ], gutter: D.gutter, bg: { global: 'primary' } }, [
				C.head( D, { eyebrow: 'Since 2014', title: 'From one corridor to three facilities.', onDark: true } ),
				S.numlist( [
					[ '2014', 'Amsterdam Noord opens', '84 units on a single floor of a former print works.' ],
					[ '2017', 'Second floor, climate control', 'Noord grows to 318 units, all heated and ventilated.' ],
					[ '2020', 'Amsterdam Zuidoost', 'Our first drive-up site: every door at ground level.' ],
					[ '2023', 'Haarlem Oudeweg', 'Built around business storage, with pallet delivery and racking.' ],
					[ '2026', 'Reserve online, move in today', 'The whole process moves online, from quote to PIN.' ],
				], { onDark: true, year: true, numWidth: 110, pad: 24 } ),
			] ),
			E.section( { pad: [ D.sec, D.sec ], gutter: D.gutter }, [
				C.head( D, { eyebrow: 'The team', title: 'The people who pick up the phone.' } ),
				E.col(
					{ extra: Object.assign( { css_classes: 'sb-e-rows' }, rule ) },
					[
						[ 'ED', 'Eva de Vries', 'Founder', 'Still does a shift at Noord reception every Friday.', 'primary', '#FFFFFF' ],
						[ 'JB', 'Jasper Bakker', 'Operations', 'Knows every door code change and every leaking gutter.', 'secondary', '#0D1D26' ],
						[ 'SM', 'Samira Mansour', 'Customer care', 'The voice on the phone, most likely, if you call.', 'accent', '#FFFFFF' ],
						[ 'TK', 'Tom Kuipers', 'Business accounts', 'Sets up racking, pallet deliveries and invoicing for shops.', 'sbsuccess', '#FFFFFF' ],
					].map( ( p ) =>
						E.row( { gap: 22, rowGap: 10, align: 'center', stack: 'mobile', extra: { padding: E.dims( 22, 0, 22, 0 ), flex_align_items_mobile: 'flex-start' } }, [
							E.col( { bg: { global: p[ 4 ] }, radius: 999, align: 'center', justify: 'center', extra: { width: E.px( 48 ), width_tablet: E.px( 48 ), width_mobile: E.px( 48 ), min_height: E.px( 48 ), _flex_size: 'none' } }, [
								E.heading( p[ 0 ], { tag: 'span', colorHex: p[ 5 ], align: 'center', extra: { typography_typography: 'custom', typography_font_weight: '800', typography_font_size: E.rem( 1 ) } } ),
							] ),
							E.col( { extra: { width: E.pct( 36 ), width_mobile: E.pct( 100 ) } }, [
								E.heading( p[ 1 ], { tag: 'h3', typo: 'secondary', color: 'primary', extra: { typography_typography: 'custom', typography_font_size: E.rem( 1.02 ) } } ),
								C.small( p[ 2 ], { size: 0.86 } ),
							] ),
							E.col( { extra: { width: E.pct( 100 ) } }, [ C.small( p[ 3 ], { size: 0.93 } ) ] ),
						] )
					)
				),
			] ),
			S.cta(),
		],
	};

	pages.contact = {
		title: 'Contact',
		settings: { hide_title: 'yes', storebox_transparent_header: 'off' },
		content: () => [
			S.pageHero( { crumb: 'Contact', eyebrow: 'Contact', title: 'Talk to a person, not a form.', lede: 'Call, email or drop in. We answer the phone during office hours, and email replies usually come within the hour.' } ),
			E.section( { pad: [ 56, D.sec ], gutter: D.gutter }, [
				E.row( { gap: 70, gapTablet: 44, align: 'flex-start' }, [
					E.col(
						{ w: 50, extra: Object.assign( { css_classes: 'sb-e-rows' }, rule ) },
						[
							[ 'Call', PHONE, 'Mon–Sat 08:00–18:00', PHONE_URL ],
							[ 'Email', 'hello@example.com', 'Usually answered within the hour', 'mailto:hello@example.com' ],
							[ 'WhatsApp', '06 00 00 00 00', 'Photos of what you are storing welcome', '' ],
							[ 'Visit', 'Three facilities', 'Walk-round any time during office hours', url.locations ],
						].map( ( r ) =>
							E.row( { gap: 20, rowGap: 6, stack: 'mobile', extra: { padding: E.dims( 22, 0, 22, 0 ) } }, [
								E.col( { extra: { width: E.px( 120 ), width_tablet: E.px( 120 ), width_mobile: E.pct( 100 ), _flex_size: 'none', padding: E.dims( 4, 0, 0, 0 ) } }, [ E.heading( r[ 0 ], { tag: 'p', typo: 'accent', color: 'sbmuted' } ) ] ),
								E.col( { extra: { width: E.pct( 100 ) } }, [
									E.heading( r[ 1 ], { tag: 'p', color: 'primary', extra: Object.assign( { typography_typography: 'custom', typography_font_weight: '700', typography_font_size: E.rem( 1.15 ) }, r[ 3 ] ? { link: { url: r[ 3 ], is_external: '', nofollow: '' } } : {} ) } ),
									C.small( r[ 2 ], { size: 0.9, margin: [ 3, 0, 0, 0 ] } ),
								] ),
							] )
						)
					),
					E.col( { w: 50 }, [ E.widget( 'storebox-enquiry-form', { design: '', type: 'contact', form_id: 'contact', card: 'yes', label_location: 'Location', note: 'We only use your details to answer this message.' } ) ] ),
				] ),
			] ),
			S.faq( { pad: D.sec, extra: true } ),
		],
	};

	return { sections: S, pages };
};

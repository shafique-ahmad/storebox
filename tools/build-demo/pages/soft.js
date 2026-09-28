/**
 * Demo 1 — Self Storage (soft design): page layouts.
 */
'use strict';

const C = require( './common' );

const { E, url, sid, PHONE, PHONE_URL } = C;

module.exports = ( D, B ) => {
	const S = {};
	const white70 = 'rgba(255,255,255,0.72)';

	/* ------------------------------------------------------------ sections */

	/** Dark page hero with photo, breadcrumb, eyebrow, title and lead. */
	S.pageHero = ( o ) =>
		E.section(
			{
				pad: o.short ? [ 158, 62 ] : [ 170, 78 ],
				padTablet: o.short ? [ 140, 56 ] : [ 150, 70 ],
				padMobile: [ 130, 58 ],
				bg: o.image ? { image: o.image, global: 'primary', overlay: { gradient: [ 'rgba(13,29,38,0.94)', 'rgba(13,29,38,0.55)' ], angle: 100, stops: [ 0, 100 ] } } : { global: 'primary' },
				classes: 'sb-e-overlay-top',
			},
			[
				C.small( '<a href="' + url.home + '">Home</a>' + ( o.parent ? ' <span aria-hidden="true">/</span> <a href="' + o.parent[ 1 ] + '">' + o.parent[ 0 ] + '</a>' : '' ) + ' <span aria-hidden="true">/</span> <span aria-current="page">' + o.crumb + '</span>', { color: null, colorHex: 'rgba(255,255,255,0.72)', size: 0.84, margin: [ 0, 0, 22, 0 ], extra: { _css_classes: 'sb-e-crumbs sb-e-crumbs--on-dark' } } ),
				C.eyebrow( D, o.eyebrow, { onDark: true } ),
				E.heading( o.title, { tag: 'h1', typo: 'sbpage', color: 'sbwhite', margin: [ 16, 0, 0, 0 ], width: 760 } ),
				o.lede ? E.text( o.lede, { typo: 'sblede', colorHex: 'rgba(255,255,255,0.76)', margin: [ 20, 0, 0, 0 ], width: 600 } ) : null,
			]
		);

	/** Image band call to action. */
	S.cta = ( o = {} ) =>
		E.section(
			{
				pad: [ 118, 118 ],
				padMobile: [ 88, 88 ],
				bg: { image: 'shutters-row', global: 'primary', overlay: { gradient: [ 'rgba(13,29,38,0.86)', 'rgba(13,29,38,0.93)' ], angle: 180 } },
				extra: { flex_align_items: 'center' },
			},
			[
				C.eyebrow( D, o.eyebrow || 'Ready when you are', { onDark: true, align: 'center' } ),
				E.heading( o.title || 'Reserve a unit in under two minutes.', { tag: 'h2', typo: 'sbh2', color: 'sbwhite', align: 'center', margin: [ 22, 0, 0, 0 ], width: 580 } ),
				E.text( o.lede || 'No deposit, no contract to sign today, and we hold it free for seven days.', { typo: 'sblede', colorHex: 'rgba(255,255,255,0.76)', align: 'center', margin: [ 22, 0, 0, 0 ], width: 560 } ),
				E.row( { gap: 14, justify: 'center', stack: 'mobile', extra: { margin: E.dims( 38, 0, 0, 0 ), flex_align_items_mobile: 'center', width: E.pct( 100 ) } }, [
					E.button( 'Find my size', url.sizes, B.yellow ),
					E.button( PHONE, PHONE_URL, B.ghost ),
				] ),
			]
		);

	/** FAQ section. */
	S.faq = ( o = {} ) =>
		E.section( { pad: [ D.secHome, D.secHome ], bg: o.bg, id: 'faq' }, [
			C.head( D, { eyebrow: 'Questions', title: 'The things people actually ask.' } ),
			C.faq( D, C.faqItems( D, o.extra ) ),
		] );

	/** Two-column split: text left, rounded image right. */
	S.textImage = ( o ) =>
		E.section( { pad: [ 64, 64 ], bg: { global: 'sbsurface' } }, [
			E.row( { gap: 64, gapTablet: 40, align: 'center' }, [
				E.col( { w: 50 }, [
					C.eyebrow( D, o.eyebrow ),
					E.heading( o.title, { typo: 'sbh2', color: 'primary', margin: [ 16, 0, 0, 0 ] } ),
					E.text( o.lede, { typo: 'sblede', color: 'sbmuted', margin: [ 18, 0, 0, 0 ], width: 560 } ),
					E.button( o.button, o.buttonUrl, B.dark, { icon: 'fas fa-arrow-right', margin: [ 26, 0, 0, 0 ] } ),
				] ),
				E.col( { w: 50 }, [ E.img( o.image, { alt: o.alt, height: [ 360, 380, 240 ], radius: D.radiusLg } ) ] ),
			] ),
		] );

	/** Size calculator next to a photo with a floating callout. */
	S.calculator = ( o ) =>
		E.section( { pad: [ o.pad || D.secHome, o.pad || D.secHome ], bg: { global: 'sbsurface' }, id: 'calc' }, [
			E.row( { gap: 64, gapTablet: 44, align: 'center' }, [
				E.col( { w: 48 }, [
					E.img( o.image || 'packing-table', { alt: o.alt || '', height: [ 700, 520, 300 ], radius: D.radiusLg } ),
					E.col(
						{
							bg: '#FFFFFF',
							radius: D.radius,
							pad: [ 20, 24, 20, 24 ],
							extra: {
								width: E.px( 300 ),
								width_mobile: E.pct( 88 ),
								margin: E.dims( -126, -22, 0, 0 ),
								margin_tablet: E.dims( -126, 20, 0, 0 ),
								margin_mobile: E.dims( -40, 0, 0, 20 ),
								_flex_align_self: 'flex-end',
								_flex_align_self_mobile: 'flex-start',
								z_index: 2,
								box_shadow_box_shadow_type: 'yes',
								box_shadow_box_shadow: { horizontal: 0, vertical: 22, blur: 50, spread: 0, color: 'rgba(13,29,38,0.2)' },
							},
						},
						[
							E.heading( '30 sec', { tag: 'p', typo: 'sbbig', color: 'primary' } ),
							C.small( 'Average time to a size and a price', { size: 0.82, margin: [ 5, 0, 0, 0 ] } ),
						]
					),
				] ),
				E.col( { w: 52 }, [
					C.eyebrow( D, o.eyebrow ),
					E.heading( o.title, { typo: 'sbh2', color: 'primary', margin: [ 18, 0, 20, 0 ] } ),
					E.text( o.lede, { typo: 'sblede', color: 'sbmuted', margin: [ 0, 0, 30, 0 ], width: 560 } ),
					E.widget( 'storebox-size-calculator', { design: '', button_url: { url: url.units, is_external: '', nofollow: '' } } ),
				] ),
			] ),
		] );

	/* ---------------------------------------------------------------- home */

	S.hero = () =>
		E.section(
			{
				pad: [ 140, 122 ],
				padTablet: [ 124, 110 ],
				padMobile: [ 120, 104 ],
				bg: { image: 'storage-corridor-wide', global: 'primary', overlay: { gradient: [ 'rgba(13,29,38,0.62)', 'rgba(13,29,38,0.92)' ], angle: 180 } },
				classes: 'sb-e-overlay-top',
				extra: { min_height: E.vh( 100 ), flex_justify_content: 'flex-end' },
			},
			[
				C.eyebrow( D, 'Amsterdam &amp; Haarlem', { onDark: true } ),
				E.heading( 'Space for the things you\'re <em>not</em> ready to part with.', { tag: 'h1', typo: 'sbh1', color: 'sbwhite', classes: 'sb-e-accent sb-e-balance', margin: [ 20, 0, 0, 0 ], width: 685, extra: { _element_width_tablet: 'initial', _element_custom_width_tablet: E.px( 410 ) } } ),
				E.text( 'Clean, dry, monitored units from 2 to 30 m². Month to month, your own key, and no deposit to get started.', { typo: 'sbledel', colorHex: 'rgba(255,255,255,0.8)', margin: [ 26, 0, 0, 0 ], width: 620 } ),
				E.row( { gap: 14, stack: 'mobile', extra: { margin: E.dims( 38, 0, 0, 0 ), flex_align_items_mobile: 'flex-start' } }, [
					E.button( 'Find my size', '#calc', B.yellow, { icon: 'fas fa-arrow-right' } ),
					E.button( 'See units &amp; prices', url.units, B.ghost ),
				] ),
				E.col(
					{
						extra: {
							margin: E.dims( 52, 0, 0, 0 ),
							margin_mobile: E.dims( 34, 0, 0, 0 ),
							padding: E.dims( 28, 0, 0, 0 ),
							border_border: 'solid',
							border_width: E.dims( 1, 0, 0, 0 ),
							border_color: 'rgba(255,255,255,0.16)',
						},
					},
					[
						E.iconList(
							[
								{ text: '24/7 access with your own PIN', icon: 'far fa-clock' },
								{ text: 'Your lock, your key', icon: 'fas fa-lock' },
								{ text: 'Cancel any time, two weeks\' notice', icon: 'fas fa-check' },
							],
							{ inline: true, space: 34, iconColor: 'secondary', textColorHex: white70, extra: { icon_typography_typography: 'custom', icon_typography_font_size: E.rem( 0.92 ) } }
						),
					]
				),
			]
		);

	S.stats = () => {
		const stat = ( value, label, suffix ) =>
			E.col(
				{
					bg: '#FFFFFF',
					pad: [ 32, 28, 32, 28 ],
					extra: { width: E.pct( 20 ), width_tablet: E.pct( 45 ), width_mobile: E.pct( 45 ), _flex_size: 'grow', css_classes: 'sb-e-dash', flex_gap: E.gap( 10 ) },
				},
				[ E.counter( value, label, { suffix } ) ]
			);
		return E.section(
			{ pad: [ 0, 0 ], padTablet: [ 0, 0 ], padMobile: [ 0, 0 ], tag: 'div', extra: { margin: E.dims( -56, 0, 0, 0 ), z_index: 3 } },
			[
				E.container(
					{
						flex_direction: 'row',
						flex_wrap: 'wrap',
						flex_gap: E.gap( 1 ),
						background_background: 'classic',
						__globals__: { background_color: E.color( 'sbborder' ) },
						border_radius: E.dims( D.radiusLg ),
						overflow: 'hidden',
						box_shadow_box_shadow_type: 'yes',
						box_shadow_box_shadow: { horizontal: 0, vertical: 26, blur: 60, spread: 0, color: 'rgba(13,29,38,0.18)' },
					},
					[
						stat( 3, 'Facilities across Noord-Holland' ),
						stat( 740, 'Units, from 2 m² to 30 m²' ),
						stat( 24, 'Access, every day of the year', '/7' ),
						stat( 4.8, 'Average rating from 1,200 reviews' ),
					]
				),
			]
		);
	};

	S.how = () =>
		E.section( { pad: [ 78, D.secHome ], id: 'how' }, [
			C.head( D, { eyebrow: 'How it works', title: 'Moved in by this afternoon.', lede: 'No viewings, no paperwork, no waiting on a callback. Three steps and the unit is yours.' } ),
			// Two steps per row on tablets, as in the design.
			E.row(
				{ gap: 26, stack: 'mobile', extra: { flex_wrap_tablet: 'wrap' } },
				[
					[ '01', 'Work out your size', 'Tell the calculator what you\'re storing. It gives you a size and a price in about thirty seconds — no phone call needed.' ],
					[ '02', 'Reserve it online', 'Pick your facility and move-in date. We hold the unit for seven days, free, while you sort the van.' ],
					[ '03', 'Collect your PIN', 'Sign at reception or on your phone, fit your own padlock, and you\'re in. Trolleys and the loading bay are free.' ],
				].map( ( step ) =>
					E.col( { w: 33.33, pad: [ 30, 0, 0, 0 ], extra: { width_tablet: E.pct( 48 ), border_border: 'solid', border_width: E.dims( 2, 0, 0, 0 ), __globals__: { border_color: E.color( 'sbborder' ) } } }, C.numbered( D, step[ 0 ], step[ 1 ], step[ 2 ] ) )
				)
			),
		] );

	S.homeUnits = () =>
		E.section( { pad: [ D.secHome, D.secHome ], bg: { global: 'sbsurface' }, id: 'sizes' }, [
			C.headSplit( D, { eyebrow: 'Units &amp; prices', title: 'Available this week.' }, E.button( 'View all 740 units', url.units, B.dark ) ),
			E.widget( 'storebox-unit-grid', {
				design: '',
				source: 'manual',
				ids: [ sid( 'unit-small-5' ), sid( 'unit-medium-10' ), sid( 'unit-large-20' ) ],
				columns: '3',
				columns_tablet: '2',
				columns_mobile: '1',
				description: 'bullets',
				bullets: 'highlights',
				bullet_limit: 3,
				bullet_location: '',
				subline: 'dimensions',
			} ),
		] );

	S.bento = () => {
		const cell = ( o, children ) =>
			E.col(
				{
					radius: D.radiusLg,
					pad: o.photo ? [ 0, 0, 0, 0 ] : [ 26, 26, 26, 26 ],
					bg: o.photo ? { image: o.photo } : ( o.bg || { global: 'sbsurface' } ),
					extra: {
						width: E.pct( o.w ),
						width_tablet: E.pct( o.wt || o.w ),
						width_mobile: E.pct( 100 ),
						min_height: E.px( o.h || 204 ),
						min_height_mobile: E.px( o.photo ? 220 : 186 ),
						overflow: 'hidden',
						flex_justify_content: o.tall ? 'space-between' : 'flex-start',
					},
				},
				children
			);
		const feature = ( iconName, title, body, tone ) => {
			const dark = 'dark' === tone;
			const yellow = 'yellow' === tone;
			return [
				E.icon( iconName, { size: 24, primary: dark ? 'secondary' : ( yellow ? 'sbdark' : 'accent' ), margin: [ 0, 0, 20, 0 ] } ),
				E.col( {}, [
					E.heading( title, { tag: 'h3', typo: 'secondary', color: dark ? 'sbwhite' : 'primary', margin: [ 0, 0, 8, 0 ], extra: { typography_typography: 'custom', typography_font_size: E.rem( 1.06 ) } } ),
					C.small( body, { size: 0.9, color: dark || yellow ? null : 'sbmuted', colorHex: dark ? 'rgba(255,255,255,0.66)' : ( yellow ? 'rgba(13,29,38,0.72)' : undefined ) } ),
				] ),
			];
		};

		return E.section( { pad: [ D.secHome, D.secHome ], id: 'why' }, [
			C.head( D, { eyebrow: 'Why StoreBox', title: 'The boring things, done properly.', lede: 'Dry units, real security and prices that don\'t creep up after three months.' } ),
			E.col( { gap: 20 }, [
				E.row( { gap: 20, stack: 'mobile' }, [
					cell( { w: 25, wt: 34, h: 428, tall: true, bg: { global: 'primary' } }, feature( 'fas fa-shield-alt', 'Alarmed, monitored, recorded', 'Every unit door is individually alarmed. Cameras cover the corridors and entrances around the clock, and footage is kept for 30 days.', 'dark' ) ),
					E.col( { w: 75, gap: 20, extra: { width_tablet: E.pct( 66 ) } }, [
						E.row( { gap: 20, stack: 'mobile' }, [
							cell( { w: 66.66, photo: 'unit-open-door' }, [] ),
							cell( { w: 33.33, bg: { global: 'secondary' } }, feature( 'far fa-clock', '24/7, genuinely', 'Not "extended hours". Your PIN works at 3am on a Sunday.', 'yellow' ) ),
						] ),
						E.row( { gap: 20, stack: 'mobile' }, [
							cell( { w: 33.33 }, feature( 'fas fa-thermometer-half', 'Held at 10–20°C', 'Documents, electronics and furniture stay dry through a Dutch winter.' ) ),
							cell( { w: 66.66 }, feature( 'fas fa-truck', 'Park at the door', 'Drive-up bays at every facility, with free trolleys and a covered loading area so you\'re not carrying boxes through the rain.' ) ),
						] ),
					] ),
				] ),
				E.row( { gap: 20, stack: 'mobile' }, [
					cell( { w: 50, photo: 'unit-shelving' }, [] ),
					cell( { w: 25 }, feature( 'fas fa-tag', 'The price you were quoted', 'No admin fee, no deposit, and no increase in your first twelve months.' ) ),
					cell( { w: 25 }, feature( 'fas fa-exchange-alt', 'Swap sizes for free', 'Too big or too small? Move to another unit any time and just pay the difference.' ) ),
				] ),
			] ),
		] );
	};

	S.homeLocations = () =>
		E.section( { pad: [ D.secHome, D.secHome ], bg: { global: 'sbsurface' }, id: 'locations' }, [
			C.headSplit( D, { eyebrow: 'Locations', title: 'Three facilities, one price list.' }, E.button( 'Get directions', url.locations, B.dark ) ),
			E.widget( 'storebox-location-grid', { design: '', skin: 'overlay', columns: '3', columns_tablet: '2', columns_mobile: '1', tag_limit: 1, show_access: 'yes', show_free: 'yes' } ),
		] );

	S.quote = () =>
		E.section( { pad: [ D.secHome, D.secHome ], bg: { global: 'primary' } }, [
			E.row( { gap: 64, gapTablet: 44, align: 'center' }, [
				E.col( { w: 56 }, [
					C.eyebrow( D, 'What people say', { onDark: true } ),
					E.heading( '"I booked at nine in the evening and had my stuff in by lunchtime the next day."', { tag: 'p', typo: 'sbquote', color: 'sbwhite', margin: [ 26, 0, 0, 0 ], width: 620 } ),
					E.row( { gap: 15, align: 'center', stack: false, extra: { margin: E.dims( 34, 0, 0, 0 ) } }, [
						E.col( { bg: { global: 'secondary' }, radius: 999, align: 'center', justify: 'center', extra: { width: E.px( 48 ), width_tablet: E.px( 48 ), width_mobile: E.px( 48 ), min_height: E.px( 48 ), _flex_size: 'none' } }, [
							E.heading( 'MK', { tag: 'span', color: 'sbdark', align: 'center', extra: { typography_typography: 'custom', typography_font_weight: '800', typography_font_size: E.rem( 1 ) } } ),
						] ),
						E.col( {}, [
							E.heading( 'Marieke K.', { tag: 'p', color: 'sbwhite', extra: { typography_typography: 'custom', typography_font_weight: '700', typography_font_size: E.rem( 0.98 ) } } ),
							C.small( 'Moved from Amsterdam Oost, stored 6 months', { color: null, colorHex: 'rgba(255,255,255,0.6)', size: 0.86 } ),
						] ),
					] ),
				] ),
				E.col( { w: 44 }, [ E.img( 'moving-boxes-room', { alt: 'Moving boxes stacked in a bright room', height: [ 470, 440, 320 ], radius: D.radiusLg } ) ] ),
			] ),
		] );

	/* --------------------------------------------------------------- pages */

	const pages = {};

	pages.home = {
		title: 'Home',
		settings: { hide_title: 'yes', storebox_transparent_header: 'on' },
		content: () => [
			S.hero(),
			S.stats(),
			S.how(),
			S.homeUnits(),
			S.calculator( {
				eyebrow: 'Size calculator',
				title: 'Stop guessing how much space you need.',
				lede: 'Most people overpay by renting a size too big. Add what you\'re storing and we\'ll tell you exactly what fits.',
				alt: 'Open cardboard box on a table with packing tape and a marker',
			} ),
			S.bento(),
			S.homeLocations(),
			S.quote(),
			S.faq(),
			S.cta( { lede: 'No deposit, no contract to sign today, and we\'ll hold it free for seven days.' } ),
		],
	};

	pages.units = {
		title: 'Units',
		settings: { hide_title: 'yes', storebox_transparent_header: 'on' },
		content: () => [
			S.pageHero( { image: 'storage-corridor-wide', crumb: 'Units', eyebrow: 'Units &amp; prices', title: 'Every unit, every price, updated daily.', lede: 'Eight sizes across three facilities, from a 2 m² locker to a 30 m² stockroom. Prices include insurance-grade security and 24/7 access.' } ),
			E.section( { pad: [ 0, D.sec ] }, [
				E.widget( 'storebox-unit-grid', {
					design: '',
					source: 'all',
					columns: '4',
					columns_tablet: '2',
					columns_mobile: '1',
					filters: 'yes',
					filters_overlap: 'yes',
					description: 'bullets',
					bullets: 'features',
					bullet_limit: 3,
					bullet_location: 'yes',
					show_name: 'yes',
					subline: 'dimensions',
				} ),
			] ),
			S.textImage( { eyebrow: 'Not sure which?', title: 'Tell us what you are storing instead.', lede: 'The calculator turns a list of furniture into a size in about thirty seconds, and it errs small — most people are surprised how little space they need.', button: 'Open the size calculator', buttonUrl: url.sizes, image: 'unit-shelving', alt: 'Inside a storage unit: shelving with boxes and bins' } ),
			S.cta(),
		],
	};

	pages.sizes = {
		title: 'Find your size',
		slug: 'sizes',
		settings: { hide_title: 'yes', storebox_transparent_header: 'on' },
		content: () => [
			S.pageHero( { image: 'unit-open-door', crumb: 'Find your size', eyebrow: 'Find your size', title: 'Stop guessing how much space you need.', lede: 'Three ways to get it right: the calculator, a to-scale comparison, and a room-by-room guide.' } ),
			S.calculator( { pad: D.sec, eyebrow: '1 · The calculator', title: 'Add what you are storing.', lede: 'Rough numbers are fine. It assumes you stack to 2.2 m and leave an aisle to reach the back.', alt: 'Open cardboard box on a table with packing tape and a marker' } ),
			E.section( { pad: [ D.sec, D.sec ], bg: { global: 'primary' } }, [
				C.head( D, { eyebrow: '2 · Drawn to scale', title: 'Compare them the way you would compare rooms.', lede: 'Each box is proportional to its floor area. Tap one for the details.', onDark: true } ),
				E.widget( 'storebox-size-chooser', { design: '', selected: 3, button_url: { url: url.units, is_external: '', nofollow: '' } } ),
			] ),
			E.section( { pad: [ D.sec, D.sec ] }, [
				C.head( D, { eyebrow: '3 · Room by room', title: 'The size guide.', lede: 'What each size holds in practice, based on what our customers actually store.' } ),
				E.widget( 'storebox-size-guide', { design: '' } ),
			] ),
			E.section( { pad: [ D.sec, D.sec ], bg: { global: 'sbsurface' } }, [
				C.head( D, { eyebrow: 'Before you book', title: 'Three things that change the answer.' } ),
				E.row(
					{ gap: 22 },
					[
						[ '01', 'Height is free space', 'Every unit is at least 2.4 m tall. Sturdy boxes of the same size stack to the ceiling, which can halve the floor area you need.' ],
						[ '02', 'Sofas eat floor', 'Stand a sofa on its end and it takes a third of the space. Ask us for a sofa cover — we lend them free.' ],
						[ '03', 'You can swap later', 'Guessed wrong? Move to another size any time and pay only the difference. There is no fee.' ],
					].map( ( tip ) =>
						E.col( { w: 33.33, bg: '#FFFFFF', radius: D.radiusLg, pad: [ 28, 28, 28, 28 ], extra: { border_border: 'solid', border_width: E.dims( 1 ), __globals__: { border_color: E.color( 'sbborder' ) } } }, C.numbered( D, tip[ 0 ], tip[ 1 ], tip[ 2 ], { numGap: 14, titleGap: 8, bodySize: 0.94 } ) )
					)
				),
			] ),
			S.cta(),
		],
	};

	pages.locations = {
		title: 'Locations',
		settings: { hide_title: 'yes', storebox_transparent_header: 'on' },
		content: () => [
			S.pageHero( { image: 'drive-up-units', crumb: 'Locations', eyebrow: 'Locations', title: 'Three facilities, one price list.', lede: 'Two in Amsterdam, one in Haarlem. Same prices, same security, same contract at all three.' } ),
			E.section( { pad: [ 72, 0 ] }, [ E.widget( 'storebox-location-map', { design: '', source: 'all', ratio: '16 / 7' } ) ] ),
			E.section( { pad: [ 56, D.sec ] }, [
				E.widget( 'storebox-location-grid', { design: '', skin: 'card', columns: '3', columns_tablet: '2', columns_mobile: '1', show_excerpt: 'yes', show_meta: 'yes', address: 'full' } ),
			] ),
			E.section( { pad: [ 64, 64 ], bg: { global: 'sbsurface' } }, [
				E.row( { gap: 64, gapTablet: 32, align: 'center' }, [
					E.col( { w: 50 }, [ C.eyebrow( D, 'At every facility' ), E.heading( 'The same standard, whichever you pick.', { typo: 'sbh2', color: 'primary', margin: [ 16, 0, 0, 0 ] } ) ] ),
					E.col( { w: 50 }, [
						E.row( { gap: 24, stack: 'mobile', extra: { flex_gap_mobile: E.gap( 12 ) } }, [
							E.col( { w: 50 }, [ E.iconList( [ { text: 'Individually alarmed doors' }, { text: 'Free trolleys and loading bay' }, { text: 'Monthly rolling contract' } ], { iconColor: 'sbsuccess', textColor: 'text', space: 12, extra: { icon_typography_typography: 'custom', icon_typography_font_size: E.rem( 0.96 ) } } ) ] ),
							E.col( { w: 50 }, [ E.iconList( [ { text: 'CCTV kept for 30 days' }, { text: 'Your own padlock and PIN' }, { text: 'Swap sizes without a fee' } ], { iconColor: 'sbsuccess', textColor: 'text', space: 12, extra: { icon_typography_typography: 'custom', icon_typography_font_size: E.rem( 0.96 ) } } ) ] ),
						] ),
					] ),
				] ),
			] ),
			S.cta(),
		],
	};

	pages.about = {
		title: 'About',
		settings: { hide_title: 'yes', storebox_transparent_header: 'on' },
		content: () => {
			const person = ( initials, name, role, bio, bg, fg ) =>
				E.col( { w: 25, bg: '#FFFFFF', radius: D.radiusLg, pad: [ 26, 26, 26, 26 ], extra: { width_tablet: E.pct( 45 ), _flex_size_tablet: 'grow', border_border: 'solid', border_width: E.dims( 1 ), __globals__: { border_color: E.color( 'sbborder' ) } } }, [
					E.col( { bg: bg, radius: 999, align: 'center', justify: 'center', extra: { width: E.px( 56 ), width_tablet: E.px( 56 ), width_mobile: E.px( 56 ), min_height: E.px( 56 ), margin: E.dims( 0, 0, 16, 0 ) } }, [
						E.heading( initials, { tag: 'span', colorHex: fg, align: 'center', extra: { typography_typography: 'custom', typography_font_weight: '800', typography_font_size: E.rem( 1.05 ) } } ),
					] ),
					E.heading( name, { tag: 'h3', typo: 'secondary', color: 'primary', margin: [ 0, 0, 3, 0 ], extra: { typography_typography: 'custom', typography_font_size: E.rem( 1.02 ) } } ),
					C.small( role, { size: 0.88 } ),
					C.small( bio, { size: 0.9, margin: [ 12, 0, 0, 0 ] } ),
				] );
			return [
				S.pageHero( { image: 'storage-corridor-trolley', crumb: 'About', eyebrow: 'About', title: 'We started StoreBox because we needed a unit ourselves.', lede: 'In 2014 the options were a damp garage or a warehouse with a four-page contract. So we built the thing we wanted to rent.' } ),
				E.section( { pad: [ D.sec, D.sec ] }, [
					E.row( { gap: 64, gapTablet: 40, align: 'center' }, [
						E.col( { w: 50 }, [ E.img( 'moving-boxes-room', { alt: 'Moving boxes stacked in a bright room beside a large window', height: [ 640, 520, 340 ], radius: D.radiusLg } ) ] ),
						E.col( { w: 50 }, [
							C.eyebrow( D, 'Our story' ),
							E.heading( 'Simple storage, done properly.', { typo: 'sbh2', color: 'primary', margin: [ 16, 0, 22, 0 ] } ),
							E.text(
								'<p>Our founders were between flats with a houseful of furniture and nowhere to put it. The places they found wanted a deposit, a twelve-month contract and a viewing appointment three days away.</p><p>So in 2014 we opened our first facility in Amsterdam Noord with one rule: it should be as easy to rent a storage unit as it is to book a hotel room. No deposit, no minimum term, and a price you can see before you call.</p><p>Twelve years and three facilities later, that is still the rule.</p>',
								{ typo: 'text', color: 'text', classes: 'sb-e-prose' }
							),
						] ),
					] ),
				] ),
				E.section( { pad: [ D.sec, D.sec ], bg: { global: 'sbsurface' } }, [
					C.head( D, { eyebrow: 'What we stand for', title: 'Three promises we have never broken.' } ),
					E.row(
						{ gap: 26 },
						[
							[ '01', 'The price you see is the price you pay', 'No admin fee, no deposit, and no increase in your first twelve months. If a competitor is cheaper for the same size, tell us.' ],
							[ '02', 'Your things, your key', 'Only you can open your unit. Staff cannot get in without you, and we never cut a lock without a court order.' ],
							[ '03', 'Leave whenever you like', 'Two weeks\' notice, unused days refunded. We would rather you came back than felt trapped.' ],
						].map( ( v ) => E.col( { w: 33.33, pad: [ 26, 0, 0, 0 ], extra: { border_border: 'solid', border_width: E.dims( 2, 0, 0, 0 ), __globals__: { border_color: E.color( 'primary' ) } } }, C.numbered( D, v[ 0 ], v[ 1 ], v[ 2 ], { numGap: 12 } ) ) )
					),
				] ),
				E.section( { pad: [ D.sec, D.sec ], bg: { global: 'primary' } }, [
					E.row( { gap: 64, gapTablet: 44, align: 'flex-start' }, [
						E.col( { w: 50 }, [
							C.eyebrow( D, 'Since 2014', { onDark: true } ),
							E.heading( 'From one corridor to three facilities.', { typo: 'sbh2', color: 'sbwhite', margin: [ 16, 0, 0, 0 ] } ),
							E.text( 'Grown entirely by word of mouth, which is why we still answer the phone ourselves.', { typo: 'sblede', colorHex: 'rgba(255,255,255,0.66)', margin: [ 18, 0, 0, 0 ], width: 520 } ),
							E.container(
								{ flex_direction: 'row', flex_gap: E.gap( 1 ), margin: E.dims( 34, 0, 0, 0 ), border_radius: E.dims( D.radiusLg ), overflow: 'hidden', background_background: 'classic', __globals__: { background_color: E.color( 'sbborder' ) } },
								[
									E.col( { bg: '#FFFFFF', pad: [ 28, 26, 28, 26 ], extra: { width: E.pct( 50 ), width_tablet: E.pct( 50 ), width_mobile: E.pct( 50 ), css_classes: 'sb-e-dash', flex_gap: E.gap( 10 ) } }, [ E.counter( 740, 'Units' ) ] ),
									E.col( { bg: '#FFFFFF', pad: [ 28, 26, 28, 26 ], extra: { width: E.pct( 50 ), width_tablet: E.pct( 50 ), width_mobile: E.pct( 50 ), css_classes: 'sb-e-dash', flex_gap: E.gap( 10 ) } }, [ E.counter( 4.8, 'Average rating' ) ] ),
								]
							),
						] ),
						E.col(
							{ w: 50, extra: { css_classes: 'sb-e-timeline' } },
							[
								[ '2014', 'Amsterdam Noord opens', '84 units on a single floor of a former print works.' ],
								[ '2017', 'Second floor, climate control', 'Noord grows to 318 units, all heated and ventilated.' ],
								[ '2020', 'Amsterdam Zuidoost', 'Our first drive-up site: every door at ground level.' ],
								[ '2023', 'Haarlem Oudeweg', 'Built around business storage, with pallet delivery and racking.' ],
								[ '2026', 'Reserve online, move in today', 'The whole process moves online, from quote to PIN.' ],
							].map( ( y, i, all ) =>
								E.col( { pad: [ 0, 0, i === all.length - 1 ? 0 : 30, 40 ], extra: { css_classes: 'sb-e-timeline-item' } }, [
									E.heading( y[ 0 ], { tag: 'p', color: 'secondary', extra: { typography_typography: 'custom', typography_font_size: E.rem( 0.84 ), typography_font_weight: '800', typography_letter_spacing: E.em( 0.08 ) } } ),
									E.heading( y[ 1 ], { tag: 'h3', typo: 'secondary', color: 'sbwhite', margin: [ 6, 0, 6, 0 ] } ),
									C.small( y[ 2 ], { color: null, colorHex: 'rgba(255,255,255,0.66)', size: 0.95 } ),
								] )
							)
						),
					] ),
				] ),
				E.section( { pad: [ D.sec, D.sec ] }, [
					C.head( D, { eyebrow: 'The team', title: 'The people who pick up the phone.' } ),
					E.row( { gap: 24, stack: 'mobile', extra: { flex_direction_tablet: 'row', flex_wrap_tablet: 'wrap' } }, [
						person( 'ED', 'Eva de Vries', 'Founder', 'Still does a shift at Noord reception every Friday.', { global: 'primary' }, '#FFFFFF' ),
						person( 'JB', 'Jasper Bakker', 'Operations', 'Knows every door code change and every leaking gutter.', { global: 'secondary' }, '#0D1D26' ),
						person( 'SM', 'Samira Mansour', 'Customer care', 'The voice on the phone, most likely, if you call.', { global: 'accent' }, '#FFFFFF' ),
						person( 'TK', 'Tom Kuipers', 'Business accounts', 'Sets up racking, pallet deliveries and invoicing for shops.', { global: 'sbsuccess' }, '#FFFFFF' ),
					] ),
				] ),
				S.cta( { title: 'Come and have a look.', lede: 'Every facility is open for a walk-round during office hours, no appointment needed.' } ),
			];
		},
	};

	pages.contact = {
		title: 'Contact',
		settings: { hide_title: 'yes', storebox_transparent_header: 'on' },
		content: () => {
			const way = ( iconName, title, strong, line, link ) =>
				E.container(
					Object.assign(
						{
							flex_direction: 'row',
							flex_gap: E.gap( 16 ),
							flex_align_items: 'flex-start',
							padding: E.dims( 20 ),
							border_radius: E.dims( D.radiusLg ),
							background_background: 'classic',
							__globals__: { background_color: E.color( 'sbsurface' ) },
							css_classes: 'sb-e-way',
						},
						link ? { html_tag: 'a', link: { url: link, is_external: '', nofollow: '' } } : {}
					),
					[
						E.icon( iconName, { view: 'stacked', shape: 'square', size: 18, padding: 13, radius: 12, primary: 'primary', secondary: 'secondary', extra: { _flex_size: 'none' } } ),
						E.col( {}, [
							E.heading( title, { tag: 'h3', typo: 'secondary', color: 'primary', margin: [ 0, 0, 3, 0 ], extra: { typography_typography: 'custom', typography_font_size: E.rem( 1 ) } } ),
							C.small( '<b>' + strong + '</b><br>' + line, { size: 0.92 } ),
						] ),
					]
				);
			return [
				S.pageHero( { image: 'unit-small-yellow', crumb: 'Contact', eyebrow: 'Contact', title: 'Talk to a person, not a form.', lede: 'Call, email or drop in. We answer the phone during office hours, and email replies usually come within the hour.' } ),
				E.section( { pad: [ D.sec, D.sec ] }, [
					E.row( { gap: 56, gapTablet: 44, align: 'flex-start' }, [
						E.col( { w: 42 }, [
							C.eyebrow( D, 'Get in touch' ),
							E.heading( 'Whichever is easiest.', { typo: 'sbh2', color: 'primary', margin: [ 16, 0, 26, 0 ] } ),
							E.col( { gap: 14 }, [
								way( 'fas fa-phone-alt', 'Call', PHONE, 'Mon–Sat 08:00–18:00', PHONE_URL ),
								way( 'far fa-envelope', 'Email', 'hello@example.com', 'Usually answered within the hour', 'mailto:hello@example.com' ),
								way( 'far fa-comment-dots', 'WhatsApp', '06 00 00 00 00', 'Photos of what you are storing welcome', '' ),
								way( 'fas fa-map-marker-alt', 'Visit', 'Three facilities', 'Walk-round any time during office hours', url.locations ),
							] ),
						] ),
						E.col( { w: 58 }, [ E.widget( 'storebox-enquiry-form', { design: '', type: 'contact', form_id: 'contact', card: 'yes', note: 'We only use your details to answer this message.' } ) ] ),
					] ),
				] ),
				S.faq( { bg: { global: 'sbsurface' }, extra: true } ),
			];
		},
	};

	return { sections: S, pages };
};

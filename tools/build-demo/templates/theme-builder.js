/**
 * Elementor Pro Theme Builder templates of both demos, and the list of
 * sections exported as standalone templates.
 *
 * Templates use Pro widgets (Nav Menu, Post Content, Search Form…), core
 * widgets and Storebox widgets, plus dynamic tags for titles and fields.
 */
'use strict';

const C = require( '../pages/common' );

const { E, url, PHONE, PHONE_URL } = C;

/* -------------------------------------------------------------- sections */

/** Sections exported to elementor-templates/sections. */
function sectionsFor( demo, S ) {
	const soft = 'soft' === demo.preset;
	const list = soft
		? [
			[ 'hero', 'Hero', () => S.hero() ],
			[ 'stats', 'Stats', () => S.stats() ],
			[ 'how-it-works', 'How it works', () => S.how() ],
			[ 'units', 'Units and prices', () => S.homeUnits() ],
			[ 'calculator', 'Size calculator', () => S.calculator( { eyebrow: 'Size calculator', title: 'Stop guessing how much space you need.', lede: 'Most people overpay by renting a size too big. Add what you\'re storing and we\'ll tell you exactly what fits.' } ) ],
			[ 'features-bento', 'Features (bento)', () => S.bento() ],
			[ 'locations', 'Locations', () => S.homeLocations() ],
			[ 'testimonial', 'Testimonial', () => S.quote() ],
			[ 'faq', 'FAQ', () => S.faq() ],
			[ 'cta', 'Call to action', () => S.cta() ],
			[ 'page-hero', 'Page hero', () => S.pageHero( { image: 'storage-corridor-wide', crumb: 'Page', eyebrow: 'Eyebrow', title: 'A page title that says what the page is for.', lede: 'One or two sentences that tell visitors what they will find here.' } ) ],
		]
		: [
			[ 'hero', 'Hero (split)', () => S.hero() ],
			[ 'units-rail', 'Units rail', () => S.rail() ],
			[ 'size-chooser', 'Size chooser', () => S.chooser( { eyebrow: 'Find your size', title: 'Drawn to scale, so you can actually picture it.', lede: 'Every size compared side by side at true proportion. Tap one to see what fits and what it costs.', button: 'Reserve this size', image: 'packing-table', alt: '' } ) ],
			[ 'features-list', 'Features (numbered list)', () => S.features() ],
			[ 'locations', 'Locations list', () => S.homeLocations() ],
			[ 'testimonial', 'Testimonial', () => S.quote() ],
			[ 'faq', 'FAQ', () => S.faq( { lede: 'Can\'t see yours? Call us on ' + PHONE + ' — a person answers.' } ) ],
			[ 'cta', 'Call to action', () => S.cta() ],
			[ 'page-header', 'Page header', () => S.pageHero( { crumb: 'Page', eyebrow: 'Eyebrow', title: 'A page title that says what the page is for.', lede: 'One or two sentences that tell visitors what they will find here.' } ) ],
		];
	return list.map( ( [ key, title, build ] ) => ( { key, title, build } ) );
}

/* ------------------------------------------------------------- templates */

function templatesFor( demo, D, B, S ) {
	const soft = 'soft' === demo.preset;
	const onDarkText = 'rgba(255,255,255,0.72)';

	/** Logo: three-bar mark and the site title (dynamic). */
	const logo = ( light ) =>
		E.container(
			{
				content_width: 'full',
				flex_direction: 'row',
				flex_align_items: 'center',
				flex_gap: E.gap( 11 ),
				html_tag: 'a',
				link: { url: url.home, is_external: '', nofollow: '' },
				css_classes: 'sb-e-logo',
				width: E.px( 180 ),
				width_tablet: E.px( 180 ),
				width_mobile: E.px( 160 ),
				_flex_size: 'none',
			},
			[
				E.container( { content_width: 'full', flex_gap: E.gap( 3 ), width: E.px( 30 ), width_tablet: E.px( 30 ), width_mobile: E.px( 30 ), _flex_size: 'none' }, [
					[ 22, 'rgba(242,183,5,1)' ],
					[ 30, soft ? 'rgba(242,183,5,0.8)' : 'rgba(242,183,5,1)' ],
					[ 26, 'rgba(242,183,5,0.55)' ],
				].map( ( bar ) =>
					E.container( { content_width: 'full', width: E.px( bar[ 0 ] ), width_tablet: E.px( bar[ 0 ] ), width_mobile: E.px( bar[ 0 ] ), min_height: E.px( 5 ), background_background: 'classic', background_color: bar[ 1 ], border_radius: E.dims( 2 ), css_classes: 'sb-e-logo__bar' } )
				) ),
				E.dyn(
					E.heading( 'StoreBox', {
						tag: 'span',
						color: light ? 'sbwhite' : 'primary',
						extra: { typography_typography: 'custom', typography_font_family: 'Archivo', typography_font_weight: soft ? '800' : '900', typography_font_size: E.rem( soft ? 1.3 : 1.28 ), typography_letter_spacing: E.em( soft ? -0.04 : -0.05 ), typography_line_height: E.em( 1 ) },
					} ),
					'title',
					E.tag( 'site-title' )
				),
			]
		);

	const navMenu = ( light ) =>
		E.widget( 'nav-menu', {
			menu: '{{menu:primary}}',
			layout: 'horizontal',
			align_items: 'right',
			pointer: 'underline',
			animation_line: 'slide',
			indicator: 'chevron-down',
			dropdown: 'tablet',
			toggle: 'burger',
			full_width: 'stretch',
			text_align: 'aside',
			color_menu_item: light ? 'rgba(255,255,255,0.82)' : D.colors.primary,
			color_menu_item_hover: light ? '#FFFFFF' : D.colors.primary,
			color_menu_item_active: light ? '#FFFFFF' : D.colors.primary,
			pointer_width: E.px( 2 ),
			padding_horizontal_menu_item: E.px( 0 ),
			padding_vertical_menu_item: E.px( 6 ),
			menu_space_between: E.px( 30 ),
			toggle_color: light ? '#FFFFFF' : D.colors.primary,
			toggle_size: E.px( 24 ),
			color_dropdown_item: light ? '#FFFFFF' : D.colors.primary,
			background_color_dropdown_item: light ? D.colors.primary : D.colors.bg,
			color_dropdown_item_hover: light ? D.colors.secondary : D.colors.primary,
			background_color_dropdown_item_hover: light ? D.colors.primary : D.colors.surface,
			padding_vertical_dropdown_item: E.px( 14 ),
			_flex_size: 'grow',
			__globals__: {
				menu_typography_typography: E.typo( 'sbnav' ),
				pointer_color_menu_item_hover: E.color( 'secondary' ),
				pointer_color_menu_item_active: E.color( 'secondary' ),
				dropdown_typography_typography: E.typo( 'sbnav' ),
			},
		} );

	/** Page hero used by the Theme Builder templates. */
	const hero = ( o ) => {
		const crumbs = o.crumbs && C.small( o.crumbs, { color: soft ? null : 'sbmuted', colorHex: soft ? onDarkText : undefined, size: soft ? 0.84 : 0.82, margin: [ 0, 0, soft ? 22 : 26, 0 ], extra: { _css_classes: 'sb-e-crumbs' + ( soft ? ' sb-e-crumbs--on-dark' : '' ) } } );
		const eyebrow = C.eyebrow( D, o.eyebrow || 'Eyebrow', { onDark: soft } );
		if ( o.eyebrowTag ) {
			E.dyn( eyebrow, 'title', o.eyebrowTag );
		}
		const title = E.heading( o.title || 'Title', { tag: 'h1', typo: 'sbpage', color: soft ? 'sbwhite' : 'primary', margin: [ 16, 0, 0, 0 ], width: 760 } );
		if ( o.titleTag ) {
			E.dyn( title, 'title', o.titleTag );
		}
		const children = [ crumbs || null, eyebrow, title ];
		if ( o.ledeTag ) {
			children.push( E.dyn( E.heading( 'Lead paragraph', { tag: 'p', typo: 'sblede', colorHex: soft ? 'rgba(255,255,255,0.76)' : undefined, color: soft ? undefined : 'sbmuted', margin: [ 20, 0, 0, 0 ], width: 600 } ), 'title', o.ledeTag ) );
		}
		if ( o.after ) {
			children.push( ...o.after );
		}
		const bg = soft
			? Object.assign( { global: 'primary' }, o.imageTag ? { overlay: { gradient: [ 'rgba(13,29,38,0.94)', 'rgba(13,29,38,0.55)' ], angle: 100 } } : {} )
			: { global: 'sbbg' };
		const sec = E.section(
			{
				pad: soft ? ( o.short ? [ 158, 62 ] : [ 170, 78 ] ) : [ 64, 60 ],
				padMobile: soft ? [ 130, 58 ] : [ 44, 44 ],
				gutter: D.gutter,
				bg,
				classes: soft ? 'sb-e-overlay-top' : '',
				extra: soft ? {} : { border_border: 'solid', border_width: E.dims( 0, 0, 1, 0 ), __globals__: { border_color: E.color( 'sbborder' ) } },
			},
			children
		);
		if ( o.imageTag ) {
			sec.settings.background_image = { url: '', id: '', size: '', alt: '', source: 'library' };
			sec.settings.background_position = 'center center';
			sec.settings.background_size = 'cover';
			E.dyn( sec, 'background_image', o.imageTag );
		}
		return sec;
	};

	const cta = () => S.cta();
	const T = [];

	/* ---------------------------------------------------------- header */
	T.push( {
		key: 'header',
		title: 'Storebox — Header',
		type: 'header',
		folder: 'headers',
		conditions: [ 'include/general' ],
		content: () => {
			if ( soft ) {
				return [
					E.container(
						{
							content_width: 'boxed',
							boxed_width: E.px( D.contentWidth ),
							flex_direction: 'row',
							flex_align_items: 'center',
							flex_justify_content: 'space-between',
							flex_gap: E.gap( 34 ),
							flex_gap_tablet: E.gap( 20 ),
							min_height: E.px( 92 ),
							min_height_mobile: E.px( 72 ),
							padding: E.dims( 0, D.gutter, 0, D.gutter ),
							padding_mobile: E.dims( 0, 20, 0, 20 ),
							html_tag: 'header',
							css_classes: 'sb-e-header-overlay',
						},
						[
							logo( true ),
							navMenu( true ),
							E.hide( E.heading( PHONE, { tag: 'p', color: 'sbwhite', extra: { link: { url: PHONE_URL, is_external: '', nofollow: '' }, typography_typography: 'custom', typography_font_weight: '700', typography_font_size: E.rem( 0.95 ), _flex_size: 'none' } } ), [ 'mobile' ] ),
							E.hide( E.button( 'Reserve a unit', url.units, B.yellow, { extra: { _flex_size: 'none' } } ), [ 'tablet', 'mobile' ] ),
						]
					),
				];
			}
			return [
				E.container(
					{
						content_width: 'boxed',
						boxed_width: E.px( D.contentWidth ),
						flex_direction: 'row',
						flex_wrap: 'wrap',
						flex_justify_content: 'space-between',
						flex_align_items: 'center',
						flex_gap: E.gap( 26, 8 ),
						padding: E.dims( 10, D.gutter, 10, D.gutter ),
						padding_mobile: E.dims( 10, 20, 10, 20 ),
						background_background: 'classic',
						__globals__: { background_color: E.color( 'sbdark' ) },
					},
					[
						E.iconList(
							[ { text: 'Open 24/7, every day of the year', icon: 'far fa-clock' }, { text: 'Amsterdam &amp; Haarlem', icon: 'fas fa-map-marker-alt' } ],
							{ inline: true, space: 26, iconSize: 13, iconColor: 'secondary', textColorHex: onDarkText, extra: { icon_typography_typography: 'custom', icon_typography_font_size: E.rem( 0.82 ) } }
						),
						E.iconList(
							[ { text: 'hello@example.com', icon: 'far fa-envelope', url: 'mailto:hello@example.com' }, { text: PHONE, icon: 'fas fa-phone-alt', url: PHONE_URL } ],
							{ inline: true, space: 26, iconSize: 13, iconColor: 'secondary', textColorHex: onDarkText, extra: { icon_typography_typography: 'custom', icon_typography_font_size: E.rem( 0.82 ), text_color_hover: D.colors.secondary } }
						),
					]
				),
				E.container(
					{
						content_width: 'boxed',
						boxed_width: E.px( D.contentWidth ),
						flex_direction: 'row',
						flex_align_items: 'center',
						flex_gap: E.gap( 36 ),
						flex_gap_tablet: E.gap( 20 ),
						min_height: E.px( 80 ),
						min_height_mobile: E.px( 68 ),
						padding: E.dims( 0, D.gutter, 0, D.gutter ),
						padding_mobile: E.dims( 0, 20, 0, 20 ),
						html_tag: 'header',
						background_background: 'classic',
						border_border: 'solid',
						border_width: E.dims( 0, 0, 1, 0 ),
						__globals__: { background_color: E.color( 'sbbg' ), border_color: E.color( 'sbborder' ) },
						sticky: 'top',
						sticky_on: [ 'desktop', 'tablet', 'mobile' ],
						sticky_offset: 0,
						sticky_effects_offset: 0,
					},
					[
						logo( false ),
						navMenu( false ),
						E.hide( E.button( 'Reserve a unit', url.units, Object.assign( {}, B.dark, { text_padding: E.dims( 13, 24 ) } ), { extra: { _flex_size: 'none' } } ), [ 'tablet', 'mobile' ] ),
					]
				),
			];
		},
	} );

	/* ---------------------------------------------------------- footer */
	T.push( {
		key: 'footer',
		title: 'Storebox — Footer',
		type: 'footer',
		folder: 'footers',
		conditions: [ 'include/general' ],
		content: () => {
			const column = ( title, menuKey ) =>
				E.col( { w: 20, extra: { width_tablet: E.pct( 45 ), _flex_size: 'grow' } }, [
					E.heading( title, { tag: 'h2', color: 'sbwhite', margin: [ 0, 0, soft ? 16 : 17, 0 ], extra: { typography_typography: 'custom', typography_font_size: E.rem( soft ? 0.78 : 0.74 ), typography_font_weight: soft ? '700' : '800', typography_letter_spacing: E.em( soft ? 0.12 : 0.18 ), typography_text_transform: 'uppercase' } } ),
					E.widget( 'nav-menu', {
						menu: '{{menu:' + menuKey + '}}',
						layout: 'vertical',
						align_items: 'left',
						pointer: 'none',
						dropdown: 'none',
						toggle: '',
						color_menu_item: 'rgba(255,255,255,0.6)',
						color_menu_item_hover: D.colors.secondary,
						padding_horizontal_menu_item: E.px( 0 ),
						padding_vertical_menu_item: E.px( 5.5 ),
						menu_typography_typography: 'custom',
						menu_typography_font_size: E.rem( 0.94 ),
						menu_typography_font_weight: '400',
					} ),
				] );
			return [
				E.section(
					{ pad: [ soft ? 76 : 80, soft ? 30 : 32 ], padMobile: [ 60, 28 ], gutter: D.gutter, tag: 'footer', bg: { global: 'sbdark' } },
					[
						E.row( { gap: soft ? 44 : 46, wrap: true, stack: 'mobile', extra: { margin: E.dims( 0, 0, soft ? 52 : 54, 0 ), flex_wrap_tablet: 'wrap', flex_direction_tablet: 'row' } }, [
							E.col( { w: 34, extra: { width_tablet: E.pct( 100 ) } }, [
								logo( true ),
								C.small( 'Self storage in Amsterdam and Haarlem. Month to month, no deposit, your own key.', { color: null, colorHex: 'rgba(255,255,255,0.6)', size: 0.94, lh: 1.6, margin: [ 18, 0, 0, 0 ], extra: E.measure( 320 ) } ),
								E.widget( 'social-icons', {
									social_icon_list: [
										{ _id: E.id(), social_icon: { value: 'fab fa-facebook-f', library: 'fa-brands' }, link: { url: 'https://facebook.com/', is_external: 'on', nofollow: '' } },
										{ _id: E.id(), social_icon: { value: 'fab fa-instagram', library: 'fa-brands' }, link: { url: 'https://instagram.com/', is_external: 'on', nofollow: '' } },
										{ _id: E.id(), social_icon: { value: 'fab fa-linkedin-in', library: 'fa-brands' }, link: { url: 'https://linkedin.com/', is_external: 'on', nofollow: '' } },
									],
									shape: soft ? 'circle' : 'rounded',
									align: 'left',
									icon_color: 'custom',
									icon_primary_color: 'rgba(255,255,255,0.1)',
									icon_secondary_color: 'rgba(255,255,255,0.8)',
									icon_size: E.px( 15 ),
									icon_padding: { unit: 'em', size: 0.75, sizes: [] },
									icon_spacing: E.px( 9 ),
									border_radius: E.dims( soft ? 50 : 4, soft ? 50 : 4, soft ? 50 : 4, soft ? 50 : 4, soft ? '%' : 'px' ),
									hover_primary_color: D.colors.secondary,
									hover_secondary_color: D.colors.dark,
									_margin: E.dims( 22, 0, 0, 0 ),
								} ),
							] ),
							column( 'Units', 'footer-units' ),
							column( 'Locations', 'footer-locations' ),
							column( 'Help', 'footer-help' ),
						] ),
						E.row(
							{ gap: 18, justify: 'space-between', stack: 'mobile', wrap: true, extra: { padding: E.dims( 26, 0, 0, 0 ), border_border: 'solid', border_width: E.dims( 1, 0, 0, 0 ), border_color: 'rgba(255,255,255,0.1)' } },
							[
								E.dyn( C.small( '© 2026 StoreBox', { color: null, colorHex: 'rgba(255,255,255,0.6)', size: 0.85 } ), 'editor', E.tag( 'current-date-time', { date_format: 'custom', time_format: '', custom_format: 'Y', before: '© ', after: ' StoreBox' } ) ),
								C.small( 'Demo content · Storebox WordPress theme', { color: null, colorHex: 'rgba(255,255,255,0.6)', size: 0.85 } ),
							]
						),
					]
				),
			];
		},
	} );

	/* ----------------------------------------------------- single post */
	T.push( {
		key: 'single-post',
		title: 'Storebox — Single post',
		type: 'single-post',
		folder: 'single',
		conditions: [ 'include/singular/post' ],
		settings: { preview_type: 'single/post', preview_id: '{{post:post-how-to-pack-a-storage-unit}}' },
		content: () => [
			hero( {
				short: true,
				crumbs: '<a href="' + url.home + '">Home</a> <span aria-hidden="true">/</span> <a href="' + url.blog + '">Blog</a>',
				eyebrow: 'Category',
				eyebrowTag: E.tag( 'post-terms', { taxonomy: 'category', separator: ', ', link: '' } ),
				title: 'Post title',
				titleTag: E.tag( 'post-title' ),
				after: [
					E.row( { gap: 8, stack: false, align: 'center', extra: { margin: E.dims( 18, 0, 0, 0 ) } }, [
						E.dyn( E.heading( 'Date', { tag: 'span', colorHex: soft ? onDarkText : undefined, color: soft ? undefined : 'sbmuted', extra: { typography_typography: 'custom', typography_font_size: E.rem( soft ? 1.05 : 0.95 ) } } ), 'title', E.tag( 'post-date', { type: 'post_date_gmt', format: 'default' } ) ),
						E.dyn( E.heading( 'Read time', { tag: 'span', colorHex: soft ? onDarkText : undefined, color: soft ? undefined : 'sbmuted', extra: { typography_typography: 'custom', typography_font_size: E.rem( soft ? 1.05 : 0.95 ) } } ), 'title', E.tag( 'storebox-read-time', { before: '· ' } ) ),
					] ),
				],
			} ),
			E.section( { pad: [ 0, D.sec ], gutter: D.gutter, width: 760 }, [
				E.widget( 'theme-post-featured-image', {
					image_size: 'storebox-wide',
					width: E.pct( 100 ),
					height: E.px( 440 ),
					height_mobile: E.px( 240 ),
					'object-fit': 'cover',
					image_border_radius: E.dims( D.radiusLg ),
					_margin: soft ? E.dims( -24, 0, 40, 0 ) : E.dims( 48, 0, 40, 0 ),
					_z_index: 2,
				} ),
				E.widget( 'theme-post-content', { _css_classes: 'sb-prose', __globals__: { text_color: E.color( 'text' ), typography_typography: E.typo( 'text' ) } } ),
				E.divider( { gap: 36 } ),
				E.widget( 'author-box', {
					source: 'current',
					show_avatar: 'yes',
					layout: 'left',
					show_biography: 'yes',
					link_to: '',
					avatar_size: E.px( 52 ),
					author_name_tag: 'p',
					__globals__: { name_color: E.color( 'primary' ), bio_color: E.color( 'sbmuted' ) },
				} ),
				soft ? E.widget( 'share-buttons', {
					share_buttons: [
						{ _id: E.id(), button: 'facebook' },
						{ _id: E.id(), button: 'twitter' },
						{ _id: E.id(), button: 'linkedin' },
						{ _id: E.id(), button: 'email' },
					],
					view: 'icon',
					skin: 'framed',
					shape: soft ? 'circle' : 'square',
					columns: '0',
					alignment: 'left',
					color_source: 'custom',
					primary_color: D.colors.border,
					secondary_color: D.colors.primary,
					_margin: E.dims( 24, 0, 0, 0 ),
				} ) : null,
			] ),
			E.section( { pad: [ D.sec, D.sec ], gutter: D.gutter, bg: { global: 'sbsurface' } }, [
				C.headSplit( D, { eyebrow: 'Keep reading', title: 'More guides.' }, E.button( 'All posts', url.blog, soft ? B.dark : B.outline ) ),
				E.widget( 'storebox-post-grid', { design: '', source: 'related', count: 3, featured: '', layout: 'grid', columns: '3', columns_tablet: '2', columns_mobile: '1' } ),
			] ),
			cta(),
		],
	} );

	/* --------------------------------------------------------- archive */
	T.push( {
		key: 'archive',
		title: 'Storebox — Blog and archives',
		type: 'archive',
		folder: 'archive',
		conditions: [ 'include/archive' ],
		settings: { preview_type: 'archive/recent_posts' },
		content: () => [
			hero( {
				crumbs: '<a href="' + url.home + '">Home</a> <span aria-hidden="true">/</span> <span aria-current="page">Blog</span>',
				eyebrow: 'Blog',
				title: 'Guides from people who store things for a living.',
				titleTag: E.tag( 'archive-title', { include_context: '', fallback: 'Guides from people who store things for a living.' } ),
				ledeTag: E.tag( 'archive-description', { fallback: 'Packing, sizing, moving and running a business out of a storage unit.' } ),
			} ),
			E.section( { pad: [ D.sec, D.sec ], gutter: D.gutter }, [
				E.widget( 'storebox-post-grid', { design: '', source: 'current', chips: 'yes', pagination: 'yes', featured: 'yes', layout: 'auto', columns: '3', columns_tablet: '2', columns_mobile: '1' } ),
			] ),
			cta(),
		],
	} );

	/* ---------------------------------------------------------- search */
	T.push( {
		key: 'search',
		title: 'Storebox — Search results',
		type: 'search-results',
		folder: 'archive',
		conditions: [ 'include/archive/search' ],
		settings: { preview_type: 'search', preview_search_term: 'storage' },
		content: () => [
			hero( {
				eyebrow: 'Search',
				title: 'Search results',
				titleTag: E.tag( 'archive-title', { include_context: 'yes' } ),
				after: [
					E.widget( 'search-form', {
						skin: 'classic',
						placeholder: 'Search the site…',
						button_type: 'text',
						button_text: 'Search',
						size: 'md',
						input_text_color: D.colors.text,
						input_background_color: '#FFFFFF',
						border_width: E.px( 1 ),
						border_color: D.colors.border,
						border_radius: E.px( soft ? 99 : 4 ),
						button_text_color: D.colors.dark,
						button_background_color: D.colors.secondary,
						_margin: E.dims( 28, 0, 0, 0 ),
						_element_width: 'initial',
						_element_custom_width: E.px( 560 ),
						_element_width_mobile: 'inherit',
					} ),
				],
			} ),
			E.section( { pad: [ D.sec, D.sec ], gutter: D.gutter }, [
				E.widget( 'storebox-post-grid', { design: '', source: 'current', pagination: 'yes', featured: '', layout: 'grid', columns: '3', columns_tablet: '2', columns_mobile: '1' } ),
			] ),
		],
	} );

	/* ------------------------------------------------------------- 404 */
	T.push( {
		key: '404',
		title: 'Storebox — 404',
		type: 'error-404',
		folder: 'pages',
		conditions: [ 'include/singular/not_found404' ],
		content: () => [
			E.section(
				{
					pad: soft ? [ 244, 184 ] : [ 186, 186 ],
					padMobile: soft ? [ 212, 152 ] : [ 150, 150 ],
					gutter: D.gutter,
					bg: soft ? { global: 'primary' } : { global: 'sbbg' },
					classes: soft ? 'sb-e-overlay-top' : '',
					extra: Object.assign( { min_height: soft ? E.vh( 100 ) : E.vh( 72 ), flex_justify_content: 'center' }, soft ? {} : { border_border: 'solid', border_width: E.dims( 0, 0, 1, 0 ), __globals__: { border_color: E.color( 'sbborder' ) } } ),
				},
				[
					// As in the designs, the block shrinks to its content and sits centred.
					E.col( { extra: { width: E.custom( 'fit-content' ), width_tablet: E.custom( 'fit-content' ), width_mobile: E.pct( 100 ), _flex_align_self: 'center' } }, [
						E.heading( soft ? '404' : '<mark>404</mark>', { tag: 'p', color: soft ? 'secondary' : 'primary', classes: soft ? '' : 'sb-e-highlight', extra: { typography_typography: 'custom', typography_font_size: E.custom( soft ? 'clamp(7rem, 20vw, 15rem)' : 'clamp(7rem, 19vw, 14rem)' ), typography_font_weight: '900', typography_line_height: E.em( 0.85 ), typography_letter_spacing: E.em( -0.07 ) } } ),
						soft
							? E.heading( 'This unit is empty.', { tag: 'h1', color: 'sbwhite', margin: [ 18, 0, 0, 0 ], extra: Object.assign( { typography_typography: 'custom', typography_font_size: E.custom( 'clamp(1.9rem, 4vw, 3rem)' ), typography_font_weight: '800', typography_letter_spacing: E.em( -0.035 ), typography_line_height: E.em( 1.05 ) }, E.measure( 620 ) ) } )
							: E.heading( 'This unit is empty.', { tag: 'h1', typo: 'sbh1', color: 'primary', margin: [ 22, 0, 0, 0 ] } ),
						E.text( 'The page you were after has moved, or never existed. Everything else is where you left it.', { typo: 'sblede', colorHex: soft ? 'rgba(255,255,255,0.72)' : undefined, color: soft ? undefined : 'sbmuted', margin: [ 16, 0, 0, 0 ], width: 560 } ),
						E.row( { gap: 12, stack: 'mobile', extra: { margin: E.dims( soft ? 34 : 32, 0, 0, 0 ), flex_align_items_mobile: 'flex-start' } }, [
							E.button( 'Back to the homepage', url.home, soft ? B.yellow : B.dark ),
							E.button( 'Browse units', url.units, soft ? B.ghost : B.outline ),
						] ),
					] ),
				]
			),
		],
	} );

	/* ----------------------------------------------------- single unit */
	T.push( {
		key: 'single-unit',
		title: 'Storebox — Single unit',
		type: 'single-post',
		folder: 'single',
		conditions: [ 'include/singular/sb_unit' ],
		settings: { preview_type: 'single/sb_unit', preview_id: '{{post:unit-medium-10}}' },
		content: () => [
			hero( {
				short: true,
				crumbs: '<a href="' + url.home + '">Home</a> <span aria-hidden="true">/</span> <a href="' + url.units + '">Units</a>',
				eyebrow: 'Unit type',
				eyebrowTag: E.tag( 'storebox-unit-field', { field: 'type_label' } ),
				title: 'Unit name — size',
				titleTag: E.tag( 'storebox-unit-field', { field: 'display_title' } ),
				ledeTag: E.tag( 'storebox-unit-field', { field: 'summary' } ),
			} ),
			E.section( { pad: [ D.sec, D.sec ], gutter: D.gutter }, [
				E.row( { gap: 56, gapTablet: 40, align: 'flex-start' }, [
					E.col( { w: 59, gap: 0 }, [
						E.widget( 'storebox-unit-gallery', { design: '', source: 'unit' } ),
						E.widget( 'storebox-unit-specs', { design: '' } ),
						E.widget( 'theme-post-content', { _css_classes: 'sb-prose' } ),
						E.heading( 'This unit has', { tag: 'h2', color: 'primary', margin: [ 40, 0, 14, 0 ], extra: { typography_typography: 'custom', typography_font_size: E.rem( 1.6 ), typography_font_weight: soft ? '800' : '900', typography_letter_spacing: E.em( -0.03 ) } } ),
						E.widget( 'storebox-unit-features', { design: '' } ),
						E.text( C.reservingHtml( D ), { classes: 'sb-prose', margin: [ 40, 0, 40, 0 ] } ),
						E.widget( 'storebox-enquiry-form', { design: '', type: 'auto', form_id: 'reserve', card: 'yes' } ),
					] ),
					E.col(
						{ w: 41, extra: { sticky: 'top', sticky_on: [ 'desktop' ], sticky_offset: soft ? 96 : 104, sticky_parent: 'yes' } },
						[ E.widget( 'storebox-unit-booking', { design: '', help: 'yes' } ) ]
					),
				] ),
			] ),
			E.section( { pad: [ D.sec, D.sec ], gutter: D.gutter, bg: { global: 'sbsurface' } }, [
				C.headSplit( D, { eyebrow: 'Similar sizes', title: soft ? 'Or a little bigger, or smaller.' : 'A little bigger, or smaller.' }, E.button( 'All units', url.units, soft ? B.dark : B.outline ) ),
				E.widget( 'storebox-unit-grid', { design: '', source: 'similar', count: 3, columns: '3', columns_tablet: '2', columns_mobile: '1' } ),
			] ),
		],
	} );

	/* ------------------------------------------------- single location */
	T.push( {
		key: 'single-location',
		title: 'Storebox — Single location',
		type: 'single-post',
		folder: 'single',
		conditions: [ 'include/singular/sb_location' ],
		settings: { preview_type: 'single/sb_location', preview_id: '{{post:location-noord}}' },
		content: () => [
			hero( {
				crumbs: '<a href="' + url.home + '">Home</a> <span aria-hidden="true">/</span> <a href="' + url.locations + '">Locations</a>',
				eyebrow: 'Area',
				eyebrowTag: E.tag( 'storebox-location-field', { field: 'area_name' } ),
				title: 'Location name',
				titleTag: E.tag( 'post-title' ),
				ledeTag: E.tag( 'post-excerpt', { max_length: '' } ),
				imageTag: soft ? E.tag( 'post-featured-image' ) : null,
			} ),
			E.section( { pad: soft ? [ 0, 0 ] : [ 56, 0 ], padMobile: soft ? [ 0, 0 ] : [ 40, 0 ], gutter: D.gutter }, [
				E.widget( 'storebox-location-facts', { design: '', overlap: soft ? 'yes' : '' } ),
			] ),
			E.section( { pad: [ soft ? 40 : 20, D.sec ], gutter: D.gutter }, [
				E.row( { gap: soft ? 64 : 70, gapTablet: 40, align: 'flex-start' }, [
					E.col( { w: 50 }, [
						C.eyebrow( D, 'About this facility' ),
						E.widget( 'theme-post-content', { _css_classes: 'sb-prose', _margin: E.dims( 16, 0, 0, 0 ) } ),
						E.widget( 'storebox-location-tags', Object.assign( { design: '', _margin: E.dims( soft ? 22 : 20, 0, 0, 0 ) }, soft ? {} : { limit: 1, show_free: 'yes' } ) ),
					] ),
					E.col( { w: 50, gap: 26 }, [
						soft ? E.dyn( E.img( 'storage-corridor-wide', { alt: '', height: [ 420, 440, 260 ], radius: D.radiusLg } ), 'image', E.tag( 'storebox-location-image', { source: 'gallery' } ) ) : null,
						E.widget( 'storebox-opening-hours', { design: '' } ),
					] ),
				] ),
			] ),
			E.section( { pad: [ D.sec, D.sec ], gutter: D.gutter, bg: { global: 'sbsurface' } }, [
				C.headSplit(
					D,
					{ eyebrow: 'Units here', title: 'Available here.' },
					E.button( 'All locations', url.units, soft ? B.dark : B.outline )
				),
				E.widget( 'storebox-unit-grid', Object.assign( { design: '', source: 'location', location: '', count: 8, columns: '3', columns_tablet: '2', columns_mobile: '1' }, soft ? { show_name: 'yes' } : {} ) ),
			] ),
			E.section( { pad: [ D.sec, D.sec ], gutter: D.gutter }, [
				C.head( D, { eyebrow: 'Getting here', title: 'Address' } ),
				E.widget( 'storebox-location-map', { design: '', source: 'current', zoom: E.px( 14 ) } ),
			] ),
			cta(),
		],
	} );

	// Dynamic headings that need the location name or address.
	const location = T[ T.length - 1 ];
	const build = location.content;
	location.content = () => {
		const elements = build();
		const walk = ( list ) =>
			list.forEach( ( el ) => {
				if ( 'heading' === el.widgetType && 'Available here.' === el.settings.title ) {
					E.dyn( el, 'title', E.tag( 'storebox-location-field', { field: 'name', before: 'Available at ', after: '.' } ) );
				}
				if ( 'heading' === el.widgetType && 'Address' === el.settings.title ) {
					E.dyn( el, 'title', E.tag( 'storebox-location-field', { field: 'address_inline' } ) );
				}
				walk( el.elements || [] );
			} );
		walk( elements );
		return elements;
	};

	return T;
}

module.exports = { sectionsFor, templatesFor };

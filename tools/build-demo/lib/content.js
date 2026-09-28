/**
 * Demo content (content.json): images, terms, units, locations, posts,
 * pages, menus, theme mods, options and Storebox settings.
 */
'use strict';

const path = require( 'path' );
const scenes = require( path.join( __dirname, '..', '..', 'demo-images', 'scenes' ) );

const LOCATION_SLUGS = { noord: 'amsterdam-noord', zuidoost: 'amsterdam-zuidoost', haarlem: 'haarlem-oudeweg' };
const LOCATION_BY_TITLE = { 'Amsterdam Noord': 'noord', 'Amsterdam Zuidoost': 'zuidoost', 'Haarlem Oudeweg': 'haarlem' };
// Approximate street locations, for the map pins.
const COORDS = { noord: [ 52.3938, 4.8923 ], zuidoost: [ 52.3009, 4.9532 ], haarlem: [ 52.3867, 4.6621 ] };
const FEATURES = [
	[ 'climate', 'Climate controlled', true ],
	[ 'drive-up', 'Drive-up access', true ],
	[ 'power', 'Power socket', true ],
	[ 'alarm', 'Individually alarmed', false ],
	[ 'cctv', 'CCTV monitored', false ],
	[ 'padlock', 'Your own padlock', false ],
	[ 'trolleys', 'Trolleys available', false ],
	[ 'pin', '24/7 PIN access', false ],
];
// Bullets shown on the home page cards of the soft demo.
const HIGHLIGHTS = {
	'small-5': [ 'Fits a one-bedroom flat', 'Ground floor, drive-up access', 'Power socket available' ],
	'medium-10': [ 'Fits a two-bedroom home', 'Climate controlled', 'Trolley and loading bay' ],
	'large-20': [ 'Fits a four-bedroom home', 'Vehicle access to the door', 'Suitable for business stock' ],
};
const MONTHS = { Jan: 0, Feb: 1, Mar: 2, Apr: 3, May: 4, Jun: 5, Jul: 6, Aug: 7, Sep: 8, Oct: 9, Nov: 10, Dec: 11 };

/* --------------------------------------------------------------- helpers */

const esc = ( s ) => String( s ).replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' );

/** Converts simple article HTML to block markup. */
function toBlocks( html ) {
	const out = [];
	const re = /<(p|h2|h3|ul|ol|blockquote|div)([^>]*)>([\s\S]*?)<\/\1>/g;
	let m;
	while ( ( m = re.exec( html ) ) ) {
		const [ , tag, attrs, inner ] = m;
		if ( 'p' === tag ) {
			out.push( '<!-- wp:paragraph -->\n<p>' + inner + '</p>\n<!-- /wp:paragraph -->' );
		} else if ( 'h2' === tag || 'h3' === tag ) {
			const level = tag.slice( 1 );
			out.push( '<!-- wp:heading' + ( '3' === level ? ' {"level":3}' : '' ) + ' -->\n<' + tag + ' class="wp-block-heading">' + inner + '</' + tag + '>\n<!-- /wp:heading -->' );
		} else if ( 'ul' === tag || 'ol' === tag ) {
			const items = [ ...inner.matchAll( /<li>([\s\S]*?)<\/li>/g ) ].map( ( li ) => '<!-- wp:list-item -->\n<li>' + li[ 1 ] + '</li>\n<!-- /wp:list-item -->' );
			out.push( '<!-- wp:list' + ( 'ol' === tag ? ' {"ordered":true}' : '' ) + ' -->\n<' + tag + '>' + items.join( '\n' ) + '</' + tag + '>\n<!-- /wp:list -->' );
		} else if ( 'blockquote' === tag ) {
			out.push( '<!-- wp:quote -->\n<blockquote class="wp-block-quote"><!-- wp:paragraph -->\n<p>' + inner.replace( /<\/?p>/g, '' ) + '</p>\n<!-- /wp:paragraph --></blockquote>\n<!-- /wp:quote -->' );
		} else if ( 'div' === tag && /callout/.test( attrs ) ) {
			const h = ( inner.match( /<h3>([\s\S]*?)<\/h3>/ ) || [] )[ 1 ] || '';
			const p = ( inner.match( /<p>([\s\S]*?)<\/p>/ ) || [] )[ 1 ] || '';
			out.push(
				'<!-- wp:group {"className":"is-style-sb-callout","layout":{"type":"constrained"}} -->\n<div class="wp-block-group is-style-sb-callout">' +
					'<!-- wp:heading {"level":3} -->\n<h3 class="wp-block-heading">' + h + '</h3>\n<!-- /wp:heading -->\n' +
					'<!-- wp:paragraph -->\n<p>' + p + '</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:group -->'
			);
		}
	}
	return out.join( '\n\n' );
}

function isoDate( label ) {
	const m = /(\d+) (\w{3}) (\d{4})/.exec( label );
	if ( ! m ) {
		return '';
	}
	const d = new Date( Date.UTC( +m[ 3 ], MONTHS[ m[ 2 ] ], +m[ 1 ], 9, 0, 0 ) );
	return d.toISOString().slice( 0, 19 ).replace( 'T', ' ' );
}

/* ------------------------------------------------------------------ build */

/**
 * @param {Object} src     source.json.
 * @param {Object} demo    { slug, preset, name }.
 * @param {Object} built   { pages: {key: {title, slug, file, settings}}, templates: [...] }.
 * @param {Object} kit     Kit settings.
 */
function build( src, demo, built, kit ) {
	const soft = 'soft' === demo.preset;

	// Images.
	const images = {};
	Object.entries( scenes ).forEach( ( [ key, scene ] ) => {
		images[ key ] = { file: key + '.jpg', title: scene.title, alt: scene.alt };
	} );

	// Terms.
	const terms = {
		sb_unit_type: [
			{ key: 'self', name: 'Self storage', slug: 'self-storage', meta: { sb_order: 1 } },
			{ key: 'business', name: 'Business storage', slug: 'business-storage', meta: { sb_order: 2 } },
			{ key: 'vehicle', name: 'Vehicle storage', slug: 'vehicle-storage', meta: { sb_order: 3 } },
		],
		sb_unit_size: [
			{ key: 'small', name: 'Small', slug: 'small', description: 'up to 5 m²', meta: { sb_order: 1 } },
			{ key: 'medium', name: 'Medium', slug: 'medium', description: '10 to 15 m²', meta: { sb_order: 2 } },
			{ key: 'large', name: 'Large', slug: 'large', description: '18 m² and up', meta: { sb_order: 3 } },
		],
		sb_unit_feature: FEATURES.map( ( f, i ) => ( { key: f[ 0 ], name: f[ 1 ], slug: f[ 0 ], meta: { sb_order: i + 1, sb_card: f[ 2 ] ? '1' : '' } } ) ),
		category: Object.entries( src.categories ).map( ( [ key, name ] ) => ( { key, name, slug: key } ) ),
	};
	const featureKey = ( name ) => {
		const clean = name.replace( / door$/, '' );
		const found = FEATURES.find( ( f ) => f[ 1 ] === clean );
		return found ? found[ 0 ] : null;
	};

	const posts = [];

	// Locations.
	src.locations.forEach( ( l, i ) => {
		const hours = {};
		const dayIndex = { Sunday: 0, Monday: 1, Tuesday: 2, Wednesday: 3, Thursday: 4, Friday: 5, Saturday: 6 };
		l.hours.forEach( ( h ) => {
			hours[ dayIndex[ h[ 0 ] ] ] = h[ 1 ];
		} );
		posts.push( {
			key: 'location-' + l.slug,
			type: 'sb_location',
			title: l.title,
			slug: LOCATION_SLUGS[ l.slug ],
			excerpt: l.lede,
			content: toBlocks( '<h2>' + esc( l.heading ) + '</h2>' + l.paragraphs.map( ( p ) => '<p>' + esc( p ) + '</p>' ).join( '' ) ),
			menu_order: { noord: 1, zuidoost: 2, haarlem: 3 }[ l.slug ] || i + 1,
			// Hero photos sit under a dark overlay: prefer the greyer scenes.
			image: { noord: 'storage-lockers', zuidoost: 'drive-up-units', haarlem: 'vehicle-bay' }[ l.slug ] || l.hero_photo,
			meta: {
				_sb_area_name: l.area_name,
				_sb_street: l.street,
				_sb_postcode: l.postcode,
				_sb_city: l.city,
				_sb_phone: l.phone,
				_sb_email: '',
				_sb_access: l.access,
				_sb_units_total: l.units_total,
				_sb_units_free: l.units_free,
				_sb_tags: l.tags.join( '\n' ),
				_sb_lat: COORDS[ l.slug ][ 0 ],
				_sb_lng: COORDS[ l.slug ][ 1 ],
				_sb_gallery: '{{images:' + l.photo + '}}',
				_sb_hours: hours,
			},
		} );
	} );

	// Units.
	src.units.forEach( ( u, i ) => {
		const features = u.features.map( featureKey ).filter( Boolean );
		const excerpt = u.rail_business || u.card_business || u.fits + '.';
		posts.push( {
			key: 'unit-' + u.slug,
			type: 'sb_unit',
			title: u.name,
			slug: u.slug,
			excerpt,
			content: toBlocks( u.content ),
			menu_order: i + 1,
			image: u.gallery[ 0 ],
			meta: {
				_sb_area: u.area,
				_sb_width: u.width,
				_sb_depth: u.depth,
				_sb_ceiling: u.ceiling,
				_sb_floor: u.floor,
				_sb_price: u.price,
				_sb_available: u.available,
				_sb_location: '{{post:location-' + LOCATION_BY_TITLE[ u.location ] + '}}',
				_sb_fits: u.fits,
				_sb_highlights: ( HIGHLIGHTS[ u.slug ] || [] ).join( '\n' ),
				_sb_gallery: '{{images:' + u.gallery.slice( 1 ).join( ',' ) + '}}',
				_sb_featured: HIGHLIGHTS[ u.slug ] ? '1' : '',
			},
			terms: {
				sb_unit_type: [ u.type_key ],
				sb_unit_size: [ u.size_key ],
				sb_unit_feature: features,
			},
		} );
	} );

	// Blog posts (newest first in the design; dates set so the order holds).
	const body = toBlocks( src.post_body );
	src.posts.forEach( ( p ) => {
		posts.push( {
			key: 'post-' + p.slug,
			type: 'post',
			title: p.title,
			slug: p.slug,
			excerpt: p.excerpt,
			content: body,
			date: isoDate( p.date ),
			image: p.photo,
			comments: false,
			meta: { _storebox_read_time: p.read_time },
			terms: { category: [ p.category ] },
		} );
	} );

	// Pages.
	Object.entries( built.pages ).forEach( ( [ key, page ], i ) => {
		posts.push( {
			key: 'page-' + key,
			type: 'page',
			title: page.title,
			slug: page.slug || key,
			content: page.fallback || '',
			menu_order: i,
			comments: false,
			template: 'elementor_header_footer',
			elementor: page.file,
		} );
	} );
	posts.push( {
		key: 'page-blog',
		type: 'page',
		title: 'Blog',
		slug: 'blog',
		content: '',
		menu_order: 20,
		comments: false,
		image: 'unit-shelving',
		meta: {
			_storebox_hero_title: 'Guides from people who store things for a living.',
			_storebox_hero_intro: 'Packing, sizing, moving and running a business out of a storage unit.',
		},
	} );

	// Menus.
	const menus = [
		{
			key: 'primary',
			name: 'Main menu',
			location: 'primary',
			items: [
				{ title: 'Units', post: 'page-units' },
				{ title: 'Find your size', post: 'page-sizes' },
				{ title: 'Locations', post: 'page-locations' },
				{ title: 'About', post: 'page-about' },
				{ title: 'Blog', post: 'page-blog' },
				{ title: 'Contact', post: 'page-contact' },
			],
		},
		{
			key: 'footer-units',
			name: 'Units',
			location: 'footer-1',
			items: soft
				? [
					{ title: 'Small — 5 m²', post: 'unit-small-5' },
					{ title: 'Medium — 10 m²', post: 'unit-medium-10' },
					{ title: 'Large — 20 m²', post: 'unit-large-20' },
					{ title: 'Business storage', url: '{{url:page-units}}?unit_type=business-storage' },
				]
				: [
					{ title: '2 m² — €35', post: 'unit-locker-2' },
					{ title: '5 m² — €59', post: 'unit-small-5' },
					{ title: '10 m² — €89', post: 'unit-medium-10' },
					{ title: 'Business storage', url: '{{url:page-units}}?unit_type=business-storage' },
				],
		},
		{
			key: 'footer-locations',
			name: 'Locations',
			location: 'footer-2',
			items: [
				{ title: 'Amsterdam Noord', post: 'location-noord' },
				{ title: 'Amsterdam Zuidoost', post: 'location-zuidoost' },
				{ title: soft ? 'Haarlem' : 'Haarlem Oudeweg', post: 'location-haarlem' },
			],
		},
		{
			key: 'footer-help',
			name: 'Help',
			location: 'footer-3',
			items: [
				{ title: soft ? 'Size calculator' : 'Find your size', url: '{{url:page-sizes#calc}}' },
				{ title: 'FAQ', url: '{{url:page-contact#faq}}' },
				{ title: 'Contact', post: 'page-contact' },
				{ title: 'Blog', post: 'page-blog' },
			],
		},
	];

	const themeMods = {
		preset: demo.preset,
		header_layout: 'auto',
		header_transparent: 'all',
		header_phone: '020 000 0000',
		header_cta_text: 'Reserve a unit',
		header_cta_url: '{{url:page-units}}',
		topbar_enable: ! soft,
		topbar_item_1: soft ? '' : 'Open 24/7, every day of the year',
		topbar_item_2: soft ? '' : 'Amsterdam & Haarlem',
		topbar_email: soft ? '' : 'hello@example.com',
		footer_text: 'Self storage in Amsterdam and Haarlem. Month to month, no deposit, your own key.',
		footer_copyright: '© {year} {site}',
		footer_note: 'Demo content · Storebox WordPress theme',
		social_facebook: 'https://facebook.com/',
		social_instagram: 'https://instagram.com/',
		social_linkedin: 'https://linkedin.com/',
		hero_image: '{{image:storage-corridor-wide}}',
		breadcrumbs: true,
		cta_enable: true,
		cta_eyebrow: 'Ready when you are',
		cta_title: 'Reserve a unit in under two minutes.',
		cta_text: soft ? 'No deposit, no contract to sign today, and we hold it free for seven days.' : 'No deposit, nothing to sign today, and we hold it free for seven days while you sort the van.',
		cta_btn1_text: soft ? 'Find my size' : 'Find your size',
		cta_btn1_url: '{{url:page-sizes}}',
		cta_btn2_text: '020 000 0000',
		cta_btn2_url: 'tel:+31200000000',
		cta_image: soft ? '{{image:shutters-row}}' : '{{image:storage-corridor-trolley}}',
		blog_related_eyebrow: 'Keep reading',
		blog_related_title: 'More guides.',
		notfound_title: 'This unit is empty.',
		notfound_text: 'The page you were after has moved, or never existed. Everything else is where you left it.',
	};

	const settings = {
		currency_symbol: '€',
		currency_position: 'before',
		units_page: '{{post:page-units}}',
		locations_page: '{{post:page-locations}}',
		help_phone: '020 000 0000',
		reservation_info: '<h2>How reserving works</h2>\n<ul>\n<li>Reserve online with no deposit and nothing to sign. We hold it for seven days.</li>\n<li>Sign on your phone or at reception on move-in day, fit your own padlock, and collect your PIN.</li>\n<li>Pay monthly. Give two weeks\' notice to leave, and unused days are refunded.</li>\n</ul>',
		booking_rows: 'Deposit | None\nMinimum term | None',
		booking_note: 'Held free for 7 days · cancel any time',
		privacy_note: 'We only use your details to answer this enquiry.',
	};

	return {
		version: 1,
		demo: demo.slug,
		images,
		terms,
		posts,
		templates: built.templates,
		menus,
		// The theme stores its options as storebox_{key}.
		theme_mods: Object.fromEntries( Object.entries( themeMods ).map( ( [ k, v ] ) => [ 'storebox_' + k, v ] ) ),
		options: {
			show_on_front: 'page',
			page_on_front: '{{post:page-home}}',
			page_for_posts: '{{post:page-blog}}',
			posts_per_page: 9,
			elementor_disable_color_schemes: 'yes',
			elementor_disable_typography_schemes: 'yes',
			'elementor_experiment-container': 'active',
			elementor_load_fa4_shim: '',
			elementor_font_display: 'swap',
		},
		settings,
		kit,
		sidebars: { 'footer-1': [], 'footer-2': [], 'footer-3': [], 'sidebar-blog': [] },
	};
}

module.exports = { build, toBlocks };

#!/usr/bin/env node
/**
 * Builds the Storebox demo packages and the standalone Elementor templates.
 *
 *   node tools/build-demo/build.js
 *
 * Writes:
 *   storebox-core/demo/{demo}/demo.json          manifest shown in the importer
 *   storebox-core/demo/{demo}/content.json       content, menus, settings, kit
 *   storebox-core/demo/{demo}/elementor/*.json   Elementor documents (with import placeholders)
 *   elementor-templates/...                      the same layouts for Templates → Import
 *
 * Input: tools/build-demo/data/source.json (see extract.py).
 */
'use strict';

const fs = require( 'fs' );
const path = require( 'path' );
const E = require( './lib/elementor' );
const { designs, buttons, kit } = require( './lib/designs' );
const content = require( './lib/content' );
const themeBuilder = require( './templates/theme-builder' );

const ROOT = path.resolve( __dirname, '..', '..' );
const DEMO_DIR = path.join( ROOT, 'storebox-core', 'demo' );
const EXPORT_DIR = path.join( ROOT, 'elementor-templates' );
const src = JSON.parse( fs.readFileSync( path.join( __dirname, 'data', 'source.json' ), 'utf8' ) );

const DEMOS = [
	{
		slug: 'demo-1',
		preset: 'soft',
		name: 'Self Storage',
		description: 'Rounded, photo-led design with an overlay header, bento features and a size calculator.',
		builder: require( './pages/soft' ),
		order: 1,
	},
	{
		slug: 'demo-2',
		preset: 'editorial',
		name: 'Business Storage',
		description: 'Editorial design with a split hero, a sliding unit rail, numbered lists and a sticky header.',
		builder: require( './pages/editorial' ),
		order: 2,
	},
];

/* --------------------------------------------------------------- helpers */

function write( file, data ) {
	fs.mkdirSync( path.dirname( file ), { recursive: true } );
	fs.writeFileSync( file, JSON.stringify( data, null, '\t' ) + '\n' );
}

/** Elementor template export format (Templates → Import). */
function exportDoc( title, type, elements, settings ) {
	return {
		content: elements,
		page_settings: settings && Object.keys( settings ).length ? settings : [],
		version: '0.4',
		title,
		type,
	};
}

/** Breadcrumbs and eyebrow labels are left out of the fallback text. */
function isChrome( s ) {
	const classes = ( s._css_classes || '' ) + ' ' + ( s.css_classes || '' );
	const typography = s.__globals__ && s.__globals__.typography_typography;
	return /\bsb-e-crumbs\b/.test( classes ) || /id=accent$/.test( typography || '' );
}

/** Plain HTML fallback of a layout (post content without Elementor, SEO plugins). */
function fallbackHtml( elements ) {
	const out = [];
	const walk = ( list ) =>
		list.forEach( ( el ) => {
			if ( 'widget' === el.elType && ! isChrome( el.settings ) ) {
				const s = el.settings;
				if ( 'heading' === el.widgetType && s.title ) {
					const tag = /^h[2-6]$/.test( s.header_size ) ? s.header_size : null;
					if ( 'h1' !== s.header_size ) {
						out.push( tag ? '<' + tag + '>' + s.title + '</' + tag + '>' : '<p>' + s.title + '</p>' );
					}
				} else if ( 'text-editor' === el.widgetType && s.editor ) {
					out.push( s.editor );
				} else if ( 'button' === el.widgetType && s.text && s.link && s.link.url ) {
					out.push( '<p><a href="' + s.link.url + '">' + s.text + '</a></p>' );
				}
			}
			walk( el.elements || [] );
		} );
	walk( elements );
	return out.join( '\n' );
}

/**
 * Standalone copy of a document: import placeholders are replaced with
 * neutral values (Elementor shows its placeholder image, links become #).
 */
function standalone( value, parentKey = '' ) {
	if ( Array.isArray( value ) ) {
		return value.map( ( v ) => standalone( v, parentKey ) );
	}
	if ( value && 'object' === typeof value ) {
		const out = {};
		Object.entries( value ).forEach( ( [ k, v ] ) => {
			out[ k ] = standalone( v, k );
		} );
		// Unit grids that list chosen units show the first units instead.
		if ( 'storebox-unit-grid' === value.widgetType && 'manual' === value.settings.source ) {
			out.settings.source = 'all';
			out.settings.count = value.settings.ids.length;
			delete out.settings.ids;
		}
		return out;
	}
	if ( 'string' !== typeof value || ! value.includes( '{{' ) ) {
		return value;
	}
	if ( /^\{\{(image|sid):[^}]+\}\}$/.test( value ) ) {
		return '';
	}
	return value
		.replace( /\{\{image_url:[^}]+\}\}/g, '' )
		.replace( /\{\{url:[^}#]+(#[^}]+)?\}\}/g, ( m, hash ) => hash || '#' )
		.replace( /\{\{home\}\}/g, '/' )
		.replace( /\{\{menu:[^}]+\}\}/g, '' )
		.replace( /\{\{[a-z_]+:[^}]*\}\}/g, '' );
}

/**
 * A widget cannot use a global typography and override one of its values:
 * Elementor applies the global to every value. Where a layout asks for both
 * (a global font with a custom size), the global's values are copied into
 * the widget and the custom ones kept.
 */
function unlinkTypography( elements, kitSettings ) {
	const globals = {};
	[].concat( kitSettings.system_typography, kitSettings.custom_typography ).forEach( ( t ) => {
		globals[ t._id ] = t;
	} );
	const walk = ( list ) =>
		list.forEach( ( el ) => {
			const s = el.settings || {};
			Object.keys( s ).forEach( ( key ) => {
				const m = key.match( /^(.*)_typography$/ );
				const ref = m && s.__globals__ && s.__globals__[ key ];
				if ( ! ref || 'custom' !== s[ key ] ) {
					return;
				}
				const entry = globals[ ref.split( 'id=' )[ 1 ] ] || {};
				Object.keys( entry ).forEach( ( field ) => {
					const local = field.replace( /^typography_/, m[ 1 ] + '_' );
					if ( field.startsWith( 'typography_' ) && 'typography_typography' !== field && undefined === s[ local ] ) {
						s[ local ] = entry[ field ];
					}
				} );
				delete s.__globals__[ key ];
				if ( ! Object.keys( s.__globals__ ).length ) {
					delete s.__globals__;
				}
			} );
			walk( el.elements || [] );
		} );
	walk( elements );
	return elements;
}

function clean( dir ) {
	if ( fs.existsSync( dir ) ) {
		fs.rmSync( dir, { recursive: true, force: true } );
	}
}

/* ----------------------------------------------------------------- build */

clean( EXPORT_DIR );

const summary = [];

DEMOS.forEach( ( demo ) => {
	const D = designs[ demo.preset ];
	const B = buttons( D );
	const { pages, sections } = demo.builder( D, B );
	const base = path.join( DEMO_DIR, demo.slug );
	const built = { pages: {}, templates: [] };
	const kitSettings = kit( D );

	clean( path.join( base, 'elementor' ) );

	// Pages.
	Object.entries( pages ).forEach( ( [ key, page ] ) => {
		E.begin( demo.slug + ':page:' + key );
		const elements = unlinkTypography( E.finalize( page.content() ), kitSettings );
		const doc = exportDoc( page.title, 'page', elements, page.settings );
		const file = 'elementor/page-' + key + '.json';
		write( path.join( base, file ), doc );
		write( path.join( EXPORT_DIR, demo.slug, 'pages', key + '.json' ), standalone( Object.assign( {}, doc, { title: 'Storebox ' + demo.name + ' — ' + page.title } ) ) );
		built.pages[ key ] = { title: page.title, slug: page.slug || key, file, fallback: fallbackHtml( elements ) };
		summary.push( demo.slug + ' page ' + key + ': ' + JSON.stringify( elements ).length + ' bytes' );
	} );

	// Reusable sections (Templates → Import, type "section").
	const sectionList = themeBuilder.sectionsFor( demo, sections );
	sectionList.forEach( ( s ) => {
		E.begin( demo.slug + ':section:' + s.key );
		const elements = unlinkTypography( E.finalize( [ s.build() ] ), kitSettings );
		write( path.join( EXPORT_DIR, 'sections', demo.slug + '-' + s.key + '.json' ), standalone( exportDoc( 'Storebox ' + demo.name + ' — ' + s.title, 'section', elements ) ) );
	} );

	// Theme Builder templates (Elementor Pro).
	themeBuilder.templatesFor( demo, D, B, sections ).forEach( ( t ) => {
		E.begin( demo.slug + ':template:' + t.key );
		const elements = unlinkTypography( E.finalize( t.content() ), kitSettings );
		const doc = exportDoc( t.title, t.type, elements, t.settings || {} );
		const file = 'elementor/template-' + t.key + '.json';
		write( path.join( base, file ), doc );
		write( path.join( EXPORT_DIR, t.folder, demo.slug + '-' + t.key + '.json' ), standalone( doc ) );
		built.templates.push( { key: t.key, title: t.title, type: t.type, conditions: t.conditions, elementor: file } );
	} );

	// Content, kit and manifest.
	write( path.join( base, 'content.json' ), content.build( src, demo, built, kitSettings ) );
	write( path.join( base, 'demo.json' ), {
		slug: demo.slug,
		name: demo.name,
		description: demo.description,
		preset: demo.preset,
		preview: 'preview.jpg',
		order: demo.order,
		requires: { elementor: '3.20.0' },
	} );
	write( path.join( EXPORT_DIR, 'global', demo.slug + '-site-settings.json' ), { title: 'Storebox ' + demo.name + ' — Site Settings (reference)', settings: kitSettings } );
} );

console.log( summary.join( '\n' ) );
console.log( 'Done.' );

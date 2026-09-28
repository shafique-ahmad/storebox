/**
 * Scene registry: file name => { draw, alt, title }.
 */
'use strict';

const corridor = require( './corridor' );
const interior = require( './interior' );
const exterior = require( './exterior' );
const closeups = require( './closeups' );
const rooms = require( './rooms' );

module.exports = {
	'storage-corridor-trolley': {
		title: 'Storage corridor with a loaded hand truck',
		alt: 'Bright storage corridor lined with blue roller doors, a hand truck loaded with boxes in front',
		draw: () => corridor(),
	},
	'storage-corridor-wide': {
		title: 'Wide storage corridor',
		alt: 'Wide, clean corridor of self storage units with navy roller doors',
		draw: () => corridor( { trolley: false, doors: [ '#16303F', '#2C5871', '#2C5871', '#D6DDE1' ], cx: 960, w: 2.5, seed: 5, lines: '#ffffff' } ),
	},
	'unit-shelving': {
		title: 'Storage unit with shelving',
		alt: 'Inside a storage unit: steel shelving with labelled boxes and storage bins',
		draw: () => interior(),
	},
	'unit-open-door': {
		title: 'Open storage unit',
		alt: 'Open roller door showing a tidy storage unit with shelves and boxes',
		draw: () => interior( { outside: true, seed: 11 } ),
	},
	'unit-small-yellow': {
		title: 'Small storage unit',
		alt: 'Small storage unit behind an open yellow roller door, neatly packed with boxes',
		draw: () => interior( { outside: true, w: 1.45, depth: 3.4, door: '#F2B705', seed: 21, cx: 930 } ),
	},
	'shutters-row': {
		title: 'Row of colourful roller doors',
		alt: 'Row of colourful roller shutter doors along a storage building on a clear day',
		draw: () => exterior(),
	},
	'drive-up-units': {
		title: 'Drive-up storage units',
		alt: 'Yellow drive-up storage units with a delivery van parked in front',
		draw: () => exterior( { doors: [ '#F2B705' ], van: true, yaw: 0.58, cx: 700, seed: 9, sky: [ '#B7D3E2', '#EEF4F6' ] } ),
	},
	'door-padlock': {
		title: 'Red storage door with a padlock',
		alt: 'Red roller door of a storage unit secured with a brass padlock',
		draw: () => closeups.padlock(),
	},
	'storage-lockers': {
		title: 'Wall of storage lockers',
		alt: 'Wall of small storage lockers in navy and yellow',
		draw: () => closeups.lockers(),
	},
	'vehicle-bay': {
		title: 'Vehicle storage bay',
		alt: 'Wide vehicle storage bay with a covered car inside',
		draw: () => closeups.bay(),
	},
	'packing-table': {
		title: 'Packing boxes on a table',
		alt: 'Open cardboard box on a table with packing tape, a marker and flat-packed boxes',
		draw: () => rooms.packing(),
	},
	'moving-boxes-room': {
		title: 'Moving boxes in a bright room',
		alt: 'Moving boxes stacked in a bright, empty room beside a large window',
		draw: () => rooms.moving(),
	},
};

=== Storebox Core ===
Contributors: storebox
Tags: self storage, storage units, locations, elementor, reservations
Requires at least: 6.3
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Units, locations, reservation requests, Storebox Elementor widgets and the demo importer for the Storebox theme.

== Description ==

Storebox Core is the companion plugin of the Storebox theme. It keeps your
content independent of the theme: if you ever switch themes, your units,
locations and enquiries stay in WordPress.

* **Storage units** — size, dimensions, ceiling height, floor, monthly price,
  availability, location, features and a photo gallery.
* **Locations** — address, phone, email, access hours, office hours, map
  coordinates and a gallery.
* **Unit types, sizes and features** — ordered taxonomies used by the filters
  and the unit cards.
* **Reservation and contact requests** — spam-protected forms (nonce,
  honeypot, time check and rate limiting) that email your team and are stored
  under Storebox → Enquiries. Personal data can be exported and erased with
  the WordPress privacy tools.
* **Elementor widgets** — Unit Grid, Unit Gallery, Unit Specs, Unit Features,
  Unit Booking, Location Grid, Location Facts, Opening Hours, Location Map,
  Size Calculator, Size Chooser, Size Guide, Post Grid and Enquiry Form.
* **Demo importer** — Appearance → Demo Import, or `wp storebox demo import demo-1`.

Assets are loaded only on pages that use a component.

== Installation ==

The Storebox theme installs this plugin for you (Appearance → Storebox).
To install it manually, upload `storebox-core.zip` in Plugins → Add New →
Upload Plugin and activate it.

== Frequently Asked Questions ==

= Does the map load anything from other servers? =

The illustrative map does not. The live map loads map tiles from the tile
server set in Storebox → Settings → Map (OpenStreetMap by default). Enable
"Load the live map only after a click" there if your privacy policy requires
consent.

= Can I override a component's HTML? =

Yes. Copy a file from `storebox-core/templates/` to
`your-child-theme/storebox-core/` and edit the copy.

= What happens when I delete the plugin? =

Its settings are removed. Units, locations and enquiries are kept. To remove
them too, add `define( 'STOREBOX_CORE_REMOVE_ALL_DATA', true );` to
wp-config.php before deleting the plugin.

== Third-party resources ==

* Leaflet 1.9.4 — https://leafletjs.com — BSD 2-Clause License,
  (c) 2010-2023 Vladimir Agafonkin, (c) 2010-2011 CloudMade.
  Bundled in `assets/vendor/leaflet/`.
* Map tiles (live map, optional) — © OpenStreetMap contributors, data under
  the Open Database License. Tiles are loaded from tile.openstreetmap.org by
  default; heavy commercial use should switch to a tile provider in
  Storebox → Settings → Map (see the OpenStreetMap tile usage policy).
* Demo images — original artwork created for Storebox, released under
  GPL-2.0-or-later together with the plugin.

== Changelog ==

= 1.0.0 =
* Initial release.

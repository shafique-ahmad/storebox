# Storebox — source inventory and implementation architecture

This document records what was found in the two source ZIPs and how each part
was mapped onto WordPress, Elementor and Elementor Pro. It is the developer's
companion to the customer documentation in `docs/`.

## 1. Source inventory

| | Demo 1 — Self Storage (`storebox-design-self-storage.zip`) | Demo 2 — Business Storage (`storebox-design-business-storage.zip`) |
|---|---|---|
| Files | 28 HTML pages, `assets/storebox.css` (700 lines), `assets/storebox.js`, README | 28 HTML pages, `assets/storebox.css` (571 lines), `assets/storebox.js`, README |
| Images | 12 Pexels photos hot-linked (not in the ZIP) | same 12 photos, used in different places |
| Fonts | Archivo 400–900 (Google Fonts) | Archivo 400–900 (Google Fonts) |
| Libraries | none (vanilla JS, no jQuery) | none |
| Header | fixed, transparent over a dark hero, turns solid navy after 40 px scroll, 92→72 px | dark info strip + sticky light header with bottom rule |
| Look | dark photo page headers, cards, 14/22 px radius, pill buttons | light page headers with image beside the title, rows and lists, 4/6 px radius, 2 px navy rules |
| Breakpoints | 1100 / 1000 / 680 (+ short-viewport rule) | 1080 / 680 |
| Container | 1240 px incl. 28 px gutters (20 px mobile) | 1280 px incl. 30 px gutters (20 px mobile) |

Both demos describe the same business with the same data, so the page list is
identical:

| Page | Count | Components |
|---|---|---|
| Home | 1 | hero, stats (D1), how it works (D1), unit cards (D1) / unit rail slider (D2), size calculator (D1) / to-scale chooser (D2), bento features (D1) / editorial numbered list (D2), locations cards (D1) / rows (D2), quote, FAQ, CTA |
| Units | 1 | page hero, filter bar (size, location, type, sort, available now), result count, no-results state, unit grid, "not sure" split, CTA |
| Single unit | 8 | page hero, gallery with thumbnails, spec row, prose, feature checklist, reservation form, sticky booking panel, help box, similar units |
| Find your size | 1 | calculator, to-scale chooser, size-guide table, tips (D1) / numbered steps (D2) |
| Locations | 1 | map with pins, location cards (D1) / rows (D2), "at every facility" checklist |
| Single location | 3 | facts row, about text + tags, office hours with today highlight, units at this location, map |
| About | 1 | story (D1) / big quote (D2), values, timeline, team |
| Contact | 1 | contact routes, contact form, FAQ |
| Blog | 1 | category chips, feature/lead post, card grid (D1) / list (D2), pagination |
| Single post | 7 | page hero, featured image, prose (blockquote, callout, lists), author box, share (D1), related posts |
| 404 | 1 | big 404, message, two buttons |

Source issues found and fixed during conversion:

* `style="width:100%%"` on the booking button of every unit page (template artifact).
* Global `nav` / `nav a` selectors also styled the blog pagination `<nav>`; all
  navigation selectors are now scoped.
* Blog posts all printed "Guides · 14 Sep 2026 · 6 min read" on the single page
  regardless of the real category/date; WordPress now prints real data.
* The "3 left / 3 available" status for the 30 m² unit and "Fully booked /
  Waitlist" wording differed between pages; status is now computed from one
  rule (threshold configurable) with configurable labels.
* Muted text (#6C7B84) and the success/warning status colours failed WCAG AA
  for their small text sizes; they were darkened slightly (#5F6E77, #1A7F53,
  #AD601E). The CTA kicker on yellow was raised from 60 % to 72 % opacity.
* Client-side blog filtering only hid posts on the current page; category
  chips are now real category links that work with pagination and SEO.
* The illustrative CSS maps are replaced by a real Leaflet map (the
  illustrative style is kept as an offline / consent-friendly mode).
* Demo forms only faked a "sent" state; enquiries are now stored and emailed.

## 2. Packaging and responsibilities

ThemeForest treats custom post types, taxonomies, shortcodes, page-builder
elements and form handling as *plugin territory*. The product is therefore two
installable parts plus a child theme:

| Part | Responsibility |
|---|---|
| `storebox/` (theme, `storebox.zip`) | Presentation only: templates, template parts, design tokens and CSS for both design presets, header/footer, Customizer (presentation options), menus, widget areas, Elementor locations + Theme Builder compatibility, local fonts, plugin installer screen, bundled `storebox-core.zip`. |
| `storebox-core/` (plugin) | Units and Locations post types, taxonomies and fields, enquiry handling, reusable component renderers, 14 custom Elementor widgets, dynamic tags, the demo importer and the demo content. |
| `storebox-child/` | Child theme starter. |

Without Elementor the theme still renders every template (fallback PHP
templates). Without the plugin the theme works as a blog/business theme.

## 3. Content model

* `sb_unit` (Units) — title = unit name (e.g. "Small"), fields: area m²,
  width, depth, ceiling, floor level, monthly price, number available,
  location, "typically fits", card highlights, gallery, featured flag.
  Taxonomies: `sb_unit_type` (Self / Business / Vehicle storage),
  `sb_unit_size` (Small / Medium / Large — label + range hint),
  `sb_unit_feature` (ordered features with a "show on cards" flag).
* `sb_location` (Locations) — short area name, street, postcode, city,
  phone, email, access hours, units total / free, tags, 7-day office hours,
  latitude / longitude, gallery.
* `sb_enquiry` (private) — reservation, waitlist and contact requests.
* Blog uses native posts, categories and authors; read time is computed with an
  optional override.

Status rule: 0 available → "Fully booked" (or "Waitlist"), ≤ threshold (3) →
"N left", otherwise "N available". Weekly price = monthly × 12 / 52.

## 4. Elementor architecture

* All demo pages are Flexbox Container layouts with native widgets: Heading,
  Text Editor, Button, Image, Icon, Icon List, Counter, Social Icons, Nested
  Accordion, Divider, Spacer.
* Global Colors and Global Fonts carry the design tokens; theme CSS reads the
  same values (`--e-global-color-*`) so theme templates and Elementor pages stay
  in sync.
* Theme helper classes (added through *Advanced → CSS Classes*) supply only what
  Elementor cannot express: eyebrow dash, accented `<em>` in headings, card
  hover lift / image zoom, bento spans, timeline line and dots, stat dash.
* A custom entrance animation ("Storebox Rise") reproduces the source reveal.

### Custom widgets (plugin) and why they exist

| Widget | Reason no native widget fits |
|---|---|
| Unit Grid | queries units, grid or auto-advancing rail, live filter/sort bar |
| Unit Gallery | main image + thumbnail switcher bound to unit data |
| Unit Specs | unit data (area, dimensions, ceiling, floor) |
| Unit Features | ordered feature taxonomy with yes/no state |
| Unit Booking Panel | price, weekly price, status, location, CTA that switches to waitlist |
| Location Grid | queries locations; overlay cards, info cards or rows |
| Location Facts | location data (address, access, units, phone) |
| Opening Hours | 7-day table with today highlight |
| Location Map | real multi-pin map (Leaflet) + illustrative offline mode |
| Size Calculator | interactive volume → size recommendation |
| Size Chooser | to-scale interactive size comparison |
| Size Guide | data table (Elementor has no table widget) |
| Post Grid | styled post listing, featured/lead post, category chips, pagination, related posts, current-query mode for archives (Free has no posts widget) |
| Enquiry Form | reservation / waitlist / contact requests tied to units and locations |

### Elementor Pro

Theme Builder templates are supplied for Header, Footer, Single Post, Blog
Archive, Search Results, 404, Single Unit and Single Location, with display
conditions set by the importer. Pro-only native widgets used there: Site Logo,
Nav Menu, Post Title, Post Content, Featured Image, Post Info, Author Box,
Search Form, Archive Title. Dynamic tags for unit and location data are
registered for Pro users. Nothing from Pro is re-implemented: Free users get the
theme's own header/footer (Customizer + menus) and PHP templates.

## 5. Demo import

A self-contained importer in Storebox Core (Storebox → Demo Import). Existing
importers (One Click Demo Import / WordPress Importer) fetch media from remote
URLs and do not remap media or post IDs inside Elementor data, which would leave
every image hot-linked to a demo server. The Storebox importer reads a JSON
manifest per demo, imports bundled images from local files, replaces
placeholders (`{{image:…}}`, `{{page:…}}`, `{{unit:…}}`, `{{location:…}}`,
`{{term:…}}`) inside Elementor data, applies the Elementor kit, menus,
widgets, Customizer settings and front page, and assigns Theme Builder
conditions when Elementor Pro is active. Every imported item is tagged so it can
be removed again from the same screen.

## 6. Assets and licensing

* Photography: the Pexels photos are not redistributable in the package; they
  are replaced by 12 original illustrations generated for Storebox (licensed
  with the theme). See `docs/` → Asset licensing.
* Archivo (SIL OFL 1.1) is bundled as two variable WOFF2 subsets and
  registered with Elementor as a local font, so no Google request is made.
* Leaflet 1.9.4 (BSD-2-Clause) is bundled in the plugin and loaded only on
  pages with a Location Map. Map tiles default to OpenStreetMap (attribution
  shown; configurable tile URL).
* Icons in native widgets use Font Awesome Free, which ships with Elementor.
  Custom widgets and templates use inline SVG line icons drawn for Storebox.

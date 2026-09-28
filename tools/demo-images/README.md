# Storebox demo artwork

The HTML designs used stock photos from Pexels that are licensed for the live
demo only. The theme package ships these original illustrations instead. They
are drawn from code (perspective-projected vector scenes in the Storebox
palette) and rendered to JPEG with headless Chromium.

    node tools/demo-images/render.js storebox-core/demo/images          # all scenes
    node tools/demo-images/render.js /tmp/out storage-corridor-trolley   # one scene
    SVG=1 node tools/demo-images/render.js /tmp/out                      # also keep the SVG

Scenes live in `scenes/`, shared drawing helpers in `lib.js`. `sheet.js`
builds a contact sheet for reviewing a batch.

Requires Node 18+ and Playwright with Chromium.

## Licence

The artwork is original work created for Storebox and is distributed under
GPL-2.0-or-later with the theme and plugin. Buyers may use the images on
their own sites, including the live site.

| File | Replaces (design) | Subject |
| --- | --- | --- |
| storage-corridor-trolley.jpg | Pexels 5759145 | Corridor with roller doors, hand truck with boxes |
| storage-corridor-wide.jpg | Pexels 5759037 | Wide corridor of units |
| unit-open-door.jpg | Pexels 5759147 | Open unit seen from the corridor |
| unit-shelving.jpg | Pexels 6169022 | Inside a unit: shelving, boxes, bins |
| unit-small-yellow.jpg | Pexels 5759123 | Small unit behind a yellow door |
| storage-lockers.jpg | Pexels 851305 | Wall of lockers |
| door-padlock.jpg | Pexels 38573375 | Red roller door with a padlock |
| vehicle-bay.jpg | Pexels 20491127 | Vehicle bay with a covered car |
| shutters-row.jpg | Pexels 32011828 | Row of colourful roller doors, outside |
| drive-up-units.jpg | Pexels 32151280 | Yellow drive-up units with a van |
| packing-table.jpg | Pexels 4246123 | Packing a box on a table |
| moving-boxes-room.jpg | Pexels 7464683 | Moving boxes in a bright room |

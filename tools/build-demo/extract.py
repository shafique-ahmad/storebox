#!/usr/bin/env python3
"""
Extracts the demo content (units, locations, posts) from the StoreBox HTML
designs into tools/build-demo/data/source.json, the input of build.js.

Usage: python3 tools/build-demo/extract.py <self-storage-dir> <business-storage-dir>
"""
import html
import json
import os
import re
import sys

SELF, BUSINESS = sys.argv[1], sys.argv[2]
OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'data', 'source.json')

# Pexels photo IDs in the designs -> the original artwork that replaces them.
PHOTOS = {
    '5759145': 'storage-corridor-trolley', '5759037': 'storage-corridor-wide',
    '5759147': 'unit-open-door', '6169022': 'unit-shelving', '5759123': 'unit-small-yellow',
    '851305': 'storage-lockers', '38573375': 'door-padlock', '20491127': 'vehicle-bay',
    '32011828': 'shutters-row', '32151280': 'drive-up-units', '4246123': 'packing-table',
    '7464683': 'moving-boxes-room',
}


def read(path):
    with open(path, encoding='utf-8') as f:
        return f.read()


def text(fragment):
    fragment = re.sub(r'<svg.*?</svg>', '', fragment, flags=re.S)
    fragment = re.sub(r'<br\s*/?>', '\n', fragment)
    fragment = re.sub(r'<[^>]+>', '', fragment)
    return html.unescape(re.sub(r'[ \t]+', ' ', fragment)).strip()


def photo(fragment):
    m = re.search(r'pexels-photo-(\d+)', fragment)
    return PHOTOS.get(m.group(1)) if m else None


def first(pattern, s, flags=re.S):
    m = re.search(pattern, s, flags)
    return m.group(1) if m else ''


def clean_html(fragment):
    """Keeps simple article markup; photo URLs are dropped."""
    fragment = re.sub(r'<svg.*?</svg>', '', fragment, flags=re.S)
    fragment = re.sub(r'\s+', ' ', fragment)
    fragment = re.sub(r'>\s+<', '><', fragment)
    return fragment.strip()


# ---------------------------------------------------------------- units
units = []
for name in sorted(os.listdir(SELF)):
    if not name.startswith('unit-') or not name.endswith('.html'):
        continue
    s = read(os.path.join(SELF, name))
    b = read(os.path.join(BUSINESS, name))
    slug = name[5:-5]
    title = text(first(r'<h1[^>]*>(.*?)</h1>', s))
    type_label = text(first(r'<span class="eyebrow">(.*?)</span>', s))
    lede = text(first(r'<p class="lede">(.*?)</p>', s))
    specs = dict((text(k), text(v)) for k, v in re.findall(r'<div class="spec"><span>(.*?)</span><b>(.*?)</b></div>', s))
    gallery = [PHOTOS.get(p) for p in re.findall(r'data-full="[^"]*pexels-photo-(\d+)', s)]
    checks = [text(li) for li in re.findall(r'<li>(.*?)</li>', first(r'<ul class="checks">(.*?)</ul>', s))]
    checks_b = [text(li) for li in re.findall(r'<li>(.*?)</li>', first(r'<ul class="checks">(.*?)</ul>', b))]
    book = first(r'<div class="book">(.*?)</aside>', s)
    price = first(r'<div class="book-price"><b>€(\d+)</b>', book)
    tag = text(first(r'<span class="tag [a-z]+"><i></i>(.*?)</span>', book))
    location = text(first(r'<span>Location</span><a[^>]*>(.*?)</a>', book))
    access = text(first(r'<span>Access</span><b>(.*?)</b>', book))
    prose = first(r'<div class="prose">(.*?)</div>', s)
    what_fits = clean_html(first(r'(<h2>What fits</h2>.*?)<h2>This unit has</h2>', prose))
    fits = lede.split(' at ')[0].rstrip('.')
    m = re.match(r'(\d+) available|(\d+) left', tag)
    available = int(m.group(1) or m.group(2)) if m else 0
    area = float(specs.get('Floor area', '0').replace(' m²', ''))
    dims = specs.get('Dimensions', '').replace(' m', '')
    width, depth = [float(x) for x in dims.split(' × ')] if ' × ' in dims else (0, 0)
    units.append({
        'slug': slug,
        'title': title,
        'name': title.split(' — ')[0],
        'type_label': type_label,
        'area': area,
        'width': width,
        'depth': depth,
        'ceiling': float(specs.get('Ceiling', '0').replace(' m', '')),
        'floor': specs.get('Floor', ''),
        'price': int(price or 0),
        'available': available,
        'location': location,
        'access': access,
        'fits': fits,
        'lede': lede,
        'features': checks,
        'features_business': checks_b,
        'gallery': gallery,
        'content': what_fits,
    })

# Card copy from the business units page (editorial cards).
bu = read(os.path.join(BUSINESS, 'units.html'))
for card in re.findall(r'<article class="card"(.*?)</article>', bu, flags=re.S):
    href = first(r'href="unit-([a-z0-9-]+)\.html"', card)
    for u in units:
        if u['slug'] == href:
            u['card_business'] = text(first(r'<p class="fits">(.*?)</p>', card))
            u['size_key'] = first(r'data-size="([a-z]+)"', card)
            u['loc_key'] = first(r'data-loc="([a-z]+)"', card)
            u['type_key'] = first(r'data-type="([a-z]+)"', card)
# Rail copy from the business home page.
bh = read(os.path.join(BUSINESS, 'index.html'))
for card in re.findall(r'<article class="card">(.*?)</article>', bh, flags=re.S):
    href = first(r'href="unit-([a-z0-9-]+)\.html"', card)
    for u in units:
        if u['slug'] == href:
            u['rail_business'] = text(first(r'<p class="fits">(.*?)</p>', card))
            u['rail_photo'] = photo(card)

# ------------------------------------------------------------ locations
locations = []
for name in sorted(os.listdir(SELF)):
    if not name.startswith('location-') or not name.endswith('.html'):
        continue
    s = read(os.path.join(SELF, name))
    slug = name[9:-5]
    facts = dict((text(k), v) for k, v in re.findall(r'<div class="fact"><span>(.*?)</span><b>(.*?)</b></div>', s))
    address = [l.strip() for l in text(facts.get('Address', '')).split('\n') if l.strip()]
    units_fact = text(facts.get('Units', ''))
    m = re.match(r'(\d+) · (\d+) free', units_fact)
    about = first(r'About this facility</span>(.*?)<div class="pill-row"', s)
    paragraphs = [text(p) for p in re.findall(r'<p>(.*?)</p>', about)]
    hours = [(text(d), text(t)) for d, t in re.findall(r'<tr><td>(.*?)</td><td>(.*?)</td></tr>', first(r'<table class="hours">(.*?)</table>', s))]
    street, _, rest = (address + ['', ''])[0], None, (address + ['', ''])[1]
    postcode = ' '.join(rest.split(' ')[:2]) if rest else ''
    city = ' '.join(rest.split(' ')[2:]) if rest else ''
    locations.append({
        'slug': slug,
        'title': text(first(r'<h1[^>]*>(.*?)</h1>', s)),
        'area_name': text(first(r'<span class="eyebrow">(.*?)</span>', s)),
        'lede': text(first(r'<p class="lede">(.*?)</p>', s)),
        'street': street,
        'postcode': postcode,
        'city': city,
        'access': text(facts.get('Access', '')),
        'units_total': int(m.group(1)) if m else 0,
        'units_free': int(m.group(2)) if m else 0,
        'phone': text(facts.get('Phone', '')),
        'heading': text(first(r'About this facility</span>\s*<h2[^>]*>(.*?)</h2>', s)),
        'paragraphs': paragraphs,
        'tags': [text(t) for t in re.findall(r'<span class="pill">(.*?)</span>', s)],
        'hours': hours,
        'hero_photo': photo(first(r'<div class="phero-bg">(.*?)</div>', s)),
        'photo': photo(first(r'<div class="rounded"[^>]*>(.*?)</div>', s)),
    })

# ---------------------------------------------------------------- posts
posts = []
blog = read(os.path.join(SELF, 'blog.html'))
categories = {}
for key, label in re.findall(r'data-cat="([a-z-]+)" aria-pressed="false">(.*?)</button>', blog):
    categories[key] = text(label)
for card in re.findall(r'<a class="post[^"]*"(.*?)</a>', blog, flags=re.S):
    href = first(r'href="post-([a-z0-9-]+)\.html"', card)
    meta = re.findall(r'<span[^>]*>(.*?)</span>', first(r'<div class="post-meta">(.*?)</div>', card))
    posts.append({
        'slug': href,
        'category': first(r'data-cat="([a-z-]+)"', card),
        'title': text(first(r'<h3>(.*?)</h3>', card)),
        'excerpt': text(first(r'<p>(.*?)</p>', card)),
        'date': text(meta[1]) if len(meta) > 1 else '',
        'read_time': int(re.sub(r'\D', '', text(meta[2])) or 0) if len(meta) > 2 else 0,
        'photo': photo(card),
    })
post_page = read(os.path.join(SELF, 'post-how-to-pack-a-storage-unit.html'))
body = clean_html(first(r'<div class="prose">(.*?)</div>\s*<div class="author">', post_page))
author = {
    'initials': text(first(r'<div class="author">\s*<span class="av">(.*?)</span>', post_page)),
    'name': text(first(r'<div class="author">.*?<b>(.*?)</b>', post_page)),
    'bio': text(first(r'<div class="author">.*?<b>.*?</b><span>(.*?)</span>', post_page)),
}

# The business design has its own, shorter article copy and byline.
post_page_b = read(os.path.join(BUSINESS, 'post-how-to-pack-a-storage-unit.html'))
body_b = clean_html(first(r'<div class="prose">(.*?)</div>\s*<div class="byline">', post_page_b))
author['bio_business'] = text(first(r'<div class="byline">.*?<b>.*?</b><span>(.*?)</span>', post_page_b))

os.makedirs(os.path.dirname(OUT), exist_ok=True)
with open(OUT, 'w', encoding='utf-8') as f:
    json.dump({'units': units, 'locations': locations, 'posts': posts, 'categories': categories, 'post_body': body, 'post_body_business': body_b, 'author': author}, f, indent=1, ensure_ascii=False)
print('units', len(units), 'locations', len(locations), 'posts', len(posts), '->', OUT)

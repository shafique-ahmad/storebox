<?php
/**
 * Public API of Storebox Core.
 *
 * Themes and widgets use these functions to read unit and location data and
 * to print components. All output helpers return escaped HTML.
 *
 * @package Storebox_Core
 */

defined( 'ABSPATH' ) || exit;

/*
 * -------------------------------------------------------------------------
 * Settings
 * -------------------------------------------------------------------------
 */

/**
 * Default plugin settings. Empty strings fall back to translated defaults.
 *
 * @return array<string, mixed>
 */
function storebox_core_settings_defaults() {
	return apply_filters(
		'storebox_core/settings_defaults',
		array(
			'currency_symbol'   => '€',
			'currency_position' => 'before',
			'price_decimals'    => 0,
			'low_threshold'     => 3,
			'label_full'        => '',
			'label_low'         => '',
			'label_ok'          => '',
			'weekly_text'       => '',
			'units_page'        => 0,
			'locations_page'    => 0,
			'notify_email'      => '',
			'store_enquiries'   => 1,
			'reservation_info'  => '',
			'privacy_note'      => '',
			'help_phone'        => '',
			'booking_rows'      => '',
			'booking_note'      => '',
			'help_title'        => '',
			'help_text'         => '',
			'map_mode'          => 'live',
			'map_tiles'         => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
			'map_attribution'   => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
			'map_consent'       => 0,
			'map_muted'         => 1,
		)
	);
}

/**
 * Returns a plugin setting.
 *
 * @param string $key Setting name.
 * @return mixed
 */
function storebox_core_setting( $key ) {
	$settings = get_option( 'storebox_core_settings', array() );
	$defaults = storebox_core_settings_defaults();

	if ( is_array( $settings ) && isset( $settings[ $key ] ) && '' !== $settings[ $key ] ) {
		return $settings[ $key ];
	}

	return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
}

/**
 * URL of the page chosen as the Units or Locations page.
 *
 * @param string $which "units" or "locations".
 * @return string
 */
function storebox_core_page_url( $which ) {
	$page_id = absint( storebox_core_setting( $which . '_page' ) );

	if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
		return (string) get_permalink( $page_id );
	}

	return '';
}

/**
 * Design style for a component: "soft" or "editorial". An empty request uses
 * the theme's preset (via the storebox_core/default_style filter).
 *
 * @param string $requested Requested style.
 * @return string
 */
function storebox_core_style( $requested = '' ) {
	if ( in_array( $requested, array( 'soft', 'editorial' ), true ) ) {
		return $requested;
	}

	$default = apply_filters( 'storebox_core/default_style', 'soft' );

	return in_array( $default, array( 'soft', 'editorial' ), true ) ? $default : 'soft';
}

/*
 * -------------------------------------------------------------------------
 * Formatting
 * -------------------------------------------------------------------------
 */

/**
 * Formats a number with only the decimals it needs (2 → "2", 2.5 → "2.5").
 *
 * @param float|int|string $number       Number.
 * @param int              $max_decimals Maximum decimals.
 * @return string
 */
function storebox_core_format_number( $number, $max_decimals = 2 ) {
	$number   = (float) $number;
	$decimals = $max_decimals;

	for ( $i = 0; $i <= $max_decimals; $i++ ) {
		if ( abs( round( $number, $i ) - $number ) < 0.00001 ) {
			$decimals = $i;
			break;
		}
	}

	return number_format_i18n( $number, $decimals );
}

/**
 * Formats a price with the configured currency.
 *
 * @param float|int $amount Amount.
 * @return string Plain text (not escaped).
 */
function storebox_core_format_price( $amount ) {
	$value  = number_format_i18n( (float) $amount, absint( storebox_core_setting( 'price_decimals' ) ) );
	$symbol = (string) storebox_core_setting( 'currency_symbol' );

	switch ( storebox_core_setting( 'currency_position' ) ) {
		case 'after':
			$formatted = $value . $symbol;
			break;
		case 'before_space':
			$formatted = $symbol . "\u{00A0}" . $value;
			break;
		case 'after_space':
			$formatted = $value . "\u{00A0}" . $symbol;
			break;
		default:
			$formatted = $symbol . $value;
	}

	return apply_filters( 'storebox_core/format_price', $formatted, $amount );
}

/**
 * Square metres to square feet (rounded).
 *
 * @param float $m2 Area in m².
 * @return int
 */
function storebox_core_m2_to_ft2( $m2 ) {
	return (int) round( (float) $m2 * 10.7639 );
}

/**
 * "5 m²".
 *
 * @param float $m2 Area.
 * @return string
 */
function storebox_core_format_area( $m2 ) {
	/* translators: %s: area in square metres. */
	return sprintf( _x( '%s m²', 'area in square metres', 'storebox-core' ), storebox_core_format_number( $m2, 1 ) );
}

/**
 * "54 ft²".
 *
 * @param float $m2 Area in m².
 * @return string
 */
function storebox_core_format_ft2( $m2 ) {
	/* translators: %s: area in square feet. */
	return sprintf( _x( '%s ft²', 'area in square feet', 'storebox-core' ), number_format_i18n( storebox_core_m2_to_ft2( $m2 ) ) );
}

/**
 * "2.4 m".
 *
 * @param float $metres Length.
 * @return string
 */
function storebox_core_format_length( $metres ) {
	/* translators: %s: length in metres. */
	return sprintf( _x( '%s m', 'length in metres', 'storebox-core' ), storebox_core_format_number( $metres ) );
}

/**
 * "2 × 2.5 m".
 *
 * @param float $width Width in metres.
 * @param float $depth Depth in metres.
 * @return string
 */
function storebox_core_format_dimensions( $width, $depth ) {
	if ( ! $width || ! $depth ) {
		return '';
	}

	/* translators: 1: width, 2: depth, both in metres. */
	return sprintf( _x( '%1$s × %2$s m', 'unit dimensions', 'storebox-core' ), storebox_core_format_number( $width ), storebox_core_format_number( $depth ) );
}

/**
 * Availability status of a unit.
 *
 * @param int $available Number of units free.
 * @return array{key: string, label: string, available: int}
 */
function storebox_core_unit_status( $available ) {
	$available = max( 0, (int) $available );
	$threshold = absint( storebox_core_setting( 'low_threshold' ) );

	if ( 0 === $available ) {
		$key   = 'full';
		$label = storebox_core_setting( 'label_full' );
		$label = $label ? $label : __( 'Fully booked', 'storebox-core' );
	} elseif ( $available <= $threshold ) {
		$key      = 'low';
		$template = storebox_core_setting( 'label_low' );
		/* translators: %d: number of units still free. */
		$template = ( $template && false !== strpos( $template, '%d' ) ) ? $template : _n( '%d left', '%d left', $available, 'storebox-core' );
		$label    = sprintf( $template, $available );
	} else {
		$key      = 'ok';
		$template = storebox_core_setting( 'label_ok' );
		/* translators: %d: number of units free. */
		$template = ( $template && false !== strpos( $template, '%d' ) ) ? $template : _n( '%d available', '%d available', $available, 'storebox-core' );
		$label    = sprintf( $template, $available );
	}

	return apply_filters(
		'storebox_core/unit_status',
		array(
			'key'       => $key,
			'label'     => $label,
			'available' => $available,
		),
		$available
	);
}

/**
 * Access hours as a label. "24/7" becomes "24/7 access" in chip context.
 *
 * @param string $access  Access hours.
 * @param string $context "plain" or "chip".
 * @return string
 */
function storebox_core_access_label( $access, $context = 'plain' ) {
	$access = trim( (string) $access );

	if ( '' === $access ) {
		return '';
	}

	if ( 'chip' === $context && preg_match( '#^24\s*/\s*7$#', $access ) ) {
		/* translators: %s: access hours, e.g. 24/7. */
		return sprintf( __( '%s access', 'storebox-core' ), $access );
	}

	return $access;
}

/**
 * Parses a list of attachment/post IDs.
 *
 * @param string|array $value Comma-separated string or array.
 * @return int[]
 */
function storebox_core_parse_ids( $value ) {
	if ( ! is_array( $value ) ) {
		$value = explode( ',', (string) $value );
	}

	return array_values( array_unique( array_filter( array_map( 'absint', $value ) ) ) );
}

/**
 * Splits a textarea value into trimmed, non-empty lines.
 *
 * @param string $value Text.
 * @return string[]
 */
function storebox_core_lines( $value ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $value ) ) ) );
}

/*
 * -------------------------------------------------------------------------
 * Data
 * -------------------------------------------------------------------------
 */

/**
 * Terms of a Storebox taxonomy sorted by their "Order" field, then name.
 *
 * @param string $taxonomy   Taxonomy name.
 * @param bool   $hide_empty Hide terms without units.
 * @return WP_Term[]
 */
function storebox_core_get_terms_ordered( $taxonomy, $hide_empty = false ) {
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => $hide_empty,
		)
	);

	if ( is_wp_error( $terms ) || ! $terms ) {
		return array();
	}

	usort(
		$terms,
		static function ( $a, $b ) {
			$oa = (int) get_term_meta( $a->term_id, 'sb_order', true );
			$ob = (int) get_term_meta( $b->term_id, 'sb_order', true );
			return $oa === $ob ? strcasecmp( $a->name, $b->name ) : $oa - $ob;
		}
	);

	return $terms;
}

/**
 * All unit features, in their configured order.
 *
 * @return array<int, array{id: int, slug: string, name: string, card: bool}>
 */
function storebox_core_get_features() {
	static $features = null;

	if ( null !== $features ) {
		return $features;
	}

	$features = array();
	foreach ( storebox_core_get_terms_ordered( 'sb_unit_feature' ) as $term ) {
		$features[] = array(
			'id'   => (int) $term->term_id,
			'slug' => $term->slug,
			'name' => $term->name,
			'card' => (bool) get_term_meta( $term->term_id, 'sb_card', true ),
		);
	}

	return $features;
}

/**
 * A location's tags: one per line (or comma separated), without the one that
 * repeats the access chip (it is shown separately).
 *
 * @param string $raw         Stored tags.
 * @param string $access_chip Access chip label, e.g. "24/7 access".
 * @return string[]
 */
function storebox_core_location_tags( $raw, $access_chip = '' ) {
	$tags = storebox_core_lines( str_replace( ',', "\n", $raw ) );

	if ( '' !== $access_chip ) {
		$tags = array_values(
			array_filter(
				$tags,
				static function ( $tag ) use ( $access_chip ) {
					return 0 !== strcasecmp( trim( $tag ), trim( $access_chip ) );
				}
			)
		);
	}

	return $tags;
}

/**
 * Location data, normalised for templates.
 *
 * @param int|WP_Post|null $post Location post.
 * @return array|null
 */
function storebox_core_get_location( $post = null ) {
	static $cache = array();

	$post = get_post( $post );
	if ( ! $post || 'sb_location' !== $post->post_type ) {
		return null;
	}

	if ( isset( $cache[ $post->ID ] ) ) {
		return $cache[ $post->ID ];
	}

	$meta = static function ( $key ) use ( $post ) {
		return get_post_meta( $post->ID, $key, true );
	};

	$street   = (string) $meta( '_sb_street' );
	$postcode = (string) $meta( '_sb_postcode' );
	$city     = (string) $meta( '_sb_city' );
	$total    = absint( $meta( '_sb_units_total' ) );
	$free     = absint( $meta( '_sb_units_free' ) );
	$phone    = (string) $meta( '_sb_phone' );
	$access   = (string) $meta( '_sb_access' );
	$lat      = $meta( '_sb_lat' );
	$lng      = $meta( '_sb_lng' );

	$data = array(
		'id'             => (int) $post->ID,
		'name'           => get_the_title( $post ),
		'url'            => get_permalink( $post ),
		'area_name'      => (string) $meta( '_sb_area_name' ),
		'street'         => $street,
		'postcode'       => $postcode,
		'city'           => $city,
		'address_lines'  => array_values( array_filter( array( $street, trim( $postcode . ' ' . $city ) ) ) ),
		'address_short'  => implode( ', ', array_filter( array( $street, $postcode ) ) ),
		'address_inline' => implode( ', ', array_filter( array( $street, trim( $postcode . ' ' . $city ) ) ) ),
		'phone'          => $phone,
		'phone_href'     => $phone ? 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) : '',
		'email'          => sanitize_email( (string) $meta( '_sb_email' ) ),
		'access'         => $access,
		'access_chip'    => storebox_core_access_label( $access, 'chip' ),
		'units_total'    => $total,
		'units_free'     => $free,
		/* translators: 1: total number of units, 2: number free now. */
		'units_label'    => $total ? sprintf( __( '%1$s · %2$s free now', 'storebox-core' ), number_format_i18n( $total ), number_format_i18n( $free ) ) : '',
		/* translators: 1: total number of units, 2: number free now. */
		'units_meta'     => $total ? sprintf( __( '%1$s units · %2$s free now', 'storebox-core' ), number_format_i18n( $total ), number_format_i18n( $free ) ) : '',
		/* translators: %s: number of units free now. */
		'free_label'     => sprintf( __( '%s free', 'storebox-core' ), number_format_i18n( $free ) ),
		'tags'           => storebox_core_location_tags( (string) $meta( '_sb_tags' ), storebox_core_access_label( $access, 'chip' ) ),
		'hours'          => storebox_core_location_hours( $post->ID ),
		'lat'            => '' !== $lat && null !== $lat ? (float) $lat : 0.0,
		'lng'            => '' !== $lng && null !== $lng ? (float) $lng : 0.0,
		'image_id'       => (int) get_post_thumbnail_id( $post ),
		'gallery'        => storebox_core_parse_ids( $meta( '_sb_gallery' ) ),
		'excerpt'        => has_excerpt( $post ) ? get_the_excerpt( $post ) : '',
	);

	$cache[ $post->ID ] = apply_filters( 'storebox_core/location_data', $data, $post );

	return $cache[ $post->ID ];
}

/**
 * Office hours of a location, ordered from the site's first day of the week.
 *
 * @param int $post_id Location ID.
 * @return array<int, array{day: int, label: string, value: string}> Day uses 0 = Sunday … 6 = Saturday.
 */
function storebox_core_location_hours( $post_id ) {
	global $wp_locale;

	$stored = get_post_meta( $post_id, '_sb_hours', true );
	$stored = is_array( $stored ) ? $stored : array();
	$start  = (int) get_option( 'start_of_week', 1 );
	$rows   = array();

	for ( $i = 0; $i < 7; $i++ ) {
		$day    = ( $start + $i ) % 7;
		$rows[] = array(
			'day'   => $day,
			'label' => $wp_locale ? $wp_locale->get_weekday( $day ) : gmdate( 'l', strtotime( "Sunday +{$day} days" ) ),
			'value' => isset( $stored[ $day ] ) ? (string) $stored[ $day ] : '',
		);
	}

	return $rows;
}

/**
 * Unit data, normalised for templates.
 *
 * @param int|WP_Post|null $post Unit post.
 * @return array|null
 */
function storebox_core_get_unit( $post = null ) {
	static $cache = array();

	$post = get_post( $post );
	if ( ! $post || 'sb_unit' !== $post->post_type ) {
		return null;
	}

	if ( isset( $cache[ $post->ID ] ) ) {
		return $cache[ $post->ID ];
	}

	$meta = static function ( $key ) use ( $post ) {
		return get_post_meta( $post->ID, $key, true );
	};

	$name      = get_the_title( $post );
	$area      = (float) $meta( '_sb_area' );
	$width     = (float) $meta( '_sb_width' );
	$depth     = (float) $meta( '_sb_depth' );
	$ceiling   = (float) $meta( '_sb_ceiling' );
	$price     = (float) $meta( '_sb_price' );
	$available = absint( $meta( '_sb_available' ) );
	$fits      = (string) $meta( '_sb_fits' );
	$status    = storebox_core_unit_status( $available );
	$location  = storebox_core_get_location( absint( $meta( '_sb_location' ) ) );

	$types = get_the_terms( $post->ID, 'sb_unit_type' );
	$type  = ( $types && ! is_wp_error( $types ) ) ? $types[0] : null;
	$sizes = get_the_terms( $post->ID, 'sb_unit_size' );
	$size  = ( $sizes && ! is_wp_error( $sizes ) ) ? $sizes[0] : null;

	$has_features = wp_get_post_terms( $post->ID, 'sb_unit_feature', array( 'fields' => 'ids' ) );
	$has_features = is_wp_error( $has_features ) ? array() : array_map( 'intval', $has_features );
	$features     = array();
	foreach ( storebox_core_get_features() as $feature ) {
		$feature['has'] = in_array( $feature['id'], $has_features, true );
		$features[]     = $feature;
	}

	$images = storebox_core_parse_ids( $meta( '_sb_gallery' ) );
	$thumb  = (int) get_post_thumbnail_id( $post );
	if ( $thumb ) {
		$images = array_values( array_unique( array_merge( array( $thumb ), $images ) ) );
	}

	$area_label = storebox_core_format_area( $area );

	$summary = array();
	if ( $fits && $location ) {
		/* translators: 1: what the unit typically fits, 2: location name. */
		$summary[] = sprintf( __( '%1$s at %2$s.', 'storebox-core' ), $fits, $location['name'] );
	} elseif ( $fits ) {
		$summary[] = rtrim( $fits, '.' ) . '.';
	}
	$summary[] = $available > 0 ? __( 'Available to move into today.', 'storebox-core' ) : __( 'Fully booked right now.', 'storebox-core' );

	$weekly_text = storebox_core_setting( 'weekly_text' );
	/* translators: %s: price per week. */
	$weekly_text = ( $weekly_text && false !== strpos( $weekly_text, '%s' ) ) ? $weekly_text : __( 'About %s a week · first month pro-rata', 'storebox-core' );

	$data = array(
		'id'               => (int) $post->ID,
		'name'             => $name,
		'url'              => get_permalink( $post ),
		/* translators: 1: unit name, 2: area. */
		'display_title'    => sprintf( _x( '%1$s — %2$s', 'unit name — area', 'storebox-core' ), $name, $area_label ),
		/* translators: 1: unit name, 2: area. */
		'short_title'      => sprintf( _x( '%1$s %2$s', 'unit name and area', 'storebox-core' ), $name, $area_label ),
		'area'             => $area,
		'area_label'       => $area_label,
		'area_ft'          => storebox_core_m2_to_ft2( $area ),
		'area_ft_label'    => storebox_core_format_ft2( $area ),
		'width'            => $width,
		'depth'            => $depth,
		'dimensions_label' => storebox_core_format_dimensions( $width, $depth ),
		'ceiling'          => $ceiling,
		'ceiling_label'    => $ceiling ? storebox_core_format_length( $ceiling ) : '',
		'floor'            => (string) $meta( '_sb_floor' ),
		'fits'             => $fits,
		'highlights'       => storebox_core_lines( $meta( '_sb_highlights' ) ),
		'price'            => $price,
		'price_label'      => storebox_core_format_price( $price ),
		'weekly_label'     => sprintf( $weekly_text, storebox_core_format_price( round( $price * 12 / 52 ) ) ),
		'available'        => $available,
		'status'           => $status['key'],
		'status_label'     => $status['label'],
		'location'         => $location,
		'type'             => $type ? array(
			'id'   => (int) $type->term_id,
			'slug' => $type->slug,
			'name' => $type->name,
		) : null,
		'type_label'       => $type ? $type->name : '',
		'size'             => $size ? array(
			'id'   => (int) $size->term_id,
			'slug' => $size->slug,
			'name' => $size->name,
		) : null,
		'features'         => $features,
		'images'           => $images,
		'image_id'         => $images ? $images[0] : 0,
		'excerpt'          => has_excerpt( $post ) ? get_the_excerpt( $post ) : '',
		'featured'         => (bool) $meta( '_sb_featured' ),
		'summary'          => implode( ' ', $summary ),
	);

	$cache[ $post->ID ] = apply_filters( 'storebox_core/unit_data', $data, $post );

	return $cache[ $post->ID ];
}

/**
 * Card bullets for a unit: its "show on cards" features, then the location.
 *
 * @param array  $unit          Unit data.
 * @param string $source        "features" or "highlights" (custom lines, falling back to features).
 * @param int    $limit         Maximum bullets.
 * @param bool   $with_location Append the location name.
 * @return string[]
 */
function storebox_core_unit_bullets( $unit, $source = 'features', $limit = 3, $with_location = true ) {
	if ( 'highlights' === $source && ! empty( $unit['highlights'] ) ) {
		return array_slice( $unit['highlights'], 0, $limit );
	}

	$bullets = array();
	foreach ( $unit['features'] as $feature ) {
		if ( $feature['has'] && $feature['card'] ) {
			$bullets[] = $feature['name'];
		}
	}

	if ( $with_location && ! empty( $unit['location'] ) ) {
		$bullets[] = $unit['location']['name'];
	}

	return array_slice( $bullets, 0, max( 0, (int) $limit ) );
}

/**
 * Queries units and returns their IDs in display order.
 *
 * @param array $args {
 *     Query options.
 *
 *     @type string $source    all | featured | manual | location | similar.
 *     @type int[]  $ids       Unit IDs for "manual".
 *     @type int    $location  Location ID for "location".
 *     @type int    $unit      Reference unit for "similar".
 *     @type string $type      Unit type slug filter.
 *     @type string $size      Unit size slug filter.
 *     @type int    $count     Maximum units (0 = all).
 *     @type string $orderby   area | price | menu_order | title.
 *     @type string $order     ASC | DESC.
 *     @type bool   $hide_full Exclude fully booked units.
 * }
 * @return int[]
 */
function storebox_core_query_units( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'source'    => 'all',
			'ids'       => array(),
			'location'  => 0,
			'unit'      => 0,
			'type'      => '',
			'size'      => '',
			'count'     => 0,
			'orderby'   => 'area',
			'order'     => 'ASC',
			'hide_full' => false,
		)
	);

	$count = absint( $args['count'] );
	$query = array(
		'post_type'      => 'sb_unit',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => array(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Small, indexed post type.
		'tax_query'      => array(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Optional filter.
	);

	switch ( $args['source'] ) {
		case 'featured':
			$query['meta_query'][] = array(
				'key'   => '_sb_featured',
				'value' => '1',
			);
			break;
		case 'manual':
			$ids = storebox_core_parse_ids( $args['ids'] );
			if ( ! $ids ) {
				return array();
			}
			$query['post__in'] = $ids;
			break;
		case 'location':
			$query['meta_query'][] = array(
				'key'   => '_sb_location',
				'value' => absint( $args['location'] ),
			);
			break;
		case 'similar':
			if ( $args['unit'] ) {
				$query['post__not_in'] = array( absint( $args['unit'] ) );
			}
			break;
	}

	foreach ( array(
		'type' => 'sb_unit_type',
		'size' => 'sb_unit_size',
	) as $arg => $taxonomy ) {
		if ( ! empty( $args[ $arg ] ) ) {
			$query['tax_query'][] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_title', (array) $args[ $arg ] ),
			);
		}
	}

	if ( $args['hide_full'] ) {
		$query['meta_query'][] = array(
			'key'     => '_sb_available',
			'value'   => 0,
			'compare' => '>',
			'type'    => 'NUMERIC',
		);
	}

	$order = 'DESC' === strtoupper( (string) $args['order'] ) ? 'DESC' : 'ASC';
	switch ( $args['orderby'] ) {
		case 'price':
			$query['meta_key'] = '_sb_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$query['orderby']  = array(
				'meta_value_num' => $order,
				'title'          => 'ASC',
			);
			break;
		case 'menu_order':
			$query['orderby'] = array(
				'menu_order' => $order,
				'title'      => 'ASC',
			);
			break;
		case 'title':
			$query['orderby'] = array( 'title' => $order );
			break;
		default:
			$query['meta_key'] = '_sb_area'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$query['orderby']  = array(
				'meta_value_num' => $order,
				'title'          => 'ASC',
			);
	}

	if ( 'manual' === $args['source'] ) {
		$query['orderby'] = 'post__in';
		unset( $query['meta_key'] );
	}

	$ids = get_posts( apply_filters( 'storebox_core/unit_query_args', $query, $args ) );

	if ( 'similar' === $args['source'] && $args['unit'] ) {
		$reference = (float) get_post_meta( absint( $args['unit'] ), '_sb_area', true );
		usort(
			$ids,
			static function ( $a, $b ) use ( $reference ) {
				$da = abs( (float) get_post_meta( $a, '_sb_area', true ) - $reference );
				$db = abs( (float) get_post_meta( $b, '_sb_area', true ) - $reference );
				return $da === $db ? 0 : ( $da < $db ? -1 : 1 );
			}
		);
	}

	return $count ? array_slice( $ids, 0, $count ) : $ids;
}

/*
 * -------------------------------------------------------------------------
 * Components
 * -------------------------------------------------------------------------
 */

/**
 * Returns the HTML of a component (see templates/ for the markup).
 *
 * @param string $name Component name, e.g. "unit-grid".
 * @param array  $args Component arguments.
 * @return string
 */
function storebox_core_get_component( $name, $args = array() ) {
	return \Storebox_Core\Components::render( $name, $args );
}

/**
 * Prints a component.
 *
 * @param string $name Component name.
 * @param array  $args Component arguments.
 */
function storebox_core_component( $name, $args = array() ) {
	echo storebox_core_get_component( $name, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Templates escape their output.
}

/**
 * Enqueues the styles and scripts of the given components.
 *
 * @param string[] $names Component names.
 */
function storebox_core_enqueue_components( $names ) {
	\Storebox_Core\Components::enqueue( (array) $names );
}

/*
 * -------------------------------------------------------------------------
 * Size tools
 * -------------------------------------------------------------------------
 */

/**
 * Default size list for the calculator, chooser and size guide.
 *
 * @return array<int, array{m2: float, dims: string, fits: string, ref: string, practice: string, price: float}>
 */
function storebox_core_default_sizes() {
	return apply_filters(
		'storebox_core/default_sizes',
		array(
			array(
				'm2'       => 2,
				'dims'     => '1.4 × 1.4 m',
				'fits'     => __( 'A wardrobe and a bike', 'storebox-core' ),
				'ref'      => __( 'a large wardrobe', 'storebox-core' ),
				'practice' => __( 'Seasonal boxes, a bike, suitcases', 'storebox-core' ),
				'price'    => 35,
			),
			array(
				'm2'       => 5,
				'dims'     => '2.0 × 2.5 m',
				'fits'     => __( 'A one-bedroom flat', 'storebox-core' ),
				'ref'      => __( 'a one-bedroom flat', 'storebox-core' ),
				'practice' => __( 'Double bed, sofa, table and chairs, ~20 boxes', 'storebox-core' ),
				'price'    => 59,
			),
			array(
				'm2'       => 10,
				'dims'     => '2.5 × 4.0 m',
				'fits'     => __( 'A two-bedroom home', 'storebox-core' ),
				'ref'      => __( 'a single garage', 'storebox-core' ),
				'practice' => __( 'Two bedrooms including white goods', 'storebox-core' ),
				'price'    => 89,
			),
			array(
				'm2'       => 15,
				'dims'     => '3.0 × 5.0 m',
				'fits'     => __( 'A three-bedroom home', 'storebox-core' ),
				'ref'      => __( 'a three-bedroom home', 'storebox-core' ),
				'practice' => __( 'Three bedrooms, garden furniture', 'storebox-core' ),
				'price'    => 119,
			),
			array(
				'm2'       => 20,
				'dims'     => '4.0 × 5.0 m',
				'fits'     => __( 'A four-bedroom house', 'storebox-core' ),
				'ref'      => __( 'a four-bedroom home', 'storebox-core' ),
				'practice' => __( 'Four bedrooms, or a car and some boxes', 'storebox-core' ),
				'price'    => 149,
			),
			array(
				'm2'       => 30,
				'dims'     => '5.0 × 6.0 m',
				'fits'     => __( 'A small business stockroom', 'storebox-core' ),
				'ref'      => __( 'a small business stockroom', 'storebox-core' ),
				'practice' => __( 'Racked stock, pallets, an office clear-out', 'storebox-core' ),
				'price'    => 209,
			),
		)
	);
}

/**
 * Default items for the size calculator (volume in m³ when stacked).
 *
 * @return array<int, array{name: string, volume: float}>
 */
function storebox_core_default_calculator_items() {
	return apply_filters(
		'storebox_core/default_calculator_items',
		array(
			array(
				'name'   => __( 'Boxes (medium)', 'storebox-core' ),
				'volume' => 0.10,
			),
			array(
				'name'   => __( 'Single bed', 'storebox-core' ),
				'volume' => 1.00,
			),
			array(
				'name'   => __( 'Double bed', 'storebox-core' ),
				'volume' => 1.80,
			),
			array(
				'name'   => __( 'Sofa, 2-seat', 'storebox-core' ),
				'volume' => 1.50,
			),
			array(
				'name'   => __( 'Sofa, 3-seat', 'storebox-core' ),
				'volume' => 2.20,
			),
			array(
				'name'   => __( 'Armchair', 'storebox-core' ),
				'volume' => 0.80,
			),
			array(
				'name'   => __( 'Wardrobe', 'storebox-core' ),
				'volume' => 1.50,
			),
			array(
				'name'   => __( 'Dining table', 'storebox-core' ),
				'volume' => 1.20,
			),
			array(
				'name'   => __( 'Desk', 'storebox-core' ),
				'volume' => 0.80,
			),
			array(
				'name'   => __( 'Bookshelf', 'storebox-core' ),
				'volume' => 0.70,
			),
			array(
				'name'   => __( 'Fridge / freezer', 'storebox-core' ),
				'volume' => 0.80,
			),
			array(
				'name'   => __( 'Washing machine', 'storebox-core' ),
				'volume' => 0.45,
			),
			array(
				'name'   => __( 'Bicycle', 'storebox-core' ),
				'volume' => 0.60,
			),
			array(
				'name'   => __( 'TV', 'storebox-core' ),
				'volume' => 0.20,
			),
		)
	);
}

/**
 * Lowest monthly price of the published units with exactly this floor area.
 *
 * @param float $m2 Floor area.
 * @return float|null Null when no unit has this area.
 */
function storebox_core_price_for_area( $m2 ) {
	static $prices = null;

	if ( null === $prices ) {
		$prices = array();
		$ids    = get_posts(
			array(
				'post_type'      => 'sb_unit',
				'post_status'    => 'publish',
				'posts_per_page' => 200, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- IDs only, for the price range; capped.
				'no_found_rows'  => true,
			)
		);
		foreach ( $ids as $id ) {
			$area  = (string) round( (float) get_post_meta( $id, '_sb_area', true ), 2 );
			$price = (float) get_post_meta( $id, '_sb_price', true );
			if ( $price > 0 && ( ! isset( $prices[ $area ] ) || $price < $prices[ $area ] ) ) {
				$prices[ $area ] = $price;
			}
		}
	}

	$key = (string) round( (float) $m2, 2 );

	return isset( $prices[ $key ] ) ? $prices[ $key ] : null;
}

/**
 * Normalises a size list (from a widget repeater or the defaults).
 *
 * @param array|null $sizes       Rows with m2/dims/fits/ref/practice/price keys.
 * @param bool       $sync_prices Replace prices with the cheapest unit of that area.
 * @return array
 */
function storebox_core_normalize_sizes( $sizes, $sync_prices = true ) {
	$sizes = is_array( $sizes ) && $sizes ? $sizes : storebox_core_default_sizes();
	$out   = array();

	foreach ( $sizes as $size ) {
		$m2 = isset( $size['m2'] ) ? (float) str_replace( ',', '.', (string) $size['m2'] ) : 0;
		if ( $m2 <= 0 ) {
			continue;
		}

		$price = isset( $size['price'] ) ? (float) $size['price'] : 0;
		if ( $sync_prices ) {
			$unit_price = storebox_core_price_for_area( $m2 );
			if ( null !== $unit_price ) {
				$price = $unit_price;
			}
		}

		$out[] = array(
			'm2'       => $m2,
			'dims'     => isset( $size['dims'] ) ? (string) $size['dims'] : '',
			'fits'     => isset( $size['fits'] ) ? (string) $size['fits'] : '',
			'ref'      => isset( $size['ref'] ) ? (string) $size['ref'] : '',
			'practice' => isset( $size['practice'] ) ? (string) $size['practice'] : '',
			'price'    => $price,
		);
	}

	usort(
		$out,
		static function ( $a, $b ) {
			return $a['m2'] === $b['m2'] ? 0 : ( $a['m2'] < $b['m2'] ? -1 : 1 );
		}
	);

	return $out;
}

/*
 * -------------------------------------------------------------------------
 * Posts
 * -------------------------------------------------------------------------
 */

/**
 * Estimated reading time in minutes ("_storebox_read_time" overrides it).
 *
 * @param int $post_id Post ID.
 * @return int
 */
function storebox_core_read_time( $post_id ) {
	if ( function_exists( 'storebox_read_time' ) ) {
		return storebox_read_time( $post_id );
	}

	$override = absint( get_post_meta( $post_id, '_storebox_read_time', true ) );
	if ( $override ) {
		return $override;
	}

	$content = wp_strip_all_tags( strip_shortcodes( (string) get_post_field( 'post_content', $post_id ) ) );
	$words   = count( preg_split( '/\s+/u', trim( $content ), -1, PREG_SPLIT_NO_EMPTY ) );

	return max( 1, (int) ceil( $words / 220 ) );
}

/**
 * Primary category of a post (Yoast SEO / Rank Math aware).
 *
 * @param int $post_id Post ID.
 * @return WP_Term|null
 */
function storebox_core_primary_category( $post_id ) {
	if ( function_exists( 'storebox_primary_category' ) ) {
		return storebox_primary_category( $post_id );
	}

	$categories = get_the_category( $post_id );

	return $categories ? $categories[0] : null;
}

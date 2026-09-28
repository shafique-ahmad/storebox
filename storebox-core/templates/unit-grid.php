<?php
/**
 * Unit grid / rail with optional filter bar.
 *
 * Filters work without JavaScript (GET parameters); the script filters and
 * sorts instantly and keeps the URL in sync.
 *
 * Override: copy to {theme}/storebox-core/unit-grid.php.
 *
 * @package Storebox_Core
 *
 * @var array $args Component arguments.
 */

use Storebox_Core\Components;

defined( 'ABSPATH' ) || exit;

$style  = $args['style'];
$layout = 'rail' === $args['layout'] ? 'rail' : 'grid';
$uid    = Components::uid( 'sb-units' );
$labels = wp_parse_args(
	(array) $args['labels'],
	array(
		'size'          => __( 'Size', 'storebox-core' ),
		'location'      => __( 'Location', 'storebox-core' ),
		'type'          => __( 'Type', 'storebox-core' ),
		'sort'          => __( 'Sort by', 'storebox-core' ),
		'available'     => __( 'Available now', 'storebox-core' ),
		'any_size'      => __( 'Any size', 'storebox-core' ),
		'all_locations' => __( 'All locations', 'storebox-core' ),
		'any_type'      => __( 'Any type', 'storebox-core' ),
		/* translators: %s: number of units shown. */
		'results'       => __( 'Showing %s units', 'storebox-core' ),
		'reset'         => __( 'Clear filters', 'storebox-core' ),
		'empty_title'   => __( 'Nothing matches those filters.', 'storebox-core' ),
		'empty_text'    => __( 'Try another location, or call us — we often have units coming free that are not listed yet.', 'storebox-core' ),
		'filter_label'  => __( 'Filter units', 'storebox-core' ),
		'prev'          => __( 'Previous units', 'storebox-core' ),
		'next'          => __( 'More units', 'storebox-core' ),
	)
);

$sort_options = array(
	'area-asc'   => __( 'Size: small to large', 'storebox-core' ),
	'area-desc'  => __( 'Size: large to small', 'storebox-core' ),
	'price-asc'  => __( 'Price: low to high', 'storebox-core' ),
	'price-desc' => __( 'Price: high to low', 'storebox-core' ),
);

$show_filters = $args['filters'] && 'grid' === $layout;

// Current filter values (public, read-only query parameters).
// phpcs:disable WordPress.Security.NonceVerification.Recommended
$current = array(
	'size'      => isset( $_GET['unit_size'] ) ? sanitize_title( wp_unslash( $_GET['unit_size'] ) ) : '',
	'location'  => isset( $_GET['unit_location'] ) ? sanitize_title( wp_unslash( $_GET['unit_location'] ) ) : '',
	'type'      => isset( $_GET['unit_type'] ) ? sanitize_title( wp_unslash( $_GET['unit_type'] ) ) : '',
	'sort'      => isset( $_GET['unit_sort'] ) ? sanitize_key( wp_unslash( $_GET['unit_sort'] ) ) : 'area-asc',
	'available' => ! empty( $_GET['unit_available'] ),
);
// phpcs:enable
if ( ! $show_filters || ! isset( $sort_options[ $current['sort'] ] ) ) {
	$current = array_merge( $current, array( 'size' => '', 'location' => '', 'type' => '', 'available' => false ) );
	$current['sort'] = 'area-asc';
}

$query_args = array(
	'source'    => $args['source'],
	'ids'       => $args['ids'],
	'location'  => $args['location'],
	'unit'      => $args['unit'],
	'type'      => $args['type'],
	'size'      => $args['size'],
	'count'     => $args['count'],
	'orderby'   => $args['orderby'],
	'order'     => $args['order'],
	'hide_full' => $args['hide_full'],
);

if ( $show_filters ) {
	list( $sort_key, $sort_dir ) = explode( '-', $current['sort'] );
	$query_args['orderby']       = $sort_key;
	$query_args['order']         = strtoupper( $sort_dir );
}

$unit_ids = storebox_core_query_units( $query_args );
$units    = array_filter( array_map( 'storebox_core_get_unit', $unit_ids ) );

if ( ! $units ) {
	if ( ! empty( $args['empty_message'] ) ) {
		echo '<p class="sb-c sb-unit-grid__none">' . esc_html( $args['empty_message'] ) . '</p>';
	}
	return;
}

// Which cards match the current (server-side) filters.
$visible = 0;
foreach ( $units as $index => $unit ) {
	$match = ( ! $current['size'] || ( $unit['size'] && $unit['size']['slug'] === $current['size'] ) )
		&& ( ! $current['location'] || ( $unit['location'] && get_post_field( 'post_name', $unit['location']['id'] ) === $current['location'] ) )
		&& ( ! $current['type'] || ( $unit['type'] && $unit['type']['slug'] === $current['type'] ) )
		&& ( ! $current['available'] || $unit['available'] > 0 );

	$units[ $index ]['_hidden'] = ! $match;
	if ( $match ) {
		++$visible;
	}
}

$card_args = array_intersect_key(
	$args,
	array_flip( array( 'style', 'image_size', 'show_image', 'show_status', 'subline', 'show_name', 'description', 'bullets', 'bullet_limit', 'bullet_location', 'show_price', 'cta_style', 'cta_text', 'waitlist_text', 'full_label' ) )
);

$columns = max( 1, min( 4, absint( $args['columns'] ) ) );
$classes = array( 'sb-c', 'sb-unit-grid', 'sb-s-' . $style, 'sb-unit-grid--' . $layout, 'sb-cols-' . $columns );
if ( ! empty( $args['filters_overlap'] ) && $show_filters ) {
	$classes[] = 'sb-unit-grid--overlap';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-sb-component="unit-grid"<?php echo 'rail' === $layout ? ' data-autoplay="' . absint( $args['autoplay'] ) . '"' : ''; ?> style="--sb-cols: <?php echo esc_attr( $columns ); ?>">

	<?php if ( $args['header'] || ( 'rail' === $layout && $args['show_arrows'] ) ) : ?>
		<div class="sb-rail-head<?php echo $args['header'] ? '' : ' sb-rail-head--arrows-only'; ?>">
			<?php if ( $args['header'] ) : ?>
				<div class="sb-rail-head__text">
					<?php if ( $args['header_eyebrow'] ) : ?>
						<span class="<?php echo 'editorial' === $style ? 'sb-kicker' : 'sb-eyebrow sb-eyebrow--dark'; ?>"><?php echo esc_html( $args['header_eyebrow'] ); ?></span>
					<?php endif; ?>
					<?php if ( $args['header_title'] ) : ?>
						<?php $tag = in_array( $args['header_tag'], array( 'h1', 'h2', 'h3', 'h4', 'div', 'p' ), true ) ? $args['header_tag'] : 'h2'; ?>
						<<?php echo esc_html( $tag ); ?> class="sb-rail-head__title"><?php echo esc_html( $args['header_title'] ); ?></<?php echo esc_html( $tag ); ?>>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="sb-rail-head__actions">
				<?php if ( 'rail' === $layout && $args['show_arrows'] ) : ?>
					<div class="sb-rail-nav">
						<button type="button" class="sb-rail-btn" data-sb-rail-prev aria-label="<?php echo esc_attr( $labels['prev'] ); ?>" aria-controls="<?php echo esc_attr( $uid ); ?>-items"><?php echo Components::icon( 'arrow-left', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></button>
						<button type="button" class="sb-rail-btn" data-sb-rail-next aria-label="<?php echo esc_attr( $labels['next'] ); ?>" aria-controls="<?php echo esc_attr( $uid ); ?>-items"><?php echo Components::icon( 'arrow-right', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></button>
					</div>
				<?php endif; ?>
				<?php if ( $args['header'] && $args['header_button_text'] && $args['header_button_url'] ) : ?>
					<a class="sb-btn <?php echo 'editorial' === $style ? 'sb-btn--outline' : 'sb-btn--dark'; ?>" href="<?php echo esc_url( $args['header_button_url'] ); ?>"><?php echo esc_html( $args['header_button_text'] ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( $show_filters ) : ?>
		<form class="sb-filters" method="get" action="" role="search" aria-label="<?php echo esc_attr( $labels['filter_label'] ); ?>" data-sb-filters>
			<?php if ( $args['filter_size'] ) : ?>
				<?php
				$size_terms = storebox_core_get_terms_ordered( 'sb_unit_size', true );
				?>
				<div class="sb-fgroup">
					<label for="<?php echo esc_attr( $uid ); ?>-size"><?php echo esc_html( $labels['size'] ); ?></label>
					<select id="<?php echo esc_attr( $uid ); ?>-size" name="unit_size" data-sb-filter="size">
						<option value=""><?php echo esc_html( $labels['any_size'] ); ?></option>
						<?php foreach ( $size_terms as $term ) : ?>
							<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $current['size'], $term->slug ); ?>><?php echo esc_html( $term->description ? $term->name . ' — ' . $term->description : $term->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>

			<?php if ( $args['filter_location'] ) : ?>
				<?php
				$locations = get_posts(
					array(
						'post_type'      => 'sb_location',
						'posts_per_page' => 50,
						'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
						'no_found_rows'  => true,
					)
				);
				?>
				<div class="sb-fgroup">
					<label for="<?php echo esc_attr( $uid ); ?>-loc"><?php echo esc_html( $labels['location'] ); ?></label>
					<select id="<?php echo esc_attr( $uid ); ?>-loc" name="unit_location" data-sb-filter="loc">
						<option value=""><?php echo esc_html( $labels['all_locations'] ); ?></option>
						<?php foreach ( $locations as $location ) : ?>
							<option value="<?php echo esc_attr( $location->post_name ); ?>" <?php selected( $current['location'], $location->post_name ); ?>><?php echo esc_html( get_the_title( $location ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>

			<?php if ( $args['filter_type'] ) : ?>
				<?php $type_terms = storebox_core_get_terms_ordered( 'sb_unit_type', true ); ?>
				<div class="sb-fgroup">
					<label for="<?php echo esc_attr( $uid ); ?>-type"><?php echo esc_html( $labels['type'] ); ?></label>
					<select id="<?php echo esc_attr( $uid ); ?>-type" name="unit_type" data-sb-filter="type">
						<option value=""><?php echo esc_html( $labels['any_type'] ); ?></option>
						<?php foreach ( $type_terms as $term ) : ?>
							<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $current['type'], $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>

			<?php if ( $args['filter_sort'] ) : ?>
				<div class="sb-fgroup">
					<label for="<?php echo esc_attr( $uid ); ?>-sort"><?php echo esc_html( $labels['sort'] ); ?></label>
					<select id="<?php echo esc_attr( $uid ); ?>-sort" name="unit_sort" data-sb-filter="sort">
						<?php foreach ( $sort_options as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current['sort'], $value ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>

			<?php if ( $args['filter_available'] ) : ?>
				<label class="sb-fcheck"><input type="checkbox" name="unit_available" value="1" data-sb-filter="avail" <?php checked( $current['available'] ); ?>> <?php echo esc_html( $labels['available'] ); ?></label>
			<?php endif; ?>

			<noscript><button type="submit" class="sb-btn sb-btn--dark"><?php esc_html_e( 'Apply', 'storebox-core' ); ?></button></noscript>
		</form>

		<div class="sb-results">
			<p aria-live="polite">
				<?php
				printf(
					esc_html( $labels['results'] ),
					'<b data-sb-count>' . esc_html( number_format_i18n( $visible ) ) . '</b>'
				);
				?>
			</p>
			<button type="button" class="sb-btn-reset" data-sb-reset><?php echo esc_html( $labels['reset'] ); ?></button>
		</div>
	<?php endif; ?>

	<div class="<?php echo 'rail' === $layout ? 'sb-rail' : 'sb-unit-grid__items'; ?>" id="<?php echo esc_attr( $uid ); ?>-items" data-sb-items<?php echo 'rail' === $layout ? ' tabindex="0" aria-label="' . esc_attr__( 'Units', 'storebox-core' ) . '"' : ''; ?>>
		<?php
		foreach ( $units as $unit ) {
			storebox_core_component(
				'unit-card',
				array_merge(
					$card_args,
					array(
						'unit'   => $unit,
						'hidden' => $unit['_hidden'],
					)
				)
			);
		}
		?>
	</div>

	<?php if ( $show_filters ) : ?>
		<div class="sb-no-results" data-sb-empty<?php echo $visible ? ' hidden' : ''; ?>>
			<h3><?php echo esc_html( $labels['empty_title'] ); ?></h3>
			<p><?php echo esc_html( $labels['empty_text'] ); ?></p>
			<p><button type="button" class="sb-btn sb-btn--dark" data-sb-reset><?php echo esc_html( $labels['reset'] ); ?></button></p>
		</div>
	<?php endif; ?>

	<?php if ( 'rail' === $layout && ( $args['show_progress'] || $args['hint'] ) ) : ?>
		<div class="sb-rail-foot">
			<?php if ( $args['hint'] ) : ?>
				<span class="sb-rail-hint"><?php echo esc_html( $args['hint'] ); ?></span>
			<?php endif; ?>
			<?php if ( $args['show_progress'] ) : ?>
				<span class="sb-rail-bar" aria-hidden="true"><i data-sb-rail-bar></i></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>

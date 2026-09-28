<?php
/**
 * Locations as overlay cards, info cards or rows.
 *
 * @package Storebox_Core
 *
 * @var array $args Component arguments.
 */

use Storebox_Core\Components;

defined( 'ABSPATH' ) || exit;

$style = $args['style'];
$skin  = Components::auto( $args['skin'], $style, 'card', 'row' );
$skin  = in_array( $skin, array( 'overlay', 'card', 'row' ), true ) ? $skin : 'card';

$query = array(
	'post_type'      => 'sb_location',
	'post_status'    => 'publish',
	'posts_per_page' => $args['count'] ? absint( $args['count'] ) : 50,
	'orderby'        => array(
		'menu_order' => 'ASC',
		'title'      => 'ASC',
	),
	'no_found_rows'  => true,
);

$ids = storebox_core_parse_ids( $args['ids'] );
if ( $ids ) {
	$query['post__in'] = $ids;
	$query['orderby']  = 'post__in';
}

$posts = get_posts( $query );
if ( ! $posts ) {
	return;
}

$heading = in_array( $args['heading_tag'], array( 'h2', 'h3', 'h4', 'div' ), true ) ? $args['heading_tag'] : 'h3';
$link    = $args['link_text'] ? $args['link_text'] : __( 'View facility', 'storebox-core' );
$columns = max( 1, min( 4, absint( $args['columns'] ) ) );

/**
 * Chips for a location: access, first tags, units free.
 *
 * @param array $location Location data.
 * @param array $args     Component arguments.
 * @return string[]
 */
$chips_for = static function ( $location, $args ) {
	$chips = array();
	if ( $args['show_access'] && $location['access_chip'] ) {
		$chips[] = $location['access_chip'];
	}
	$limit = absint( $args['tag_limit'] );
	$chips = array_merge( $chips, $limit ? array_slice( $location['tags'], 0, $limit ) : $location['tags'] );
	if ( $args['show_free'] && $location['units_total'] ) {
		$chips[] = $location['free_label'];
	}
	return $chips;
};
?>
<div class="sb-c sb-locs sb-locs--<?php echo esc_attr( $skin ); ?> sb-s-<?php echo esc_attr( $style ); ?>" style="--sb-cols: <?php echo esc_attr( $columns ); ?>">
	<?php foreach ( $posts as $post_item ) : ?>
		<?php
		$location = storebox_core_get_location( $post_item );
		$address  = 'full' === $args['address'] ? $location['address_inline'] : $location['address_short'];
		$chips    = $chips_for( $location, $args );
		$image_id = $location['image_id'] ? $location['image_id'] : ( $location['gallery'] ? $location['gallery'][0] : 0 );
		?>
		<?php if ( 'overlay' === $skin ) : ?>
			<a class="sb-loc sb-loc--overlay" href="<?php echo esc_url( $location['url'] ); ?>">
				<?php
				if ( $image_id ) {
					echo wp_get_attachment_image(
						$image_id,
						'storebox-card',
						false,
						array(
							'alt'     => '',
							'loading' => 'lazy',
							'sizes'   => '(max-width: 680px) 100vw, (max-width: 1000px) 50vw, 400px',
						)
					);
				}
				?>
				<div class="sb-loc__in">
					<?php if ( $location['area_name'] ) : ?>
						<span class="sb-loc__city"><?php echo esc_html( $location['area_name'] ); ?></span>
					<?php endif; ?>
					<<?php echo esc_html( $heading ); ?> class="sb-loc__name"><?php echo esc_html( $location['name'] ); ?></<?php echo esc_html( $heading ); ?>>
					<?php if ( $address ) : ?>
						<span class="sb-loc__address"><?php echo esc_html( $address ); ?></span>
					<?php endif; ?>
					<?php if ( $chips ) : ?>
						<div class="sb-loc__chips">
							<?php foreach ( $chips as $chip ) : ?>
								<span><?php echo esc_html( $chip ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</a>
		<?php elseif ( 'card' === $skin ) : ?>
			<a class="sb-loc sb-loc--card" href="<?php echo esc_url( $location['url'] ); ?>">
				<span class="sb-loc__img">
					<?php
					if ( $image_id ) {
						echo wp_get_attachment_image(
							$image_id,
							'storebox-card',
							false,
							array(
								'alt'     => '',
								'loading' => 'lazy',
								'sizes'   => '(max-width: 680px) 100vw, (max-width: 1000px) 50vw, 400px',
							)
						);
					}
					?>
				</span>
				<div class="sb-loc__body">
					<?php if ( $location['area_name'] ) : ?>
						<span class="sb-loc__city"><?php echo esc_html( $location['area_name'] ); ?></span>
					<?php endif; ?>
					<<?php echo esc_html( $heading ); ?> class="sb-loc__name"><?php echo esc_html( $location['name'] ); ?></<?php echo esc_html( $heading ); ?>>
					<?php if ( $args['show_excerpt'] && $location['excerpt'] ) : ?>
						<span class="sb-loc__excerpt"><?php echo esc_html( $location['excerpt'] ); ?></span>
					<?php endif; ?>
					<?php if ( $args['show_meta'] ) : ?>
						<div class="sb-loc__meta">
							<?php if ( $location['address_inline'] ) : ?>
								<span><?php echo Components::icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?> <?php echo esc_html( $location['address_inline'] ); ?></span>
							<?php endif; ?>
							<?php if ( $location['access'] ) : ?>
								<span><?php echo Components::icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?> <?php echo esc_html( sprintf( /* translators: %s: access hours. */ __( 'Access %s', 'storebox-core' ), $location['access'] ) ); ?></span>
							<?php endif; ?>
							<?php if ( $location['units_meta'] ) : ?>
								<span><?php echo Components::icon( 'box' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?> <?php echo esc_html( $location['units_meta'] ); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<span class="sb-loc__more"><span><?php echo esc_html( $link ); ?></span> <?php echo Components::icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></span>
				</div>
			</a>
		<?php else : ?>
			<a class="sb-loc sb-loc--row" href="<?php echo esc_url( $location['url'] ); ?>">
				<span class="sb-loc__thumb">
					<?php
					if ( $image_id ) {
						echo wp_get_attachment_image(
							$image_id,
							'storebox-thumb',
							false,
							array(
								'alt'     => '',
								'loading' => 'lazy',
								'sizes'   => '108px',
							)
						);
					}
					?>
				</span>
				<div class="sb-loc__text">
					<<?php echo esc_html( $heading ); ?> class="sb-loc__name"><?php echo esc_html( $location['name'] ); ?></<?php echo esc_html( $heading ); ?>>
					<?php if ( $address ) : ?>
						<span class="sb-loc__address"><?php echo esc_html( $address ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( $chips ) : ?>
					<div class="sb-loc__tags">
						<?php foreach ( $chips as $chip ) : ?>
							<span><?php echo esc_html( $chip ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<span class="sb-loc__go" aria-hidden="true"><?php echo Components::icon( 'arrow-right', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></span>
			</a>
		<?php endif; ?>
	<?php endforeach; ?>
</div>

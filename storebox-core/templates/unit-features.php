<?php
/**
 * Unit features checklist. Features the unit lacks are shown as "no" (optional).
 *
 * @package Storebox_Core
 *
 * @var array $args Component arguments.
 */

defined( 'ABSPATH' ) || exit;

$unit = storebox_core_get_unit( $args['unit'] ? $args['unit'] : get_the_ID() );
if ( ! $unit || ! $unit['features'] ) {
	return;
}

$features = array_filter(
	$unit['features'],
	static function ( $feature ) use ( $args ) {
		return $feature['has'] || $args['show_missing'];
	}
);

if ( ! $features ) {
	return;
}

$editorial = 'editorial' === $args['style'];
?>
<ul class="sb-c sb-checks sb-s-<?php echo esc_attr( $args['style'] ); ?>">
	<?php foreach ( $features as $feature ) : ?>
		<li class="<?php echo $feature['has'] ? 'is-yes' : 'is-no'; ?>">
			<span class="sb-checks__name"><?php echo esc_html( $feature['name'] ); ?></span>
			<?php if ( $editorial ) : ?>
				<span class="sb-checks__state"><?php echo $feature['has'] ? esc_html__( 'Yes', 'storebox-core' ) : esc_html__( 'No', 'storebox-core' ); ?></span>
			<?php else : ?>
				<span class="screen-reader-text"><?php echo $feature['has'] ? esc_html__( '(included)', 'storebox-core' ) : esc_html__( '(not available)', 'storebox-core' ); ?></span>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>

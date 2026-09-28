<?php
/**
 * Booking panel: price, status, key facts and the reserve / waitlist button.
 *
 * @package Storebox_Core
 *
 * @var array $args Component arguments.
 */

defined( 'ABSPATH' ) || exit;

$unit = storebox_core_get_unit( $args['unit'] ? $args['unit'] : get_the_ID() );
if ( ! $unit ) {
	return;
}

$is_full = 'full' === $unit['status'];
$labels  = wp_parse_args(
	(array) $args['labels'],
	array(
		'location' => __( 'Location', 'storebox-core' ),
		'access'   => __( 'Access', 'storebox-core' ),
	)
);

// Extra rows: widget rows, or the "Booking panel rows" setting ("Label | Value" per line).
$rows = $args['rows'];
if ( null === $rows ) {
	$rows    = array();
	$setting = (string) storebox_core_setting( 'booking_rows' );
	$lines   = $setting ? storebox_core_lines( $setting ) : array(
		__( 'Deposit', 'storebox-core' ) . ' | ' . __( 'None', 'storebox-core' ),
		__( 'Minimum term', 'storebox-core' ) . ' | ' . __( 'None', 'storebox-core' ),
	);
	foreach ( $lines as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( 2 === count( $parts ) && '' !== $parts[0] ) {
			$rows[] = array(
				'label' => $parts[0],
				'value' => $parts[1],
			);
		}
	}
}

$note = null === $args['note'] ? storebox_core_setting( 'booking_note' ) : $args['note'];
$note = null === $args['note'] && '' === (string) $note ? __( 'Held free for 7 days · cancel any time', 'storebox-core' ) : $note;

if ( $is_full ) {
	$button_text = $args['waitlist_text'] ? $args['waitlist_text'] : __( 'Join the waitlist', 'storebox-core' );
	$button_url  = $args['waitlist_url'] ? $args['waitlist_url'] : '#reserve';
} else {
	$button_text = $args['button_text'] ? $args['button_text'] : __( 'Reserve this unit', 'storebox-core' );
	$button_url  = $args['button_url'] ? $args['button_url'] : '#reserve';
}

$help_phone = $args['help_phone'] ? $args['help_phone'] : storebox_core_setting( 'help_phone' );
$help_phone = apply_filters( 'storebox_core/default_phone', $help_phone );
$help_title = null === $args['help_title'] ? ( storebox_core_setting( 'help_title' ) ? storebox_core_setting( 'help_title' ) : __( 'Rather talk it through?', 'storebox-core' ) ) : $args['help_title'];
$help_text  = null === $args['help_text'] ? ( storebox_core_setting( 'help_text' ) ? storebox_core_setting( 'help_text' ) : __( 'A person answers, usually within three rings.', 'storebox-core' ) ) : $args['help_text'];
?>
<div class="sb-c sb-booking sb-s-<?php echo esc_attr( $args['style'] ); ?>">
	<div class="sb-book">
		<div class="sb-book__price">
			<b><?php echo esc_html( $unit['price_label'] ); ?></b>
			<span><?php echo esc_html_x( '/ month', 'price period', 'storebox-core' ); ?></span>
		</div>
		<?php if ( $args['show_weekly'] ) : ?>
			<p class="sb-book__alt"><?php echo esc_html( $unit['weekly_label'] ); ?></p>
		<?php endif; ?>

		<?php if ( $args['show_status'] ) : ?>
			<span class="sb-tag sb-tag--inline sb-tag--<?php echo esc_attr( $unit['status'] ); ?>"><i></i><?php echo esc_html( $unit['status_label'] ); ?></span>
		<?php endif; ?>

		<ul class="sb-book__rows">
			<?php if ( $args['show_location'] && $unit['location'] ) : ?>
				<li><span><?php echo esc_html( $labels['location'] ); ?></span><a href="<?php echo esc_url( $unit['location']['url'] ); ?>"><?php echo esc_html( $unit['location']['name'] ); ?></a></li>
			<?php endif; ?>
			<?php if ( $args['show_access'] && $unit['location'] && $unit['location']['access'] ) : ?>
				<li><span><?php echo esc_html( $labels['access'] ); ?></span><b><?php echo esc_html( $unit['location']['access'] ); ?></b></li>
			<?php endif; ?>
			<?php foreach ( (array) $rows as $row ) : ?>
				<?php
				if ( empty( $row['label'] ) ) {
					continue;
				}
				?>
				<li><span><?php echo esc_html( $row['label'] ); ?></span><b><?php echo esc_html( isset( $row['value'] ) ? $row['value'] : '' ); ?></b></li>
			<?php endforeach; ?>
		</ul>

		<a class="sb-btn sb-btn--block <?php echo $is_full ? 'sb-btn--dark' : 'sb-btn--primary'; ?>" href="<?php echo esc_url( $button_url ); ?>"><?php echo esc_html( $button_text ); ?></a>

		<?php if ( $note ) : ?>
			<p class="sb-book__note"><?php echo esc_html( $note ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( $args['help'] && $help_phone ) : ?>
		<div class="sb-help">
			<?php if ( $help_title ) : ?>
				<h3 class="sb-help__title"><?php echo esc_html( $help_title ); ?></h3>
			<?php endif; ?>
			<?php if ( $help_text ) : ?>
				<p><?php echo esc_html( $help_text ); ?></p>
			<?php endif; ?>
			<a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $help_phone ), array( 'tel' ) ); ?>"><?php echo esc_html( $help_phone ); ?></a>
		</div>
	<?php endif; ?>
</div>

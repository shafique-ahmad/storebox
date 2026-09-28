<?php
/**
 * Office hours table; today's row is highlighted by hours.js in the site's time zone.
 *
 * @package Storebox_Core
 *
 * @var array $args Component arguments.
 */

defined( 'ABSPATH' ) || exit;

$location = storebox_core_get_location( $args['location'] ? $args['location'] : get_the_ID() );
if ( ! $location ) {
	return;
}

$rows = array_filter(
	$location['hours'],
	static function ( $row ) {
		return '' !== $row['value'];
	}
);

if ( ! $rows ) {
	return;
}

$title = null === $args['title'] ? __( 'Office hours', 'storebox-core' ) : $args['title'];

$note = $args['note'];
if ( null === $note && $location['access'] ) {
	/* translators: %s: access hours, e.g. 24/7. */
	$note = sprintf( __( 'Access to your unit is %s regardless.', 'storebox-core' ), $location['access'] );
}
?>
<div class="sb-c sb-hours sb-s-<?php echo esc_attr( $args['style'] ); ?>" data-sb-component="hours"<?php echo $args['highlight_today'] ? ' data-today="1"' : ''; ?>>
	<?php if ( $title ) : ?>
		<h3 class="sb-hours__title"><?php echo esc_html( $title ); ?></h3>
	<?php endif; ?>
	<?php if ( $note ) : ?>
		<p class="sb-hours__note"><?php echo esc_html( $note ); ?></p>
	<?php endif; ?>
	<table class="sb-hours__table">
		<?php if ( $title ) : ?>
			<caption class="screen-reader-text"><?php echo esc_html( $title . ' — ' . $location['name'] ); ?></caption>
		<?php endif; ?>
		<tbody>
			<?php foreach ( $rows as $row ) : ?>
				<tr data-day="<?php echo esc_attr( $row['day'] ); ?>">
					<th scope="row"><?php echo esc_html( $row['label'] ); ?></th>
					<td><?php echo esc_html( $row['value'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

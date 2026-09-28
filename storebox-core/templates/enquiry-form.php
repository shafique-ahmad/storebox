<?php
/**
 * Enquiry form: reservation, waitlist or contact request.
 *
 * Posts to admin-post.php (works without JavaScript); the script submits it
 * with fetch and shows the result in place.
 *
 * @package Storebox_Core
 *
 * @var array $args Component arguments.
 */

use Storebox_Core\Components;
use Storebox_Core\Enquiries;

defined( 'ABSPATH' ) || exit;

$unit_id = absint( $args['unit'] );
if ( ! $unit_id && is_singular( 'sb_unit' ) && in_array( $args['type'], array( 'auto', 'reservation', 'waitlist' ), true ) ) {
	$unit_id = get_queried_object_id();
}
$unit = $unit_id ? storebox_core_get_unit( $unit_id ) : null;

$type = $args['type'];
if ( 'auto' === $type ) {
	$type = $unit ? ( 'full' === $unit['status'] ? 'waitlist' : 'reservation' ) : 'contact';
}
if ( ! in_array( $type, array( 'reservation', 'waitlist', 'contact' ), true ) ) {
	$type = 'contact';
}

$location_id = absint( $args['location'] );
if ( ! $location_id && $unit && $unit['location'] ) {
	$location_id = $unit['location']['id'];
}

$defaults = array(
	'reservation' => array(
		'title'  => __( 'Reserve this unit', 'storebox-core' ),
		'intro'  => __( 'We will confirm by email within the hour during office hours.', 'storebox-core' ),
		'button' => __( 'Reserve this unit', 'storebox-core' ),
	),
	'waitlist'    => array(
		'title'  => __( 'Join the waitlist', 'storebox-core' ),
		'intro'  => __( 'We will email you as soon as a unit this size comes free.', 'storebox-core' ),
		'button' => __( 'Request to join the waitlist', 'storebox-core' ),
	),
	'contact'     => array(
		'title'  => __( 'Send us a message', 'storebox-core' ),
		'intro'  => __( 'Tell us roughly what you are storing and when — we will come back with a size and a price.', 'storebox-core' ),
		'button' => __( 'Send message', 'storebox-core' ),
	),
);

$title   = null === $args['title'] ? $defaults[ $type ]['title'] : $args['title'];
$intro   = null === $args['intro'] ? $defaults[ $type ]['intro'] : $args['intro'];
$button  = null === $args['button_text'] ? $defaults[ $type ]['button'] : $args['button_text'];
$success = null === $args['success_text'] ? __( 'Thanks — we will be in touch shortly.', 'storebox-core' ) : $args['success_text'];
$note    = $args['note'];
if ( null === $note ) {
	$note = storebox_core_setting( 'privacy_note' );
	$note = $note ? $note : __( 'We only use your details to answer this enquiry.', 'storebox-core' );
}

$show_date     = 'auto' === $args['show_date'] ? 'contact' !== $type : (bool) $args['show_date'];
$show_location = 'auto' === $args['show_location'] ? 'contact' === $type : (bool) $args['show_location'];

$labels = wp_parse_args(
	(array) $args['labels'],
	array(
		'name'     => __( 'Name', 'storebox-core' ),
		'email'    => __( 'Email', 'storebox-core' ),
		'phone'    => __( 'Phone', 'storebox-core' ),
		'date'     => __( 'Move-in date', 'storebox-core' ),
		'location' => __( 'Preferred location', 'storebox-core' ),
		'any'      => __( 'No preference', 'storebox-core' ),
		'message'  => 'contact' === $type ? __( 'Message', 'storebox-core' ) : __( 'Anything else?', 'storebox-core' ),
		'honeypot' => __( 'Leave this field empty', 'storebox-core' ),
	)
);

$form_id = $args['form_id'] ? sanitize_html_class( $args['form_id'] ) : Components::uid( 'sb-enquiry' );
$uid     = $form_id . '-f';

// Sent state after a no-JavaScript submission.
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Display-only flags.
$sent  = isset( $_GET['sb_enquiry'], $_GET['sb_form'] ) && 'sent' === $_GET['sb_enquiry'] && sanitize_html_class( wp_unslash( $_GET['sb_form'] ) ) === $form_id;
$error = isset( $_GET['sb_enquiry'], $_GET['sb_form'] ) && 'error' === $_GET['sb_enquiry'] && sanitize_html_class( wp_unslash( $_GET['sb_form'] ) ) === $form_id;
// phpcs:enable

$locations = array();
if ( $show_location ) {
	$locations = get_posts(
		array(
			'post_type'      => 'sb_location',
			'posts_per_page' => 50,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'no_found_rows'  => true,
		)
	);
}

$classes = array( 'sb-c', 'sb-enquiry', 'sb-s-' . $args['style'] );
if ( $args['card'] ) {
	$classes[] = 'sb-enquiry--card';
}
if ( $sent ) {
	$classes[] = 'is-sent';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" id="<?php echo esc_attr( $form_id ); ?>">
	<form class="sb-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-sb-component="enquiry">
		<?php if ( $title ) : ?>
			<?php $title_tag = in_array( $args['title_tag'], array( 'h2', 'h3', 'h4', 'p' ), true ) ? $args['title_tag'] : 'h3'; ?>
			<<?php echo esc_attr( $title_tag ); ?> class="sb-form__title"><?php echo esc_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
		<?php endif; ?>
		<?php if ( $intro ) : ?>
			<p class="sb-form__intro"><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>

		<div class="sb-form__grid">
			<div class="sb-field">
				<label for="<?php echo esc_attr( $uid ); ?>-name"><?php echo esc_html( $labels['name'] ); ?> <span class="sb-field__req" aria-hidden="true">*</span></label>
				<input id="<?php echo esc_attr( $uid ); ?>-name" type="text" name="sb_name" autocomplete="name" required maxlength="120">
			</div>
			<div class="sb-field">
				<label for="<?php echo esc_attr( $uid ); ?>-email"><?php echo esc_html( $labels['email'] ); ?> <span class="sb-field__req" aria-hidden="true">*</span></label>
				<input id="<?php echo esc_attr( $uid ); ?>-email" type="email" name="sb_email" autocomplete="email" required maxlength="160">
			</div>
			<?php if ( $args['show_phone'] ) : ?>
				<div class="sb-field">
					<label for="<?php echo esc_attr( $uid ); ?>-phone"><?php echo esc_html( $labels['phone'] ); ?></label>
					<input id="<?php echo esc_attr( $uid ); ?>-phone" type="tel" name="sb_phone" autocomplete="tel" maxlength="40">
				</div>
			<?php endif; ?>
			<?php if ( $show_date ) : ?>
				<div class="sb-field">
					<label for="<?php echo esc_attr( $uid ); ?>-date"><?php echo esc_html( $labels['date'] ); ?></label>
					<input id="<?php echo esc_attr( $uid ); ?>-date" type="date" name="sb_date" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>">
				</div>
			<?php endif; ?>
			<?php if ( $show_location && $locations ) : ?>
				<div class="sb-field">
					<label for="<?php echo esc_attr( $uid ); ?>-loc"><?php echo esc_html( $labels['location'] ); ?></label>
					<select id="<?php echo esc_attr( $uid ); ?>-loc" name="sb_location">
						<option value=""><?php echo esc_html( $labels['any'] ); ?></option>
						<?php foreach ( $locations as $location ) : ?>
							<option value="<?php echo esc_attr( $location->ID ); ?>" <?php selected( $location_id, $location->ID ); ?>><?php echo esc_html( get_the_title( $location ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>
			<div class="sb-field sb-field--wide">
				<label for="<?php echo esc_attr( $uid ); ?>-msg"><?php echo esc_html( $labels['message'] ); ?><?php echo 'contact' === $type ? ' <span class="sb-field__req" aria-hidden="true">*</span>' : ''; ?></label>
				<textarea id="<?php echo esc_attr( $uid ); ?>-msg" name="sb_message" rows="<?php echo 'contact' === $type ? 5 : 3; ?>" maxlength="4000"<?php echo 'contact' === $type ? ' required' : ''; ?>></textarea>
			</div>
		</div>

		<div class="sb-form__hp" aria-hidden="true">
			<label for="<?php echo esc_attr( $uid ); ?>-web"><?php echo esc_html( $labels['honeypot'] ); ?></label>
			<input id="<?php echo esc_attr( $uid ); ?>-web" type="text" name="sb_website" value="" tabindex="-1" autocomplete="off">
		</div>

		<input type="hidden" name="action" value="storebox_enquiry">
		<input type="hidden" name="sb_type" value="<?php echo esc_attr( $type ); ?>">
		<input type="hidden" name="sb_unit" value="<?php echo esc_attr( $unit ? $unit['id'] : 0 ); ?>">
		<?php if ( ! $show_location ) : ?>
			<input type="hidden" name="sb_location" value="<?php echo esc_attr( $location_id ); ?>">
		<?php endif; ?>
		<input type="hidden" name="sb_form" value="<?php echo esc_attr( $form_id ); ?>">
		<input type="hidden" name="sb_token" value="<?php echo esc_attr( Enquiries::time_token() ); ?>">
		<?php wp_nonce_field( Enquiries::NONCE, 'sb_nonce', true ); ?>

		<button class="sb-btn sb-btn--primary sb-btn--block sb-form__submit" type="submit"><?php echo esc_html( $button ); ?></button>

		<?php if ( $note ) : ?>
			<p class="sb-form__note"><?php echo esc_html( $note ); ?></p>
		<?php endif; ?>
		<p class="sb-form__ok" role="status" tabindex="-1"<?php echo $sent ? '' : ' hidden'; ?>><?php echo esc_html( $success ); ?></p>
		<p class="sb-form__error" role="alert"<?php echo $error ? '' : ' hidden'; ?>><?php echo $error ? esc_html__( 'Sorry, your message could not be sent. Please check the form and try again, or call us.', 'storebox-core' ) : ''; ?></p>
	</form>
</div>

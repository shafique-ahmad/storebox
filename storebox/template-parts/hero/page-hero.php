<?php
/**
 * Page header for theme templates.
 *
 * Soft preset: dark header with an optional background photo.
 * Editorial preset: light header with an optional image beside the title.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

$storebox_hero = wp_parse_args(
	isset( $args ) && is_array( $args ) ? $args : array(),
	array(
		'eyebrow'  => '',
		'title'    => '',
		'lede'     => '',
		'image_id' => 0,
		'short'    => false,
		'split'    => false,
	)
);

$storebox_allowed_title = array(
	'em'     => array(),
	'strong' => array(),
	'span'   => array( 'class' => true ),
	'br'     => array(),
);

$storebox_image_id = absint( $storebox_hero['image_id'] );

if ( 'editorial' === storebox_preset() ) :
	$storebox_split = $storebox_hero['split'] && $storebox_image_id;
	?>
	<header class="sb-ph<?php echo $storebox_split ? ' sb-ph--split' : ''; ?>">
		<div class="sb-wrap sb-ph__in">
			<div class="sb-ph__text">
				<?php storebox_breadcrumbs(); ?>
				<?php if ( $storebox_hero['eyebrow'] ) : ?>
					<span class="sb-kicker"><?php echo esc_html( $storebox_hero['eyebrow'] ); ?></span>
				<?php endif; ?>
				<h1 class="sb-ph__title"><?php echo wp_kses( $storebox_hero['title'], $storebox_allowed_title ); ?></h1>
				<?php if ( $storebox_hero['lede'] ) : ?>
					<p class="sb-lede"><?php echo wp_kses_post( $storebox_hero['lede'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $storebox_split ) : ?>
				<div class="sb-ph__img">
					<?php
					echo wp_get_attachment_image(
						$storebox_image_id,
						'storebox-wide',
						false,
						array(
							'alt'           => '',
							'loading'       => false,
							'fetchpriority' => 'high',
							'sizes'         => '(max-width: 1080px) 100vw, 560px',
						)
					);
					?>
				</div>
			<?php endif; ?>
		</div>
	</header>
	<?php
else :
	$storebox_show_bg = ! $storebox_hero['short'] && $storebox_image_id;
	?>
	<header class="sb-phero<?php echo $storebox_hero['short'] ? ' sb-phero--short' : ''; ?>">
		<?php if ( $storebox_show_bg ) : ?>
			<div class="sb-phero__bg">
				<?php
				echo wp_get_attachment_image(
					$storebox_image_id,
					'storebox-hero',
					false,
					array(
						'alt'           => '',
						'loading'       => false,
						'fetchpriority' => 'high',
						'sizes'         => '100vw',
					)
				);
				?>
			</div>
		<?php endif; ?>
		<div class="sb-wrap sb-phero__in">
			<?php storebox_breadcrumbs(); ?>
			<?php if ( $storebox_hero['eyebrow'] ) : ?>
				<span class="sb-eyebrow"><?php echo esc_html( $storebox_hero['eyebrow'] ); ?></span>
			<?php endif; ?>
			<h1 class="sb-phero__title"><?php echo wp_kses( $storebox_hero['title'], $storebox_allowed_title ); ?></h1>
			<?php if ( $storebox_hero['lede'] ) : ?>
				<p class="sb-lede"><?php echo wp_kses_post( $storebox_hero['lede'] ); ?></p>
			<?php endif; ?>
		</div>
	</header>
	<?php
endif;

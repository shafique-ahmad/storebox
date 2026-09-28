<?php
/**
 * Inline SVG icons used by theme templates.
 *
 * Line icons follow the stroke style of the Storebox design. Brand marks are
 * from Simple Icons (CC0 1.0).
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

/**
 * Icon definitions: inner SVG markup and whether the icon is stroked or filled.
 *
 * @return array<string, array{0: string, 1: string}>
 */
function storebox_icon_library() {
	$icons = array(
		'arrow-right' => array( 'stroke', '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>' ),
		'arrow-left'  => array( 'stroke', '<path d="M19 12H5"/><path d="m11 18-6-6 6-6"/>' ),
		'clock'       => array( 'stroke', '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>' ),
		'pin'         => array( 'stroke', '<path d="M20 10.5c0 5.6-8 12-8 12s-8-6.4-8-12a8 8 0 1 1 16 0z"/><circle cx="12" cy="10.3" r="2.6"/>' ),
		'lock'        => array( 'stroke', '<rect x="3" y="11" width="18" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>' ),
		'check'       => array( 'stroke', '<path d="M20 6 9 17l-5-5"/>' ),
		'phone'       => array( 'stroke', '<path d="M5 4h3.2l1.6 4.2-2.2 1.4a11 11 0 0 0 6.8 6.8l1.4-2.2L20 15.8V19a1 1 0 0 1-1 1A16 16 0 0 1 4 5a1 1 0 0 1 1-1z"/>' ),
		'mail'        => array( 'stroke', '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 6.5 8.5 6.5 8.5-6.5"/>' ),
		'message'     => array( 'stroke', '<path d="M4 5h16v11H9l-5 4z"/>' ),
		'box'         => array( 'stroke', '<path d="M3.5 7.5 12 3l8.5 4.5v9L12 21l-8.5-4.5z"/><path d="M3.5 7.5 12 12l8.5-4.5M12 12v9"/>' ),
		'search'      => array( 'stroke', '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>' ),
		'chevron'     => array( 'stroke', '<path d="m6 9 6 6 6-6"/>' ),
		'close'       => array( 'stroke', '<path d="M18 6 6 18M6 6l12 12"/>' ),
		'facebook'    => array( 'fill', '<path d="M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647Z"/>' ),
		'instagram'   => array( 'fill', '<path d="M7.03.084c-1.277.06-2.149.264-2.91.563a5.874 5.874 0 0 0-2.124 1.388 5.878 5.878 0 0 0-1.38 2.127C.321 4.926.12 5.8.064 7.076.008 8.354-.005 8.764.001 12.023c.007 3.259.021 3.667.083 4.947.061 1.277.264 2.149.563 2.911.308.789.72 1.457 1.388 2.123a5.872 5.872 0 0 0 2.129 1.38c.763.295 1.636.496 2.913.552 1.278.056 1.689.069 4.947.063 3.257-.007 3.668-.021 4.947-.082 1.28-.06 2.147-.265 2.91-.563a5.881 5.881 0 0 0 2.123-1.388 5.881 5.881 0 0 0 1.38-2.129c.295-.763.496-1.636.551-2.912.056-1.28.07-1.69.063-4.948-.006-3.258-.02-3.667-.081-4.947-.06-1.28-.264-2.148-.564-2.911a5.892 5.892 0 0 0-1.387-2.123 5.857 5.857 0 0 0-2.128-1.38C19.074.322 18.202.12 16.924.066 15.647.009 15.236-.006 11.977 0 8.718.008 8.31.021 7.03.084m.14 21.693c-1.17-.05-1.805-.245-2.228-.408a3.736 3.736 0 0 1-1.382-.895 3.695 3.695 0 0 1-.9-1.378c-.165-.423-.363-1.058-.417-2.228-.06-1.264-.072-1.644-.08-4.848-.006-3.204.006-3.583.062-4.848.05-1.169.246-1.805.408-2.228.216-.561.477-.96.895-1.382a3.705 3.705 0 0 1 1.379-.9c.423-.166 1.057-.361 2.227-.417 1.265-.06 1.644-.072 4.848-.08 3.203-.006 3.583.006 4.849.062 1.169.05 1.805.244 2.227.408.562.216.96.475 1.382.895.421.42.682.817.9 1.378.166.422.362 1.056.417 2.227.06 1.265.074 1.645.08 4.848.005 3.203-.006 3.583-.061 4.848-.051 1.17-.245 1.805-.408 2.23-.216.56-.477.96-.896 1.38a3.705 3.705 0 0 1-1.378.9c-.422.165-1.058.361-2.226.417-1.266.06-1.645.072-4.85.079-3.204.007-3.582-.006-4.848-.06m9.783-16.192a1.44 1.44 0 1 0 1.437-1.442 1.44 1.44 0 0 0-1.437 1.442M5.839 12.012a6.161 6.161 0 1 0 12.323-.024 6.162 6.162 0 0 0-12.323.024M8 12.008A4 4 0 1 1 12.008 16 4 4 0 0 1 8 12.008"/>' ),
		'linkedin'    => array( 'fill', '<circle cx="3.2" cy="3.4" r="2.6"/><rect x="0.7" y="8.3" width="5" height="15"/><path d="M9 8.3h4.8v2.05h.07c.67-1.2 2.3-2.47 4.73-2.47 5.06 0 6 3.1 6 7.14V23.3h-5v-6.8c0-1.62-.03-3.7-2.4-3.7-2.4 0-2.77 1.76-2.77 3.58v6.92H9z"/>' ),
		'x'           => array( 'fill', '<path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z"/>' ),
		'youtube'     => array( 'fill', '<path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>' ),
	);

	return apply_filters( 'storebox_icon_library', $icons );
}

/**
 * Returns an inline SVG icon. Decorative by default (aria-hidden).
 *
 * @param string $name Icon name.
 * @param int    $size Width and height in pixels.
 * @param array  $args Optional: 'class', 'stroke_width', 'label' (makes it non-decorative).
 * @return string SVG markup, or an empty string for unknown icons.
 */
function storebox_icon( $name, $size = 16, $args = array() ) {
	$icons = storebox_icon_library();

	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}

	list( $style, $paths ) = $icons[ $name ];

	$args = wp_parse_args(
		$args,
		array(
			'class'        => '',
			'stroke_width' => 2.2,
			'label'        => '',
		)
	);

	$paint = 'fill' === $style
		? 'fill="currentColor"'
		: sprintf( 'fill="none" stroke="currentColor" stroke-width="%s" stroke-linecap="round" stroke-linejoin="round"', esc_attr( $args['stroke_width'] ) );

	$a11y = $args['label']
		? sprintf( 'role="img" aria-label="%s"', esc_attr( $args['label'] ) )
		: 'aria-hidden="true" focusable="false"';

	return sprintf(
		'<svg class="sb-icon %1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" %3$s %4$s>%5$s</svg>',
		esc_attr( trim( 'sb-icon--' . $name . ' ' . $args['class'] ) ),
		absint( $size ),
		$paint,
		$a11y,
		$paths
	);
}

/**
 * Echoes an icon. The markup is built from the static library above.
 *
 * @param string $name Icon name.
 * @param int    $size Size in pixels.
 * @param array  $args See storebox_icon().
 */
function storebox_the_icon( $name, $size = 16, $args = array() ) {
	echo storebox_icon( $name, $size, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG from storebox_icon_library(); attributes escaped.
}

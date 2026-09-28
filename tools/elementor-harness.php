<?php
/**
 * Development harness: validates and renders the Storebox Elementor widgets
 * without Elementor, using minimal stand-ins for the Elementor classes.
 *
 * Run inside WordPress:  wp eval-file tools/elementor-harness.php [render]
 *
 * Checks, per widget: sections are opened and closed, controls live inside a
 * section, control IDs are unique, SELECT defaults exist in their options,
 * conditions point at existing controls, selectors use {{WRAPPER}} and known
 * placeholders, repeater title fields exist. With "render" it also renders
 * every widget with its default settings and reports PHP notices.
 *
 * Not shipped with the theme or plugin.
 *
 * @package Storebox
 */

namespace {

	require_once __DIR__ . '/elementor-stubs.php';

	\Elementor\Plugin::$instance = new \Elementor\Plugin();

	$storebox_render = in_array( 'render', isset( $args ) ? (array) $args : array(), true );

	// Admin context so option lists are built.
	if ( ! defined( 'WP_ADMIN' ) ) {
		set_current_screen( 'dashboard' );
	}

	require_once STOREBOX_CORE_DIR . 'includes/elementor/class-module.php';
	require_once STOREBOX_CORE_DIR . 'includes/elementor/class-widget-base.php';

	$storebox_placeholders = array( '{{WRAPPER}}', '{{VALUE}}', '{{SIZE}}', '{{UNIT}}', '{{TOP}}', '{{RIGHT}}', '{{BOTTOM}}', '{{LEFT}}' );
	$storebox_failed       = 0;
	$storebox_names        = array();

	set_error_handler(
		static function ( $errno, $errstr, $errfile, $errline ) {
			if ( false !== strpos( $errfile, 'storebox' ) ) {
				echo "  PHP notice: $errstr in " . basename( $errfile ) . ":$errline\n";
				$GLOBALS['storebox_failed']++;
			}
			return false !== strpos( $errfile, 'wp-includes' );
		}
	);

	foreach ( \Storebox_Core\Elementor\Module::WIDGETS as $file => $class ) {
		require_once STOREBOX_CORE_DIR . 'includes/elementor/widgets/class-' . $file . '.php';
		$fqcn   = '\\Storebox_Core\\Elementor\\Widgets\\' . $class;
		$widget = new $fqcn();
		$errors = $widget->errors;
		$ids    = array_keys( $widget->controls );

		$name = $widget->get_name();
		if ( isset( $storebox_names[ $name ] ) ) {
			$errors[] = "duplicate widget name $name";
		}
		$storebox_names[ $name ] = true;

		foreach ( array( 'get_title', 'get_icon' ) as $method ) {
			if ( '' === (string) $widget->$method() ) {
				$errors[] = "$method is empty";
			}
		}

		foreach ( $widget->controls as $id => $control ) {
			if ( in_array( $control['type'], array( 'select' ), true ) && isset( $control['default'] ) && empty( $control['responsive_of'] ) ) {
				$keys = array_map( 'strval', array_keys( (array) $control['options'] ) );
				if ( $keys && ! in_array( (string) $control['default'], $keys, true ) ) {
					$errors[] = "select '$id' default '{$control['default']}' not in options";
				}
			}
			foreach ( array( 'condition', 'conditions' ) as $key ) {
				if ( empty( $control[ $key ] ) ) {
					continue;
				}
				$terms = 'condition' === $key ? array_keys( $control[ $key ] ) : wp_list_pluck( $control[ $key ]['terms'], 'name' );
				foreach ( $terms as $term ) {
					$term = rtrim( $term, '!' );
					if ( ! in_array( $term, $ids, true ) ) {
						$errors[] = "control '$id' $key refers to unknown '$term'";
					}
				}
			}
			if ( ! empty( $control['selectors'] ) ) {
				foreach ( $control['selectors'] as $selector => $css ) {
					if ( false === strpos( $selector, '{{WRAPPER}}' ) ) {
						$errors[] = "control '$id' selector without {{WRAPPER}}: $selector";
					}
					foreach ( explode( ',', $selector ) as $part ) {
						if ( false === strpos( $part, '{{WRAPPER}}' ) ) {
							$errors[] = "control '$id' selector part without {{WRAPPER}}: $part";
						}
					}
					preg_match_all( '/\{\{[A-Z_]+\}\}/', $css . $selector, $found );
					foreach ( $found[0] as $placeholder ) {
						if ( ! in_array( $placeholder, $storebox_placeholders, true ) ) {
							$errors[] = "control '$id' unknown placeholder $placeholder";
						}
					}
				}
			}
			if ( 'repeater' === $control['type'] ) {
				if ( ! empty( $control['title_field'] ) && preg_match( '/\{\{\{\s*(\w+)\s*\}\}\}/', $control['title_field'], $match ) && ! isset( $control['fields'][ $match[1] ] ) ) {
					$errors[] = "repeater '$id' title field '{$match[1]}' is not a field";
				}
			}
		}

		$status = $errors ? 'FAIL' : 'ok';
		printf( "%-26s %-4s %3d controls\n", $name, $status, count( $widget->controls ) );
		foreach ( $errors as $error ) {
			echo "  - $error\n";
			++$storebox_failed;
		}

		if ( $storebox_render ) {
			$html = $widget->run_render();
			printf( "  render: %d bytes%s\n", strlen( $html ), '' === trim( $html ) ? ' (empty)' : '' );
		}
	}

	require_once STOREBOX_CORE_DIR . 'includes/elementor/class-tags.php';
	foreach ( array( 'Unit_Field', 'Location_Field', 'Location_Link', 'Location_Image', 'Phone', 'Read_Time' ) as $class ) {
		$fqcn = '\\Storebox_Core\\Elementor\\Tags\\' . $class;
		$tag  = new $fqcn();
		printf( "%-26s %-4s\n", $tag->get_name(), $tag->errors ? 'FAIL' : 'ok' );
		if ( $storebox_render ) {
			if ( method_exists( $tag, 'get_value' ) ) {
				echo '  value: ' . wp_json_encode( $tag->get_value() ) . "\n";
			} else {
				ob_start();
				$tag->render();
				echo '  render: ' . ob_get_clean() . "\n";
			}
		}
	}

	restore_error_handler();
	echo $storebox_failed ? "\n$storebox_failed problem(s)\n" : "\nAll widgets valid.\n";
}

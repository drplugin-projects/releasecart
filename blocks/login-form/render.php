<?php
/**
 * Renders the releasecart/login-form block through the shared adapter.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

defined( 'ABSPATH' ) || exit;

echo wp_kses(
	releasecart()->container()->get( DrPlugin\WP\ReleaseCart\Frontend\Adapters\Blocks\Registrar::class )->renderBlock( 'login-form', isset( $attributes ) && is_array( $attributes ) ? $attributes : array() ),
	DrPlugin\WP\ReleaseCart\Frontend\Kses::allowed()
);

<?php
/**
 * Renders the releasecart/checkout block through the shared adapter.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

defined( 'ABSPATH' ) || exit;

$releasecart_checkout_html = releasecart()->container()->get( DrPlugin\WP\ReleaseCart\Frontend\Adapters\Blocks\Registrar::class )->renderBlock( 'checkout', isset( $attributes ) && is_array( $attributes ) ? $attributes : array() );

if ( '' !== $releasecart_checkout_html ) {
	/* Keeps the aligned layout outside the theme content width. */
	printf(
		'<div %1$s>%2$s</div>',
		get_block_wrapper_attributes(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() returns escaped attributes.
		wp_kses( $releasecart_checkout_html, DrPlugin\WP\ReleaseCart\Frontend\Kses::allowed() )
	);
}

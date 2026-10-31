<?php
/**
 * Renders the releasecart/product-details block.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

defined( 'ABSPATH' ) || exit;

$releasecart_details_html = releasecart()->container()->get( DrPlugin\WP\ReleaseCart\Frontend\Adapters\Blocks\Registrar::class )->renderBlock( 'product-details', isset( $attributes ) && is_array( $attributes ) ? $attributes : array() );

if ( '' !== $releasecart_details_html ) {
	/* Keep the aligned block wrapper for wide and full layouts. */
	printf(
		'<div %1$s>%2$s</div>',
		get_block_wrapper_attributes(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() returns escaped attributes.
		wp_kses( $releasecart_details_html, DrPlugin\WP\ReleaseCart\Frontend\Kses::allowed() )
	);
}

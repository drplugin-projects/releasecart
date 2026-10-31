<?php
/**
 * Renders the releasecart/product-meta block.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

defined( 'ABSPATH' ) || exit;

$releasecart_meta_html = releasecart()->container()->get( DrPlugin\WP\ReleaseCart\Frontend\Adapters\Blocks\Registrar::class )->renderBlock( 'product-meta', isset( $attributes ) && is_array( $attributes ) ? $attributes : array() );

if ( '' !== $releasecart_meta_html ) {
	/* Keep the aligned block wrapper for wide and full layouts. */
	printf(
		'<div %1$s>%2$s</div>',
		get_block_wrapper_attributes(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() returns escaped attributes.
		wp_kses( $releasecart_meta_html, DrPlugin\WP\ReleaseCart\Frontend\Kses::allowed() )
	);
}

<?php
/**
 * Renders the releasecart/account-dashboard block through the shared adapter.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

defined( 'ABSPATH' ) || exit;

$releasecart_account_html = releasecart()->container()->get( DrPlugin\WP\ReleaseCart\Frontend\Adapters\Blocks\Registrar::class )->renderBlock( 'account-dashboard', isset( $attributes ) && is_array( $attributes ) ? $attributes : array() );

if ( '' !== $releasecart_account_html ) {
	/* Keeps the aligned layout outside the theme content width. */
	printf(
		'<div %1$s>%2$s</div>',
		get_block_wrapper_attributes(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() returns escaped attributes.
		wp_kses( $releasecart_account_html, DrPlugin\WP\ReleaseCart\Frontend\Kses::allowed() )
	);
}

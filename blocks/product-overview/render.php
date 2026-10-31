<?php
/**
 * Renders the releasecart/product-overview block.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

defined( 'ABSPATH' ) || exit;

echo wp_kses(
	releasecart()->container()->get( DrPlugin\WP\ReleaseCart\Frontend\Adapters\Blocks\Registrar::class )->renderBlock( 'product-overview', isset( $attributes ) && is_array( $attributes ) ? $attributes : array() ),
	DrPlugin\WP\ReleaseCart\Frontend\Kses::allowed()
);

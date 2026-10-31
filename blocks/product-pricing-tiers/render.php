<?php
/**
 * Renders the releasecart/product-pricing-tiers block.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

defined( 'ABSPATH' ) || exit;

echo wp_kses(
	releasecart()->container()->get( DrPlugin\WP\ReleaseCart\Frontend\Adapters\Blocks\Registrar::class )->renderBlock( 'product-pricing-tiers', isset( $attributes ) && is_array( $attributes ) ? $attributes : array() ),
	DrPlugin\WP\ReleaseCart\Frontend\Kses::allowed()
);

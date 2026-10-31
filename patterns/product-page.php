<?php
/**
 * Starter product page pattern.
 *
 * Uses granular product blocks in the default reading order.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'       => __( 'Product page', 'releasecart' ),
	'description' => __( 'A product details page built from the granular product blocks: image, overview, plans, description, screenshots, and meta.', 'releasecart' ),
	'blockTypes'  => array( 'releasecart/product-images', 'releasecart/product-overview', 'releasecart/product-pricing-tiers', 'releasecart/product-description', 'releasecart/product-screenshots', 'releasecart/product-meta' ),
	'content'     => \DrPlugin\WP\ReleaseCart\Frontend\StorefrontPageContent::productDetails(),
);

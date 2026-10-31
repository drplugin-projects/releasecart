<?php
/**
 * Starter two-column product page pattern.
 *
 * Uses the combined product-details block beside its purchase panel.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'       => __( 'Product page (two-column)', 'releasecart' ),
	'description' => __( 'A product details page built from the all-in-one block: a wide two-column hero (media beside the buying panel) with the product meta below.', 'releasecart' ),
	'blockTypes'  => array( 'releasecart/product-details', 'releasecart/product-meta' ),
	'content'     => \DrPlugin\WP\ReleaseCart\Frontend\StorefrontPageContent::productDetailsTwoColumn(),
);

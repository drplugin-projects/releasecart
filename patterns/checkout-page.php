<?php
/**
 * Starter checkout page pattern.
 *
 * The receipt block renders only after a checkout return.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'       => __( 'Checkout page', 'releasecart' ),
	'description' => __( 'A complete checkout page: the ReleaseCart payment form with its editable order summary, and the post-payment receipt.', 'releasecart' ),
	'blockTypes'  => array( 'releasecart/purchase-confirmation', 'releasecart/checkout' ),
	'content'     => \DrPlugin\WP\ReleaseCart\Frontend\StorefrontPageContent::checkout(),
);

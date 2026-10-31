<?php
/**
 * Starter account page pattern.
 *
 * Includes the account dashboard and a checkout return receipt.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'       => __( 'Account page', 'releasecart' ),
	'description' => __( 'A complete customer account page: purchase receipt (after checkout), purchases, downloads, and license management.', 'releasecart' ),
	'blockTypes'  => array( 'releasecart/account-dashboard', 'releasecart/purchase-confirmation' ),
	'content'     => \DrPlugin\WP\ReleaseCart\Frontend\StorefrontPageContent::account(),
);

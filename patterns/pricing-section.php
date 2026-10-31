<?php
/**
 * Starter pricing section pattern.
 *
 * Select a product for the pricing table after inserting this pattern.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'       => __( 'Pricing section', 'releasecart' ),
	'description' => __( 'A heading, supporting copy, and a product pricing table. Select the product in the block settings after inserting.', 'releasecart' ),
	'blockTypes'  => array( 'releasecart/pricing-table' ),
	'content'     => '<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">' . esc_html__( 'Simple pricing', 'releasecart' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Every plan includes updates and support for one year.', 'releasecart' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:releasecart/pricing-table /-->',
);

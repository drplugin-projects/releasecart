<?php
/**
 * Product pricing tiers template.
 *
 * Uses the shared purchase form for consistent tier selection and cart actions.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array<string, mixed> $data Purchase-form data.
 */

defined( 'ABSPATH' ) || exit;

$releasecart_form = ( isset( $data ) && is_array( $data ) ) ? $data : array();

if ( empty( $releasecart_form['has_tiers'] ) ) {
	return;
}
?>
<div class="releasecart-component releasecart-product-pricing-tiers">
	<?php require RELEASECART_PATH . 'templates/components/partials/product-purchase-form.php'; ?>
</div>

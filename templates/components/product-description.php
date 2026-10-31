<?php
/**
 * Product description template.
 *
 * Theme override: {theme}/releasecart/components/product-description.php.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array{description: string|null} $data View data.
 */

defined( 'ABSPATH' ) || exit;

$releasecart_description = ( isset( $data['description'] ) && null !== $data['description'] ) ? (string) $data['description'] : '';

if ( '' === $releasecart_description ) {
	return;
}
?>
<div class="releasecart-component releasecart-product-description">
	<?php echo wp_kses_post( $releasecart_description ); ?>
</div>

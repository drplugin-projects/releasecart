<?php
/**
 * Product images component template.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array{image: string|null} $data View data supplied by the component.
 */

defined( 'ABSPATH' ) || exit;

$releasecart_image = ( isset( $data['image'] ) && is_string( $data['image'] ) ) ? $data['image'] : '';

if ( '' === $releasecart_image ) {
	return;
}
?>
<figure class="releasecart-component releasecart-product-images">
	<?php echo wp_kses( $releasecart_image, \DrPlugin\WP\ReleaseCart\Frontend\Kses::image() ); ?>
</figure>

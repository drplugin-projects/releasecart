<?php
/**
 * Product overview template.
 *
 * This theme-overridable template prevents duplicate product H1 headings.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array{name: string, show_name: bool, overview: string|null} $data View data.
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="releasecart-component releasecart-product-overview">
	<?php if ( ! empty( $data['show_name'] ) ) : ?>
		<h1 class="releasecart-product-overview__name"><?php echo esc_html( (string) $data['name'] ); ?></h1>
	<?php endif; ?>

	<?php if ( isset( $data['overview'] ) && null !== $data['overview'] ) : ?>
		<div class="releasecart-product-overview__text"><?php echo wp_kses_post( (string) $data['overview'] ); ?></div>
	<?php endif; ?>
</div>

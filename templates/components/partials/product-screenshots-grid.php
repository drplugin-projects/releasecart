<?php
/**
 * Product screenshot grid shared by product templates.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array<int, array{full: string, thumb: string, alt: string, caption: string}> $releasecart_screenshots
 *     The resolved screenshots supplied by the including template.
 */

defined( 'ABSPATH' ) || exit;

$releasecart_screenshots = ( isset( $releasecart_screenshots ) && is_array( $releasecart_screenshots ) ) ? $releasecart_screenshots : array();

if ( array() === $releasecart_screenshots ) {
	return;
}

// The gallery script replaces these counter tokens.
$releasecart_counter_label =
	/* translators: 1: the current screenshot number, 2: the total number of screenshots. The %1$s and %2$s tokens are substituted by the gallery script. */
	__( '%1$s of %2$s', 'releasecart' );
?>
<div
	class="releasecart-component releasecart-product-screenshots"
	data-releasecart-lightbox-close="<?php echo esc_attr__( 'Close', 'releasecart' ); ?>"
	data-releasecart-gallery-label="<?php echo esc_attr__( 'Product screenshots', 'releasecart' ); ?>"
	data-releasecart-gallery-prev="<?php echo esc_attr__( 'Previous screenshot', 'releasecart' ); ?>"
	data-releasecart-gallery-next="<?php echo esc_attr__( 'Next screenshot', 'releasecart' ); ?>"
	data-releasecart-gallery-counter="<?php echo esc_attr( $releasecart_counter_label ); ?>"
>
	<ul class="releasecart-product-screenshots__grid">
		<?php foreach ( $releasecart_screenshots as $releasecart_shot ) : ?>
			<?php $releasecart_caption = isset( $releasecart_shot['caption'] ) ? (string) $releasecart_shot['caption'] : ''; ?>
			<li class="releasecart-product-screenshots__item">
				<a
					class="releasecart-product-screenshots__link"
					href="<?php echo esc_url( (string) $releasecart_shot['full'] ); ?>"
					data-releasecart-lightbox
					<?php if ( '' !== $releasecart_caption ) : ?>
						data-releasecart-caption="<?php echo esc_attr( $releasecart_caption ); ?>"
					<?php endif; ?>
				>
					<?php echo wp_kses( (string) $releasecart_shot['thumb'], \DrPlugin\WP\ReleaseCart\Frontend\Kses::image() ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>

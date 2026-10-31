<?php
/**
 * Product details template.
 *
 * Theme override: {theme}/releasecart/components/product-details.php.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array{
 *     name: string,
 *     show_name: bool,
 *     layout: string,
 *     main_image: array{url: string, alt: string}|null,
 *     overview: string|null,
 *     description: string|null,
 *     screenshots: array<int, array{full: string, thumb: string, alt: string, caption: string}>,
 *     purchase: array<string, mixed>
 * } $data View data.
 */

defined( 'ABSPATH' ) || exit;

$releasecart_main_image  = ( isset( $data['main_image'] ) && is_array( $data['main_image'] ) ) ? $data['main_image'] : null;
$releasecart_screenshots = ( isset( $data['screenshots'] ) && is_array( $data['screenshots'] ) ) ? $data['screenshots'] : array();
$releasecart_form        = ( isset( $data['purchase'] ) && is_array( $data['purchase'] ) ) ? $data['purchase'] : array();
$releasecart_layout      = ( isset( $data['layout'] ) && 'single' === $data['layout'] ) ? 'single' : 'two-column';
$releasecart_two_column  = ( 'two-column' === $releasecart_layout );

// Omit empty media columns.
$releasecart_has_media = ( null !== $releasecart_main_image ) || ( $releasecart_two_column && array() !== $releasecart_screenshots );
?>
<div class="releasecart-component releasecart-product-details releasecart-product-details--<?php echo esc_attr( $releasecart_layout ); ?>">
	<div class="releasecart-product-details__inner">
		<div class="releasecart-product-details__columns">
			<?php if ( $releasecart_has_media ) : ?>
				<div class="releasecart-product-details__media">
					<?php if ( null !== $releasecart_main_image ) : ?>
						<figure class="releasecart-product-details__main-image">
							<img src="<?php echo esc_url( (string) $releasecart_main_image['url'] ); ?>" alt="<?php echo esc_attr( (string) $releasecart_main_image['alt'] ); ?>" />
						</figure>
					<?php endif; ?>

					<?php
					// Render the shared screenshot gallery.
					if ( $releasecart_two_column && array() !== $releasecart_screenshots ) {
						require RELEASECART_PATH . 'templates/components/partials/product-screenshots-grid.php';
					}
					?>
				</div>
			<?php endif; ?>

			<div class="releasecart-product-details__buy">
				<?php if ( ! empty( $data['show_name'] ) ) : ?>
					<h1 class="releasecart-product-details__name"><?php echo esc_html( (string) $data['name'] ); ?></h1>
				<?php endif; ?>

				<?php if ( isset( $data['overview'] ) && null !== $data['overview'] ) : ?>
					<div class="releasecart-product-details__overview"><?php echo wp_kses_post( (string) $data['overview'] ); ?></div>
				<?php endif; ?>

				<?php
				// Use the shared purchase form.
				require RELEASECART_PATH . 'templates/components/partials/product-purchase-form.php';
				?>
			</div>
		</div>

		<?php if ( isset( $data['description'] ) && null !== $data['description'] ) : ?>
			<div class="releasecart-product-details__description"><?php echo wp_kses_post( (string) $data['description'] ); ?></div>
		<?php endif; ?>

		<?php
		// Show single-layout screenshots after details.
		if ( ! $releasecart_two_column && array() !== $releasecart_screenshots ) {
			require RELEASECART_PATH . 'templates/components/partials/product-screenshots-grid.php';
		}
		?>
	</div>
</div>

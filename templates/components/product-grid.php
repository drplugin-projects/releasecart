<?php
/**
 * Product grid template.
 *
 * Theme override: {theme}/releasecart/components/product-grid.php.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array{
 *     columns: string,
 *     products: array<int, array{name: string, monogram: string, image: string|null, description: string|null, url: string|null, price_html: string|null, buy: array{product_id: int, price_id: int, quantity: int}|null}>,
 *     filter_bar: array{action_url: string, categories: array<int, array{slug: string, name: string, depth: int}>, tags: array<int, array{slug: string, name: string}>, selected_category: string, selected_tags: array<int, string>}|null
 * } $data View data.
 */

defined( 'ABSPATH' ) || exit;

$releasecart_columns    = isset( $data['columns'] ) ? (string) $data['columns'] : '3';
$releasecart_products   = isset( $data['products'] ) && is_array( $data['products'] ) ? $data['products'] : array();
$releasecart_filter_bar = ( isset( $data['filter_bar'] ) && is_array( $data['filter_bar'] ) ) ? $data['filter_bar'] : null;

$releasecart_notices = \DrPlugin\WP\ReleaseCart\Frontend\CartNotices::view();
?>
<div class="releasecart-component releasecart-product-grid" data-columns="<?php echo esc_attr( $releasecart_columns ); ?>">
	<?php require RELEASECART_PATH . 'templates/components/partials/cart-notice.php'; ?>
	<?php if ( null !== $releasecart_filter_bar ) : ?>
		<form class="releasecart-product-grid__filters" method="get" action="<?php echo esc_url( (string) $releasecart_filter_bar['action_url'] ); ?>">
			<label class="releasecart-product-grid__filter">
				<span class="releasecart-product-grid__filter-label"><?php echo esc_html__( 'Category', 'releasecart' ); ?></span>
				<select class="releasecart-product-grid__select releasecart-select" name="releasecart_category">
					<option value=""><?php echo esc_html__( 'All categories', 'releasecart' ); ?></option>
					<?php foreach ( (array) $releasecart_filter_bar['categories'] as $releasecart_category ) : ?>
						<option value="<?php echo esc_attr( (string) $releasecart_category['slug'] ); ?>"<?php selected( (string) $releasecart_filter_bar['selected_category'], (string) $releasecart_category['slug'] ); ?>>
							<?php echo esc_html( str_repeat( '- ', max( 0, (int) $releasecart_category['depth'] ) ) . (string) $releasecart_category['name'] ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>

			<?php if ( array() !== (array) $releasecart_filter_bar['tags'] ) : ?>
				<fieldset class="releasecart-product-grid__tags">
					<legend class="releasecart-product-grid__filter-label"><?php echo esc_html__( 'Tags', 'releasecart' ); ?></legend>
					<?php foreach ( (array) $releasecart_filter_bar['tags'] as $releasecart_tag ) : ?>
						<label class="releasecart-product-grid__tag">
							<input type="checkbox" name="releasecart_tag[]" value="<?php echo esc_attr( (string) $releasecart_tag['slug'] ); ?>"<?php checked( in_array( (string) $releasecart_tag['slug'], (array) $releasecart_filter_bar['selected_tags'], true ) ); ?> />
							<span><?php echo esc_html( (string) $releasecart_tag['name'] ); ?></span>
						</label>
					<?php endforeach; ?>
				</fieldset>
			<?php endif; ?>

			<button type="submit" class="releasecart-product-grid__filter-submit releasecart-button releasecart-button--secondary"><?php echo esc_html__( 'Filter', 'releasecart' ); ?></button>
		</form>
	<?php endif; ?>

	<?php if ( array() === $releasecart_products ) : ?>
		<p class="releasecart-product-grid__empty"><?php echo esc_html__( 'No products found.', 'releasecart' ); ?></p>
	<?php else : ?>
		<ul class="releasecart-product-grid__items">
			<?php foreach ( $releasecart_products as $releasecart_product ) : ?>
				<li class="releasecart-product-grid__item releasecart-card">
					<?php
					// Hide duplicate media links from screen readers.
					$releasecart_image    = isset( $releasecart_product['image'] ) ? (string) $releasecart_product['image'] : '';
					$releasecart_monogram = (string) ( $releasecart_product['monogram'] ?? '' );
					?>
					<?php if ( isset( $releasecart_product['url'] ) && '' !== (string) $releasecart_product['url'] ) : ?>
						<a class="releasecart-product-grid__media" href="<?php echo esc_url( (string) $releasecart_product['url'] ); ?>" aria-hidden="true" tabindex="-1">
							<?php
							if ( '' !== $releasecart_image ) {
								echo wp_kses( $releasecart_image, \DrPlugin\WP\ReleaseCart\Frontend\Kses::image() );
							} else {
								printf( '<span class="releasecart-product-grid__monogram">%s</span>', esc_html( $releasecart_monogram ) );
							}
							?>
						</a>
					<?php else : ?>
						<div class="releasecart-product-grid__media" aria-hidden="true">
							<?php
							if ( '' !== $releasecart_image ) {
								echo wp_kses( $releasecart_image, \DrPlugin\WP\ReleaseCart\Frontend\Kses::image() );
							} else {
								printf( '<span class="releasecart-product-grid__monogram">%s</span>', esc_html( $releasecart_monogram ) );
							}
							?>
						</div>
					<?php endif; ?>

					<?php if ( isset( $releasecart_product['url'] ) && '' !== (string) $releasecart_product['url'] ) : ?>
						<h3 class="releasecart-product-grid__name"><a class="releasecart-product-grid__link" href="<?php echo esc_url( (string) $releasecart_product['url'] ); ?>"><?php echo esc_html( (string) $releasecart_product['name'] ); ?></a></h3>
					<?php else : ?>
						<h3 class="releasecart-product-grid__name"><?php echo esc_html( (string) $releasecart_product['name'] ); ?></h3>
					<?php endif; ?>

					<?php if ( isset( $releasecart_product['description'] ) && '' !== (string) $releasecart_product['description'] ) : ?>
						<div class="releasecart-product-grid__description"><?php echo wp_kses_post( (string) $releasecart_product['description'] ); ?></div>
					<?php endif; ?>

					<?php if ( isset( $releasecart_product['price_html'] ) && null !== $releasecart_product['price_html'] ) : ?>
						<p class="releasecart-product-grid__price"><?php echo wp_kses( $releasecart_product['price_html'], \DrPlugin\WP\ReleaseCart\Frontend\Kses::allowed() ); ?></p>
					<?php endif; ?>

					<?php if ( isset( $releasecart_product['buy'] ) && is_array( $releasecart_product['buy'] ) ) : ?>
						<form class="releasecart-product-grid__buy" method="post" action="">
							<input type="hidden" name="releasecart_cart_action" value="add" />
							<input type="hidden" name="product_id" value="<?php echo esc_attr( (string) $releasecart_product['buy']['product_id'] ); ?>" />
							<input type="hidden" name="price_id" value="<?php echo esc_attr( (string) $releasecart_product['buy']['price_id'] ); ?>" />
							<input type="hidden" name="quantity" value="<?php echo esc_attr( (string) ( $releasecart_product['buy']['quantity'] ?? 1 ) ); ?>" />
							<?php
							// Print the cart nonce field.
							wp_nonce_field(
								\DrPlugin\WP\ReleaseCart\Frontend\CartActionListener::NONCE_ACTION,
								\DrPlugin\WP\ReleaseCart\Frontend\CartActionListener::NONCE_FIELD,
								true,
								true
							);
							?>
							<button type="submit" class="releasecart-product-grid__buy-button releasecart-button releasecart-button--primary releasecart-button--small"><?php echo esc_html__( 'Buy now', 'releasecart' ); ?></button>
						</form>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>

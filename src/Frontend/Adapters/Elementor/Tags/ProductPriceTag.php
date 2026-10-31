<?php
/**
 * The product-price dynamic tag.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Frontend\Adapters\Elementor\Tags;

defined( 'ABSPATH' ) || exit;

/**
 * Outputs the selected product's default price.
 *
 * @since 1.0.0
 */
final class ProductPriceTag extends AbstractProductTag {

	/**
	 * The stable dynamic-tag name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'releasecart-product-price';
	}

	/**
	 * The tag's editor title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'ReleaseCart Product Price', 'releasecart' );
	}

	/**
	 * Render the product's default-tier price.
	 *
	 * @return void
	 */
	public function render(): void {
		echo esc_html( self::values()->productPrice( $this->productId() ) );
	}
}

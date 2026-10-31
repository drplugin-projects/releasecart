<?php
/**
 * The product-name dynamic tag.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Frontend\Adapters\Elementor\Tags;

defined( 'ABSPATH' ) || exit;

/**
 * Shows the selected product's name.
 *
 * @since 1.0.0
 */
final class ProductNameTag extends AbstractProductTag {

	/**
	 * The stable dynamic-tag name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'releasecart-product-name';
	}

	/**
	 * The tag's editor title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'ReleaseCart Product Name', 'releasecart' );
	}

	/**
	 * Render the product name.
	 *
	 * @return void
	 */
	public function render(): void {
		echo esc_html( self::values()->productName( $this->productId() ) );
	}
}

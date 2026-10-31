<?php
/**
 * Product type enum.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Products;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

/**
 * Stored product type.
 *
 * @since 1.0.0
 */
enum ProductType: string {

	/**
	 * Standalone digital product.
	 */
	case Digital = 'digital';

	/**
	 * Bundle of digital products.
	 */
	case Bundle = 'bundle';
}

<?php
/**
 * Product taxonomy enum.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Products;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

/**
 * Stored product term type.
 *
 * @since 1.0.0
 */
enum ProductTaxonomy: string {

	/**
	 * Hierarchical product category.
	 */
	case Category = 'category';

	/**
	 * Flat product tag.
	 */
	case Tag = 'tag';
}

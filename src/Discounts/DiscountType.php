<?php
/**
 * Discount amount-type enum.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Discounts;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

/**
 * Defines how a discount calculates its reduction.
 *
 * @since 1.0.0
 */
enum DiscountType: string {

	/**
	 * A percentage from `amount_percent`.
	 */
	case Percent = 'percent';

	/**
	 * A minor-unit amount from `amount_fixed`.
	 */
	case Fixed = 'fixed';
}

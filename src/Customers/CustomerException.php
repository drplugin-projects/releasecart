<?php
/**
 * Base exception for the Customers domain.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Customers;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

use InvalidArgumentException;

/**
 * Base exception for customer-domain errors.
 *
 * @since 1.0.0
 */
class CustomerException extends InvalidArgumentException {
}

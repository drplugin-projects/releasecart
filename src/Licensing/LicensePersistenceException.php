<?php
/**
 * Exception for license persistence (database write) failures.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Licensing;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

/**
 * Thrown when a license or activation write fails.
 *
 * The REST layer returns a generic `500` response. Subclasses can mark retryable writes.
 *
 * @since 1.0.0
 */
class LicensePersistenceException extends LicenseException {
}

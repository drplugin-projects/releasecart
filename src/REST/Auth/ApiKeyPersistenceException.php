<?php
/**
 * Persistence failure in the management API key domain.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\REST\Auth;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

/**
 * Raised when API key storage fails.
 *
 * @since 1.0.0
 */
final class ApiKeyPersistenceException extends ApiKeyException {
}

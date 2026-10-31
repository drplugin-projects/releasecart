<?php
/**
 * Base exception for the outbound Webhooks domain.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Webhooks;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

use RuntimeException;

/**
 * Base exception for outbound webhook operations.
 *
 * @since 1.0.0
 */
class WebhookException extends RuntimeException {
}

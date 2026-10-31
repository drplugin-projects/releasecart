<?php
/**
 * Signal that an inbound webhook could not be claimed and should be retried.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Payments;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

/**
 * Raised when a webhook claim must retry. The processor returns 500 so the
 * gateway redelivers the event.
 *
 * @since 1.0.0
 */
final class WebhookRetryException extends PaymentException {
}

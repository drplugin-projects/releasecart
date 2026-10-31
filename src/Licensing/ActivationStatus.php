<?php
/**
 * Activation status enum.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Licensing;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

/**
 * Stored state of a site activation.
 *
 * Deactivation retains the row for its history.
 *
 * @since 1.0.0
 */
enum ActivationStatus: string {

	/**
	 * The site counts against its license limit unless it is local.
	 */
	case Active = 'active';

	/**
	 * The site no longer counts against the activation limit.
	 */
	case Deactivated = 'deactivated';
}

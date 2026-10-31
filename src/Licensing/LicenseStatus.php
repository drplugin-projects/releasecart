<?php
/**
 * License lifecycle status enum.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Licensing;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

/**
 * License states stored in `releasecart_licenses.status`.
 * {@see LicenseStateMachine} controls state changes.
 *
 * @since 1.0.0
 */
enum LicenseStatus: string {

	/**
	 * Active licenses validate, activate, and receive updates.
	 */
	case Active = 'active';

	/**
	 * The license expiry time has passed.
	 */
	case Expired = 'expired';

	/**
	 * An administrator or refund disabled the license.
	 */
	case Disabled = 'disabled';
}

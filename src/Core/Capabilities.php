<?php
/**
 * The ReleaseCart custom capability model.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Core;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

/**
 * ReleaseCart capabilities for store work, settings and reports.
 *
 * Check them at init so an administrator cannot lose access through role drift.
 *
 * @since 1.0.0
 */
final class Capabilities {

	/**
	 * Store management capability.
	 *
	 * @var string
	 */
	public const MANAGE = 'manage_releasecart';

	/**
	 * Settings management capability.
	 *
	 * @var string
	 */
	public const MANAGE_SETTINGS = 'manage_releasecart_settings';

	/**
	 * Read-only reports capability.
	 *
	 * @var string
	 */
	public const VIEW_REPORTS = 'view_releasecart_reports';

	/**
	 * Get every ReleaseCart capability.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, string> Capability names.
	 */
	public static function all(): array {
		return array( self::MANAGE, self::MANAGE_SETTINGS, self::VIEW_REPORTS );
	}

	/**
	 * Grant missing capabilities to administrators.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function ensure(): void {
		$role = get_role( 'administrator' );

		if ( null === $role ) {
			return;
		}

		foreach ( self::all() as $cap ) {
			if ( ! $role->has_cap( $cap ) ) {
				$role->add_cap( $cap );
			}
		}
	}

	/**
	 * Remove ReleaseCart capabilities from all roles.
	 *
	 * Deactivation keeps role mappings. The opt-in uninstall removes them.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function remove(): void {
		foreach ( wp_roles()->role_objects as $role ) {
			foreach ( self::all() as $cap ) {
				if ( $role->has_cap( $cap ) ) {
					$role->remove_cap( $cap );
				}
			}
		}
	}
}

<?php
/**
 * Plugin deactivation handler.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Core;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

/**
 * Clears runtime scheduling on deactivation.
 *
 * Data deletion is only available through the opt-in uninstall path.
 *
 * @since 1.0.0
 */
final class Deactivator {

	/**
	 * Clear recurring maintenance jobs.
	 *
	 * Keep mint retries queued. They complete paid orders after reactivation.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function deactivate(): void {
		// These jobs reschedule on the next activation.
		as_unschedule_all_actions( \DrPlugin\WP\ReleaseCart\Licensing\LicensingListener::EXPIRY_HOOK, array(), 'releasecart' );
		as_unschedule_all_actions( \DrPlugin\WP\ReleaseCart\Jobs\ActionSchedulerPruner::PRUNE_HOOK, array(), 'releasecart' );
		as_unschedule_all_actions( \DrPlugin\WP\ReleaseCart\Jobs\AbandonedOrdersSweep::SWEEP_HOOK, array(), 'releasecart' );

		// Rebuild product rewrites after the next activation.
		\DrPlugin\WP\ReleaseCart\Frontend\ProductRewrites::clearOnDeactivate();
	}
}

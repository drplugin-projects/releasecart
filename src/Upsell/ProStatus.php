<?php
/**
 * Whether the Pro add-on is present, and where to send buyers.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Upsell;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

/**
 * The single source of truth for the upsell surfaces.
 *
 * An instance so surfaces can mock it: `defined()` cannot be stubbed.
 *
 * @since 1.0.0
 */
class ProStatus {

	/**
	 * The ReleaseCart Pro page every upgrade link points at.
	 *
	 * It carries no query string, so the link tracks no referral.
	 *
	 * @var string
	 */
	private const UPGRADE_URL = 'https://drplugin.com/plugins/releasecart';

	/**
	 * Whether the Pro add-on is active.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True when Pro is present.
	 */
	public function isActive(): bool {
		return defined( 'RELEASECART_PRO_VERSION' );
	}

	/**
	 * The upgrade destination.
	 *
	 * @since 1.0.0
	 *
	 * @return string The page URL.
	 */
	public function upgradeUrl(): string {
		return self::UPGRADE_URL;
	}
}

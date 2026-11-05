<?php
/**
 * Registers ReleaseCart's GDPR exporter and eraser with WordPress.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Privacy;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

use DrPlugin\WP\ReleaseCart\Core\Hookable;

/**
 * Register ReleaseCart privacy tools and policy text.
 *
 * @since 1.0.0
 */
final class PrivacyRegistrar implements Hookable {

	/**
	 * The key both the exporter and eraser register under.
	 *
	 * @var string
	 */
	private const KEY = 'releasecart';

	/**
	 * Wire the registrar with the exporter and eraser.
	 *
	 * @since 1.0.0
	 *
	 * @param PersonalDataExporter $exporter The personal-data exporter.
	 * @param PersonalDataEraser   $eraser   The personal-data eraser.
	 */
	public function __construct(
		private readonly PersonalDataExporter $exporter,
		private readonly PersonalDataEraser $eraser
	) {
	}

	/**
	 * Register the exporter, eraser, and privacy-policy content hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'registerExporter' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'registerEraser' ) );
		add_action( 'admin_init', array( $this, 'registerPolicyContent' ) );
	}

	/**
	 * Add ReleaseCart's exporter to the registered set.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, array<string, mixed>> $exporters The registered exporters.
	 * @return array<string, array<string, mixed>> The exporters including ReleaseCart's.
	 */
	public function registerExporter( array $exporters ): array {
		$exporters[ self::KEY ] = array(
			'exporter_friendly_name' => $this->exporter->friendlyName(),
			'callback'               => array( $this->exporter, 'export' ),
		);

		return $exporters;
	}

	/**
	 * Add ReleaseCart's eraser to the registered set.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, array<string, mixed>> $erasers The registered erasers.
	 * @return array<string, array<string, mixed>> The erasers including ReleaseCart's.
	 */
	public function registerEraser( array $erasers ): array {
		$erasers[ self::KEY ] = array(
			'eraser_friendly_name' => $this->eraser->friendlyName(),
			'callback'             => array( $this->eraser, 'erase' ),
		);

		return $erasers;
	}

	/**
	 * Contribute suggested privacy-policy text describing the personal data
	 * ReleaseCart stores, for the WordPress privacy-policy guide.
	 *
	 * @since 1.0.0
	 * @since 1.0.1 Names what a site reports when it activates a license.
	 *
	 * @return void
	 */
	public function registerPolicyContent(): void {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		// Two strings, so the first keeps its existing translations.
		$content = wp_kses_post(
			wpautop(
				__( 'When you buy from this store, ReleaseCart records your name, email address, order details, billing address, company name, tax ID, license keys, the sites where you activate a license, and the IP address used to place an order and to download files. When you pay through Stripe, Stripe receives your card details, name, email address, billing address and the products you buy. Order records are kept for tax and accounting purposes even after an erasure request; their personal fields are removed or anonymized rather than the record being deleted.', 'releasecart' )
				. "\n\n"
				. __( 'When you activate a license, ReleaseCart also records the details your site reports, such as its PHP, WordPress and product versions, and a device ID if the software provides one.', 'releasecart' )
			)
		);

		wp_add_privacy_policy_content( 'ReleaseCart', $content );
	}
}

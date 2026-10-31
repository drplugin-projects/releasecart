<?php
/**
 * The advertised Pro features and the upsell copy.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Upsell;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

/**
 * The single source the upsell surfaces read their copy from.
 *
 * Every row names a feature the separate Pro add-on ships.
 *
 * @since 1.0.0
 */
final class ProFeatures {

	/**
	 * The feature rows shown on the upgrade screen and the settings teaser.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, array{key: string, title: string, blurb: string}> The rows.
	 */
	public function all(): array {
		return array(
			array(
				'key'   => 'subscriptions',
				'title' => __( 'Subscriptions and recurring billing', 'releasecart' ),
				'blurb' => __( 'Sell plans that renew on a schedule, with trials, upgrades, and automatic retries on a failed payment.', 'releasecart' ),
			),
			array(
				'key'   => 'tax',
				'title' => __( 'EU VAT with VIES and OSS returns', 'releasecart' ),
				'blurb' => __( 'Charge VAT at each buyer\'s country rate, check VAT numbers against VIES, and prepare your OSS return.', 'releasecart' ),
			),
			array(
				'key'   => 'invoices',
				'title' => __( 'PDF invoices', 'releasecart' ),
				'blurb' => __( 'Issue a numbered PDF invoice for every order and let buyers download it from their account.', 'releasecart' ),
			),
			array(
				'key'   => 'paypal',
				'title' => __( 'PayPal payments', 'releasecart' ),
				'blurb' => __( 'Take payments through PayPal alongside Stripe.', 'releasecart' ),
			),
			array(
				'key'   => 'affiliates',
				'title' => __( 'Affiliates', 'releasecart' ),
				'blurb' => __( 'Run an affiliate program with referral tracking, commissions, and payouts.', 'releasecart' ),
			),
			array(
				'key'   => 'recovery',
				'title' => __( 'Cart recovery emails', 'releasecart' ),
				'blurb' => __( 'Email shoppers who leave without paying, on a schedule you set, each with an optional discount.', 'releasecart' ),
			),
			array(
				'key'   => 'analytics',
				'title' => __( 'Store analytics', 'releasecart' ),
				'blurb' => __( 'Track recurring revenue, active subscriptions, and customer lifetime value.', 'releasecart' ),
			),
			array(
				'key'   => 'kits',
				'title' => __( 'Template kits', 'releasecart' ),
				'blurb' => __( 'Install a ready-made storefront layout as a starting point for your own.', 'releasecart' ),
			),
			array(
				'key'   => 'migration',
				'title' => __( 'Store migration', 'releasecart' ),
				'blurb' => __( 'Import products and customers from another platform.', 'releasecart' ),
			),
			array(
				'key'   => 'elementor',
				'title' => __( 'Elementor subscription and invoice widgets', 'releasecart' ),
				'blurb' => __( 'Add Subscriptions, Invoices, and Upgrade Prompt widgets to Elementor.', 'releasecart' ),
			),
			array(
				'key'   => 'conditional_content',
				'title' => __( 'Conditional Content block', 'releasecart' ),
				'blurb' => __( 'Show inner blocks only to visitors who match a license, subscription, purchase, or activation rule.', 'releasecart' ),
			),
		);
	}

	/**
	 * The lead paragraph on the upgrade screen.
	 *
	 * @since 1.0.0
	 *
	 * @return string The intro text.
	 */
	public function intro(): string {
		return __( 'ReleaseCart Pro is a separate add-on that extends this plugin. It adds recurring billing, EU VAT handling, PDF invoices, PayPal, affiliates, cart recovery, and store analytics. The free plugin keeps working as it is.', 'releasecart' );
	}

	/**
	 * The one-line banner pitch.
	 *
	 * @since 1.0.0
	 *
	 * @return string The notice text.
	 */
	public function notice(): string {
		return __( 'ReleaseCart Pro adds subscriptions, EU VAT, PDF invoices, PayPal, affiliates, and cart recovery.', 'releasecart' );
	}

	/**
	 * The label on every upgrade link.
	 *
	 * @since 1.0.0
	 *
	 * @return string The link label.
	 */
	public function linkLabel(): string {
		return __( 'See ReleaseCart Pro', 'releasecart' );
	}

	/**
	 * The label on the upgrade screen's heading button.
	 *
	 * @since 1.0.0
	 *
	 * @return string The button label.
	 */
	public function ctaLabel(): string {
		return __( 'View ReleaseCart Pro features', 'releasecart' );
	}
}

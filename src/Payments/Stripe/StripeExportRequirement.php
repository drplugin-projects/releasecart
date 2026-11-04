<?php
/**
 * Checkout fields Stripe needs for an account in India.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Payments\Stripe;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

/**
 * Requires the buyer's name, country and billing address while the active
 * Stripe account is in India.
 *
 * Stripe refuses payments from cards issued outside India without them.
 *
 * @since 1.0.0
 */
class StripeExportRequirement {

	/**
	 * Fields the rule requires through the required seam.
	 *
	 * @var array<int, string>
	 */
	private const REQUIRED_FIELDS = array( 'first_name', 'last_name', 'billing_country' );

	/**
	 * Wire the rule.
	 *
	 * @since 1.0.0
	 *
	 * @param StripeSettings $settings The active mode and stored account countries.
	 */
	public function __construct( private readonly StripeSettings $settings ) {}

	/**
	 * Hook the checkout field seams.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_filter( 'releasecart_checkout_address_required', array( $this, 'requireAddress' ) );
		add_filter( 'releasecart_checkout_collect_field', array( $this, 'collectField' ), 10, 2 );
		add_filter( 'releasecart_checkout_field_required', array( $this, 'requireField' ), 10, 2 );
	}

	/**
	 * Whether the active mode's Stripe account is in India.
	 *
	 * Reads only the stored country, which exists only after a secret key save.
	 * The field getters fire these filters often, so no key is decrypted here.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function inForce(): bool {
		return 'IN' === $this->settings->accountCountry( $this->settings->mode() );
	}

	/**
	 * Whether the account lookup for a mode failed.
	 *
	 * @since 1.0.0
	 *
	 * @param string $mode Either 'test' or 'live'.
	 * @return bool
	 */
	public function countryUnknown( string $mode ): bool {
		return StripeSettings::ACCOUNT_COUNTRY_UNKNOWN === $this->settings->accountCountry( $mode );
	}

	/**
	 * Require the billing address while the rule is in force.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $required The value so far.
	 * @return bool
	 */
	public function requireAddress( $required ): bool {
		return $this->inForce() ? true : (bool) $required;
	}

	/**
	 * Collect the billing country while the rule is in force.
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $collect The value so far.
	 * @param string $key     The field key.
	 * @return bool
	 */
	public function collectField( $collect, $key ): bool {
		return 'billing_country' === $key && $this->inForce() ? true : (bool) $collect;
	}

	/**
	 * Require the names and the country while the rule is in force.
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $required The value so far.
	 * @param string $key      The field key.
	 * @return bool
	 */
	public function requireField( $required, $key ): bool {
		return in_array( $key, self::REQUIRED_FIELDS, true ) && $this->inForce() ? true : (bool) $required;
	}
}

<?php
/**
 * Reads which optional fields the checkout collects.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Frontend;

defined( 'ABSPATH' ) || exit;

use DrPlugin\WP\ReleaseCart\Catalog\Countries;

/**
 * Reads checkout field and account settings.
 *
 * Extensions can turn the billing country and VAT on, and require fields, through filters.
 *
 * @since 1.0.0
 */
class CheckoutFieldSettings {

	/**
	 * The settings option holding the checkout subtree.
	 *
	 * @var string
	 */
	private const SETTINGS_OPTION = 'releasecart_settings';

	/**
	 * The toggle behind each billing field the collect seam answers for.
	 *
	 * @var array<string, string>
	 */
	private const COLLECT_TOGGLES = array(
		'billing_country' => 'collect_country',
		'vat_number'      => 'collect_vat',
	);

	/**
	 * The toggle behind each name the required seam answers for.
	 *
	 * @var array<string, string>
	 */
	private const NAME_TOGGLES = array(
		'first_name' => 'require_first_name',
		'last_name'  => 'require_last_name',
	);

	/**
	 * Whether the buyer must accept the terms before paying.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function requireTerms(): bool {
		return $this->toggle( 'require_terms' );
	}

	/**
	 * Whether to show the login panel to logged-out buyers.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function showLogin(): bool {
		return $this->toggle( 'show_login', true );
	}

	/**
	 * Whether a guest purchase creates or links an account.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function autoRegister(): bool {
		return $this->toggle( 'auto_register', false );
	}

	/**
	 * Whether buyers may check out without an account.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function guestCheckout(): bool {
		return $this->toggle( 'guest_checkout', true );
	}

	/**
	 * Whether settings require a buyer account.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function loginRequired(): bool {
		return ! $this->guestCheckout() && ! $this->autoRegister();
	}

	/**
	 * Whether checkout requires a first name.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function requireFirstName(): bool {
		return $this->required( 'first_name', $this->toggle( self::NAME_TOGGLES['first_name'] ) );
	}

	/**
	 * Whether the buyer must enter a last name to check out. Defaults off.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function requireLastName(): bool {
		return $this->required( 'last_name', $this->toggle( self::NAME_TOGGLES['last_name'] ) );
	}

	/**
	 * Whether checkout requires the billing country. Only a listener on the
	 * required seam can ask for it, and only while the country is collected.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function requireCountry(): bool {
		return $this->collectCountry() && $this->required( 'billing_country', false );
	}

	/**
	 * Whether a name is required only because an extension asked for it.
	 *
	 * The settings screen uses this to say the merchant's toggle is not what is
	 * in force.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The field key: `first_name` or `last_name`.
	 * @return bool
	 */
	public function requiredByExtension( string $key ): bool {
		if ( ! isset( self::NAME_TOGGLES[ $key ] ) ) {
			return false;
		}

		return ! $this->toggle( self::NAME_TOGGLES[ $key ] ) && $this->required( $key, false );
	}

	/**
	 * Whether checkout offers company details.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function collectCompany(): bool {
		return $this->toggle( 'collect_company', true );
	}

	/**
	 * Whether to collect a billing country: the merchant's own toggle, or an
	 * extension asking for it through the collect seam.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function collectCountry(): bool {
		return $this->collect( 'billing_country' );
	}

	/**
	 * Whether to collect a VAT or tax id: the merchant's own toggle, or an
	 * extension asking for it through the collect seam.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function collectVat(): bool {
		return $this->collect( 'vat_number' );
	}

	/**
	 * Whether a billing field is collected only because an extension asked for it.
	 *
	 * The settings screen uses this to say the merchant's toggle is not what is
	 * in force.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The field key: `billing_country` or `vat_number`.
	 * @return bool
	 */
	public function collectedByExtension( string $key ): bool {
		if ( ! isset( self::COLLECT_TOGGLES[ $key ] ) ) {
			return false;
		}

		return ! $this->toggle( self::COLLECT_TOGGLES[ $key ] ) && $this->collect( $key );
	}

	/**
	 * The billing-address collection mode: `none`, `optional`, or `required`.
	 * An extension that requires the address through the seam turns any stored
	 * mode into `required`.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function addressMode(): string {
		return $this->addressRequired() ? 'required' : $this->storedAddressMode();
	}

	/**
	 * Whether any billing address is collected.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function collectsAddress(): bool {
		return 'none' !== $this->addressMode();
	}

	/**
	 * Whether the billing address is mandatory: the merchant's own setting, or
	 * an extension requiring it through the seam.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function addressRequired(): bool {
		$stored = 'required' === $this->storedAddressMode();

		/**
		 * Filters whether checkout requires a billing address.
		 *
		 * The stored setting is the default. A listener can require the
		 * address, not relax a requirement the merchant set. A listener must
		 * not read this object's address getters, which fire this filter.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $required Whether the stored setting requires the address.
		 */
		$required = (bool) apply_filters( 'releasecart_checkout_address_required', $stored );

		return $stored || $required;
	}

	/**
	 * Whether the address is required only because an extension asked for it.
	 *
	 * The settings screen uses this to say the merchant's own choice is not
	 * what is in force.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function addressRequiredByExtension(): bool {
		return 'required' !== $this->storedAddressMode() && $this->addressRequired();
	}

	/**
	 * The stored address mode, before the seam. An unknown value degrades to `none`.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function storedAddressMode(): string {
		$mode = (string) $this->value( 'collect_address', 'none' );

		return in_array( $mode, array( 'optional', 'required' ), true ) ? $mode : 'none';
	}

	/**
	 * Get checkout countries, limited to the core catalog.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, string> Country code to name.
	 */
	public function countryOptions(): array {
		$base = Countries::all();

		/**
 * Filters the country list offered by checkout.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, string> $base Country code to name.
		 */
		$filtered = apply_filters( 'releasecart_checkout_countries', $base );

		if ( ! is_array( $filtered ) ) {
			return $base;
		}

		$narrowed = array_intersect_key( $filtered, $base );

		return array() === $narrowed ? $base : $narrowed;
	}

	/**
	 * Whether a billing field is collected: its toggle, or the collect seam.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The field key: `billing_country` or `vat_number`.
	 * @return bool
	 */
	private function collect( string $key ): bool {
		$stored = $this->toggle( self::COLLECT_TOGGLES[ $key ] );

		/**
		 * Filters whether checkout collects a billing field.
		 *
		 * Seeded with the merchant's toggle. A listener can turn collection on,
		 * not off: core keeps the field while the toggle is on. As with the
		 * address seam, a later listener that returns false undoes an earlier
		 * listener's request, so answer for your own need and pass the value on
		 * otherwise.
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $collect Whether the merchant's toggle collects the field.
		 * @param string $key     The field key: `billing_country` or `vat_number`.
		 */
		$collect = (bool) apply_filters( 'releasecart_checkout_collect_field', $stored, $key );

		return $stored || $collect;
	}

	/**
	 * Whether a checkout field is required: the stored requirement, or the seam.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key    The field key: `first_name`, `last_name` or `billing_country`.
	 * @param bool   $stored Whether the merchant's settings require the field.
	 * @return bool
	 */
	private function required( string $key, bool $stored ): bool {
		/**
		 * Filters whether checkout requires a field.
		 *
		 * Seeded with the merchant's setting. A listener can require the field,
		 * not relax a requirement the merchant set. Answer for your own need and
		 * pass the value on otherwise. The country is required only while it is
		 * collected. A listener must not read this object's require getters,
		 * which fire this filter.
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $required Whether the merchant's settings require the field.
		 * @param string $key      The field key: `first_name`, `last_name` or `billing_country`.
		 */
		$required = (bool) apply_filters( 'releasecart_checkout_field_required', $stored, $key );

		return $stored || $required;
	}

	/**
	 * Read a checkout toggle with its default.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key      The key under the `checkout` settings subtree.
	 * @param bool   $fallback The value when the key is absent.
	 * @return bool
	 */
	private function toggle( string $key, bool $fallback = false ): bool {
		return (bool) $this->value( $key, $fallback );
	}

	/**
	 * Read a raw value from the `checkout` settings subtree, or a default.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key      The key under the `checkout` settings subtree.
	 * @param mixed  $fallback The value when the key is absent.
	 * @return mixed
	 */
	private function value( string $key, $fallback ) {
		$settings = get_option( self::SETTINGS_OPTION );

		if ( is_array( $settings ) && isset( $settings['checkout'][ $key ] ) ) {
			return $settings['checkout'][ $key ];
		}

		return $fallback;
	}
}

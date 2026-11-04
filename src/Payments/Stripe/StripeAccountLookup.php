<?php
/**
 * Reads the country of the Stripe account behind a saved secret key.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Payments\Stripe;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

use Throwable;

/**
 * Asks Stripe for the account country of one mode and stores the answer.
 *
 * Runs when a secret key is saved. A failed lookup stores `unknown`.
 *
 * @since 1.0.0
 */
class StripeAccountLookup {

	/**
	 * Wire the lookup.
	 *
	 * @since 1.0.0
	 *
	 * @param StripeApiClient $client   Stripe transport.
	 * @param StripeSettings  $settings Stripe credentials and the stored country.
	 */
	public function __construct(
		private readonly StripeApiClient $client,
		private readonly StripeSettings $settings
	) {}

	/**
	 * Read the account country for a mode and store it. Never throws.
	 *
	 * @since 1.0.0
	 *
	 * @param string $mode Either 'test' or 'live'.
	 * @return void
	 */
	public function refresh( string $mode ): void {
		if ( 'test' !== $mode && 'live' !== $mode ) {
			return;
		}

		try {
			$secretKey = $this->settings->secretKeyFor( $mode );

			if ( null === $secretKey ) {
				$this->settings->clearAccountCountry( $mode );

				return;
			}

			$this->settings->setAccountCountry( $mode, $this->fetchCountry( $secretKey ) );
		} catch ( Throwable $e ) {
			// A settings save must not fail because of this lookup.
			return;
		}
	}

	/**
	 * Get the account country from Stripe, or `unknown`.
	 *
	 * @since 1.0.0
	 *
	 * @param string $secretKey The mode's secret key.
	 * @return string An uppercase ISO 3166-1 alpha-2 code, or `unknown`.
	 */
	private function fetchCountry( string $secretKey ): string {
		try {
			$account = $this->client->get( $secretKey, '/v1/account' );
		} catch ( Throwable $e ) {
			// A restricted key needs Accounts read access for this call.
			return StripeSettings::ACCOUNT_COUNTRY_UNKNOWN;
		}

		$country = $account['country'] ?? null;

		if ( ! is_string( $country ) || 1 !== preg_match( '/\A[A-Za-z]{2}\z/', $country ) ) {
			return StripeSettings::ACCOUNT_COUNTRY_UNKNOWN;
		}

		return strtoupper( $country );
	}
}

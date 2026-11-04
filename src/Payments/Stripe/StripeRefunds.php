<?php
/**
 * Reads the refunds of a Stripe charge.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Payments\Stripe;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

use DrPlugin\WP\ReleaseCart\Payments\GatewayRequestException;

/**
 * Lists a charge's refunds through GET /v1/refunds.
 *
 * A charge.refunded payload has not listed its refunds since 2022-11-15.
 *
 * @since 1.0.0
 */
class StripeRefunds {

	/**
	 * Wire the lookup.
	 *
	 * @since 1.0.0
	 *
	 * @param StripeApiClient $client   Stripe transport.
	 * @param StripeSettings  $settings Stripe credentials.
	 */
	public function __construct(
		private readonly StripeApiClient $client,
		private readonly StripeSettings $settings
	) {}

	/**
	 * Get a charge's refunds, newest first, without failed, canceled or requires_action ones.
	 *
	 * @since 1.0.0
	 *
	 * @param string $chargeId The Stripe charge id.
	 * @return array<int, array{id: string, amount: int}>|null The refunds, or null when Stripe could not be read.
	 */
	public function forCharge( string $chargeId ): ?array {
		$secretKey = $this->settings->secretKey();

		if ( null === $secretKey || 1 !== preg_match( '/\A[a-z]+_[A-Za-z0-9_]+\z/', $chargeId ) ) {
			return null;
		}

		try {
			$response = $this->client->get(
				$secretKey,
				'/v1/refunds',
				array(
					'charge' => $chargeId,
					'limit'  => 100,
				)
			);
		} catch ( GatewayRequestException ) {
			return null;
		}

		if ( ! isset( $response['data'] ) || ! is_array( $response['data'] ) ) {
			return null;
		}

		$refunds = array();

		foreach ( $response['data'] as $refund ) {
			// A refund that waits for the buyer may not be in the charge's total yet.
			if ( ! is_array( $refund ) || in_array( $refund['status'] ?? '', array( 'failed', 'canceled', 'requires_action' ), true ) ) {
				continue;
			}

			$id     = is_string( $refund['id'] ?? null ) ? trim( $refund['id'] ) : '';
			$amount = (int) ( $refund['amount'] ?? 0 );

			if ( '' === $id || $amount <= 0 ) {
				continue;
			}

			$refunds[] = array(
				'id'     => $id,
				'amount' => $amount,
			);
		}

		return $refunds;
	}
}

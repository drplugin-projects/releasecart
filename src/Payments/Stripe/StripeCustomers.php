<?php
/**
 * The buyer's Stripe Customer for a payment.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Payments\Stripe;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

use DrPlugin\WP\ReleaseCart\Customers\CustomerRepository;
use DrPlugin\WP\ReleaseCart\Orders\Order;
use DrPlugin\WP\ReleaseCart\Orders\OrderPersistenceException;
use DrPlugin\WP\ReleaseCart\Orders\OrderRepository;
use DrPlugin\WP\ReleaseCart\Payments\GatewayRequestException;
use DrPlugin\WP\ReleaseCart\REST\CheckoutController;

/**
 * Finds or creates one Stripe Customer per buyer and mode, and keeps its name,
 * email and billing address current.
 *
 * The Customer id is stored on each order it paid, so the newest order holds it.
 * This class stays non-final so Pro's tests can mock it.
 *
 * @since 1.0.0
 */
class StripeCustomers {

	/**
	 * Order meta key for the Customer id, per Stripe mode.
	 *
	 * @var array<string, string>
	 */
	public const META_KEYS = array(
		'test' => '_releasecart_stripe_customer_test',
		'live' => '_releasecart_stripe_customer_live',
	);

	/**
	 * The metadata type of the order meta table.
	 *
	 * @var string
	 */
	private const ORDER_META_TYPE = 'releasecart_order';

	/**
	 * Address parts as stored on the order, mapped to Stripe's names.
	 *
	 * @var array<string, string>
	 */
	private const ADDRESS_PARTS = array(
		'address_1' => 'line1',
		'address_2' => 'line2',
		'city'      => 'city',
		'state'     => 'state',
		'postcode'  => 'postal_code',
	);

	/**
	 * The longest Customer name Stripe takes, in characters.
	 *
	 * @var int
	 */
	private const NAME_MAX_LENGTH = 256;

	/**
	 * Wire the service.
	 *
	 * @since 1.0.0
	 *
	 * @param StripeApiClient    $client    Transport to the Stripe REST API.
	 * @param StripeSettings     $settings  Stored keys and mode.
	 * @param OrderRepository    $orders    Reads the newest stored Customer id.
	 * @param CustomerRepository $customers Supplies the name when the order has none.
	 */
	public function __construct(
		private readonly StripeApiClient $client,
		private readonly StripeSettings $settings,
		private readonly OrderRepository $orders,
		private readonly CustomerRepository $customers
	) {
	}

	/**
	 * Get the Stripe Customer id for an order's buyer, creating or updating it.
	 *
	 * @since 1.0.0
	 *
	 * @param Order  $order     The pending order.
	 * @param string $secretKey The active mode's secret key.
	 * @return string The Stripe Customer id.
	 * @throws GatewayRequestException When the lookup or a Stripe call fails.
	 */
	public function forOrder( Order $order, string $secretKey ): string {
		$metaKey = self::META_KEYS[ $this->settings->mode() ] ?? self::META_KEYS['test'];
		$details = $this->details( $order );
		$known   = $this->knownId( $order, $metaKey );
		$id      = null !== $known ? $this->update( $known, $details, $order, $secretKey ) : null;

		if ( null === $id ) {
			$id = $this->idFrom( $this->client->post( $secretKey, '/v1/customers', $details, 'releasecart-cus-' . $order->uuid() ) );
		}

		$orderId = $order->id();

		if ( null !== $orderId ) {
			update_metadata( self::ORDER_META_TYPE, $orderId, $metaKey, $id );
		}

		return $id;
	}

	/**
	 * The newest Customer id stored for this buyer in this mode.
	 *
	 * @since 1.0.0
	 *
	 * @param Order  $order   The pending order.
	 * @param string $metaKey The mode's meta key.
	 * @return string|null The id, or null when none is usable.
	 * @throws GatewayRequestException When the lookup faults at the database.
	 */
	private function knownId( Order $order, string $metaKey ): ?string {
		$customerId = $order->customerId();

		if ( null === $customerId ) {
			return null;
		}

		try {
			$id = $this->orders->latestMetaForCustomer( $customerId, $metaKey );
		} catch ( OrderPersistenceException $e ) {
			// A fault must not read as "no Customer yet", or each retry makes a duplicate.
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Not HTML output here; escaped where printed.
			throw new GatewayRequestException( 'Stripe Customer lookup failed: ' . $e->getMessage() );
		}

		// The id goes into a request path, so take only the shape Stripe issues.
		return null !== $id && 1 === preg_match( '/\Acus_[A-Za-z0-9_]+\z/', $id ) ? $id : null;
	}

	/**
	 * Update a known Customer.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $id        The stored Customer id.
	 * @param array<string, mixed> $details   The Customer parameters.
	 * @param Order                $order     The pending order.
	 * @param string               $secretKey The active mode's secret key.
	 * @return string|null The id, or null when Stripe has no such Customer.
	 * @throws GatewayRequestException When the update fails for another reason.
	 */
	private function update( string $id, array $details, Order $order, string $secretKey ): ?string {
		try {
			$response = $this->client->post( $secretKey, '/v1/customers/' . $id, $details, 'releasecart-cus-update-' . $order->uuid() );
		} catch ( GatewayRequestException $e ) {
			// Deleted at Stripe, or the keys now belong to another account.
			if ( 404 === $e->httpStatus() && 'resource_missing' === $e->errorCode() ) {
				return null;
			}

			throw $e;
		}

		return $this->idFrom( $response );
	}

	/**
	 * The Customer parameters: email, then name, address and our customer id when known.
	 *
	 * @since 1.0.0
	 *
	 * @param Order $order The pending order.
	 * @return array<string, mixed> The parameters.
	 */
	private function details( Order $order ): array {
		$params = array( 'email' => $order->email() );
		$name   = $this->name( $order );

		if ( '' !== $name ) {
			// A longer name would fail the payment.
			$params['name'] = mb_substr( $name, 0, self::NAME_MAX_LENGTH );
		}

		$address = $this->address( $order );

		if ( array() !== $address ) {
			$params['address'] = $address;
		}

		$customerId = $order->customerId();

		if ( null !== $customerId ) {
			$params['metadata'] = array( 'releasecart_customer_id' => (string) $customerId );
		}

		return $params;
	}

	/**
	 * The name typed at this checkout, else the customer record's name.
	 *
	 * @since 1.0.0
	 *
	 * @param Order $order The pending order.
	 * @return string The name, or an empty string.
	 */
	private function name( Order $order ): string {
		$name = trim( $this->orderMeta( $order, CheckoutController::FIRST_NAME_META ) . ' ' . $this->orderMeta( $order, CheckoutController::LAST_NAME_META ) );

		if ( '' !== $name ) {
			return $name;
		}

		$customerId = $order->customerId();
		$customer   = null !== $customerId ? $this->customers->find( $customerId ) : null;

		if ( null === $customer ) {
			return '';
		}

		$recorded = trim( (string) $customer->name() );

		return '' !== $recorded ? $recorded : trim( $customer->firstName() . ' ' . $customer->lastName() );
	}

	/**
	 * The billing address parts this checkout collected, plus the order country.
	 *
	 * @since 1.0.0
	 *
	 * @param Order $order The pending order.
	 * @return array<string, string> Stripe address parameters.
	 */
	private function address( Order $order ): array {
		$orderId = $order->id();
		$stored  = null !== $orderId ? get_metadata( self::ORDER_META_TYPE, $orderId, CheckoutController::ADDRESS_META, true ) : array();
		$stored  = is_array( $stored ) ? $stored : array();
		$address = array();

		foreach ( self::ADDRESS_PARTS as $part => $stripeName ) {
			$value = isset( $stored[ $part ] ) ? trim( (string) $stored[ $part ] ) : '';

			if ( '' !== $value ) {
				$address[ $stripeName ] = $value;
			}
		}

		$country = trim( (string) $order->country() );

		if ( '' !== $country ) {
			$address['country'] = $country;
		}

		return $address;
	}

	/**
	 * Read a single string order meta value.
	 *
	 * @since 1.0.0
	 *
	 * @param Order  $order   The order.
	 * @param string $metaKey The meta key.
	 * @return string The trimmed value, or an empty string.
	 */
	private function orderMeta( Order $order, string $metaKey ): string {
		$orderId = $order->id();
		$value   = null !== $orderId ? get_metadata( self::ORDER_META_TYPE, $orderId, $metaKey, true ) : '';

		return is_string( $value ) ? trim( $value ) : '';
	}

	/**
	 * Take the Customer id from a Stripe response.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $response The decoded response.
	 * @return string The id.
	 * @throws GatewayRequestException When the response has no id.
	 */
	private function idFrom( array $response ): string {
		$id = $response['id'] ?? null;

		if ( ! is_string( $id ) || '' === $id ) {
			throw new GatewayRequestException( 'Stripe Customer response is missing its id.' );
		}

		return $id;
	}
}

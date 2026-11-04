<?php
/**
 * The charge description sent with Stripe payments.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Payments\Stripe;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

use DrPlugin\WP\ReleaseCart\Orders\Order;

/**
 * Builds the description Stripe shows for a payment, such as
 * "Order 1042: Acme Forms (Agency)".
 *
 * A Stripe account in India refuses export payments without one.
 *
 * @since 1.0.0
 */
final class StripeDescription {

	/**
	 * The longest description sent, in characters. Our own limit.
	 *
	 * @var int
	 */
	public const MAX_LENGTH = 500;

	/**
	 * Label a product and its price tier.
	 *
	 * @since 1.0.0
	 *
	 * @param string      $productName The product name.
	 * @param string|null $tierName    The price tier name, if any.
	 * @return string The product name, with the tier in brackets when there is one.
	 */
	public static function itemLabel( string $productName, ?string $tierName ): string {
		$productName = trim( $productName );
		$tierName    = trim( (string) $tierName );

		if ( '' === $tierName ) {
			return $productName;
		}

		/* translators: 1: a primary label, 2: a secondary detail shown in parentheses. */
		return sprintf( __( '%1$s (%2$s)', 'releasecart' ), $productName, $tierName );
	}

	/**
	 * Describe an order for its Stripe payment.
	 *
	 * @since 1.0.0
	 *
	 * @param Order $order The order being paid.
	 * @return string The description, never empty.
	 */
	public static function forOrder( Order $order ): string {
		$labels = array();

		foreach ( $order->items() as $item ) {
			$label = self::itemLabel( $item->productName(), $item->priceName() );

			if ( $item->quantity() > 1 ) {
				/* translators: 1: quantity, 2: product and tier label. */
				$label = sprintf( __( '%1$d x %2$s', 'releasecart' ), $item->quantity(), $label );
			}

			$labels[] = $label;
		}

		$default = self::bound(
			sprintf(
				/* translators: 1: order number, 2: list of products. */
				__( 'Order %1$s: %2$s', 'releasecart' ),
				(string) $order->id(),
				wp_sprintf( '%l', $labels )
			)
		);

		/**
		 * Filters the description sent to Stripe with a payment.
		 *
		 * A blank or non-string value is ignored.
		 *
		 * @since 1.0.0
		 *
		 * @param string $description The default description.
		 * @param Order  $order       The order being paid.
		 */
		$filtered = apply_filters( 'releasecart_stripe_payment_description', $default, $order );

		if ( ! is_string( $filtered ) || '' === trim( $filtered ) ) {
			return $default;
		}

		return self::bound( trim( $filtered ) );
	}

	/**
	 * Cut a description to the length limit, in characters.
	 *
	 * WordPress defines mb_substr() when mbstring is missing.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value The description.
	 * @return string The bounded description.
	 */
	private static function bound( string $value ): string {
		return mb_substr( $value, 0, self::MAX_LENGTH );
	}
}

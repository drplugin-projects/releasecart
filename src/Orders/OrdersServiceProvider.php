<?php
/**
 * Service provider for the Orders domain (cart, orders, refunds).
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Orders;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

use DrPlugin\WP\ReleaseCart\Core\Container;
use DrPlugin\WP\ReleaseCart\Core\ServiceProvider;
use DrPlugin\WP\ReleaseCart\Customers\CustomerService;
use DrPlugin\WP\ReleaseCart\Database\Logger;
use DrPlugin\WP\ReleaseCart\Products\ProductService;

/**
 * Registers cart, order, refund and state services.
 * The repository resolves `$wpdb` only when the container creates it.
 *
 * @since 1.0.0
 */
final class OrdersServiceProvider implements ServiceProvider {

	/**
	 * Bind the cart, order, and refund services and their dependencies.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $container The shared plugin container.
	 * @return void
	 */
	public function register( Container $container ): void {
		$container->singleton(
			OrderStateMachine::class,
			static fn (): OrderStateMachine => new OrderStateMachine()
		);

		$container->singleton(
			CartRepository::class,
			static fn (): CartRepository => new CartRepository()
		);

		$container->singleton(
			OrderRepository::class,
			static function ( Container $c ): OrderRepository {
				global $wpdb;

				return new OrderRepository( $wpdb, $c->get( Logger::class ) );
			}
		);

		$container->singleton(
			CartService::class,
			static fn ( Container $c ): CartService => new CartService(
				$c->get( CartRepository::class ),
				$c->get( ProductService::class )
			)
		);

		$container->singleton(
			OrderService::class,
			static fn ( Container $c ): OrderService => new OrderService(
				$c->get( OrderRepository::class ),
				$c->get( CustomerService::class ),
				$c->get( ProductService::class ),
				$c->get( CartService::class ),
				$c->get( OrderStateMachine::class ),
				// Allow lean test containers without the Discounts provider.
				$c->has( \DrPlugin\WP\ReleaseCart\Discounts\DiscountService::class )
					? $c->get( \DrPlugin\WP\ReleaseCart\Discounts\DiscountService::class )
					: null,
				$c->has( \DrPlugin\WP\ReleaseCart\Discounts\AutomaticDiscountResolver::class )
					? $c->get( \DrPlugin\WP\ReleaseCart\Discounts\AutomaticDiscountResolver::class )
					: null,
				$c->has( \DrPlugin\WP\ReleaseCart\Discounts\DiscountSettings::class )
					? $c->get( \DrPlugin\WP\ReleaseCart\Discounts\DiscountSettings::class )
					: null,
				$c->has( \DrPlugin\WP\ReleaseCart\Database\Logger::class )
					? $c->get( \DrPlugin\WP\ReleaseCart\Database\Logger::class )
					: null
			)
		);

		$container->singleton(
			RefundService::class,
			static fn ( Container $c ): RefundService => new RefundService(
				$c->get( OrderRepository::class ),
				$c->get( OrderStateMachine::class )
			)
		);
	}

	/**
	 * Services fire order hooks. The REST provider registers the controller.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $container The shared plugin container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		// No Hookables to resolve or wire.
	}
}

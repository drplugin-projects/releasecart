<?php
/**
 * Service provider for the Payments layer.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Payments;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

use DrPlugin\WP\ReleaseCart\Core\Container;
use DrPlugin\WP\ReleaseCart\Core\ServiceProvider;
use DrPlugin\WP\ReleaseCart\Customers\CustomerRepository;
use DrPlugin\WP\ReleaseCart\Database\Logger;
use DrPlugin\WP\ReleaseCart\Orders\OrderRepository;
use DrPlugin\WP\ReleaseCart\Orders\OrderService;
use DrPlugin\WP\ReleaseCart\Orders\RefundService;
use DrPlugin\WP\ReleaseCart\Payments\Stripe\StripeAccountLookup;
use DrPlugin\WP\ReleaseCart\Payments\Stripe\StripeApiClient;
use DrPlugin\WP\ReleaseCart\Payments\Stripe\StripeCustomers;
use DrPlugin\WP\ReleaseCart\Payments\Stripe\StripeEventHandlers;
use DrPlugin\WP\ReleaseCart\Payments\Stripe\StripeExportRequirement;
use DrPlugin\WP\ReleaseCart\Payments\Stripe\StripeGateway;
use DrPlugin\WP\ReleaseCart\Payments\Stripe\StripeRefunds;
use DrPlugin\WP\ReleaseCart\Payments\Stripe\StripeSettings;
use DrPlugin\WP\ReleaseCart\Payments\Stripe\StripeWebhookVerifier;
use DrPlugin\WP\ReleaseCart\Util\Crypto;
use DrPlugin\WP\ReleaseCart\Util\RateLimiter;

/**
 * Registers the payment services before REST controllers resolve them.
 *
 * @since 1.0.0
 */
final class PaymentsServiceProvider implements ServiceProvider {

	/**
	 * Bind the payments services as lazy singletons.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $container The shared plugin container.
	 * @return void
	 */
	public function register( Container $container ): void {
		$container->singleton( Crypto::class, static fn (): Crypto => new Crypto() );

		$container->singleton( RateLimiter::class, static fn (): RateLimiter => new RateLimiter() );

		$container->singleton(
			StripeSettings::class,
			static fn ( Container $c ): StripeSettings => new StripeSettings( $c->get( Crypto::class ) )
		);

		$container->singleton( StripeApiClient::class, static fn (): StripeApiClient => new StripeApiClient() );

		$container->singleton(
			StripeWebhookVerifier::class,
			static fn (): StripeWebhookVerifier => new StripeWebhookVerifier()
		);

		$container->singleton(
			StripeCustomers::class,
			static fn ( Container $c ): StripeCustomers => new StripeCustomers(
				$c->get( StripeApiClient::class ),
				$c->get( StripeSettings::class ),
				$c->get( OrderRepository::class ),
				$c->get( CustomerRepository::class )
			)
		);

		$container->singleton(
			StripeAccountLookup::class,
			static fn ( Container $c ): StripeAccountLookup => new StripeAccountLookup(
				$c->get( StripeApiClient::class ),
				$c->get( StripeSettings::class )
			)
		);

		$container->singleton(
			StripeRefunds::class,
			static fn ( Container $c ): StripeRefunds => new StripeRefunds(
				$c->get( StripeApiClient::class ),
				$c->get( StripeSettings::class )
			)
		);

		$container->singleton(
			StripeExportRequirement::class,
			static fn ( Container $c ): StripeExportRequirement => new StripeExportRequirement( $c->get( StripeSettings::class ) )
		);

		$container->singleton(
			StripeGateway::class,
			static fn ( Container $c ): StripeGateway => new StripeGateway(
				$c->get( StripeApiClient::class ),
				$c->get( StripeSettings::class ),
				$c->get( StripeWebhookVerifier::class ),
				$c->get( StripeCustomers::class )
			)
		);

		$container->singleton(
			GatewayRegistry::class,
			static fn ( Container $c ): GatewayRegistry => new GatewayRegistry(
				array( 'stripe' => $c->get( StripeGateway::class ) )
			)
		);

		$container->singleton(
			GatewayEventRepository::class,
			static function (): GatewayEventRepository {
				global $wpdb;

				return new GatewayEventRepository( $wpdb );
			}
		);

		$container->singleton(
			RefundLock::class,
			static function (): RefundLock {
				global $wpdb;

				return new RefundLock( $wpdb );
			}
		);

		$container->singleton(
			RefundCoordinator::class,
			static fn ( Container $c ): RefundCoordinator => new RefundCoordinator(
				$c->get( OrderRepository::class ),
				$c->get( RefundService::class ),
				$c->get( GatewayRegistry::class ),
				$c->get( RefundLock::class )
			)
		);

		$container->singleton(
			StripeEventHandlers::class,
			static fn ( Container $c ): StripeEventHandlers => new StripeEventHandlers(
				$c->get( OrderService::class ),
				$c->get( RefundCoordinator::class ),
				$c->get( StripeRefunds::class ),
				$c->get( Logger::class )
			)
		);

		$container->singleton(
			WebhookProcessor::class,
			static fn ( Container $c ): WebhookProcessor => new WebhookProcessor(
				$c->get( GatewayRegistry::class ),
				$c->get( GatewayEventRepository::class ),
				// Resolve extension handlers after all providers register.
				array( 'stripe' => $c->get( StripeEventHandlers::class )->handlers() )
			)
		);

		$container->singleton(
			CheckoutService::class,
			static fn ( Container $c ): CheckoutService => new CheckoutService(
				$c->get( OrderService::class ),
				$c->get( GatewayRegistry::class )
			)
		);
	}

	/**
	 * Hook the Stripe checkout field rule.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $container The shared plugin container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		$container->get( StripeExportRequirement::class )->hooks();
	}
}

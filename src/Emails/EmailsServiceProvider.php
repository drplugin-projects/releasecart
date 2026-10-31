<?php
/**
 * Service provider for the Emails domain.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Emails;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

use DrPlugin\WP\ReleaseCart\Core\Container;
use DrPlugin\WP\ReleaseCart\Core\ServiceProvider;
use DrPlugin\WP\ReleaseCart\Database\Logger;
use DrPlugin\WP\ReleaseCart\Licensing\LicenseService;
use DrPlugin\WP\ReleaseCart\Orders\OrderService;
use DrPlugin\WP\ReleaseCart\Products\ProductService;
use DrPlugin\WP\ReleaseCart\Updates\CustomerDownloads;
use DrPlugin\WP\ReleaseCart\Util\Money;

/**
 * Registers email services and their event listener.
 *
 * @since 1.0.0
 */
final class EmailsServiceProvider implements ServiceProvider {

	/**
	 * Bind the email settings, renderer, emails, registry, mailer, and listener.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $container The shared plugin container.
	 * @return void
	 */
	public function register( Container $container ): void {
		$container->singleton(
			EmailSettings::class,
			static fn (): EmailSettings => new EmailSettings()
		);

		$container->singleton(
			EmailRenderer::class,
			static fn (): EmailRenderer => new EmailRenderer( RELEASECART_PATH . 'templates/emails/' )
		);

		$container->singleton(
			PurchaseReceiptEmail::class,
			static fn ( Container $c ): PurchaseReceiptEmail => new PurchaseReceiptEmail( $c->get( EmailSettings::class ) )
		);

		$container->singleton(
			LicenseDeliveryEmail::class,
			static fn ( Container $c ): LicenseDeliveryEmail => new LicenseDeliveryEmail( $c->get( EmailSettings::class ) )
		);

		$container->singleton(
			EmailRegistry::class,
			static fn ( Container $c ): EmailRegistry => new EmailRegistry(
				array(
					$c->get( PurchaseReceiptEmail::class ),
					$c->get( LicenseDeliveryEmail::class ),
				)
			)
		);

		$container->singleton(
			Mailer::class,
			static fn ( Container $c ): Mailer => new Mailer(
				$c->get( EmailRenderer::class ),
				$c->get( EmailSettings::class ),
				$c->get( Logger::class )
			)
		);

		$container->singleton(
			EmailListener::class,
			static fn ( Container $c ): EmailListener => new EmailListener(
				$c->get( EmailRegistry::class ),
				$c->get( Mailer::class ),
				$c->get( OrderService::class ),
				$c->get( LicenseService::class ),
				$c->get( ProductService::class ),
				$c->get( CustomerDownloads::class ),
				$c->get( Money::class ),
				$c->get( Logger::class )
			)
		);
	}

	/**
	 * Attach the email layer to order and license events.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $container The shared plugin container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		$container->get( EmailListener::class )->hooks();
	}
}

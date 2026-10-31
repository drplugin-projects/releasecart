<?php
/**
 * Service provider for the Privacy (GDPR) domain.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Privacy;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

use DrPlugin\WP\ReleaseCart\Core\Container;
use DrPlugin\WP\ReleaseCart\Core\ServiceProvider;
use DrPlugin\WP\ReleaseCart\Customers\CustomerRepository;
use DrPlugin\WP\ReleaseCart\Database\LogRepository;
use DrPlugin\WP\ReleaseCart\Licensing\ActivationRepository;
use DrPlugin\WP\ReleaseCart\Licensing\LicenseRepository;
use DrPlugin\WP\ReleaseCart\Licensing\LicenseService;
use DrPlugin\WP\ReleaseCart\Orders\OrderRepository;
use DrPlugin\WP\ReleaseCart\Payments\GatewayEventRepository;

/**
 * Register privacy services as container singletons.
 *
 * @since 1.0.0
 */
final class PrivacyServiceProvider implements ServiceProvider {

	/**
	 * Bind the exporter, eraser, and registrar.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $container The shared plugin container.
	 * @return void
	 */
	public function register( Container $container ): void {
		$container->singleton(
			PersonalDataExporter::class,
			static fn ( Container $c ): PersonalDataExporter => new PersonalDataExporter(
				$c->get( CustomerRepository::class ),
				$c->get( OrderRepository::class ),
				$c->get( LicenseRepository::class ),
				$c->get( ActivationRepository::class ),
				$c->get( LogRepository::class )
			)
		);

		$container->singleton(
			PersonalDataEraser::class,
			static fn ( Container $c ): PersonalDataEraser => new PersonalDataEraser(
				$c->get( CustomerRepository::class ),
				$c->get( OrderRepository::class ),
				$c->get( LicenseRepository::class ),
				$c->get( ActivationRepository::class ),
				$c->get( LogRepository::class ),
				$c->get( LicenseService::class ),
				$c->get( GatewayEventRepository::class )
			)
		);

		$container->singleton(
			PrivacyRegistrar::class,
			static fn ( Container $c ): PrivacyRegistrar => new PrivacyRegistrar(
				$c->get( PersonalDataExporter::class ),
				$c->get( PersonalDataEraser::class )
			)
		);
	}

	/**
	 * Register the privacy hooks once the plugin has booted.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $container The shared plugin container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		$container->get( PrivacyRegistrar::class )->hooks();
	}
}

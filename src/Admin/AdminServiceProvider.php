<?php
/**
 * Service provider for the wp-admin surface.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Admin;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

use DrPlugin\WP\ReleaseCart\Core\Container;
use DrPlugin\WP\ReleaseCart\Core\ServiceProvider;
use DrPlugin\WP\ReleaseCart\Frontend\Appearance;
use DrPlugin\WP\ReleaseCart\Frontend\CheckoutFieldSettings;
use DrPlugin\WP\ReleaseCart\Payments\Stripe\StripeSettings;
use DrPlugin\WP\ReleaseCart\Upsell\Dismissals;
use DrPlugin\WP\ReleaseCart\Upsell\ProFeatures;
use DrPlugin\WP\ReleaseCart\Upsell\ProStatus;
use DrPlugin\WP\ReleaseCart\Webhooks\WebhookEndpoints;

/**
 * Registers the ReleaseCart admin menu, SPA and settings REST services.
 * It runs after the REST provider so its dependencies are available.
 *
 * @since 1.0.0
 */
final class AdminServiceProvider implements ServiceProvider {

	/**
	 * Bind the admin services.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $container The shared plugin container.
	 * @return void
	 */
	public function register( Container $container ): void {
		$container->singleton(
			SettingsSchema::class,
			static fn ( Container $c ): SettingsSchema => new SettingsSchema( $c->get( CheckoutFieldSettings::class ) )
		);
		$container->singleton(
			SettingsController::class,
			static fn ( Container $c ): SettingsController => new SettingsController(
				$c->get( SettingsSchema::class ),
				$c->get( StripeSettings::class ),
				$c->get( WebhookEndpoints::class ),
				$c->get( Appearance::class )
			)
		);
		$container->singleton(
			SpaController::class,
			static fn ( Container $c ): SpaController => new SpaController(
				$c->get( ProStatus::class ),
				$c->get( ProFeatures::class ),
				$c->get( Dismissals::class )
			)
		);
		$container->singleton(
			Menu::class,
			static fn ( Container $c ): Menu => new Menu( $c->get( SpaController::class ) )
		);
	}

	/**
	 * Wire the admin hooks.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $container The shared plugin container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		$container->get( SettingsController::class )->hooks();
		$container->get( SpaController::class )->hooks();
		$container->get( Menu::class )->hooks();
	}
}

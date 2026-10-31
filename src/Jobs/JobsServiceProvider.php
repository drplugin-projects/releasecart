<?php
/**
 * Service provider for cross-cutting scheduled maintenance jobs.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Jobs;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

use DrPlugin\WP\ReleaseCart\Core\Container;
use DrPlugin\WP\ReleaseCart\Core\ServiceProvider;
use DrPlugin\WP\ReleaseCart\Database\Logger;
use DrPlugin\WP\ReleaseCart\Orders\OrderService;

/**
 * Registers cross-domain maintenance jobs.
 *
 * Registration makes no WordPress calls. Boot attaches the job hooks.
 *
 * @since 1.0.0
 */
final class JobsServiceProvider implements ServiceProvider {

	/**
	 * Bind the maintenance jobs as container singletons.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $container The shared plugin container.
	 * @return void
	 */
	public function register( Container $container ): void {
		$container->singleton(
			ActionSchedulerPruner::class,
			static fn ( Container $c ): ActionSchedulerPruner => new ActionSchedulerPruner(
				$c->get( Logger::class )
			)
		);

		$container->singleton(
			AbandonedOrdersSweep::class,
			static fn ( Container $c ): AbandonedOrdersSweep => new AbandonedOrdersSweep(
				$c->get( OrderService::class ),
				$c->get( Logger::class )
			)
		);
	}

	/**
	 * Attach the maintenance jobs to WordPress (their self-scheduling init hook
	 * and job handlers).
	 *
	 * @since 1.0.0
	 *
	 * @param Container $container The shared plugin container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		$container->get( ActionSchedulerPruner::class )->hooks();
		$container->get( AbandonedOrdersSweep::class )->hooks();
	}
}

<?php
/**
 * Service provider for the file storage abstraction.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Storage;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

use DrPlugin\WP\ReleaseCart\Core\Container;
use DrPlugin\WP\ReleaseCart\Core\ServiceProvider;

/**
 * Registers local storage and its manager.
 * Provider filters run on the first lookup.
 *
 * @since 1.0.0
 */
final class StorageServiceProvider implements ServiceProvider {

	/**
	 * Bind LocalStorage and StorageManager as container singletons.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $container The shared plugin container.
	 * @return void
	 */
	public function register( Container $container ): void {
		$container->singleton(
			LocalStorage::class,
			static fn (): LocalStorage => new LocalStorage()
		);

		$container->singleton(
			StorageManager::class,
			static fn ( Container $c ): StorageManager => new StorageManager( $c->get( LocalStorage::class ) )
		);
	}

	/**
	 * StorageManager loads providers on first use.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $container The shared plugin container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		// Intentionally empty; see the class docblock.
	}
}

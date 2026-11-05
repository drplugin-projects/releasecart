<?php
/**
 * Service provider for the database layer (migrations runner).
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Database;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

use DrPlugin\WP\ReleaseCart\Core\Container;
use DrPlugin\WP\ReleaseCart\Core\ServiceProvider;
use DrPlugin\WP\ReleaseCart\Core\VersionUpgrade;
use DrPlugin\WP\ReleaseCart\Database\Migrations\Migration_0001_CreateCoreTables;
use DrPlugin\WP\ReleaseCart\Database\Migrations\Migration_0002_AddReportIndexes;
use DrPlugin\WP\ReleaseCart\Database\Migrations\Migration_0004_AddOrderMetaKeyOrderIndex;

/**
 * Registers database services, runs pending migrations, then the plugin version check.
 *
 * The boot-time checks cover updates, which skip plugin activation.
 *
 * @since 1.0.0
 */
final class DatabaseServiceProvider implements ServiceProvider {

	/**
	 * Bind the migrations runner and the audit logger.
	 *
	 * Services resolve `$wpdb` only when they are needed.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $container The shared plugin container.
	 * @return void
	 */
	public function register( Container $container ): void {
		$container->singleton(
			Migrator::class,
			static function (): Migrator {
				global $wpdb;

				return new Migrator( $wpdb, self::migrations() );
			}
		);

		$container->singleton(
			Logger::class,
			static function (): Logger {
				global $wpdb;

				return new Logger( $wpdb );
			}
		);

		$container->singleton(
			LogRepository::class,
			static function (): LogRepository {
				global $wpdb;

				return new LogRepository( $wpdb );
			}
		);
	}

	/**
	 * Apply pending migrations and update steps once the plugin has booted.
	 *
	 * Maps metadata tables, runs pending migrations, then the version check.
	 *
	 * @since 1.0.0
	 * @since 1.0.1 Runs the plugin version check.
	 *
	 * @param Container $container The shared plugin container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		global $wpdb;

		// Map request-local metadata tables before metadata calls.
		MetaTables::register( $wpdb );

		$container->get( Migrator::class )->migrate();

		// Runs on plugins_loaded, so before any request can write a package.
		$container->get( VersionUpgrade::class )->maybeUpgrade();
	}

	/**
	 * The ordered set of migrations shipped by the core.
	 *
	 * The {@see Migrator} sorts this set by version. Numbers 3, 5, 6, 7 and 8 are
	 * retired. A database that ran them stores 5 or 8, so a new migration takes 9 or higher.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, Migration> The registered migrations.
	 */
	private static function migrations(): array {
		return array(
			new Migration_0001_CreateCoreTables(),
			new Migration_0002_AddReportIndexes(),
			new Migration_0004_AddOrderMetaKeyOrderIndex(),
		);
	}
}

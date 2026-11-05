<?php
/**
 * Plugin version check.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Core;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

use DrPlugin\WP\ReleaseCart\Storage\LocalStorage;

/**
 * Runs one-time update steps when the stored plugin version is older than the code.
 *
 * A plugin update skips the activation hook, so this runs on load. A step that fails
 * is not retried: the version is stored anyway.
 *
 * @since 1.0.1
 */
final class VersionUpgrade {

	/**
	 * The option that stores the plugin version. Autoloaded, as it is read on every request.
	 *
	 * @since 1.0.1
	 *
	 * @var string
	 */
	public const VERSION_OPTION = 'releasecart_version';

	/**
	 * Local package storage, for the web.config step.
	 *
	 * @var LocalStorage
	 */
	private LocalStorage $storage;

	/**
	 * The plugin version of this code.
	 *
	 * @var string
	 */
	private string $version;

	/**
	 * Wire the version check.
	 *
	 * @since 1.0.1
	 *
	 * @param LocalStorage $storage Local package storage.
	 * @param string       $version The plugin version of this code.
	 */
	public function __construct( LocalStorage $storage, string $version ) {
		$this->storage = $storage;
		$this->version = $version;
	}

	/**
	 * Run the update steps when the stored version is missing or older.
	 *
	 * @since 1.0.1
	 *
	 * @return void
	 */
	public function maybeUpgrade(): void {
		$stored = (string) get_option( self::VERSION_OPTION, '' );

		if ( version_compare( $stored, $this->version, '<' ) ) {
			$this->upgrade();
		}
	}

	/**
	 * Run the update steps and store the plugin version.
	 *
	 * @since 1.0.1
	 *
	 * @return void
	 */
	public function upgrade(): void {
		$this->storage->removeLegacyWebConfig();

		update_option( self::VERSION_OPTION, $this->version, true );
	}
}

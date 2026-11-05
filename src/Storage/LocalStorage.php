<?php
/**
 * Local filesystem storage provider.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Storage;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

/**
 * Stores protected package files below the uploads directory.
 * Keys cannot resolve outside this root. Local files stream directly.
 *
 * @since 1.0.0
 */
final class LocalStorage implements StorageProvider {

	/**
	 * Apache guard file contents.
	 *
	 * @var string
	 */
	private const HTACCESS_CONTENTS = <<<'HTACCESS'
# Deny direct access to ReleaseCart package files. Downloads go only
# through ReleaseCart's signed download links.
<IfModule mod_authz_core.c>
	Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
	Deny from all
</IfModule>
HTACCESS;

	/**
	 * The web.config that version 1.0.0 wrote. Used only to find that file and delete it.
	 *
	 * @since 1.0.1
	 *
	 * @var string
	 */
	private const LEGACY_WEB_CONFIG = <<<'WEBCONFIG'
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
	<system.webServer>
		<authorization>
			<deny users="*" />
		</authorization>
	</system.webServer>
</configuration>
WEBCONFIG;

	/**
	 * Listing guard contents. The guard is an empty index.html, so no PHP file goes into uploads.
	 *
	 * @var string
	 */
	private const INDEX_HTML_CONTENTS = '';

	/**
	 * Guard-file names blocked at the packages root. A package never takes the name web.config.
	 *
	 * @var array<int, string>
	 */
	private const RESERVED_GUARD_FILES = array( '.htaccess', 'web.config', 'index.html' );

	/**
	 * Cached packages root.
	 *
	 * @var string|null
	 */
	private ?string $root = null;

	/**
	 * Unique provider identifier stored alongside file references.
	 *
	 * @since 1.0.0
	 *
	 * @return string Always 'local'.
	 */
	public function id(): string {
		return 'local';
	}

	/**
	 * Whether a stored object exists for the given key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Provider-scoped object key.
	 * @return bool True when the key resolves to a readable file under the packages root.
	 */
	public function exists( string $key ): bool {
		$path = $this->root() . '/' . $this->normalizeKey( $key );

		return is_file( $path ) && is_readable( $path );
	}

	/**
	 * Copy an uploaded file into the packages root and return its storage reference.
	 *
	 * @since 1.0.0
	 *
	 * @param string $sourcePath Absolute path of the uploaded temp file.
	 * @param string $key        Desired provider-scoped key.
	 * @return StoredFile Reference DTO: provider id, final key, filesize, sha256.
	 *
	 * @throws StorageException When the source is unreadable, the key is invalid, WordPress
	 *                          cannot write files directly, or the write fails.
	 */
	public function store( string $sourcePath, string $key ): StoredFile {
		if ( ! is_file( $sourcePath ) || ! is_readable( $sourcePath ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Not HTML output here; escaped where printed.
			throw StorageException::unreadableSource( $sourcePath );
		}

		$normalizedKey = $this->normalizeKey( $key );
		$filesystem    = $this->directFilesystem();

		// Checked before any folder or file is written.
		if ( null === $filesystem ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Not HTML output here; escaped where printed.
			throw StorageException::filesystemUnavailable();
		}

		$this->ensureRoot( $filesystem );

		$targetPath = $this->root() . '/' . $normalizedKey;

		if ( ! wp_mkdir_p( dirname( $targetPath ) ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Not HTML output here; escaped where printed.
			throw StorageException::writeFailed( $normalizedKey, 'could not create the destination directory' );
		}

		if ( ! $filesystem->copy( $sourcePath, $targetPath, true ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Not HTML output here; escaped where printed.
			throw StorageException::writeFailed( $normalizedKey, 'copy to the packages directory failed' );
		}

		$this->guardFolders( $filesystem, dirname( $normalizedKey ) );

		$filesize = filesize( $targetPath );
		$sha256   = hash_file( 'sha256', $targetPath );

		if ( false === $filesize || false === $sha256 ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Not HTML output here; escaped where printed.
			throw StorageException::writeFailed( $normalizedKey, 'could not read back the stored file to checksum it' );
		}

		return new StoredFile( $this->id(), $normalizedKey, $filesize, $sha256 );
	}

	/**
	 * Delete a stored object. Deleting a missing key is a no-op.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Provider-scoped object key.
	 * @return void
	 */
	public function delete( string $key ): void {
		$path = $this->root() . '/' . $this->normalizeKey( $key );

		if ( is_file( $path ) ) {
			wp_delete_file( $path );
		}
	}

	/**
	 * Stream the object to the current HTTP response.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Provider-scoped object key.
	 * @return bool True once the file has been streamed; false when the key does not exist.
	 */
	public function stream( string $key ): bool {
		$path = $this->root() . '/' . $this->normalizeKey( $key );

		if ( ! is_file( $path ) || ! is_readable( $path ) ) {
			return false;
		}

		if ( ! headers_sent() ) {
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Length: ' . (string) filesize( $path ) );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- Streams the package to the response; WP_Filesystem has no streaming read.
		readfile( $path );

		return true;
	}

	/**
	 * A short-lived URL the client can be redirected to.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key        Provider-scoped object key.
	 * @param int    $ttlSeconds Requested URL lifetime.
	 * @return string|null Always null: local storage always streams, it never issues a retrieval URL.
	 */
	public function retrievalUrl( string $key, int $ttlSeconds ): ?string {
		return null;
	}

	/**
	 * Delete the web.config that version 1.0.0 wrote into the packages root.
	 *
	 * Only a byte-for-byte match goes. Any other web.config is the site owner's.
	 *
	 * @since 1.0.1
	 *
	 * @return void
	 */
	public function removeLegacyWebConfig(): void {
		// Not root(): this runs on plugins_loaded, before a theme can filter upload_dir.
		$path = $this->packagesRoot() . '/web.config';

		if ( ! is_file( $path ) ) {
			return;
		}

		$filesystem = $this->directFilesystem();

		if ( null !== $filesystem && self::LEGACY_WEB_CONFIG === $filesystem->get_contents( $path ) ) {
			wp_delete_file( $path );
		}
	}

	/**
	 * Resolve and cache the packages root.
	 *
	 * @since 1.0.0
	 *
	 * @return string Absolute path of the packages root, no trailing slash.
	 */
	private function root(): string {
		if ( null === $this->root ) {
			$this->root = $this->packagesRoot();
		}

		return $this->root;
	}

	/**
	 * The packages root as uploads resolves it now. Creates no folder.
	 *
	 * @since 1.0.1
	 *
	 * @return string Absolute path of the packages root, no trailing slash.
	 */
	private function packagesRoot(): string {
		$uploadDir = wp_upload_dir( null, false );

		return rtrim( (string) $uploadDir['basedir'], '/\\' ) . '/releasecart/packages';
	}

	/**
	 * Create the packages root and its guard files.
	 *
	 * Also deletes the 1.0.0 web.config, in case the update step could not.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Filesystem_Base $filesystem The direct filesystem.
	 * @return void
	 */
	private function ensureRoot( \WP_Filesystem_Base $filesystem ): void {
		$root = $this->root();

		wp_mkdir_p( $root );

		$this->writeGuardFileIfMissing( $filesystem, $root . '/.htaccess', self::HTACCESS_CONTENTS );
		$this->writeGuardFileIfMissing( $filesystem, $root . '/index.html', self::INDEX_HTML_CONTENTS );
		$this->removeLegacyWebConfig();
	}

	/**
	 * Put an index.html in each folder of a key's path below the root.
	 *
	 * The root's .htaccess covers these folders on Apache.
	 * The index.html stops a folder listing on other servers.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Filesystem_Base $filesystem The direct filesystem.
	 * @param string              $folder     Normalized folder part of the key, or "." for none.
	 * @return void
	 */
	private function guardFolders( \WP_Filesystem_Base $filesystem, string $folder ): void {
		while ( '.' !== $folder && '' !== $folder ) {
			$this->writeGuardFileIfMissing( $filesystem, $this->root() . '/' . $folder . '/index.html', self::INDEX_HTML_CONTENTS );
			$folder = dirname( $folder );
		}
	}

	/**
	 * Write a protected-directory guard file, but only if it is not already there.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Filesystem_Base $filesystem The direct filesystem.
	 * @param string              $path       Absolute path of the guard file.
	 * @param string              $contents   Contents to write when the file is missing.
	 * @return void
	 */
	private function writeGuardFileIfMissing( \WP_Filesystem_Base $filesystem, string $path, string $contents ): void {
		if ( file_exists( $path ) ) {
			return;
		}

		$filesystem->put_contents( $path, $contents );
	}

	/**
	 * Get the direct WP_Filesystem, or null when WordPress would use another method.
	 *
	 * An instance WordPress already set up is reused only when it is direct, so an
	 * FTP instance in use elsewhere in the request is never replaced. Another method
	 * needs credentials, and a REST upload cannot ask for them.
	 *
	 * @since 1.0.0
	 *
	 * @return \WP_Filesystem_Base|null
	 */
	private function directFilesystem(): ?\WP_Filesystem_Base {
		global $wp_filesystem;

		if ( $wp_filesystem instanceof \WP_Filesystem_Base ) {
			return 'direct' === $wp_filesystem->method ? $wp_filesystem : null;
		}

		if ( ! function_exists( 'get_filesystem_method' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		// WordPress picks the method by writing a test file in this folder, so it
		// must exist. The packages root does not, before the first upload.
		$context = $this->packagesRoot();

		while ( ! is_dir( $context ) && dirname( $context ) !== $context ) {
			$context = dirname( $context );
		}

		// PHP writes the uploads folder itself, so file ownership is not checked.
		if ( 'direct' !== get_filesystem_method( array(), $context, true ) || ! WP_Filesystem( false, $context, true ) ) {
			return null;
		}

		return $wp_filesystem instanceof \WP_Filesystem_Base ? $wp_filesystem : null;
	}

	/**
	 * Normalize a key and keep it below the packages root.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Provider-scoped object key as supplied by the caller.
	 * @return string The normalized key, safe to append to the packages root.
	 *
	 * @throws StorageException When the key is empty, absolute, contains a null
	 *                           byte, attempts to traverse above the root, or targets
	 *                           one of the packages root's guard files.
	 */
	private function normalizeKey( string $key ): string {
		$normalized = ltrim( str_replace( '\\', '/', $key ), '/' );

		if (
			'' === $normalized
			|| str_contains( $normalized, "\0" )
			|| str_contains( $normalized, '..' )
		) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Not HTML output here; escaped where printed.
			throw StorageException::invalidKey( $key );
		}

		// A root-level key matching a guard file would overwrite the deny-all
		// protection when stored. Guard basenames deeper in the tree are harmless, so
		// only reject them when they resolve directly to the packages root.
		if ( '.' === dirname( $normalized ) && in_array( basename( $normalized ), self::RESERVED_GUARD_FILES, true ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Not HTML output here; escaped where printed.
			throw StorageException::invalidKey( $key );
		}

		return $normalized;
	}
}

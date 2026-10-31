<?php
/**
 * Plugin Name:       ReleaseCart
 * Plugin URI:        https://github.com/drplugin-projects/releasecart
 * Description:       Sell digital products with licensing, updates, and Stripe payments, right from WordPress.
 * Version:           1.0.0
 * Requires at least: 6.9
 * Requires PHP:      8.1
 * Author:            DrPlugin
 * Author URI:        https://drplugin.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       releasecart
 * Domain Path:       /languages
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Keep RELEASECART_VERSION equal to the plugin header version.
define( 'RELEASECART_VERSION', '1.0.0' );
define( 'RELEASECART_FILE', __FILE__ );
define( 'RELEASECART_PATH', plugin_dir_path( __FILE__ ) );
define( 'RELEASECART_URL', plugin_dir_url( __FILE__ ) );

/*
 * Keep this equal to the highest migration version.
 */
define( 'RELEASECART_DB_VERSION', 4 );

/**
 * Autoload ReleaseCart classes.
 *
 * ReleaseCart's own classes load without Composer's autoloader.
 *
 * @since 1.0.0
 *
 * @param string $class_name Fully-qualified class name being autoloaded.
 * @return void
 */
spl_autoload_register(
	static function ( string $class_name ): void {
		$prefix = 'DrPlugin\\WP\\ReleaseCart\\';
		$length = strlen( $prefix );

		/* Ignore non-core classes. */
		if ( strncmp( $prefix, $class_name, $length ) !== 0 ) {
			return;
		}

		$relative = substr( $class_name, $length );
		$path     = __DIR__ . '/src/' . str_replace( '\\', '/', $relative ) . '.php';

		if ( is_readable( $path ) ) {
			require $path;
		}
	}
);

/*
 * Load the bundled Action Scheduler job queue.
 *
 * Its own bootstrap, not vendor/autoload.php: the zip ships no autoloader, and
 * Action Scheduler's version registry picks the newest copy on the site.
 */
require_once __DIR__ . '/vendor/woocommerce/action-scheduler/action-scheduler.php';

/**
 * Access the shared ReleaseCart plugin instance.
 *
 * Build on first use. Block render files reach the container through it; extensions can too.
 *
 * @since 1.0.0
 *
 * @return \DrPlugin\WP\ReleaseCart\Core\Plugin The plugin instance.
 */
function releasecart(): \DrPlugin\WP\ReleaseCart\Core\Plugin {
	static $plugin = null;

	if ( null === $plugin ) {
		$plugin = new \DrPlugin\WP\ReleaseCart\Core\Plugin(
			RELEASECART_FILE,
			new \DrPlugin\WP\ReleaseCart\Core\Container(),
			array(
				new \DrPlugin\WP\ReleaseCart\Core\CoreServiceProvider(),
				new \DrPlugin\WP\ReleaseCart\Database\DatabaseServiceProvider(),
				new \DrPlugin\WP\ReleaseCart\Storage\StorageServiceProvider(),
				new \DrPlugin\WP\ReleaseCart\Products\ProductsServiceProvider(),
				new \DrPlugin\WP\ReleaseCart\Customers\CustomersServiceProvider(),
				new \DrPlugin\WP\ReleaseCart\Orders\OrdersServiceProvider(),
				new \DrPlugin\WP\ReleaseCart\Discounts\DiscountsServiceProvider(),
				new \DrPlugin\WP\ReleaseCart\Payments\PaymentsServiceProvider(),
				new \DrPlugin\WP\ReleaseCart\Licensing\LicensingServiceProvider(),
				new \DrPlugin\WP\ReleaseCart\Updates\UpdatesServiceProvider(),
				new \DrPlugin\WP\ReleaseCart\Emails\EmailsServiceProvider(),
				new \DrPlugin\WP\ReleaseCart\Reports\ReportsServiceProvider(),
				new \DrPlugin\WP\ReleaseCart\Webhooks\WebhooksServiceProvider(),
				new \DrPlugin\WP\ReleaseCart\Jobs\JobsServiceProvider(),
				new \DrPlugin\WP\ReleaseCart\Privacy\PrivacyServiceProvider(),
				new \DrPlugin\WP\ReleaseCart\Frontend\FrontendServiceProvider(),
				new \DrPlugin\WP\ReleaseCart\REST\RestServiceProvider(),
				new \DrPlugin\WP\ReleaseCart\Admin\AdminServiceProvider(),
				new \DrPlugin\WP\ReleaseCart\Upsell\UpsellServiceProvider(),
			)
		);
	}

	return $plugin;
}

// Register hooks. WordPress boots the plugin on plugins_loaded.
releasecart()->hooks();

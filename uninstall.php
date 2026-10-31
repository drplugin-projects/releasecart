<?php
/**
 * ReleaseCart uninstall handler.
 *
 * Delete opted-in data. Check each multisite site.
 *
 * This file has no plugin autoloader.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

// Only ever run in WordPress' uninstall context.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/*
 * On one site, stop before the sweep without explicit consent.
 */
if ( ! is_multisite() ) {
	$releasecart_settings = get_option( 'releasecart_settings' );

	if ( ! is_array( $releasecart_settings ) || empty( $releasecart_settings['delete_data_on_uninstall'] ) ) {
		return;
	}
}

/*
 * Keep this list in sync with Schema::tables().
 */
$releasecart_tables = array(
	'products',
	'product_prices',
	'bundle_items',
	'orders',
	'order_items',
	'order_meta',
	'customers',
	'licenses',
	'license_meta',
	'activations',
	'terms',
	'term_relationships',
	'discounts',
	'releases',
	'gateway_events',
	'api_keys',
	'logs',
);

// Inlined custom capabilities.
$releasecart_caps = array( 'manage_releasecart', 'manage_releasecart_settings', 'view_releasecart_reports' );

// Object cache groups the plugin's classes keep rows in (their CACHE_GROUP constants).
$releasecart_cache_groups = array(
	'releasecart',
	'releasecart_activations',
	'releasecart_customers',
	'releasecart_discounts',
	'releasecart_licenses',
	'releasecart_orders',
	'releasecart_products',
	'releasecart_terms',
);

/*
 * Get the direct WP_Filesystem, or null when WordPress would use another method.
 * An instance WordPress already set up, for example while it deletes plugins over
 * FTP, is reused only when it is direct and is never replaced.
 */
$releasecart_direct_filesystem = static function ( string $context ) {
	global $wp_filesystem;

	if ( $wp_filesystem instanceof WP_Filesystem_Base ) {
		return 'direct' === $wp_filesystem->method ? $wp_filesystem : null;
	}

	if ( ! function_exists( 'get_filesystem_method' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	// PHP writes the uploads folder itself, so file ownership is not checked.
	if ( 'direct' !== get_filesystem_method( array(), $context, true ) || ! WP_Filesystem( false, $context, true ) ) {
		return null;
	}

	return $wp_filesystem instanceof WP_Filesystem_Base ? $wp_filesystem : null;
};

/*
 * Process each site because its data and consent are site-specific.
 */
if ( is_multisite() ) {
	$releasecart_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);
} else {
	$releasecart_site_ids = array( get_current_blog_id() );
}

foreach ( $releasecart_site_ids as $releasecart_site_id ) {
	if ( is_multisite() ) {
		switch_to_blog( (int) $releasecart_site_id );

		// Sweep this site only after its own consent check.
		$releasecart_settings = get_option( 'releasecart_settings' );

		if ( ! is_array( $releasecart_settings ) || empty( $releasecart_settings['delete_data_on_uninstall'] ) ) {
			restore_current_blog();
			continue;
		}
	}

	global $wpdb;

	foreach ( $releasecart_tables as $releasecart_base ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Uninstall drops the releasecart_ tables.
		$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $wpdb->prefix . 'releasecart_' . $releasecart_base ) );
	}

	/*
	 * Drop any other table under this site's ReleaseCart prefix, such as one an
	 * extension or an older version left. Escaping keeps `_` literal, so only
	 * this site's releasecart_ tables match.
	 */
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup, nothing to cache.
	$releasecart_leftovers = $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $wpdb->prefix . 'releasecart_' ) . '%' ) );

	foreach ( $releasecart_leftovers as $releasecart_table ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Uninstall drops the releasecart_ tables.
		$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', (string) $releasecart_table ) );
	}

	/*
	 * Delete provisioned storefront pages. The ids come from the settings read
	 * above, before the options sweep below wipes the pointer. Only pages that
	 * are both tracked in settings and still carry the provisioning marker are
	 * removed: a page the merchant picked by hand is tracked but unmarked, so it
	 * survives. The marker literal is inlined because uninstall.php has no
	 * autoloader (see PageProvisioner::MARKER_META).
	 */
	$releasecart_pages = ( isset( $releasecart_settings['pages'] ) && is_array( $releasecart_settings['pages'] ) )
		? $releasecart_settings['pages']
		: array();

	foreach ( $releasecart_pages as $releasecart_page_id ) {
		$releasecart_page_id = (int) $releasecart_page_id;

		if ( $releasecart_page_id <= 0 ) {
			continue;
		}

		// Only pages ReleaseCart provisioned carry this marker.
		if ( '' === (string) get_post_meta( $releasecart_page_id, '_releasecart_page', true ) ) {
			continue;
		}

		// Force delete so the marker meta goes with the page.
		wp_delete_post( $releasecart_page_id, true );
	}

	/*
	 * Remove ReleaseCart options, secrets, and transient value and timeout rows.
	 */
	$releasecart_like = array(
		$wpdb->esc_like( 'releasecart_' ) . '%',
		$wpdb->esc_like( '_transient_releasecart_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_releasecart_' ) . '%',
		$wpdb->esc_like( '_site_transient_releasecart_' ) . '%',
		$wpdb->esc_like( '_site_transient_timeout_releasecart_' ) . '%',
	);

	// The rows go by raw SQL, so note their names to clear the cache below.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup, nothing to cache.
	$releasecart_option_names = $wpdb->get_col(
		$wpdb->prepare(
			'SELECT option_name FROM %i WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s',
			$wpdb->options,
			$releasecart_like[0],
			$releasecart_like[1],
			$releasecart_like[2],
			$releasecart_like[3],
			$releasecart_like[4]
		)
	);

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup, nothing to cache.
	$wpdb->query(
		$wpdb->prepare(
			'DELETE FROM %i WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s',
			$wpdb->options,
			$releasecart_like[0],
			$releasecart_like[1],
			$releasecart_like[2],
			$releasecart_like[3],
			$releasecart_like[4]
		)
	);

	// A cached schema version must not outlive its tables.
	foreach ( $releasecart_option_names as $releasecart_option_name ) {
		wp_cache_delete( (string) $releasecart_option_name, 'options' );
	}
	wp_cache_delete( 'alloptions', 'options' );
	wp_cache_delete( 'notoptions', 'options' );

	/*
	 * Remove the per-user upsell dismissals. update_user_option() stores them in
	 * usermeta under a blog-prefixed key, so the releasecart_% option sweep above
	 * never reaches them. The key literal mirrors Upsell\Dismissals::OPTION_KEY.
	 */
	$releasecart_dismiss_key = $wpdb->prefix . 'releasecart_upsell_dismissed';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup, nothing to cache.
	$wpdb->query( $wpdb->prepare( 'DELETE FROM %i WHERE meta_key = %s', $wpdb->usermeta, $releasecart_dismiss_key ) );

	/*
	 * Remove the ReleaseCart Action Scheduler group only. Its tables are shared.
	 */
	$releasecart_as_actions = $wpdb->prefix . 'actionscheduler_actions';
	$releasecart_as_groups  = $wpdb->prefix . 'actionscheduler_groups';
	$releasecart_as_logs    = $wpdb->prefix . 'actionscheduler_logs';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup, nothing to cache.
	$releasecart_as_present = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $releasecart_as_groups ) ) );

	if ( null !== $releasecart_as_present ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup, nothing to cache.
		$releasecart_group_id = $wpdb->get_var( $wpdb->prepare( 'SELECT group_id FROM %i WHERE slug = %s', $releasecart_as_groups, 'releasecart' ) );

		if ( null !== $releasecart_group_id ) {
			$releasecart_group_id = (int) $releasecart_group_id;

			// Remove ReleaseCart scheduler rows only.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup, nothing to cache.
			$wpdb->query( $wpdb->prepare( 'DELETE FROM %i WHERE action_id IN ( SELECT action_id FROM %i WHERE group_id = %d )', $releasecart_as_logs, $releasecart_as_actions, $releasecart_group_id ) );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup, nothing to cache.
			$wpdb->query( $wpdb->prepare( 'DELETE FROM %i WHERE group_id = %d', $releasecart_as_actions, $releasecart_group_id ) );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup, nothing to cache.
			$wpdb->query( $wpdb->prepare( 'DELETE FROM %i WHERE group_id = %d', $releasecart_as_groups, $releasecart_group_id ) );

			// Action Scheduler caches group ids by slug; drop the deleted one.
			wp_cache_delete( 'releasecart', 'action_scheduler_groups' );
		}
	}

	/*
	 * Remove legacy ReleaseCart WP-Cron events.
	 */
	$releasecart_cron = _get_cron_array();

	if ( is_array( $releasecart_cron ) ) {
		$releasecart_cron_hooks = array();

		foreach ( $releasecart_cron as $releasecart_events ) {
			if ( ! is_array( $releasecart_events ) ) {
				continue;
			}

			foreach ( array_keys( $releasecart_events ) as $releasecart_hook ) {
				if ( str_starts_with( (string) $releasecart_hook, 'releasecart_' ) ) {
					$releasecart_cron_hooks[ $releasecart_hook ] = true;
				}
			}
		}

		foreach ( array_keys( $releasecart_cron_hooks ) as $releasecart_hook ) {
			wp_unschedule_hook( (string) $releasecart_hook );
		}
	}

	/*
	 * Remove only the current site's ReleaseCart upload directory.
	 *
	 * Folders go only through the direct filesystem. Without it, the emptied folders stay.
	 */
	$releasecart_uploads     = wp_upload_dir();
	$releasecart_package_dir = trailingslashit( $releasecart_uploads['basedir'] ) . 'releasecart';

	if ( is_dir( $releasecart_package_dir ) ) {
		$releasecart_filesystem = $releasecart_direct_filesystem( $releasecart_package_dir );
		$releasecart_files      = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $releasecart_package_dir, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $releasecart_files as $releasecart_file ) {
			if ( ! $releasecart_file->isDir() ) {
				wp_delete_file( $releasecart_file->getPathname() );
				continue;
			}

			/*
			 * Remove only confirmed empty directories. Ignore filesystem failures.
			 */
			$releasecart_entries = scandir( $releasecart_file->getPathname() );

			if ( null !== $releasecart_filesystem && is_array( $releasecart_entries ) && 2 === count( $releasecart_entries ) ) {
				$releasecart_filesystem->rmdir( $releasecart_file->getPathname() );
			}
		}

		$releasecart_entries = scandir( $releasecart_package_dir );

		if ( null !== $releasecart_filesystem && is_array( $releasecart_entries ) && 2 === count( $releasecart_entries ) ) {
			$releasecart_filesystem->rmdir( $releasecart_package_dir );
		}
	}

	/*
	 * Remove custom capabilities.
	 */
	foreach ( wp_roles()->role_objects as $releasecart_role ) {
		foreach ( $releasecart_caps as $releasecart_cap ) {
			if ( $releasecart_role->has_cap( $releasecart_cap ) ) {
				$releasecart_role->remove_cap( $releasecart_cap );
			}
		}
	}

	/*
	 * Flush the plugin's cache groups so no row outlives its dropped table. Without
	 * group flushes, those rows and other cached values, such as transients in an
	 * external object cache, expire on their own.
	 */
	if ( wp_cache_supports( 'flush_group' ) ) {
		foreach ( $releasecart_cache_groups as $releasecart_cache_group ) {
			wp_cache_flush_group( $releasecart_cache_group );
		}
	}

	if ( is_multisite() ) {
		restore_current_blog();
	}
}

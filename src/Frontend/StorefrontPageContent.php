<?php
/**
 * The shared block markup for provisioned storefront pages and starter patterns.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Builds shared block markup for storefront pages.
 *
 * @since 1.0.0
 */
final class StorefrontPageContent {

	/**
	 * The store page: the product grid.
	 *
	 * @since 1.0.0
	 *
	 * @return string Block markup.
	 */
	public static function store(): string {
		return '<!-- wp:releasecart/product-grid /-->';
	}

	/**
	 * Block markup for a granular product details page.
	 *
	 * @since 1.0.0
	 *
	 * @return string Block markup.
	 */
	public static function productDetails(): string {
		return '<!-- wp:releasecart/product-images /-->

<!-- wp:releasecart/product-overview /-->

<!-- wp:releasecart/product-pricing-tiers /-->

<!-- wp:releasecart/product-description /-->

<!-- wp:releasecart/product-screenshots /-->

<!-- wp:releasecart/product-meta /-->';
	}

	/**
	 * Block markup for a wide two-column product page.
	 *
	 * @since 1.0.0
	 *
	 * @return string Block markup.
	 */
	public static function productDetailsTwoColumn(): string {
		return '<!-- wp:releasecart/product-details {"align":"wide"} /-->

<!-- wp:releasecart/product-meta {"align":"wide"} /-->';
	}

	/**
	 * Block markup for the checkout page.
	 *
	 * @since 1.0.0
	 *
	 * @return string Block markup.
	 */
	public static function checkout(): string {
		return '<!-- wp:releasecart/purchase-confirmation /-->

<!-- wp:releasecart/checkout {"align":"wide"} /-->';
	}

	/**
	 * Block markup for the account page.
	 *
	 * @since 1.0.0
	 *
	 * @return string Block markup.
	 */
	public static function account(): string {
		return '<!-- wp:releasecart/purchase-confirmation /-->

<!-- wp:releasecart/account-dashboard {"align":"wide"} /-->';
	}

	/**
	 * The sign-in page: the login form.
	 *
	 * @since 1.0.0
	 *
	 * @return string Block markup.
	 */
	public static function login(): string {
		return '<!-- wp:releasecart/login /-->';
	}

	/**
	 * The registration page: the registration form.
	 *
	 * @since 1.0.0
	 *
	 * @return string Block markup.
	 */
	public static function registration(): string {
		return '<!-- wp:releasecart/registration-form /-->';
	}

	/**
	 * The password reset page: the lost-password and set-new-password form.
	 *
	 * @since 1.0.0
	 *
	 * @return string Block markup.
	 */
	public static function passwordReset(): string {
		return '<!-- wp:releasecart/password-reset-form /-->';
	}
}

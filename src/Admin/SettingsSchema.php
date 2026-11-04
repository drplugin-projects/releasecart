<?php
/**
 * The settings field schema served to the admin SPA.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Admin;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

use DrPlugin\WP\ReleaseCart\Frontend\CheckoutFieldSettings;
use DrPlugin\WP\ReleaseCart\Payments\Stripe\StripeExportRequirement;
use DrPlugin\WP\ReleaseCart\Payments\Stripe\StripeSettings;
use DrPlugin\WP\ReleaseCart\Util\Crypto;

/**
 * Defines settings fields for the admin SPA.
 *
 * Extensions add fields through a filter. Secret storage stays server-side.
 *
 * @since 1.0.0
 */
final class SettingsSchema {

	/**
	 * Which checkout fields are in force, for copy that depends on it.
	 *
	 * @var CheckoutFieldSettings
	 */
	private CheckoutFieldSettings $checkout;

	/**
	 * The Stripe India rule, for copy that depends on it.
	 *
	 * @var StripeExportRequirement
	 */
	private StripeExportRequirement $stripeRule;

	/**
	 * Wire the schema to the checkout field settings and the Stripe India rule.
	 *
	 * @since 1.0.0
	 *
	 * @param CheckoutFieldSettings|null   $checkout   The checkout field settings, or null for the default.
	 * @param StripeExportRequirement|null $stripeRule The Stripe India rule, or null for the default.
	 */
	public function __construct( ?CheckoutFieldSettings $checkout = null, ?StripeExportRequirement $stripeRule = null ) {
		$this->checkout   = $checkout ?? new CheckoutFieldSettings();
		$this->stripeRule = $stripeRule ?? new StripeExportRequirement( new StripeSettings( new Crypto() ) );
	}

	/**
	 * Get valid settings sections in display order.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, array<string, string>> Section rows.
	 */
	public function sections(): array {
		$core = array(
			array(
				'id'    => 'general',
				'title' => __( 'General', 'releasecart' ),
			),
			array(
				'id'    => 'appearance',
				'title' => __( 'Appearance', 'releasecart' ),
			),
			array(
				'id'    => 'emails',
				'title' => __( 'Emails', 'releasecart' ),
			),
			array(
				'id'    => 'payments',
				'title' => __( 'Payments', 'releasecart' ),
			),
			array(
				'id'    => 'discounts',
				'title' => __( 'Discounts', 'releasecart' ),
			),
			array(
				'id'    => 'webhooks',
				'title' => __( 'Webhooks', 'releasecart' ),
			),
			array(
				'id'    => 'integrations',
				'title' => __( 'Integrations', 'releasecart' ),
			),
		);

		/**
		 * Filters the settings sections served to the admin SPA.
		 *
		 * Extensions add sections here. Invalid and duplicate ids are dropped.
		 *
		 * @since 1.0.0
		 *
		 * @param array<int, array<string, string>> $sections Section rows.
		 */
		$sections = apply_filters( 'releasecart_settings_sections', $core );

		$byId = array();
		foreach ( (array) $sections as $section ) {
			if ( ! is_array( $section ) || ! isset( $section['id'], $section['title'] ) ) {
				continue;
			}
			if ( ! is_string( $section['id'] ) || '' === $section['id'] || ! is_string( $section['title'] ) ) {
				continue;
			}
			if ( isset( $byId[ $section['id'] ] ) ) {
				continue;
			}
			$byId[ $section['id'] ] = array(
				'id'    => $section['id'],
				'title' => $section['title'],
			);
		}

		return array_values( $byId );
	}

	/**
	 * Get valid settings fields after extension filters.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, array<string, mixed>> Fields keyed by id.
	 */
	public function fields(): array {
		/**
		 * Filters the settings field definitions served to the admin SPA.
		 *
		 * Extensions add field definitions here.
		 *
		 * @since 1.0.0
		 *
		 * @param array<int, array<string, mixed>> $fields Field definitions.
		 */
		$fields = apply_filters( 'releasecart_settings_fields', $this->coreFields() );

		$byId = array();
		foreach ( (array) $fields as $field ) {
			if ( ! is_array( $field ) || ! isset( $field['id'], $field['section'], $field['type'], $field['label'] ) ) {
				continue;
			}
			if ( ! isset( $field['storage'] ) ) {
				$field['storage'] = 'option';
			}
			if ( 'option' === $field['storage'] && empty( $field['path'] ) ) {
				continue;
			}
			$byId[ (string) $field['id'] ] = $field;
		}

		return $byId;
	}

	/**
	 * Get one field definition.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id Field id.
	 * @return array<string, mixed>|null Field definition, or null.
	 */
	public function field( string $id ): ?array {
		return $this->fields()[ $id ] ?? null;
	}

	/**
	 * Get fields the SPA can render.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, array<string, mixed>> Client field rows.
	 */
	public function forClient(): array {
		$client = array();

		foreach ( $this->fields() as $field ) {
			$row = array(
				'id'          => $field['id'],
				'section'     => $field['section'],
				'type'        => $field['type'],
				'label'       => $field['label'],
				'description' => $field['description'] ?? '',
				'secret'      => 'secret' === $field['type'],
			);
			if ( isset( $field['options'] ) ) {
				$row['options'] = $field['options'];
			}
			// The gateway key selects a Payments sub-tab, and the label names it.
			if ( isset( $field['gateway'] ) ) {
				$row['gateway'] = $field['gateway'];
			}
			if ( isset( $field['gateway_label'] ) ) {
				$row['gateway_label'] = $field['gateway_label'];
			}
			// The group key selects a shared fieldset.
			if ( isset( $field['group'] ) ) {
				$row['group'] = $field['group'];
			}
			// Extensions persist render-only fields through their own endpoints.
			if ( 'none' === $field['storage'] ) {
				$row['external'] = true;
			}
			$client[] = $row;
		}

		return $client;
	}

	/**
	 * Describe the billing-address select, naming the override when an
	 * extension requires the address regardless of the choice here.
	 *
	 * @since 1.0.0
	 *
	 * @return string The field description.
	 */
	private function addressDescription(): string {
		if ( $this->stripeRule->inForce() ) {
			return $this->withStripeRule( __( 'Whether to ask the buyer for a full billing address at checkout.', 'releasecart' ) );
		}

		if ( $this->checkout->addressRequiredByExtension() ) {
			return __( 'Whether to ask the buyer for a full billing address at checkout. The address is currently required by another setting, so this choice takes effect only when that setting is turned off.', 'releasecart' );
		}

		return __( 'Whether to ask the buyer for a full billing address at checkout.', 'releasecart' );
	}

	/**
	 * Describe the billing-country toggle, saying so when an extension collects
	 * the country whatever the toggle says.
	 *
	 * @since 1.0.0
	 *
	 * @return string The field description.
	 */
	private function countryDescription(): string {
		if ( $this->stripeRule->inForce() ) {
			return $this->withStripeRule( __( 'Ask the buyer for their country at checkout.', 'releasecart' ) );
		}

		if ( $this->checkout->collectedByExtension( 'billing_country' ) ) {
			return __( 'Ask the buyer for their country at checkout. The country is currently collected for another setting, so this choice takes effect only when that setting is turned off.', 'releasecart' );
		}

		return __( 'Ask the buyer for their country at checkout.', 'releasecart' );
	}

	/**
	 * Describe a name toggle, giving the reason when something other than the
	 * toggle requires the name. The Stripe India rule names itself.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key       The field key: `first_name` or `last_name`.
	 * @param string $mandatory The first sentence, used with a reason.
	 * @param string $plain     The description while only the toggle decides.
	 * @return string The field description.
	 */
	private function nameDescription( string $key, string $mandatory, string $plain ): string {
		if ( $this->stripeRule->inForce() ) {
			return $this->withStripeRule( $mandatory );
		}

		if ( $this->checkout->requiredByExtension( $key ) ) {
			return $mandatory . ' ' . __( 'The name is currently required by another setting, so this choice takes effect only when that setting is turned off.', 'releasecart' );
		}

		return $plain;
	}

	/**
	 * Describe a secret key, saying so when the account lookup for its mode failed.
	 *
	 * @since 1.0.0
	 *
	 * @param string $mode  Either 'test' or 'live'.
	 * @param string $plain The description when the lookup worked or never ran.
	 * @return string The field description.
	 */
	private function secretKeyDescription( string $mode, string $plain ): string {
		if ( ! $this->stripeRule->countryUnknown( $mode ) ) {
			return $plain;
		}

		return $plain . ' ' . __( "ReleaseCart could not read this Stripe account's country. If the account is in India, save the key again so checkout asks for what Stripe needs. A restricted key needs Accounts read access.", 'releasecart' );
	}

	/**
	 * Add the Stripe India reason to a description's first sentence.
	 *
	 * @since 1.0.0
	 *
	 * @param string $sentence The field's first sentence.
	 * @return string The field description.
	 */
	private function withStripeRule( string $sentence ): string {
		return $sentence . ' ' . __( "Your Stripe account is in India, so checkout requires this. Stripe needs the buyer's name and billing address for payments with cards issued outside India.", 'releasecart' );
	}

	/**
	 * Describe the VAT toggle, saying so when an extension collects the number
	 * whatever the toggle says.
	 *
	 * @since 1.0.0
	 *
	 * @return string The field description.
	 */
	private function vatDescription(): string {
		if ( $this->checkout->collectedByExtension( 'vat_number' ) ) {
			return __( 'Ask the buyer for a VAT, GST, or tax registration number at checkout. The number is currently collected for another setting, so this choice takes effect only when that setting is turned off.', 'releasecart' );
		}

		return __( 'Ask the buyer for a VAT, GST, or tax registration number at checkout.', 'releasecart' );
	}

	/**
	 * Get core field definitions.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, array<string, mixed>> Core fields.
	 */
	private function coreFields(): array {
		return array(
			array(
				'id'          => 'currency',
				'section'     => 'general',
				'group'       => 'store',
				'type'        => 'currency',
				'label'       => __( 'Store currency', 'releasecart' ),
				'description' => __( 'The currency used for all prices. Pick it from the list.', 'releasecart' ),
				'path'        => 'currency',
				'default'     => 'USD',
			),
			array(
				'id'          => 'store_page',
				'section'     => 'general',
				'group'       => 'pages',
				'type'        => 'page',
				'label'       => __( 'Store page', 'releasecart' ),
				'description' => __( 'The page listing your products. Set automatically when the store is provisioned.', 'releasecart' ),
				'path'        => 'pages.store',
				'default'     => 0,
			),
			array(
				'id'          => 'product_page',
				'section'     => 'general',
				'group'       => 'pages',
				'type'        => 'page',
				'label'       => __( 'Product details page', 'releasecart' ),
				'description' => __( 'The page a single product opens on from the store grid.', 'releasecart' ),
				'path'        => 'pages.product',
				'default'     => 0,
			),
			array(
				'id'          => 'checkout_page',
				'section'     => 'general',
				'group'       => 'pages',
				'type'        => 'page',
				'label'       => __( 'Checkout page', 'releasecart' ),
				'description' => __( 'The page with the checkout block or shortcode. Cart links and links to buy a new license open it.', 'releasecart' ),
				'path'        => 'pages.checkout',
				'default'     => 0,
			),
			array(
				'id'          => 'account_page',
				'section'     => 'general',
				'group'       => 'pages',
				'type'        => 'page',
				'label'       => __( 'Account page', 'releasecart' ),
				'description' => __( 'Where customers manage their purchases, downloads, and license keys.', 'releasecart' ),
				'path'        => 'pages.account',
				'default'     => 0,
			),
			array(
				'id'          => 'login_page',
				'section'     => 'general',
				'group'       => 'pages',
				'type'        => 'page',
				'label'       => __( 'Sign-in page', 'releasecart' ),
				'description' => __( 'The page carrying the sign-in form. Leave unset to use the WordPress login screen.', 'releasecart' ),
				'path'        => 'pages.login',
				'default'     => 0,
			),
			array(
				'id'          => 'register_page',
				'section'     => 'general',
				'group'       => 'pages',
				'type'        => 'page',
				'label'       => __( 'Registration page', 'releasecart' ),
				'description' => __( 'The page carrying the account registration form.', 'releasecart' ),
				'path'        => 'pages.register',
				'default'     => 0,
			),
			array(
				'id'          => 'password_reset_page',
				'section'     => 'general',
				'group'       => 'pages',
				'type'        => 'page',
				'label'       => __( 'Password reset page', 'releasecart' ),
				'description' => __( 'The page carrying the lost-password and set-new-password form. While it is published, ReleaseCart\'s own forms and emails link to it, and WordPress password emails do too when wp-login is replaced. Unset it to use the WordPress screens. wp-login.php?releasecart_wp_login=1 always reaches the WordPress screen.', 'releasecart' ),
				'path'        => 'pages.password_reset',
				'default'     => 0,
			),
			array(
				'id'          => 'terms_page',
				'section'     => 'general',
				'group'       => 'pages',
				'type'        => 'page',
				'label'       => __( 'Terms page', 'releasecart' ),
				'description' => __( 'Your terms and conditions page, linked from checkout.', 'releasecart' ),
				'path'        => 'pages.terms',
				'default'     => 0,
			),
			array(
				'id'          => 'privacy_page',
				'section'     => 'general',
				'group'       => 'pages',
				'type'        => 'page',
				'label'       => __( 'Privacy policy page', 'releasecart' ),
				'description' => __( 'Your privacy policy page. Falls back to the WordPress privacy policy page when left unset.', 'releasecart' ),
				'path'        => 'pages.privacy',
				'default'     => 0,
			),
			array(
				'id'          => 'hide_admin_bar',
				'section'     => 'general',
				'group'       => 'frontend',
				'type'        => 'toggle',
				'label'       => __( 'Hide the admin toolbar for customers', 'releasecart' ),
				'description' => __( 'Hide the WordPress toolbar on the front end for logged-in users who cannot edit posts. Administrators and editors always keep it.', 'releasecart' ),
				'path'        => 'frontend.hide_admin_bar',
				'default'     => false,
			),
			array(
				'id'          => 'replace_wp_login',
				'section'     => 'general',
				'group'       => 'frontend',
				'type'        => 'toggle',
				'label'       => __( 'Use the sign-in page instead of wp-login', 'releasecart' ),
				'description' => __( 'Redirect front-end sign-in and password-reset requests, and the links in WordPress password emails, to your pages. Requires published pages; logout, confirmations and admin logins keep working, and wp-login.php?releasecart_wp_login=1 always shows the WordPress screen.', 'releasecart' ),
				'path'        => 'frontend.replace_wp_login',
				'default'     => false,
			),
			array(
				'id'          => 'grid_buy_buttons',
				'section'     => 'general',
				'group'       => 'frontend',
				'type'        => 'toggle',
				'label'       => __( 'Show buy buttons in the product grid', 'releasecart' ),
				'description' => __( 'The store-wide default for add-to-cart buttons on product grid cards. A block or shortcode can still override it.', 'releasecart' ),
				'path'        => 'storefront.grid_buy_buttons',
				'default'     => true,
			),
			array(
				'id'          => 'cart_redirect',
				'section'     => 'general',
				'group'       => 'frontend',
				'type'        => 'select',
				'label'       => __( 'After adding to the cart', 'releasecart' ),
				'description' => __( 'What happens after a shopper adds a product to the cart.', 'releasecart' ),
				'options'     => array(
					array(
						'value' => 'stay',
						'label' => __( 'Stay on the page and show a confirmation', 'releasecart' ),
					),
					array(
						'value' => 'checkout',
						'label' => __( 'Go straight to the checkout page', 'releasecart' ),
					),
				),
				'path'        => 'storefront.cart_redirect',
				'default'     => 'stay',
			),
			array(
				'id'          => 'storefront_product_title_source',
				'section'     => 'general',
				'group'       => 'frontend',
				'type'        => 'select',
				'label'       => __( 'Product page heading', 'releasecart' ),
				'description' => __( 'Where the product name shows as the heading on the product details page. Show it in the block keeps the theme page title hidden there so the block is the only heading. Use the theme page title puts the product name in the theme title instead (so a Post Title block and breadcrumbs show it), and the block hides its own; pick this only if your theme renders the page title.', 'releasecart' ),
				'options'     => array(
					array(
						'value' => 'block',
						'label' => __( 'Show the product name in the block', 'releasecart' ),
					),
					array(
						'value' => 'theme',
						'label' => __( 'Use the theme page title for the product name', 'releasecart' ),
					),
				),
				'path'        => 'storefront.product_title_source',
				'default'     => 'block',
			),
			array(
				'id'          => 'checkout_require_terms',
				'section'     => 'general',
				'group'       => 'checkout',
				'type'        => 'toggle',
				'label'       => __( 'Require terms acceptance at checkout', 'releasecart' ),
				'description' => __( 'Show a checkbox the buyer must tick to accept your terms and privacy policy before paying.', 'releasecart' ),
				'path'        => 'checkout.require_terms',
				'default'     => false,
			),
			array(
				'id'          => 'checkout_collect_country',
				'section'     => 'general',
				'group'       => 'checkout',
				'type'        => 'toggle',
				'label'       => __( 'Collect a billing country', 'releasecart' ),
				'description' => $this->countryDescription(),
				'path'        => 'checkout.collect_country',
				'default'     => false,
			),
			array(
				'id'          => 'checkout_collect_vat',
				'section'     => 'general',
				'group'       => 'checkout',
				'type'        => 'toggle',
				'label'       => __( 'Collect a VAT or tax ID', 'releasecart' ),
				'description' => $this->vatDescription(),
				'path'        => 'checkout.collect_vat',
				'default'     => false,
			),
			array(
				'id'          => 'checkout_collect_address',
				'section'     => 'general',
				'group'       => 'checkout',
				'type'        => 'select',
				'label'       => __( 'Collect a billing address', 'releasecart' ),
				'description' => $this->addressDescription(),
				'options'     => array(
					array(
						'value' => 'none',
						'label' => __( 'Do not collect an address', 'releasecart' ),
					),
					array(
						'value' => 'optional',
						'label' => __( 'Collect an optional address', 'releasecart' ),
					),
					array(
						'value' => 'required',
						'label' => __( 'Require an address', 'releasecart' ),
					),
				),
				'path'        => 'checkout.collect_address',
				'default'     => 'none',
			),
			array(
				'id'          => 'checkout_show_login',
				'section'     => 'general',
				'group'       => 'checkout',
				'type'        => 'toggle',
				'label'       => __( 'Show a login panel at checkout', 'releasecart' ),
				'description' => __( 'Let existing customers log in from the checkout page before they pay.', 'releasecart' ),
				'path'        => 'checkout.show_login',
				'default'     => true,
			),
			array(
				'id'          => 'checkout_auto_register',
				'section'     => 'general',
				'group'       => 'checkout',
				'type'        => 'toggle',
				'label'       => __( 'Create an account for guests', 'releasecart' ),
				'description' => __( 'When a guest completes a purchase, create a WordPress account for them (or link one that already uses their email) so their orders and licenses appear in the account area. They set a password from a link emailed to them.', 'releasecart' ),
				'path'        => 'checkout.auto_register',
				'default'     => false,
			),
			array(
				'id'          => 'checkout_guest_checkout',
				'section'     => 'general',
				'group'       => 'checkout',
				'type'        => 'toggle',
				'label'       => __( 'Allow guest checkout', 'releasecart' ),
				'description' => __( 'Let buyers check out without an account. Turn this off to require an account, through login, auto-registration, or a registration page. Without a registration page or auto-registration, only existing users can buy.', 'releasecart' ),
				'path'        => 'checkout.guest_checkout',
				'default'     => true,
			),
			array(
				'id'          => 'checkout_require_first_name',
				'section'     => 'general',
				'group'       => 'checkout',
				'type'        => 'toggle',
				'label'       => __( 'Require a first name at checkout', 'releasecart' ),
				'description' => $this->nameDescription(
					'first_name',
					__( 'Make the first name field mandatory.', 'releasecart' ),
					__( 'Make the first name field mandatory. Left off, it is offered but optional.', 'releasecart' )
				),
				'path'        => 'checkout.require_first_name',
				'default'     => false,
			),
			array(
				'id'          => 'checkout_require_last_name',
				'section'     => 'general',
				'group'       => 'checkout',
				'type'        => 'toggle',
				'label'       => __( 'Require a last name at checkout', 'releasecart' ),
				'description' => $this->nameDescription(
					'last_name',
					__( 'Make the last name field mandatory.', 'releasecart' ),
					__( 'Make the last name field mandatory. Left off, it is offered but optional.', 'releasecart' )
				),
				'path'        => 'checkout.require_last_name',
				'default'     => false,
			),
			array(
				'id'          => 'checkout_collect_company',
				'section'     => 'general',
				'group'       => 'checkout',
				'type'        => 'toggle',
				'label'       => __( 'Offer a company purchase option', 'releasecart' ),
				'description' => __( 'Show an "I am buying as a company" checkbox that reveals a company name field and the tax ID field. Turn off to always show the tax ID field on its own instead.', 'releasecart' ),
				'path'        => 'checkout.collect_company',
				'default'     => true,
			),
			array(
				'id'          => 'delete_data_on_uninstall',
				'section'     => 'general',
				'group'       => 'advanced',
				'type'        => 'toggle',
				'label'       => __( 'Delete all data on uninstall', 'releasecart' ),
				'description' => __( 'When enabled, deleting the plugin removes every ReleaseCart table, option, capability, and stored package file. Leave off to keep customer and license data.', 'releasecart' ),
				'path'        => 'delete_data_on_uninstall',
				'default'     => false,
			),
			array(
				'id'          => 'discount_code_combination',
				'section'     => 'discounts',
				'type'        => 'select',
				'label'       => __( 'Codes and automatic discounts', 'releasecart' ),
				'description' => __( 'What happens when a shopper has a discount code and an automatic discount also matches their cart.', 'releasecart' ),
				'options'     => array(
					array(
						'value' => 'code_replaces_auto',
						'label' => __( 'A discount code replaces automatic discounts', 'releasecart' ),
					),
					array(
						'value' => 'stack',
						'label' => __( 'A discount code stacks with automatic discounts', 'releasecart' ),
					),
				),
				'path'        => 'discounts.code_combination',
				'default'     => 'code_replaces_auto',
			),
			array(
				'id'          => 'discount_strikethrough',
				'section'     => 'discounts',
				'type'        => 'toggle',
				'label'       => __( 'Show the original price struck through', 'releasecart' ),
				'description' => __( 'When an automatic discount applies, show the original price struck through with the discounted price next to it on product pages and the store grid.', 'releasecart' ),
				'path'        => 'storefront.discount_strikethrough',
				'default'     => true,
			),
			array(
				'id'          => 'appearance_primary',
				'section'     => 'appearance',
				'type'        => 'color',
				'label'       => __( 'Primary color', 'releasecart' ),
				'description' => sprintf(
					/* translators: %s: the shipped default colour, a hex code. */
					__( 'The main accent for buttons, links, and selected states. Its hover, soft, and focus shades are derived automatically, so pick a reasonably dark colour for readable button labels. Leave empty for the default (%s).', 'releasecart' ),
					'#2563eb'
				),
				'path'        => 'appearance.primary',
				'default'     => '',
			),
			array(
				'id'          => 'appearance_text',
				'section'     => 'appearance',
				'type'        => 'color',
				'label'       => __( 'Text color', 'releasecart' ),
				'description' => sprintf(
					/* translators: %s: the shipped default colour, a hex code. */
					__( 'The body text colour inside storefront components. Leave empty for the default (%s).', 'releasecart' ),
					'#111827'
				),
				'path'        => 'appearance.text',
				'default'     => '',
			),
			array(
				'id'          => 'appearance_surface',
				'section'     => 'appearance',
				'type'        => 'color',
				'label'       => __( 'Surface color', 'releasecart' ),
				'description' => sprintf(
					/* translators: %s: the shipped default colour, a hex code. */
					__( 'The background for cards, panels, and form fields. Leave empty for the default (%s).', 'releasecart' ),
					'#ffffff'
				),
				'path'        => 'appearance.surface',
				'default'     => '',
			),
			array(
				'id'          => 'appearance_page',
				'section'     => 'appearance',
				'type'        => 'color',
				'label'       => __( 'Page background', 'releasecart' ),
				'description' => sprintf(
					/* translators: %s: the shipped default colour, a hex code. */
					__( 'The page wash behind full-page surfaces such as the checkout and account areas. Leave empty for the default (%s).', 'releasecart' ),
					'#f6f7f9'
				),
				'path'        => 'appearance.page',
				'default'     => '',
			),
			array(
				'id'          => 'appearance_border',
				'section'     => 'appearance',
				'type'        => 'color',
				'label'       => __( 'Border color', 'releasecart' ),
				'description' => sprintf(
					/* translators: %s: the shipped default colour, a hex code. */
					__( 'Borders and dividers. The stronger control border is derived from it. Leave empty for the default (%s).', 'releasecart' ),
					'#e5e7eb'
				),
				'path'        => 'appearance.border',
				'default'     => '',
			),
			array(
				'id'          => 'appearance_success',
				'section'     => 'appearance',
				'type'        => 'color',
				'label'       => __( 'Success color', 'releasecart' ),
				'description' => sprintf(
					/* translators: %s: the shipped default colour, a hex code. */
					__( 'The accent for success notices and badges; its tinted background is derived. Leave empty for the default (%s).', 'releasecart' ),
					'#15803d'
				),
				'path'        => 'appearance.success',
				'default'     => '',
			),
			array(
				'id'          => 'appearance_error',
				'section'     => 'appearance',
				'type'        => 'color',
				'label'       => __( 'Error color', 'releasecart' ),
				'description' => sprintf(
					/* translators: %s: the shipped default colour, a hex code. */
					__( 'The accent for error notices and badges; its tinted background is derived. Leave empty for the default (%s).', 'releasecart' ),
					'#b91c1c'
				),
				'path'        => 'appearance.error',
				'default'     => '',
			),
			array(
				'id'          => 'appearance_radius',
				'section'     => 'appearance',
				'type'        => 'select',
				'label'       => __( 'Corner radius', 'releasecart' ),
				'description' => __( 'How rounded corners are on buttons, inputs, and cards.', 'releasecart' ),
				'options'     => array(
					array(
						'value' => '',
						'label' => __( 'Default', 'releasecart' ),
					),
					array(
						'value' => 'small',
						'label' => __( 'Small', 'releasecart' ),
					),
					array(
						'value' => 'large',
						'label' => __( 'Large', 'releasecart' ),
					),
				),
				'path'        => 'appearance.radius',
				'default'     => '',
			),
			array(
				'id'          => 'appearance_font',
				'section'     => 'appearance',
				'type'        => 'font_stack',
				'label'       => __( 'Font family', 'releasecart' ),
				'description' => __( 'A CSS font-family stack for every storefront component, for example: Inter, Arial, sans-serif. Leave empty for the built-in system font.', 'releasecart' ),
				'path'        => 'appearance.font',
				'default'     => '',
			),
			array(
				'id'          => 'email_from_name',
				'section'     => 'emails',
				'type'        => 'text',
				'label'       => __( 'From name', 'releasecart' ),
				'description' => __( 'The sender name on store emails. Defaults to the site title when empty.', 'releasecart' ),
				'path'        => 'emails.from_name',
				'default'     => '',
			),
			array(
				'id'          => 'email_from_address',
				'section'     => 'emails',
				'type'        => 'email',
				'label'       => __( 'From address', 'releasecart' ),
				'description' => __( 'The sender address on store emails. Defaults to the site admin address when empty.', 'releasecart' ),
				'path'        => 'emails.from_address',
				'default'     => '',
			),
			array(
				'id'          => 'email_enabled_purchase_receipt',
				'section'     => 'emails',
				'type'        => 'toggle',
				'label'       => __( 'Send purchase receipts', 'releasecart' ),
				'description' => __( 'Email buyers a receipt with their downloads and license keys after a completed purchase.', 'releasecart' ),
				'path'        => 'emails.enabled_purchase_receipt',
				'default'     => true,
			),
			array(
				'id'          => 'email_enabled_license_delivery',
				'section'     => 'emails',
				'type'        => 'toggle',
				'label'       => __( 'Send license delivery emails', 'releasecart' ),
				'description' => __( 'Email keys that are generated after the receipt already went out.', 'releasecart' ),
				'path'        => 'emails.enabled_license_delivery',
				'default'     => true,
			),
			array(
				'id'            => 'stripe_mode',
				'section'       => 'payments',
				'gateway'       => 'stripe',
				'gateway_label' => __( 'Stripe', 'releasecart' ),
				'type'          => 'select',
				'label'         => __( 'Stripe mode', 'releasecart' ),
				'description'   => __( 'Test mode uses your test keys and charges nothing real.', 'releasecart' ),
				'options'       => array(
					array(
						'value' => 'test',
						'label' => __( 'Test', 'releasecart' ),
					),
					array(
						'value' => 'live',
						'label' => __( 'Live', 'releasecart' ),
					),
				),
				'path'          => 'payments.stripe.mode',
				'default'       => 'test',
			),
			array(
				'id'            => 'stripe_publishable_key_test',
				'section'       => 'payments',
				'gateway'       => 'stripe',
				'gateway_label' => __( 'Stripe', 'releasecart' ),
				'group'         => 'test',
				'type'          => 'text',
				'label'         => __( 'Test publishable key', 'releasecart' ),
				'description'   => __( 'Starts with pk_test_.', 'releasecart' ),
				'path'          => 'payments.stripe.publishable_key_test',
				'default'       => '',
			),
			array(
				'id'            => 'stripe_publishable_key_live',
				'section'       => 'payments',
				'gateway'       => 'stripe',
				'gateway_label' => __( 'Stripe', 'releasecart' ),
				'group'         => 'live',
				'type'          => 'text',
				'label'         => __( 'Live publishable key', 'releasecart' ),
				'description'   => __( 'Starts with pk_live_.', 'releasecart' ),
				'path'          => 'payments.stripe.publishable_key_live',
				'default'       => '',
			),
			array(
				'id'            => 'stripe_secret_key_test',
				'section'       => 'payments',
				'gateway'       => 'stripe',
				'gateway_label' => __( 'Stripe', 'releasecart' ),
				'group'         => 'test',
				'type'          => 'secret',
				'label'         => __( 'Test secret key', 'releasecart' ),
				'description'   => $this->secretKeyDescription( 'test', __( 'Starts with sk_test_ or rk_test_. Stored encrypted; only the last characters are ever shown again.', 'releasecart' ) ),
				'storage'       => 'stripe',
				'stripe'        => array(
					'kind' => 'secret',
					'mode' => 'test',
				),
			),
			array(
				'id'            => 'stripe_secret_key_live',
				'section'       => 'payments',
				'gateway'       => 'stripe',
				'gateway_label' => __( 'Stripe', 'releasecart' ),
				'group'         => 'live',
				'type'          => 'secret',
				'label'         => __( 'Live secret key', 'releasecart' ),
				'description'   => $this->secretKeyDescription( 'live', __( 'Starts with sk_live_ or rk_live_. Stored encrypted; only the last characters are ever shown again.', 'releasecart' ) ),
				'storage'       => 'stripe',
				'stripe'        => array(
					'kind' => 'secret',
					'mode' => 'live',
				),
			),
			array(
				'id'            => 'stripe_webhook_secret_test',
				'section'       => 'payments',
				'gateway'       => 'stripe',
				'gateway_label' => __( 'Stripe', 'releasecart' ),
				'group'         => 'test',
				'type'          => 'secret',
				'label'         => __( 'Test webhook signing secret', 'releasecart' ),
				'description'   => __( 'Starts with whsec_. From the Stripe dashboard webhook endpoint.', 'releasecart' ),
				'storage'       => 'stripe',
				'stripe'        => array(
					'kind' => 'webhook_secret',
					'mode' => 'test',
				),
			),
			array(
				'id'            => 'stripe_webhook_secret_live',
				'section'       => 'payments',
				'gateway'       => 'stripe',
				'gateway_label' => __( 'Stripe', 'releasecart' ),
				'group'         => 'live',
				'type'          => 'secret',
				'label'         => __( 'Live webhook signing secret', 'releasecart' ),
				'description'   => __( 'Starts with whsec_. From the Stripe dashboard webhook endpoint.', 'releasecart' ),
				'storage'       => 'stripe',
				'stripe'        => array(
					'kind' => 'webhook_secret',
					'mode' => 'live',
				),
			),
			array(
				'id'          => 'integrations_elementor',
				'section'     => 'integrations',
				'type'        => 'toggle',
				'label'       => __( 'Elementor widgets', 'releasecart' ),
				'description' => __( 'Add the ReleaseCart widgets and dynamic tags to the Elementor editor. Turn off to remove them without deactivating Elementor.', 'releasecart' ),
				'path'        => 'integrations.elementor',
				'default'     => true,
			),
		);
	}
}

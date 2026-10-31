<?php
/**
 * Checkout template.
 *
 * Theme override: {theme}/releasecart/components/checkout.php.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="releasecart-component releasecart-checkout releasecart-checkout--<?php echo esc_attr( 'single' === ( $data['layout'] ?? 'two-column' ) ? 'single' : 'two-column' ); ?>" data-releasecart-checkout>
	<?php // Keep the width cap outside the theme content column. ?>
	<div class="releasecart-checkout__inner">
		<?php if ( '' !== ( $data['title'] ?? '' ) ) : ?>
			<h2 class="releasecart-checkout__title"><?php echo esc_html( $data['title'] ); ?></h2>
		<?php endif; ?>

		<?php if ( ! empty( $data['cart_error'] ) ) : ?>
			<div class="releasecart-checkout__cart-error releasecart-notice releasecart-notice--error" role="alert">
				<p><?php echo esc_html( $data['cart_error'] ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $data['renewal'] ) && is_array( $data['renewal'] ) ) : ?>
			<div class="releasecart-checkout__renewal">
				<p>
					<?php
					printf(
						/* translators: %s: product name. */
						esc_html__( 'You are buying a new license for %s. It comes with a new license key.', 'releasecart' ),
						esc_html( $data['renewal']['product_name'] )
					);
					?>
				</p>
				<?php if ( empty( $data['renewal']['in_cart'] ) ) : ?>
					<form method="post" action="<?php echo esc_url( $data['action_url'] ?? '' ); ?>">
						<input type="hidden" name="releasecart_cart_action" value="add">
						<input type="hidden" name="product_id" value="<?php echo esc_attr( (string) $data['renewal']['product_id'] ); ?>">
						<input type="hidden" name="price_id" value="<?php echo esc_attr( (string) $data['renewal']['price_id'] ); ?>">
						<?php wp_nonce_field( \DrPlugin\WP\ReleaseCart\Frontend\CartActionListener::NONCE_ACTION, \DrPlugin\WP\ReleaseCart\Frontend\CartActionListener::NONCE_FIELD ); ?>
						<button type="submit" class="releasecart-checkout__renewal-add releasecart-button releasecart-button--primary">
							<?php esc_html_e( 'Add it to the cart', 'releasecart' ); ?>
						</button>
					</form>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( 'confirmation' === $data['state'] ) : ?>
			<div
				class="releasecart-checkout__confirmation releasecart-card"
				role="status"
				aria-live="polite"
				data-order-uuid="<?php echo esc_attr( $data['order_uuid'] ); ?>"
				data-msg-confirmed="<?php esc_attr_e( 'Payment confirmed! Your receipt and any license keys are on their way to your inbox.', 'releasecart' ); ?>"
			>
				<h3><?php esc_html_e( 'Thank you for your purchase!', 'releasecart' ); ?></h3>
				<p class="releasecart-checkout__confirmation-status">
					<?php esc_html_e( 'We are confirming your payment. This page will update in a moment, and your receipt is on its way to your inbox.', 'releasecart' ); ?>
				</p>
			</div>
		<?php elseif ( 'empty' === $data['state'] ) : ?>
			<p class="releasecart-checkout__empty"><?php esc_html_e( 'Your cart is empty.', 'releasecart' ); ?></p>
		<?php elseif ( 'unavailable' === $data['state'] ) : ?>
			<div class="releasecart-checkout__unavailable" role="alert">
				<p><?php esc_html_e( 'Checkout is not available right now. Please try again later.', 'releasecart' ); ?></p>
				<?php if ( ! empty( $data['admin_hint'] ) ) : ?>
					<p><?php esc_html_e( 'Admins: add your Stripe keys in the ReleaseCart payment settings to enable checkout.', 'releasecart' ); ?></p>
				<?php endif; ?>
			</div>
		<?php elseif ( 'form' === $data['state'] ) : ?>
			<?php if ( ! empty( $data['cart_notices'] ) && is_array( $data['cart_notices'] ) ) : ?>
				<div class="releasecart-checkout__notice releasecart-notice releasecart-notice--info" role="status">
					<?php foreach ( $data['cart_notices'] as $releasecart_cart_notice ) : ?>
						<p><?php echo esc_html( (string) $releasecart_cart_notice ); ?></p>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php
			/* Keep the form first in reading order. */
			$releasecart_has_aside = ! empty( $data['show_summary'] );

			// A discount needs a payment form.
			$releasecart_show_discount = ! empty( $data['show_discount'] ) && empty( $data['login_required'] );
			?>
			<?php if ( $releasecart_has_aside ) : ?>
				<div class="releasecart-checkout__columns">
					<div class="releasecart-checkout__main">
			<?php endif; ?>
			<?php if ( empty( $data['login_required'] ) && ! empty( $data['login_html'] ) ) : ?>
				<details class="releasecart-checkout__login releasecart-card">
					<summary class="releasecart-checkout__login-toggle">
						<span class="releasecart-checkout__login-prompt"><?php esc_html_e( 'Returning customer?', 'releasecart' ); ?></span>
						<span class="releasecart-checkout__login-cta"><?php esc_html_e( 'Log in', 'releasecart' ); ?></span>
					</summary>
					<div class="releasecart-checkout__login-body">
						<?php echo wp_kses( $data['login_html'], \DrPlugin\WP\ReleaseCart\Frontend\Kses::allowed() ); ?>
					</div>
				</details>
			<?php endif; ?>

			<?php if ( ! empty( $data['login_required'] ) ) : ?>
				<div class="releasecart-checkout__login releasecart-checkout__login--required releasecart-card">
					<h3 class="releasecart-checkout__login-heading"><?php esc_html_e( 'Please log in to check out', 'releasecart' ); ?></h3>
					<p><?php esc_html_e( 'An account is required to complete your purchase.', 'releasecart' ); ?></p>
					<?php echo wp_kses( $data['login_html'], \DrPlugin\WP\ReleaseCart\Frontend\Kses::allowed() ); ?>
				</div>
			<?php else : ?>
				<?php if ( ! empty( $data['account_notice'] ) ) : ?>
				<div class="releasecart-checkout__account-notice releasecart-notice releasecart-notice--info" role="status">
					<p><?php echo esc_html( (string) $data['account_notice'] ); ?></p>
				</div>
			<?php endif; ?>
			<form
				class="releasecart-checkout__form releasecart-card"
				method="post"
				action="<?php echo esc_url( $data['action_url'] ?? '' ); ?>"
				data-msg-error="<?php esc_attr_e( 'Something went wrong. Please check your details and try again.', 'releasecart' ); ?>"
				data-msg-continue="<?php esc_attr_e( 'Enter your payment details above to finish.', 'releasecart' ); ?>"
				data-msg-reload="<?php esc_attr_e( 'Reload the page to sign in.', 'releasecart' ); ?>"
				<?php
				/* The checkout code knows the total changed, not why. */
				?>
				data-msg-total-changed="<?php esc_attr_e( 'Your total changed since this page loaded. Please review it before paying.', 'releasecart' ); ?>"
			>
				<div class="releasecart-checkout__section">
					<h3 class="releasecart-checkout__section-label"><?php esc_html_e( 'Contact', 'releasecart' ); ?></h3>
					<p class="releasecart-checkout__field releasecart-field">
						<label for="releasecart-checkout-email"><?php esc_html_e( 'Email address', 'releasecart' ); ?></label>
						<input
							type="email"
							class="releasecart-input" id="releasecart-checkout-email"
							name="email"
							autocomplete="email"
							required
							aria-required="true"
							value="<?php echo esc_attr( $data['email'] ); ?>"
							<?php echo ! empty( $data['email_readonly'] ) ? 'readonly' : ''; ?>
						>
					</p>
					<?php $releasecart_require_first = ! empty( $data['require_first_name'] ); ?>
					<p class="releasecart-checkout__field releasecart-field">
						<label for="releasecart-checkout-first-name"><?php echo $releasecart_require_first ? esc_html__( 'First name', 'releasecart' ) : esc_html__( 'First name (optional)', 'releasecart' ); ?></label>
						<input
							type="text"
							class="releasecart-input" id="releasecart-checkout-first-name"
							name="first_name"
							autocomplete="given-name"
							value="<?php echo esc_attr( (string) ( $data['first_name'] ?? '' ) ); ?>"
							<?php echo $releasecart_require_first ? 'required aria-required="true"' : ''; ?>
						>
					</p>
					<?php $releasecart_require_last = ! empty( $data['require_last_name'] ); ?>
					<p class="releasecart-checkout__field releasecart-field">
						<label for="releasecart-checkout-last-name"><?php echo $releasecart_require_last ? esc_html__( 'Last name', 'releasecart' ) : esc_html__( 'Last name (optional)', 'releasecart' ); ?></label>
						<input
							type="text"
							class="releasecart-input" id="releasecart-checkout-last-name"
							name="last_name"
							autocomplete="family-name"
							value="<?php echo esc_attr( (string) ( $data['last_name'] ?? '' ) ); ?>"
							<?php echo $releasecart_require_last ? 'required aria-required="true"' : ''; ?>
						>
					</p>
				</div>
				<?php
				/* Core billing fields for the quote and payment intent. */
				$releasecart_billing = isset( $data['billing'] ) && is_array( $data['billing'] ) ? $data['billing'] : array();

				// Show a heading only when billing fields render.
				$releasecart_has_billing = ! empty( $releasecart_billing['country']['show'] )
					|| ! empty( $releasecart_billing['company']['show'] )
					|| ! empty( $releasecart_billing['vat']['show'] )
					|| 'none' !== ( $releasecart_billing['address']['mode'] ?? 'none' )
					|| array() !== (array) ( $data['fields'] ?? array() );
				?>
				<?php if ( $releasecart_has_billing ) : ?>
					<div class="releasecart-checkout__section">
						<h3 class="releasecart-checkout__section-label"><?php esc_html_e( 'Billing', 'releasecart' ); ?></h3>
						<?php if ( ! empty( $releasecart_billing['country']['show'] ) ) : ?>
							<?php
							$releasecart_country_value       = (string) ( $releasecart_billing['country']['value'] ?? '' );
							$releasecart_country_required    = ! empty( $releasecart_billing['country']['required'] );
							$releasecart_country_placeholder = $releasecart_country_required && '' === $releasecart_country_value;
							$releasecart_country_label       = (string) ( $releasecart_billing['country']['label'] ?? '' );
							$releasecart_country_ph_text     = (string) ( $releasecart_billing['country']['placeholder'] ?? '' );
							$releasecart_country_help        = (string) ( $releasecart_billing['country']['help'] ?? '' );
							?>
							<p class="releasecart-checkout__field releasecart-field releasecart-checkout__field--country">
								<label for="releasecart-checkout-billing-country"><?php echo esc_html( $releasecart_country_label ); ?></label>
								<select
									class="releasecart-select" id="releasecart-checkout-billing-country"
									name="billing_country"
									data-releasecart-field="billing_country"
									autocomplete="country"
									<?php echo '' !== $releasecart_country_help ? 'aria-describedby="releasecart-checkout-billing-country-help"' : ''; ?>
									<?php echo $releasecart_country_required ? 'required aria-required="true"' : ''; ?>
								>
									<option value=""<?php echo $releasecart_country_required ? ' disabled' : ''; ?><?php echo $releasecart_country_placeholder ? ' selected' : ''; ?>><?php echo esc_html( $releasecart_country_ph_text ); ?></option>
									<?php foreach ( (array) ( $releasecart_billing['country']['options'] ?? array() ) as $releasecart_cc => $releasecart_cname ) : ?>
										<option value="<?php echo esc_attr( (string) $releasecart_cc ); ?>"<?php echo (string) $releasecart_cc === $releasecart_country_value ? ' selected' : ''; ?>><?php echo esc_html( (string) $releasecart_cname ); ?></option>
									<?php endforeach; ?>
								</select>
								<?php if ( '' !== $releasecart_country_help ) : ?>
									<span class="releasecart-checkout__help" id="releasecart-checkout-billing-country-help"><?php echo esc_html( $releasecart_country_help ); ?></span>
								<?php endif; ?>
							</p>
						<?php endif; ?>
						<?php
						/* Render the tax ID once to keep its ID unique. */
						$releasecart_company_show = ! empty( $releasecart_billing['company']['show'] );
						$releasecart_vat_show     = ! empty( $releasecart_billing['vat']['show'] );
						$releasecart_reveal_open  = ! empty( $releasecart_billing['company']['reveal_open'] );
						?>
						<?php if ( $releasecart_company_show ) : ?>
							<p class="releasecart-checkout__field releasecart-field releasecart-checkout__field--company-toggle">
								<label class="releasecart-checkout__company-check">
									<input
										type="checkbox"
										class="releasecart-checkbox" id="releasecart-checkout-company-toggle"
										aria-controls="releasecart-checkout-company-fields"
										<?php echo $releasecart_reveal_open ? 'checked' : ''; ?>
									>
									<span><?php esc_html_e( 'I am buying as a company', 'releasecart' ); ?></span>
								</label>
							</p>
							<div id="releasecart-checkout-company-fields" class="releasecart-checkout__company-fields">
								<p class="releasecart-checkout__field releasecart-field releasecart-checkout__field--company">
									<label for="releasecart-checkout-company"><?php echo esc_html( (string) ( $releasecart_billing['company']['label'] ?? '' ) ); ?></label>
									<input
										type="text"
										class="releasecart-input" id="releasecart-checkout-company"
										name="company_name"
										data-releasecart-field="company_name"
										autocomplete="organization"
										value="<?php echo esc_attr( (string) ( $releasecart_billing['company']['value'] ?? '' ) ); ?>"
									>
								</p>
						<?php endif; ?>
						<?php if ( $releasecart_vat_show ) : ?>
							<?php
							$releasecart_vat_label = (string) ( $releasecart_billing['vat']['label'] ?? '' );
							$releasecart_vat_ph    = (string) ( $releasecart_billing['vat']['placeholder'] ?? '' );
							$releasecart_vat_help  = (string) ( $releasecart_billing['vat']['help'] ?? '' );
							?>
							<p class="releasecart-checkout__field releasecart-field releasecart-checkout__field--vat">
								<label for="releasecart-checkout-vat"><?php echo esc_html( $releasecart_vat_label ); ?></label>
								<input
									type="text"
									class="releasecart-input" id="releasecart-checkout-vat"
									name="vat_number"
									data-releasecart-field="vat_number"
									autocomplete="off"
									<?php echo '' !== $releasecart_vat_ph ? 'placeholder="' . esc_attr( $releasecart_vat_ph ) . '"' : ''; ?>
									<?php echo '' !== $releasecart_vat_help ? 'aria-describedby="releasecart-checkout-vat-help"' : ''; ?>
									value="<?php echo esc_attr( (string) ( $releasecart_billing['vat']['value'] ?? '' ) ); ?>"
								>
								<?php if ( '' !== $releasecart_vat_help ) : ?>
									<span class="releasecart-checkout__help" id="releasecart-checkout-vat-help"><?php echo esc_html( $releasecart_vat_help ); ?></span>
								<?php endif; ?>
							</p>
						<?php endif; ?>
						<?php if ( $releasecart_company_show ) : ?>
							</div>
						<?php endif; ?>
						<?php if ( 'none' !== ( $releasecart_billing['address']['mode'] ?? 'none' ) ) : ?>
							<?php $releasecart_address_required = 'required' === ( $releasecart_billing['address']['mode'] ?? 'none' ); ?>
							<fieldset class="releasecart-checkout__address">
								<legend><?php esc_html_e( 'Billing address', 'releasecart' ); ?></legend>
								<p class="releasecart-checkout__field releasecart-field">
									<label for="releasecart-checkout-address-1"><?php esc_html_e( 'Address', 'releasecart' ); ?></label>
									<input type="text" class="releasecart-input" id="releasecart-checkout-address-1" name="billing_address_1" data-releasecart-field="billing_address_1" autocomplete="address-line1" <?php echo $releasecart_address_required ? 'required aria-required="true"' : ''; ?>>
								</p>
								<p class="releasecart-checkout__field releasecart-field">
									<label for="releasecart-checkout-address-2"><?php esc_html_e( 'Address line 2 (optional)', 'releasecart' ); ?></label>
									<input type="text" class="releasecart-input" id="releasecart-checkout-address-2" name="billing_address_2" data-releasecart-field="billing_address_2" autocomplete="address-line2">
								</p>
								<p class="releasecart-checkout__field releasecart-field">
									<label for="releasecart-checkout-city"><?php esc_html_e( 'City', 'releasecart' ); ?></label>
									<input type="text" class="releasecart-input" id="releasecart-checkout-city" name="billing_city" data-releasecart-field="billing_city" autocomplete="address-level2" <?php echo $releasecart_address_required ? 'required aria-required="true"' : ''; ?>>
								</p>
								<p class="releasecart-checkout__field releasecart-field">
									<label for="releasecart-checkout-state"><?php esc_html_e( 'State or region (optional)', 'releasecart' ); ?></label>
									<input type="text" class="releasecart-input" id="releasecart-checkout-state" name="billing_state" data-releasecart-field="billing_state" autocomplete="address-level1">
								</p>
								<p class="releasecart-checkout__field releasecart-field">
									<label for="releasecart-checkout-postcode"><?php esc_html_e( 'Postal code (optional)', 'releasecart' ); ?></label>
									<input type="text" class="releasecart-input" id="releasecart-checkout-postcode" name="billing_postcode" data-releasecart-field="billing_postcode" autocomplete="postal-code">
								</p>
							</fieldset>
						<?php endif; ?>
						<?php
						/* Extensions add fields used by the quote and payment intent. */
						foreach ( (array) ( $data['fields'] ?? array() ) as $releasecart_field ) :
							$releasecart_field_id   = 'releasecart-checkout-field-' . $releasecart_field['id'];
							$releasecart_field_help = '' !== $releasecart_field['help'] ? $releasecart_field_id . '-help' : '';
							?>
							<div class="releasecart-checkout__field releasecart-field releasecart-checkout__field--extra">
								<label for="<?php echo esc_attr( $releasecart_field_id ); ?>"><?php echo esc_html( $releasecart_field['label'] ); ?></label>
								<?php if ( 'select' === $releasecart_field['type'] ) : ?>
									<select
										id="<?php echo esc_attr( $releasecart_field_id ); ?>"
										data-releasecart-field="<?php echo esc_attr( $releasecart_field['id'] ); ?>"
										<?php echo '' !== $releasecart_field['autocomplete'] ? 'autocomplete="' . esc_attr( $releasecart_field['autocomplete'] ) . '"' : ''; ?>
										<?php echo '' !== $releasecart_field_help ? 'aria-describedby="' . esc_attr( $releasecart_field_help ) . '"' : ''; ?>
										<?php echo $releasecart_field['required'] ? 'required aria-required="true"' : ''; ?>
									>
										<?php
										/* Keep the required-select placeholder invalid until a choice. */
										$releasecart_placeholder_selected = $releasecart_field['required'] && '' === $releasecart_field['value'];
										?>
										<option value=""<?php echo $releasecart_field['required'] ? ' disabled' : ''; ?><?php echo $releasecart_placeholder_selected ? ' selected' : ''; ?>><?php echo esc_html( $releasecart_field['placeholder'] ); ?></option>
										<?php foreach ( (array) $releasecart_field['options'] as $releasecart_option_value => $releasecart_option_label ) : ?>
											<option value="<?php echo esc_attr( (string) $releasecart_option_value ); ?>"<?php echo (string) $releasecart_option_value === $releasecart_field['value'] ? ' selected' : ''; ?>><?php echo esc_html( (string) $releasecart_option_label ); ?></option>
										<?php endforeach; ?>
									</select>
								<?php elseif ( 'checkbox' === $releasecart_field['type'] ) : ?>
									<input
										type="checkbox"
										id="<?php echo esc_attr( $releasecart_field_id ); ?>"
										data-releasecart-field="<?php echo esc_attr( $releasecart_field['id'] ); ?>"
										value="1"
										<?php echo '' !== $releasecart_field_help ? 'aria-describedby="' . esc_attr( $releasecart_field_help ) . '"' : ''; ?>
										<?php echo $releasecart_field['required'] ? 'required aria-required="true"' : ''; ?>
										<?php echo '1' === $releasecart_field['value'] ? 'checked' : ''; ?>
									>
								<?php else : ?>
									<input
										type="<?php echo esc_attr( $releasecart_field['type'] ); ?>"
										id="<?php echo esc_attr( $releasecart_field_id ); ?>"
										data-releasecart-field="<?php echo esc_attr( $releasecart_field['id'] ); ?>"
										value="<?php echo esc_attr( $releasecart_field['value'] ); ?>"
										<?php echo '' !== $releasecart_field['placeholder'] ? 'placeholder="' . esc_attr( $releasecart_field['placeholder'] ) . '"' : ''; ?>
										<?php echo '' !== $releasecart_field['autocomplete'] ? 'autocomplete="' . esc_attr( $releasecart_field['autocomplete'] ) . '"' : ''; ?>
										<?php echo '' !== $releasecart_field_help ? 'aria-describedby="' . esc_attr( $releasecart_field_help ) . '"' : ''; ?>
										<?php echo $releasecart_field['required'] ? 'required aria-required="true"' : ''; ?>
									>
								<?php endif; ?>
								<?php if ( '' !== $releasecart_field_help ) : ?>
									<p class="releasecart-checkout__help" id="<?php echo esc_attr( $releasecart_field_help ); ?>"><?php echo esc_html( $releasecart_field['help'] ); ?></p>
								<?php endif; ?>
							</div>
							<?php
						endforeach;
						?>
					</div>
				<?php endif; ?>
				<div class="releasecart-checkout__section">
					<h3 class="releasecart-checkout__section-label"><?php esc_html_e( 'Payment', 'releasecart' ); ?></h3>
					<?php if ( ! empty( $releasecart_billing['terms']['show'] ) ) : ?>
						<?php
						$releasecart_terms_text   = esc_html__( 'Terms and Conditions', 'releasecart' );
						$releasecart_privacy_text = esc_html__( 'Privacy Policy', 'releasecart' );
						$releasecart_terms_url    = $releasecart_billing['terms']['terms_url'] ?? null;
						$releasecart_privacy_url  = $releasecart_billing['terms']['privacy_url'] ?? null;
						$releasecart_terms_link   = null !== $releasecart_terms_url
							? '<a href="' . esc_url( (string) $releasecart_terms_url ) . '" target="_blank" rel="noopener noreferrer">' . $releasecart_terms_text . '</a>'
							: $releasecart_terms_text;
						$releasecart_privacy_link = null !== $releasecart_privacy_url
							? '<a href="' . esc_url( (string) $releasecart_privacy_url ) . '" target="_blank" rel="noopener noreferrer">' . $releasecart_privacy_text . '</a>'
							: $releasecart_privacy_text;
						$releasecart_terms_label  = sprintf(
							/* translators: 1: link to or name of the terms page, 2: link to or name of the privacy policy page. */
							esc_html__( 'I agree to the %1$s and %2$s.', 'releasecart' ),
							$releasecart_terms_link,
							$releasecart_privacy_link
						);
						$releasecart_terms_allowed = array(
							'a' => array(
								'href'   => array(),
								'target' => array(),
								'rel'    => array(),
							),
						);
						?>
						<p class="releasecart-checkout__field releasecart-field releasecart-checkout__field--terms">
							<label class="releasecart-checkout__terms">
								<input
									type="checkbox"
									class="releasecart-checkbox" id="releasecart-checkout-terms"
									name="terms_accepted"
									data-releasecart-field="terms_accepted"
									value="1"
									required
									aria-required="true"
								>
								<span><?php echo wp_kses( $releasecart_terms_label, $releasecart_terms_allowed ); ?></span>
							</label>
						</p>
					<?php endif; ?>
					<?php $releasecart_gateways = isset( $data['gateways'] ) && is_array( $data['gateways'] ) ? $data['gateways'] : array(); ?>
					<?php $releasecart_default_gateway = (string) ( $data['default_gateway'] ?? '' ); ?>

					<?php if ( count( $releasecart_gateways ) > 1 ) : ?>
						<fieldset class="releasecart-checkout__gateways" data-releasecart-gateways>
							<legend><?php esc_html_e( 'Payment method', 'releasecart' ); ?></legend>
							<?php foreach ( $releasecart_gateways as $releasecart_gid => $releasecart_glabel ) : ?>
								<label class="releasecart-checkout__gateway-choice">
									<input
										type="radio"
										name="releasecart_gateway"
										value="<?php echo esc_attr( (string) $releasecart_gid ); ?>"
										data-releasecart-gateway-radio
										<?php echo ( (string) $releasecart_gid === $releasecart_default_gateway ) ? 'checked' : ''; ?>
									>
									<span><?php echo esc_html( (string) $releasecart_glabel ); ?></span>
								</label>
							<?php endforeach; ?>
						</fieldset>
					<?php endif; ?>

					<?php foreach ( $releasecart_gateways as $releasecart_gid => $releasecart_glabel ) : ?>
						<div
							class="releasecart-checkout__gateway"
							data-gateway="<?php echo esc_attr( (string) $releasecart_gid ); ?>"
							<?php echo ( (string) $releasecart_gid === $releasecart_default_gateway ) ? '' : 'hidden'; ?>
						>
							<?php if ( 'stripe' === (string) $releasecart_gid ) : ?>
								<div id="releasecart-checkout-payment" class="releasecart-checkout__payment" aria-label="<?php esc_attr_e( 'Payment details', 'releasecart' ); ?>"></div>
							<?php endif; ?>
							<button type="submit" class="releasecart-checkout__submit releasecart-button releasecart-button--primary" data-releasecart-submit data-gateway="<?php echo esc_attr( (string) $releasecart_gid ); ?>">
								<?php
								if ( 'stripe' === (string) $releasecart_gid ) {
									echo esc_html( $data['button_text'] );
								} else {
									printf(
										/* translators: %s: payment gateway name. */
										esc_html__( 'Continue with %s', 'releasecart' ),
										esc_html( (string) $releasecart_glabel )
									);
								}
								?>
							</button>
							<p class="releasecart-checkout__secure"><?php esc_html_e( 'Payments are processed securely.', 'releasecart' ); ?></p>
						</div>
					<?php endforeach; ?>
					<div class="releasecart-checkout__errors" role="alert" aria-live="assertive"></div>
					<noscript>
						<p class="releasecart-checkout__noscript">
							<?php esc_html_e( 'JavaScript is required to complete your payment securely. Please enable it and reload this page.', 'releasecart' ); ?>
						</p>
					</noscript>
				</div>
			</form>
			<?php endif; ?>
			<?php if ( $releasecart_has_aside ) : ?>
				</div><?php // .releasecart-checkout__main ?>
				<div class="releasecart-checkout__aside">
					<?php
					// The editable summary lets the buyer change a quantity or drop a
					// line straight from the aside. Its forms are CartActionListener
					// POSTs, and they must stay OUTSIDE the payment form (nested forms
					// silently flatten): the aside is a sibling of the form column, so
					// the placement guarantees it. A merchant can turn this off for a
					// read-only summary, which keeps the plain "x N" quantity badge.
					$releasecart_summary_editable = ! empty( $data['editable_summary'] );
					?>
					<section class="releasecart-checkout__summary releasecart-card" aria-label="<?php esc_attr_e( 'Order summary', 'releasecart' ); ?>">
						<h3><?php esc_html_e( 'Order summary', 'releasecart' ); ?></h3>
						<ul class="releasecart-checkout__lines">
							<?php foreach ( (array) $data['lines'] as $releasecart_line ) : ?>
								<?php $releasecart_line_editable = $releasecart_summary_editable && empty( $releasecart_line['fixed_single'] ); ?>
								<li class="releasecart-checkout__line<?php echo $releasecart_summary_editable ? ' releasecart-checkout__line--editable' : ''; ?>">
									<span class="releasecart-checkout__line-name">
										<?php echo esc_html( $releasecart_line['name'] ); ?>
										<?php if ( '' !== $releasecart_line['price_name'] ) : ?>
											<span class="releasecart-checkout__line-tier"><?php echo esc_html( $releasecart_line['price_name'] ); ?></span>
										<?php endif; ?>
									</span>
									<?php // A read-only summary, or a fixed-single line with no editable quantity, shows the figure as a badge. ?>
									<?php if ( ! $releasecart_line_editable ) : ?>
										<span class="releasecart-checkout__line-qty releasecart-badge">
											<?php
											printf(
												/* translators: %d: quantity of one cart line. */
												esc_html__( 'x %d', 'releasecart' ),
												(int) $releasecart_line['quantity']
											);
											?>
										</span>
									<?php endif; ?>
									<span class="releasecart-checkout__line-amount"><?php echo esc_html( $releasecart_line['subtotal'] ); ?></span>
									<?php
									// The plan-detail meta lines repeat the tier facts from the product page.
									// They drop to their own full-width row beneath the name and amount, above
									// any edit controls. Empty when the tier is gone or the toggle is off.
									$releasecart_line_meta = ( ! empty( $data['show_plan_details'] ) && ! empty( $releasecart_line['meta'] ) && is_array( $releasecart_line['meta'] ) )
										? $releasecart_line['meta']
										: array();
									?>
									<?php if ( array() !== $releasecart_line_meta ) : ?>
										<ul class="releasecart-checkout__line-meta">
											<?php foreach ( $releasecart_line_meta as $releasecart_meta_line ) : ?>
												<li class="releasecart-checkout__line-meta-item"><?php echo esc_html( (string) $releasecart_meta_line ); ?></li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
									<?php
									if ( $releasecart_summary_editable ) {
										$releasecart_line_controls = array(
											'product_id'   => $releasecart_line['product_id'] ?? 0,
											'price_id'     => $releasecart_line['price_id'] ?? 0,
											'quantity'     => $releasecart_line['quantity'] ?? 1,
											'min'          => $releasecart_line['min'] ?? 1,
											'max'          => $releasecart_line['max'] ?? null,
											'fixed_single' => ! empty( $releasecart_line['fixed_single'] ),
											'action_url'   => $data['action_url'] ?? '',
											// Scope the control id to this surface so a page that also carries a
											// cart-summary block does not emit the same quantity-input id twice.
											'id'           => 'checkout-' . ( $releasecart_line['product_id'] ?? 0 ) . '-' . ( $releasecart_line['price_id'] ?? 0 ),
										);
										require RELEASECART_PATH . 'templates/components/partials/cart-line-controls.php';
									}
									?>
								</li>
							<?php endforeach; ?>
						</ul>
						<?php
						// A stored valid discount code renders its line server-side, in the
						// exact node shape the checkout module's writeDiscount() builds, so
						// the JS updates or removes this line rather than duplicating it. It
						// carries data-discount-minor so reconcileTotal can attribute a
						// discount that dies before payment.
						$releasecart_discount = isset( $data['discount'] ) && is_array( $data['discount'] ) ? $data['discount'] : array();
						?>
						<?php if ( 'applied' === ( $releasecart_discount['state'] ?? '' ) ) : ?>
							<p class="releasecart-checkout__discount-line" data-releasecart-discount-line data-discount-minor="<?php echo esc_attr( (string) ( $releasecart_discount['amount_minor'] ?? 0 ) ); ?>">
								<span><?php echo esc_html( (string) ( $releasecart_discount['label'] ?? '' ) ); ?></span>
								<span data-releasecart-discount-amount>-<?php echo esc_html( (string) ( $releasecart_discount['amount'] ?? '' ) ); ?></span>
							</p>
						<?php endif; ?>
						<?php
						// The automatic (no-code) discount rides its own line, right after the code
						// line, in the node shape the module's writeAutoDiscount() builds. It carries
						// its own data-discount-minor so reconcileTotal can sum both reductions.
						$releasecart_auto = isset( $releasecart_discount['auto'] ) && is_array( $releasecart_discount['auto'] ) ? $releasecart_discount['auto'] : array();
						?>
						<?php if ( ! empty( $releasecart_auto['applied'] ) ) : ?>
							<p class="releasecart-checkout__discount-line" data-releasecart-auto-discount-line data-discount-minor="<?php echo esc_attr( (string) ( $releasecart_auto['amount_minor'] ?? 0 ) ); ?>">
								<span><?php echo esc_html( (string) ( $releasecart_auto['label'] ?? '' ) ); ?></span>
								<span data-releasecart-auto-discount-amount>-<?php echo esc_html( (string) ( $releasecart_auto['amount'] ?? '' ) ); ?></span>
							</p>
						<?php endif; ?>
						<p
							class="releasecart-checkout__tax"
							data-releasecart-tax-line
							data-tax-minor="<?php echo esc_attr( (string) ( $data['tax_minor'] ?? 0 ) ); ?>"
							<?php echo empty( $data['tax_minor'] ) ? 'hidden' : ''; ?>
						>
							<span><?php echo esc_html( $data['tax_label'] ?? __( 'Tax', 'releasecart' ) ); ?></span>
							<strong data-releasecart-tax-amount><?php echo esc_html( (string) ( $data['tax'] ?? '' ) ); ?></strong>
						</p>
						<p class="releasecart-checkout__total">
							<span><?php esc_html_e( 'Total', 'releasecart' ); ?></span>
							<strong data-total-minor="<?php echo esc_attr( (string) ( $data['total_minor'] ?? '' ) ); ?>"><?php echo esc_html( $data['total'] ); ?></strong>
						</p>
					</section>
					<?php if ( $releasecart_show_discount ) : ?>
						<?php require RELEASECART_PATH . 'templates/components/partials/checkout-discount.php'; ?>
					<?php endif; ?>
				</div>
			</div><?php // .releasecart-checkout__columns ?>
			<?php endif; ?>
			<?php // No aside to host the discount section: it falls to a sibling right after the form. ?>
			<?php if ( ! $releasecart_has_aside && $releasecart_show_discount ) : ?>
				<?php require RELEASECART_PATH . 'templates/components/partials/checkout-discount.php'; ?>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>

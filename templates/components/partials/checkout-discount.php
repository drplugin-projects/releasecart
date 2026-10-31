<?php
/**
 * Checkout discount controls.
 *
 * The forms work without JavaScript.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

defined( 'ABSPATH' ) || exit;

/* translators: %s: the discount code the buyer applied. */
$releasecart_discount_applied_tpl = __( 'Code %s applied', 'releasecart' );

$releasecart_discount       = isset( $data['discount'] ) && is_array( $data['discount'] ) ? $data['discount'] : array();
$releasecart_discount_state = (string) ( $releasecart_discount['state'] ?? '' );
$releasecart_is_applied     = 'applied' === $releasecart_discount_state;
$releasecart_is_invalid     = 'invalid' === $releasecart_discount_state;
$releasecart_discount_code  = (string) ( $releasecart_discount['code'] ?? '' );
$releasecart_discount_error = (string) ( $releasecart_discount['error'] ?? '' );
$releasecart_action_url     = (string) ( $data['action_url'] ?? '' );

?>
<section
	class="releasecart-checkout__discount releasecart-card"
	aria-label="<?php esc_attr_e( 'Discount code', 'releasecart' ); ?>"
	data-msg-applied="<?php echo esc_attr( $releasecart_discount_applied_tpl ); ?>"
	data-msg-error="<?php esc_attr_e( 'The discount could not be applied. Please try again.', 'releasecart' ); ?>"
>
	<div class="releasecart-checkout__discount-field releasecart-field">
		<label for="releasecart-checkout-discount"><?php esc_html_e( 'Discount code', 'releasecart' ); ?></label>
		<?php // A normal POST keeps Apply working without JavaScript. ?>
		<form class="releasecart-checkout__discount-apply-form" method="post" action="<?php echo esc_url( $releasecart_action_url ); ?>">
			<input type="hidden" name="releasecart_cart_action" value="apply_discount">
			<?php wp_nonce_field( \DrPlugin\WP\ReleaseCart\Frontend\CartActionListener::NONCE_ACTION, \DrPlugin\WP\ReleaseCart\Frontend\CartActionListener::NONCE_FIELD ); ?>
			<div class="releasecart-checkout__discount-row"<?php echo $releasecart_is_applied ? ' hidden' : ''; ?>>
				<input
					type="text"
					class="releasecart-input" id="releasecart-checkout-discount"
					name="discount_code"
					autocomplete="off"
					autocapitalize="characters"
					spellcheck="false"
					maxlength="50"
				>
				<button type="submit" class="releasecart-checkout__discount-apply releasecart-button releasecart-button--secondary" data-releasecart-discount-apply>
					<?php esc_html_e( 'Apply', 'releasecart' ); ?>
				</button>
			</div>
		</form>
		<?php // Removing a code uses a separate PRG form. ?>
		<div class="releasecart-checkout__discount-chip" data-releasecart-discount-chip<?php echo $releasecart_is_applied ? '' : ' hidden'; ?>>
			<span class="releasecart-checkout__discount-chip-label" data-releasecart-discount-chip-label>
				<?php
				if ( $releasecart_is_applied ) {
					echo esc_html( sprintf( $releasecart_discount_applied_tpl, $releasecart_discount_code ) );
				}
				?>
			</span>
			<form class="releasecart-checkout__discount-remove-form" method="post" action="<?php echo esc_url( $releasecart_action_url ); ?>">
				<input type="hidden" name="releasecart_cart_action" value="remove_discount">
				<?php wp_nonce_field( \DrPlugin\WP\ReleaseCart\Frontend\CartActionListener::NONCE_ACTION, \DrPlugin\WP\ReleaseCart\Frontend\CartActionListener::NONCE_FIELD ); ?>
				<button type="submit" class="releasecart-checkout__discount-remove releasecart-button releasecart-button--ghost releasecart-button--small" data-releasecart-discount-remove>
					<?php esc_html_e( 'Remove', 'releasecart' ); ?>
				</button>
			</form>
		</div>
		<p class="releasecart-checkout__discount-error" data-releasecart-discount-error role="alert"<?php echo $releasecart_is_invalid ? '' : ' hidden'; ?>><?php echo $releasecart_is_invalid ? esc_html( $releasecart_discount_error ) : ''; ?></p>
	</div>
	<noscript>
		<p class="releasecart-checkout__discount-note"><?php esc_html_e( 'You can apply or remove a discount code without JavaScript. Completing payment still requires JavaScript.', 'releasecart' ); ?></p>
	</noscript>
</section>

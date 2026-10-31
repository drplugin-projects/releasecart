<?php
/**
 * Cart line controls for cart and checkout.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array{
 *     product_id: int,
 *     price_id: int,
 *     quantity: int,
 *     min: int,
 *     max: int|null,
 *     fixed_single: bool,
 *     action_url: string,
 *     id?: string
 * } $releasecart_line_controls Line-control data supplied by the including template.
 */

defined( 'ABSPATH' ) || exit;

$releasecart_line_controls = ( isset( $releasecart_line_controls ) && is_array( $releasecart_line_controls ) ) ? $releasecart_line_controls : array();

$releasecart_lc_product = (int) ( $releasecart_line_controls['product_id'] ?? 0 );
$releasecart_lc_price   = (int) ( $releasecart_line_controls['price_id'] ?? 0 );
$releasecart_lc_qty     = (int) ( $releasecart_line_controls['quantity'] ?? 1 );
$releasecart_lc_min     = (int) ( $releasecart_line_controls['min'] ?? 1 );
$releasecart_lc_max     = ( array_key_exists( 'max', $releasecart_line_controls ) && null !== $releasecart_line_controls['max'] ) ? (int) $releasecart_line_controls['max'] : null;
$releasecart_lc_fixed   = ! empty( $releasecart_line_controls['fixed_single'] );
$releasecart_lc_action  = (string) ( $releasecart_line_controls['action_url'] ?? '' );
$releasecart_lc_id      = (string) ( $releasecart_line_controls['id'] ?? ( $releasecart_lc_product . '-' . $releasecart_lc_price ) );
?>
<div class="releasecart-cart-line-controls">
	<?php
	// Single-unit tiers have no quantity control.
	?>
	<?php if ( ! $releasecart_lc_fixed ) : ?>
		<form method="post" action="<?php echo esc_url( $releasecart_lc_action ); ?>" class="releasecart-cart-line-controls__qty-form">
			<input type="hidden" name="releasecart_cart_action" value="update">
			<input type="hidden" name="product_id" value="<?php echo esc_attr( (string) $releasecart_lc_product ); ?>">
			<input type="hidden" name="price_id" value="<?php echo esc_attr( (string) $releasecart_lc_price ); ?>">
			<label class="screen-reader-text" for="releasecart-cart-qty-<?php echo esc_attr( $releasecart_lc_id ); ?>">
				<?php echo esc_html__( 'Quantity', 'releasecart' ); ?>
			</label>
			<input
				type="number"
				class="releasecart-input releasecart-checkout__line-qty-input"
				id="releasecart-cart-qty-<?php echo esc_attr( $releasecart_lc_id ); ?>"
				name="quantity"
				value="<?php echo esc_attr( (string) $releasecart_lc_qty ); ?>"
				min="<?php echo esc_attr( (string) $releasecart_lc_min ); ?>"
				<?php if ( null !== $releasecart_lc_max ) : ?>
					max="<?php echo esc_attr( (string) $releasecart_lc_max ); ?>"
				<?php endif; ?>
			>
			<?php wp_nonce_field( \DrPlugin\WP\ReleaseCart\Frontend\CartActionListener::NONCE_ACTION, \DrPlugin\WP\ReleaseCart\Frontend\CartActionListener::NONCE_FIELD ); ?>
			<button type="submit" class="releasecart-cart-line-controls__update releasecart-button releasecart-button--secondary releasecart-button--small"><?php echo esc_html__( 'Update', 'releasecart' ); ?></button>
		</form>
	<?php endif; ?>
	<form method="post" action="<?php echo esc_url( $releasecart_lc_action ); ?>" class="releasecart-cart-line-controls__remove-form">
		<input type="hidden" name="releasecart_cart_action" value="remove">
		<input type="hidden" name="product_id" value="<?php echo esc_attr( (string) $releasecart_lc_product ); ?>">
		<input type="hidden" name="price_id" value="<?php echo esc_attr( (string) $releasecart_lc_price ); ?>">
		<?php wp_nonce_field( \DrPlugin\WP\ReleaseCart\Frontend\CartActionListener::NONCE_ACTION, \DrPlugin\WP\ReleaseCart\Frontend\CartActionListener::NONCE_FIELD ); ?>
		<button type="submit" class="releasecart-checkout__line-remove releasecart-button releasecart-button--danger releasecart-button--small"><?php echo esc_html__( 'Remove', 'releasecart' ); ?></button>
	</form>
</div>

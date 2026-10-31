<?php
/**
 * Buy button template.
 *
 * Theme override: {theme}/releasecart/components/buy-button.php.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array{action_url: string, product_id: int, price_id: int, quantity: int, label: string, price_html: string, nonce_action: string, nonce_field: string} $data View data.
 */

defined( 'ABSPATH' ) || exit;

$releasecart_notices = \DrPlugin\WP\ReleaseCart\Frontend\CartNotices::view();
?>
<div class="releasecart-component releasecart-buy-button">
	<?php require RELEASECART_PATH . 'templates/components/partials/cart-notice.php'; ?>
	<form class="releasecart-buy-button__form" method="post" action="<?php echo esc_url( $data['action_url'] ); ?>">
		<input type="hidden" name="releasecart_cart_action" value="add">
		<input type="hidden" name="product_id" value="<?php echo esc_attr( (string) $data['product_id'] ); ?>">
		<input type="hidden" name="price_id" value="<?php echo esc_attr( (string) $data['price_id'] ); ?>">
		<input type="hidden" name="quantity" value="<?php echo esc_attr( (string) ( $data['quantity'] ?? 1 ) ); ?>">
		<?php wp_nonce_field( $data['nonce_action'], $data['nonce_field'] ); ?>
		<button type="submit" class="releasecart-buy-button__button releasecart-button releasecart-button--primary">
			<span class="releasecart-buy-button__label"><?php echo esc_html( $data['label'] ); ?></span>
			<?php if ( '' !== $data['price_html'] ) : ?>
				<span class="releasecart-buy-button__price"><?php echo wp_kses( $data['price_html'], \DrPlugin\WP\ReleaseCart\Frontend\Kses::allowed() ); ?></span>
			<?php endif; ?>
		</button>
	</form>
</div>

<?php
/**
 * Shared cart notice markup.
 *
 * The notice helper supplies safe messages for all purchase templates.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array{success: array{show: bool, text: string, checkout_url: string|null}, error: array{show: bool, text: string}} $releasecart_notices Notice data.
 */

defined( 'ABSPATH' ) || exit;

$releasecart_notices = ( isset( $releasecart_notices ) && is_array( $releasecart_notices ) ) ? $releasecart_notices : array();
$releasecart_success = ( isset( $releasecart_notices['success'] ) && is_array( $releasecart_notices['success'] ) ) ? $releasecart_notices['success'] : array( 'show' => false );
$releasecart_error   = ( isset( $releasecart_notices['error'] ) && is_array( $releasecart_notices['error'] ) ) ? $releasecart_notices['error'] : array( 'show' => false );
?>
<?php if ( ! empty( $releasecart_success['show'] ) ) : ?>
	<div class="releasecart-cart-notice releasecart-cart-notice--success" role="status">
		<p class="releasecart-cart-notice__text"><?php echo esc_html( (string) ( $releasecart_success['text'] ?? '' ) ); ?></p>
		<?php if ( ! empty( $releasecart_success['checkout_url'] ) ) : ?>
			<a class="releasecart-cart-notice__checkout" href="<?php echo esc_url( (string) $releasecart_success['checkout_url'] ); ?>"><?php echo esc_html__( 'Go to checkout', 'releasecart' ); ?></a>
		<?php endif; ?>
	</div>
<?php endif; ?>
<?php if ( ! empty( $releasecart_error['show'] ) ) : ?>
	<div class="releasecart-cart-notice releasecart-cart-notice--error" role="alert">
		<p class="releasecart-cart-notice__text"><?php echo esc_html( (string) ( $releasecart_error['text'] ?? '' ) ); ?></p>
	</div>
<?php endif; ?>

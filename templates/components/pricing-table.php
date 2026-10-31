<?php
/**
 * Pricing table template.
 *
 * Theme override: {theme}/releasecart/components/pricing-table.php.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array{columns: string, button_text: string, popular_label: string, action_url: string, nonce_action: string, nonce_field: string, plans: array<int, array<string, mixed>>} $data View data.
 */

defined( 'ABSPATH' ) || exit;

$releasecart_notices = \DrPlugin\WP\ReleaseCart\Frontend\CartNotices::view();
?>
<section class="releasecart-component releasecart-pricing-table" data-columns="<?php echo esc_attr( $data['columns'] ); ?>">
	<?php require RELEASECART_PATH . 'templates/components/partials/cart-notice.php'; ?>
	<?php foreach ( $data['plans'] as $releasecart_plan ) : ?>
		<article class="<?php echo esc_attr( $releasecart_plan['plan_class'] ); ?>">
			<?php if ( $releasecart_plan['is_highlight'] ) : ?>
				<span class="releasecart-pricing-table__badge"><?php echo esc_html( $data['popular_label'] ); ?></span>
			<?php endif; ?>

			<h3 class="releasecart-pricing-table__name"><?php echo esc_html( $releasecart_plan['name'] ); ?></h3>

			<p class="releasecart-pricing-table__price">
				<span class="releasecart-pricing-table__amount"><?php echo wp_kses( $releasecart_plan['price_html'], \DrPlugin\WP\ReleaseCart\Frontend\Kses::allowed() ); ?></span>
				<span class="releasecart-pricing-table__tier"><?php echo esc_html( $releasecart_plan['tier_name'] ); ?></span>
			</p>

			<?php
			$releasecart_meta = ( isset( $releasecart_plan['meta'] ) && is_array( $releasecart_plan['meta'] ) ) ? $releasecart_plan['meta'] : array();
			?>
			<?php if ( array() !== $releasecart_meta ) : ?>
				<ul class="releasecart-pricing-table__tier-meta">
					<?php foreach ( $releasecart_meta as $releasecart_line ) : ?>
						<li class="releasecart-pricing-table__tier-meta-item"><?php echo esc_html( (string) $releasecart_line ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( '' !== $releasecart_plan['description'] ) : ?>
				<div class="releasecart-pricing-table__description"><?php echo wp_kses_post( $releasecart_plan['description'] ); ?></div>
			<?php endif; ?>

			<?php if ( array() !== $releasecart_plan['features'] ) : ?>
				<ul class="releasecart-pricing-table__features">
					<?php foreach ( $releasecart_plan['features'] as $releasecart_feature ) : ?>
						<li class="releasecart-pricing-table__feature"><?php echo esc_html( $releasecart_feature ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<form class="releasecart-pricing-table__form" method="post" action="<?php echo esc_url( $data['action_url'] ); ?>">
				<input type="hidden" name="releasecart_cart_action" value="add">
				<input type="hidden" name="product_id" value="<?php echo esc_attr( (string) $releasecart_plan['product_id'] ); ?>">
				<input type="hidden" name="price_id" value="<?php echo esc_attr( (string) $releasecart_plan['price_id'] ); ?>">
				<input type="hidden" name="quantity" value="<?php echo esc_attr( (string) ( $releasecart_plan['quantity'] ?? 1 ) ); ?>">
				<?php wp_nonce_field( $data['nonce_action'], $data['nonce_field'] ); ?>
				<button type="submit" class="releasecart-pricing-table__button releasecart-button releasecart-button--primary"><?php echo esc_html( $data['button_text'] ); ?></button>
			</form>
		</article>
	<?php endforeach; ?>
</section>

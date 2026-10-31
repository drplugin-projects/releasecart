<?php
/**
 * Purchase form shared by product templates.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array{
 *     block: string,
 *     tiers: array<int, array{id: int, name: string, price_html: string, is_default: bool, min: int, max: int|null, fixed_single: bool, meta: array<int, string>}>,
 *     has_tiers: bool,
 *     quantity: array{show: bool, min: int, max: int|null, value: int},
 *     product_id: int,
 *     action_url: string,
 *     nonce_action: string,
 *     nonce_field: string,
 *     add_label: string
 * } $releasecart_form Purchase-form data supplied by the including template.
 */

defined( 'ABSPATH' ) || exit;

$releasecart_form = ( isset( $releasecart_form ) && is_array( $releasecart_form ) ) ? $releasecart_form : array();

if ( empty( $releasecart_form['has_tiers'] ) ) {
	return;
}

$releasecart_block       = (string) ( $releasecart_form['block'] ?? 'releasecart-product-pricing-tiers' );
$releasecart_tiers       = ( isset( $releasecart_form['tiers'] ) && is_array( $releasecart_form['tiers'] ) ) ? $releasecart_form['tiers'] : array();
$releasecart_quantity    = ( isset( $releasecart_form['quantity'] ) && is_array( $releasecart_form['quantity'] ) ) ? $releasecart_form['quantity'] : array( 'show' => false );
$releasecart_quantity_id = $releasecart_block . '-quantity';

$releasecart_notices = \DrPlugin\WP\ReleaseCart\Frontend\CartNotices::view();
require RELEASECART_PATH . 'templates/components/partials/cart-notice.php';
?>
<form class="<?php echo esc_attr( $releasecart_block ); ?>__purchase" method="post" action="<?php echo esc_url( (string) $releasecart_form['action_url'] ); ?>">
	<input type="hidden" name="releasecart_cart_action" value="add" />
	<input type="hidden" name="product_id" value="<?php echo esc_attr( (string) $releasecart_form['product_id'] ); ?>" />

	<?php if ( 1 === count( $releasecart_tiers ) ) : ?>
		<?php
		$releasecart_only = $releasecart_tiers[0];
		$releasecart_meta = ( isset( $releasecart_only['meta'] ) && is_array( $releasecart_only['meta'] ) ) ? $releasecart_only['meta'] : array();
		?>
		<input type="hidden" name="price_id" value="<?php echo esc_attr( (string) $releasecart_only['id'] ); ?>" />
		<p class="<?php echo esc_attr( $releasecart_block ); ?>__price">
			<span class="<?php echo esc_attr( $releasecart_block ); ?>__tier-name"><?php echo esc_html( (string) $releasecart_only['name'] ); ?></span>
			<span class="<?php echo esc_attr( $releasecart_block ); ?>__tier-price"><?php echo wp_kses( $releasecart_only['price_html'], \DrPlugin\WP\ReleaseCart\Frontend\Kses::allowed() ); ?></span>
		</p>
		<?php if ( array() !== $releasecart_meta ) : ?>
			<ul class="<?php echo esc_attr( $releasecart_block ); ?>__tier-meta">
				<?php foreach ( $releasecart_meta as $releasecart_line ) : ?>
					<li class="<?php echo esc_attr( $releasecart_block ); ?>__tier-meta-item"><?php echo esc_html( (string) $releasecart_line ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	<?php else : ?>
		<fieldset class="<?php echo esc_attr( $releasecart_block ); ?>__tiers">
			<legend class="<?php echo esc_attr( $releasecart_block ); ?>__tiers-legend"><?php echo esc_html__( 'Choose a plan', 'releasecart' ); ?></legend>
			<?php foreach ( $releasecart_tiers as $releasecart_tier ) : ?>
				<?php
				$releasecart_tier_id  = $releasecart_block . '-tier-' . (string) $releasecart_tier['id'];
				$releasecart_meta     = ( isset( $releasecart_tier['meta'] ) && is_array( $releasecart_tier['meta'] ) ) ? $releasecart_tier['meta'] : array();
				$releasecart_meta_id  = $releasecart_tier_id . '-meta';
				$releasecart_has_meta = array() !== $releasecart_meta;
				?>
				<div class="<?php echo esc_attr( $releasecart_block ); ?>__tier">
					<input
						type="radio"
						id="<?php echo esc_attr( $releasecart_tier_id ); ?>"
						name="price_id"
						value="<?php echo esc_attr( (string) $releasecart_tier['id'] ); ?>"<?php checked( (bool) $releasecart_tier['is_default'], true ); ?>
						data-min="<?php echo esc_attr( (string) $releasecart_tier['min'] ); ?>"
						data-max="<?php echo esc_attr( null === $releasecart_tier['max'] ? '0' : (string) $releasecart_tier['max'] ); ?>"
						data-fixed="<?php echo esc_attr( $releasecart_tier['fixed_single'] ? '1' : '0' ); ?>"
						<?php echo $releasecart_has_meta ? 'aria-describedby="' . esc_attr( $releasecart_meta_id ) . '"' : ''; ?>
					/>
					<label class="<?php echo esc_attr( $releasecart_block ); ?>__tier-label" for="<?php echo esc_attr( $releasecart_tier_id ); ?>">
						<span class="<?php echo esc_attr( $releasecart_block ); ?>__tier-name"><?php echo esc_html( (string) $releasecart_tier['name'] ); ?></span>
						<span class="<?php echo esc_attr( $releasecart_block ); ?>__tier-price"><?php echo wp_kses( $releasecart_tier['price_html'], \DrPlugin\WP\ReleaseCart\Frontend\Kses::allowed() ); ?></span>
					</label>
					<?php if ( $releasecart_has_meta ) : ?>
						<ul class="<?php echo esc_attr( $releasecart_block ); ?>__tier-meta" id="<?php echo esc_attr( $releasecart_meta_id ); ?>">
							<?php foreach ( $releasecart_meta as $releasecart_line ) : ?>
								<li class="<?php echo esc_attr( $releasecart_block ); ?>__tier-meta-item"><?php echo esc_html( (string) $releasecart_line ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</fieldset>
	<?php endif; ?>

	<?php
	// Fixed single-unit tiers show plain text. The hidden input still posts one.
	?>
	<div class="<?php echo esc_attr( $releasecart_block ); ?>__quantity"<?php echo ( empty( $releasecart_quantity['show'] ) ) ? ' hidden' : ''; ?>>
		<label class="<?php echo esc_attr( $releasecart_block ); ?>__quantity-label" for="<?php echo esc_attr( $releasecart_quantity_id ); ?>"><?php echo esc_html__( 'Quantity', 'releasecart' ); ?></label>
		<input
			type="number"
			class="releasecart-input"
			id="<?php echo esc_attr( $releasecart_quantity_id ); ?>"
			name="quantity"
			value="<?php echo esc_attr( (string) ( $releasecart_quantity['value'] ?? 1 ) ); ?>"
			min="<?php echo esc_attr( (string) ( $releasecart_quantity['min'] ?? 1 ) ); ?>"
			<?php if ( isset( $releasecart_quantity['max'] ) && null !== $releasecart_quantity['max'] ) : ?>
				max="<?php echo esc_attr( (string) $releasecart_quantity['max'] ); ?>"
			<?php endif; ?>
		/>
	</div>

	<?php // Fixed single-unit tiers show this instead of a quantity control. ?>
	<p class="<?php echo esc_attr( $releasecart_block ); ?>__quantity-fixed"<?php echo ( empty( $releasecart_quantity['show'] ) ) ? '' : ' hidden'; ?>>
		<span class="<?php echo esc_attr( $releasecart_block ); ?>__quantity-label"><?php echo esc_html__( 'Quantity', 'releasecart' ); ?></span>
		<span class="<?php echo esc_attr( $releasecart_block ); ?>__quantity-fixed-value"><?php echo esc_html( (string) ( $releasecart_quantity['value'] ?? 1 ) ); ?></span>
	</p>

	<?php
	// wp_nonce_field() prints its escaped nonce markup.
	wp_nonce_field( (string) $releasecart_form['nonce_action'], (string) $releasecart_form['nonce_field'], true, true );
	?>
	<button type="submit" class="<?php echo esc_attr( $releasecart_block ); ?>__add releasecart-button releasecart-button--primary"><?php echo esc_html( (string) $releasecart_form['add_label'] ); ?></button>
</form>

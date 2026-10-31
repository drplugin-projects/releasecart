<?php
/**
 * Cart summary template.
 *
 * Theme override: {theme}/releasecart/components/cart-summary.php.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array<string, mixed> $data {
 *     @type string $title Optional heading.
 *     @type bool $show_actions Show cart controls.
 *     @type bool $show_plan_details Show plan details.
 *     @type bool $is_empty Cart has no lines.
 *     @type array<int, array<string, mixed>> $lines Cart lines.
 *     @type string $subtotal Formatted total.
 *     @type string $total_label Label of the last row.
 *     @type array<string, mixed> $discount Discount data.
 *     @type string|null $checkout_url Checkout link.
 *     @type array<int, string> $notices Cart notices.
 * }
 */

use DrPlugin\WP\ReleaseCart\Frontend\CartNotices;

defined( 'ABSPATH' ) || exit;

// The shared success and error notices.
$releasecart_notices = CartNotices::view();
$releasecart_success = $releasecart_notices['success'];
$releasecart_error   = $releasecart_notices['error'];

// Keep mutation forms off the error-marker URL.
$releasecart_form_action_raw = remove_query_arg( 'releasecart_cart_error' );
?>
<div class="releasecart-component releasecart-cart-summary">
	<?php if ( ! empty( $releasecart_success['show'] ) ) : ?>
		<div class="releasecart-cart-notice releasecart-cart-notice--success" role="status">
			<p class="releasecart-cart-notice__text"><?php echo esc_html( (string) ( $releasecart_success['text'] ?? '' ) ); ?></p>
			<?php if ( ! empty( $releasecart_success['checkout_url'] ) ) : ?>
				<a class="releasecart-cart-notice__checkout" href="<?php echo esc_url( (string) $releasecart_success['checkout_url'] ); ?>"><?php echo esc_html__( 'Go to checkout', 'releasecart' ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $releasecart_error['show'] ) ) : ?>
		<div class="releasecart-cart-summary__error releasecart-notice releasecart-notice--error" role="alert">
			<?php echo esc_html( (string) ( $releasecart_error['text'] ?? '' ) ); ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $data['notices'] ) && is_array( $data['notices'] ) ) : ?>
		<div class="releasecart-cart-summary__notice releasecart-notice releasecart-notice--info" role="status">
			<?php foreach ( $data['notices'] as $releasecart_notice ) : ?>
				<p><?php echo esc_html( (string) $releasecart_notice ); ?></p>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="releasecart-cart-summary__panel releasecart-card">
		<?php if ( '' !== $data['title'] ) : ?>
			<h2 class="releasecart-cart-summary__title"><?php echo esc_html( $data['title'] ); ?></h2>
		<?php endif; ?>

		<?php if ( $data['is_empty'] ) : ?>

			<p class="releasecart-cart-summary__empty"><?php echo esc_html__( 'Your cart is empty.', 'releasecart' ); ?></p>

		<?php else : ?>

			<table class="releasecart-cart-summary__table releasecart-table">
				<thead>
					<tr>
						<th scope="col"><?php echo esc_html__( 'Product', 'releasecart' ); ?></th>
						<th scope="col"><?php echo esc_html__( 'Quantity', 'releasecart' ); ?></th>
						<th scope="col"><?php echo esc_html__( 'Price', 'releasecart' ); ?></th>
						<th scope="col"><?php echo esc_html__( 'Subtotal', 'releasecart' ); ?></th>
						<?php if ( $data['show_actions'] ) : ?>
							<th scope="col"><span class="screen-reader-text"><?php echo esc_html__( 'Actions', 'releasecart' ); ?></span></th>
						<?php endif; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $data['lines'] as $releasecart_line ) : ?>
						<?php $releasecart_line_id = $releasecart_line['product_id'] . '-' . $releasecart_line['price_id']; ?>
						<tr class="releasecart-cart-summary__line">
							<td data-label="<?php echo esc_attr__( 'Product', 'releasecart' ); ?>">
								<?php echo esc_html( $releasecart_line['product_name'] ); ?>
								<?php if ( null !== $releasecart_line['price_name'] && '' !== $releasecart_line['price_name'] ) : ?>
									<span class="releasecart-cart-summary__tier releasecart-badge"><?php echo esc_html( $releasecart_line['price_name'] ); ?></span>
								<?php endif; ?>
								<?php
								$releasecart_line_meta = ( ! empty( $data['show_plan_details'] ) && ! empty( $releasecart_line['meta'] ) && is_array( $releasecart_line['meta'] ) )
									? $releasecart_line['meta']
									: array();
								?>
								<?php if ( array() !== $releasecart_line_meta ) : ?>
									<ul class="releasecart-cart-summary__line-meta">
										<?php foreach ( $releasecart_line_meta as $releasecart_meta_line ) : ?>
											<li class="releasecart-cart-summary__line-meta-item"><?php echo esc_html( (string) $releasecart_meta_line ); ?></li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</td>
							<td data-label="<?php echo esc_attr__( 'Quantity', 'releasecart' ); ?>">
								<?php echo esc_html( (string) $releasecart_line['quantity'] ); ?>
							</td>
							<td data-label="<?php echo esc_attr__( 'Price', 'releasecart' ); ?>">
								<?php echo esc_html( $releasecart_line['unit_amount'] ); ?>
							</td>
							<td data-label="<?php echo esc_attr__( 'Subtotal', 'releasecart' ); ?>">
								<?php echo esc_html( $releasecart_line['subtotal'] ); ?>
							</td>
							<?php if ( $data['show_actions'] ) : ?>
								<td class="releasecart-cart-summary__actions">
									<?php
									// Use the shared cart controls.
									$releasecart_line_controls = array(
										'product_id'   => $releasecart_line['product_id'],
										'price_id'     => $releasecart_line['price_id'],
										'quantity'     => $releasecart_line['quantity'],
										'min'          => $releasecart_line['min'] ?? 1,
										'max'          => $releasecart_line['max'] ?? null,
										'fixed_single' => ! empty( $releasecart_line['fixed_single'] ),
										'action_url'   => $releasecart_form_action_raw,
										'id'           => $releasecart_line_id,
									);
									require RELEASECART_PATH . 'templates/components/partials/cart-line-controls.php';
									?>
								</td>
							<?php endif; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<?php // A stored valid discount code shows its own row above the total, which is already discounted. ?>
					<?php if ( 'applied' === ( $data['discount']['state'] ?? '' ) ) : ?>
						<tr class="releasecart-cart-summary__discount">
							<th scope="row" colspan="3"><?php echo esc_html( (string) ( $data['discount']['label'] ?? '' ) ); ?></th>
							<td>-<?php echo esc_html( (string) ( $data['discount']['amount'] ?? '' ) ); ?></td>
							<?php if ( $data['show_actions'] ) : ?>
								<td></td>
							<?php endif; ?>
						</tr>
					<?php endif; ?>
					<?php // The automatic (no-code) discount shows its own row, so the lines and the total reconcile. ?>
					<?php if ( ! empty( $data['discount']['auto']['applied'] ) ) : ?>
						<tr class="releasecart-cart-summary__discount releasecart-cart-summary__discount--auto">
							<th scope="row" colspan="3"><?php echo esc_html( (string) ( $data['discount']['auto']['label'] ?? '' ) ); ?></th>
							<td>-<?php echo esc_html( (string) ( $data['discount']['auto']['amount'] ?? '' ) ); ?></td>
							<?php if ( $data['show_actions'] ) : ?>
								<td></td>
							<?php endif; ?>
						</tr>
					<?php endif; ?>
					<tr class="releasecart-cart-summary__totals">
						<th scope="row" colspan="3"><?php echo esc_html( $data['total_label'] ?? __( 'Total', 'releasecart' ) ); ?></th>
						<td><?php echo esc_html( $data['subtotal'] ); ?></td>
						<?php if ( $data['show_actions'] ) : ?>
							<td></td>
						<?php endif; ?>
					</tr>
				</tfoot>
			</table>

			<?php if ( null !== $data['checkout_url'] ) : ?>
				<a class="releasecart-cart-summary__checkout releasecart-button releasecart-button--primary" href="<?php echo esc_url( $data['checkout_url'] ); ?>">
					<?php echo esc_html__( 'Proceed to checkout', 'releasecart' ); ?>
				</a>
			<?php endif; ?>

		<?php endif; ?>
	</div>
</div>

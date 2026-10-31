<?php
/**
 * Purchase confirmation template.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array<string, mixed> $data {
 *     State-specific view data.
 *
 *     @type string $state          Page state.
 *     @type string $title          Heading, empty to hide.
 *     @type string $login_url      Sign-in URL.
 *     @type string $order_uuid     Order UUID for polling.
 *     @type string $message        Processing message.
 *     @type int    $order_ref      Order reference.
 *     @type string $date           Localized order date.
 *     @type array<int, array<string, mixed>> $items    Order items.
 *     @type string $total          Formatted order total.
 *     @type bool   $show_licenses  Whether to show licenses.
 *     @type bool   $show_downloads Whether to show downloads.
 *     @type array<int, array<string, string>> $licenses License rows.
 *     @type array<int, array<string, mixed>>  $downloads Download entries.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="releasecart-component releasecart-purchase-confirmation">

	<?php if ( 'not_found' === $data['state'] ) : ?>

		<div class="releasecart-purchase-confirmation__notice" role="alert">
			<p><?php esc_html_e( 'We could not find that order.', 'releasecart' ); ?></p>
		</div>

	<?php elseif ( 'sign_in' === $data['state'] ) : ?>

		<div class="releasecart-purchase-confirmation__sign-in">
			<p><?php esc_html_e( 'Please sign in to view this order.', 'releasecart' ); ?></p>
			<a class="releasecart-purchase-confirmation__login releasecart-button releasecart-button--primary" href="<?php echo esc_url( $data['login_url'] ); ?>">
				<?php esc_html_e( 'Sign in', 'releasecart' ); ?>
			</a>
		</div>

	<?php elseif ( 'processing' === $data['state'] ) : ?>

		<?php if ( '' !== ( $data['title'] ?? '' ) ) : ?>
			<h2 class="releasecart-purchase-confirmation__title"><?php echo esc_html( $data['title'] ); ?></h2>
		<?php endif; ?>

		<div
			class="releasecart-purchase-confirmation__processing"
			data-releasecart-order-status
			data-order-uuid="<?php echo esc_attr( $data['order_uuid'] ); ?>"
			data-msg-confirmed="<?php esc_attr_e( 'Payment confirmed. Loading your receipt.', 'releasecart' ); ?>"
			role="status"
			aria-live="polite"
		>
			<p class="releasecart-purchase-confirmation__processing-message">
				<?php echo esc_html( $data['message'] ); ?>
			</p>
			<noscript>
				<p class="releasecart-purchase-confirmation__noscript">
					<?php esc_html_e( 'Refresh this page in a few moments to see your receipt.', 'releasecart' ); ?>
				</p>
			</noscript>
		</div>

	<?php elseif ( 'refunded' === $data['state'] ) : ?>

		<?php if ( '' !== ( $data['title'] ?? '' ) ) : ?>
			<h2 class="releasecart-purchase-confirmation__title"><?php echo esc_html( $data['title'] ); ?></h2>
		<?php endif; ?>

		<div class="releasecart-purchase-confirmation__refunded" role="status">
			<p>
				<?php
				printf(
					/* translators: %s: the order reference number. */
					esc_html__( 'Order #%s was refunded.', 'releasecart' ),
					esc_html( (string) $data['order_ref'] )
				);
				?>
			</p>
		</div>

	<?php else : ?>

		<?php if ( '' !== ( $data['title'] ?? '' ) ) : ?>
			<h2 class="releasecart-purchase-confirmation__title"><?php echo esc_html( $data['title'] ); ?></h2>
		<?php endif; ?>

		<div class="releasecart-purchase-confirmation__receipt releasecart-card">
			<dl class="releasecart-purchase-confirmation__summary">
				<div class="releasecart-purchase-confirmation__summary-item">
					<dt><?php esc_html_e( 'Order', 'releasecart' ); ?></dt>
					<dd>
						<?php
						printf(
							/* translators: %s: the order reference number. */
							esc_html__( '#%s', 'releasecart' ),
							esc_html( (string) $data['order_ref'] )
						);
						?>
					</dd>
				</div>
				<?php if ( '' !== $data['date'] ) : ?>
					<div class="releasecart-purchase-confirmation__summary-item">
						<dt><?php esc_html_e( 'Date', 'releasecart' ); ?></dt>
						<dd><?php echo esc_html( $data['date'] ); ?></dd>
					</div>
				<?php endif; ?>
			</dl>

			<table class="releasecart-purchase-confirmation__items releasecart-table">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Item', 'releasecart' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Quantity', 'releasecart' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Total', 'releasecart' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( (array) $data['items'] as $releasecart_item ) : ?>
						<tr class="releasecart-purchase-confirmation__item">
							<td class="releasecart-purchase-confirmation__item-name"><?php echo esc_html( (string) $releasecart_item['name'] ); ?></td>
							<td class="releasecart-purchase-confirmation__item-qty"><?php echo esc_html( (string) $releasecart_item['quantity'] ); ?></td>
							<td class="releasecart-purchase-confirmation__item-total"><?php echo esc_html( (string) $releasecart_item['line_total'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr class="releasecart-purchase-confirmation__total-row">
						<th scope="row" colspan="2"><?php esc_html_e( 'Order total', 'releasecart' ); ?></th>
						<td class="releasecart-purchase-confirmation__total"><?php echo esc_html( (string) $data['total'] ); ?></td>
					</tr>
				</tfoot>
			</table>

			<?php if ( $data['show_licenses'] && array() !== $data['licenses'] ) : ?>
				<section class="releasecart-purchase-confirmation__licenses">
					<h3 class="releasecart-purchase-confirmation__licenses-title">
						<?php esc_html_e( 'Your license keys', 'releasecart' ); ?>
					</h3>
					<ul class="releasecart-purchase-confirmation__license-list">
						<?php foreach ( $data['licenses'] as $releasecart_license ) : ?>
							<li class="releasecart-purchase-confirmation__license">
								<span class="releasecart-purchase-confirmation__license-product">
									<?php echo esc_html( (string) $releasecart_license['product_name'] ); ?>
								</span>
								<code class="releasecart-purchase-confirmation__license-key">
									<?php echo esc_html( (string) $releasecart_license['license_key'] ); ?>
								</code>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ( $data['show_downloads'] && array() !== $data['downloads'] ) : ?>
				<section class="releasecart-purchase-confirmation__downloads">
					<h3 class="releasecart-purchase-confirmation__downloads-title">
						<?php esc_html_e( 'Your downloads', 'releasecart' ); ?>
					</h3>
					<ul class="releasecart-purchase-confirmation__download-products">
						<?php foreach ( $data['downloads'] as $releasecart_entry ) : ?>
							<li class="releasecart-purchase-confirmation__download-product">
								<span class="releasecart-purchase-confirmation__download-name">
									<?php echo esc_html( (string) $releasecart_entry['product_name'] ); ?>
								</span>
								<ul class="releasecart-purchase-confirmation__files">
									<?php foreach ( $releasecart_entry['files'] as $releasecart_file ) : ?>
										<li class="releasecart-purchase-confirmation__file">
											<a
												class="releasecart-purchase-confirmation__file-link"
												href="<?php echo esc_url( (string) $releasecart_file['download_url'] ); ?>"
												download
											>
												<?php echo esc_html( (string) $releasecart_file['label'] ); ?>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>
		</div>

	<?php endif; ?>
</div>

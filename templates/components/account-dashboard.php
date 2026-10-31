<?php
/**
 * Account dashboard template.
 *
 * Theme override: {theme}/releasecart/components/account-dashboard.php.
 * Tabs enhance the live view.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array<string, mixed> $data {
 *     @type string $state      View state.
 *     @type string $title      Optional heading.
 *     @type string $login_html Login form markup.
 *     @type string $login_url  Login link.
 *     @type array{avatar: string, greeting: string} $header Greeting data.
 *     @type array<int, array<string, mixed>> $tabs Prepared tabs.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="releasecart-component releasecart-account-dashboard">
	<?php if ( '' !== ( $data['title'] ?? '' ) ) : ?>
		<h2 class="releasecart-account-dashboard__title"><?php echo esc_html( $data['title'] ); ?></h2>
	<?php endif; ?>

	<?php if ( 'logged_out' === $data['state'] ) : ?>

		<div class="releasecart-account-dashboard__prompt">
			<p><?php esc_html_e( 'Please log in to view your account.', 'releasecart' ); ?></p>
			<?php if ( '' !== $data['login_html'] ) : ?>
				<?php echo wp_kses( $data['login_html'], \DrPlugin\WP\ReleaseCart\Frontend\Kses::allowed() ); ?>
			<?php else : ?>
				<a class="releasecart-account-dashboard__login releasecart-button releasecart-button--primary" href="<?php echo esc_url( $data['login_url'] ); ?>">
					<?php esc_html_e( 'Log in', 'releasecart' ); ?>
				</a>
			<?php endif; ?>
		</div>

	<?php else : ?>

		<div class="releasecart-account-dashboard__header">
			<?php if ( '' !== $data['header']['avatar'] ) : ?>
				<span class="releasecart-account-dashboard__avatar">
					<?php echo wp_kses( $data['header']['avatar'], \DrPlugin\WP\ReleaseCart\Frontend\Kses::image() ); ?>
				</span>
			<?php endif; ?>
			<p class="releasecart-account-dashboard__greeting"><?php echo esc_html( $data['header']['greeting'] ); ?></p>
		</div>

		<div class="releasecart-account-dashboard__tabs" data-releasecart-account-dashboard>
			<ul class="releasecart-account-dashboard__tablist" role="tablist" aria-label="<?php esc_attr_e( 'Account sections', 'releasecart' ); ?>">
				<?php foreach ( (array) $data['tabs'] as $releasecart_tab ) : ?>
					<li class="releasecart-account-dashboard__tabitem" role="presentation">
						<a
							class="releasecart-account-dashboard__tab"
							id="<?php echo esc_attr( $releasecart_tab['tab_id'] ); ?>"
							href="#<?php echo esc_attr( $releasecart_tab['panel_id'] ); ?>"
							role="tab"
							aria-controls="<?php echo esc_attr( $releasecart_tab['panel_id'] ); ?>"
						<?php echo true === $releasecart_tab['selected'] ? ' aria-selected="true" tabindex="0"' : ''; ?><?php echo false === $releasecart_tab['selected'] ? ' aria-selected="false" tabindex="-1"' : ''; ?>><?php echo esc_html( $releasecart_tab['label'] ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php foreach ( (array) $data['tabs'] as $releasecart_tab ) : ?>
				<section
					class="releasecart-account-dashboard__panel"
					id="<?php echo esc_attr( $releasecart_tab['panel_id'] ); ?>"
					role="tabpanel"
					aria-labelledby="<?php echo esc_attr( $releasecart_tab['tab_id'] ); ?>"
				<?php echo $releasecart_tab['hidden'] ? ' hidden' : ''; ?>>
					<h3 class="releasecart-account-dashboard__panel-title"><?php echo esc_html( $releasecart_tab['label'] ); ?></h3>

					<?php if ( 'purchases' === $releasecart_tab['key'] ) : ?>

						<?php if ( array() === $releasecart_tab['purchases'] ) : ?>
							<p class="releasecart-account-dashboard__empty">
								<?php esc_html_e( 'You have no purchases yet.', 'releasecart' ); ?>
							</p>
						<?php else : ?>
							<ul class="releasecart-account-dashboard__orders">
								<?php foreach ( $releasecart_tab['purchases'] as $releasecart_order ) : ?>
									<li class="releasecart-account-dashboard__order releasecart-card">
										<div class="releasecart-account-dashboard__order-header">
											<span class="releasecart-account-dashboard__order-ref"><?php echo esc_html( $releasecart_order['reference'] ); ?></span>
											<span class="releasecart-account-dashboard__order-date"><?php echo esc_html( $releasecart_order['date'] ); ?></span>
											<span class="releasecart-account-dashboard__order-status releasecart-badge releasecart-account-dashboard__order-status--<?php echo esc_attr( $releasecart_order['status_slug'] ); ?>">
												<?php echo esc_html( $releasecart_order['status_label'] ); ?>
											</span>
										</div>
										<ul class="releasecart-account-dashboard__order-items">
											<?php foreach ( $releasecart_order['items'] as $releasecart_item ) : ?>
												<li class="releasecart-account-dashboard__order-item">
													<span class="releasecart-account-dashboard__order-item-name"><?php echo esc_html( $releasecart_item['name'] ); ?></span>
													<span class="releasecart-account-dashboard__order-item-qty"><span aria-hidden="true">&times;</span><?php echo esc_html( (string) $releasecart_item['quantity'] ); ?></span>
												</li>
											<?php endforeach; ?>
										</ul>
										<p class="releasecart-account-dashboard__order-total"><?php echo esc_html( $releasecart_order['total'] ); ?></p>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>

					<?php else : ?>
						<?php echo wp_kses( $releasecart_tab['html'], \DrPlugin\WP\ReleaseCart\Frontend\Kses::allowed() ); ?>
					<?php endif; ?>
				</section>
			<?php endforeach; ?>
		</div>

	<?php endif; ?>
</div>

<?php
/**
 * License manager template.
 *
 * Theme override: {theme}/releasecart/components/license-manager.php.
 * Show keys only to owners.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array<string, mixed> $data {
 *     @type string $state View state.
 *     @type string $title Optional heading.
 *     @type string $login_url Login link.
 *     @type string|null $registration_url Registration link.
 *     @type bool $show_key Show license keys.
 *     @type bool $show_activations Show site controls.
 *     @type array<int, array<string, mixed>> $licenses License rows.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="releasecart-component releasecart-license-manager" data-msg-error="<?php esc_attr_e( 'Something went wrong. Please try again.', 'releasecart' ); ?>">
	<?php if ( '' !== ( $data['title'] ?? '' ) ) : ?>
		<h2 class="releasecart-license-manager__title"><?php echo esc_html( $data['title'] ); ?></h2>
	<?php endif; ?>

	<?php if ( 'logged_out' === $data['state'] ) : ?>

		<div class="releasecart-license-manager__prompt">
			<p><?php esc_html_e( 'Please log in to view and manage your licenses.', 'releasecart' ); ?></p>
			<p>
				<a class="releasecart-license-manager__login" href="<?php echo esc_url( $data['login_url'] ); ?>">
					<?php esc_html_e( 'Log in', 'releasecart' ); ?>
				</a>
				<?php if ( null !== $data['registration_url'] ) : ?>
					<a class="releasecart-license-manager__register" href="<?php echo esc_url( $data['registration_url'] ); ?>">
						<?php esc_html_e( 'Create an account', 'releasecart' ); ?>
					</a>
				<?php endif; ?>
			</p>
		</div>

	<?php elseif ( 'empty' === $data['state'] ) : ?>

		<p class="releasecart-license-manager__empty">
			<?php esc_html_e( 'You do not have any licenses yet. Your license keys will appear here after your first purchase.', 'releasecart' ); ?>
		</p>

	<?php else : ?>

		<?php if ( $data['show_activations'] ) : ?>
			<div class="releasecart-license-manager__feedback" role="alert" aria-live="assertive" data-releasecart-feedback></div>
			<noscript>
				<p class="releasecart-license-manager__noscript">
					<?php esc_html_e( 'Managing your site activations requires JavaScript. Please enable it to activate or deactivate sites.', 'releasecart' ); ?>
				</p>
			</noscript>
		<?php endif; ?>

		<ul class="releasecart-license-manager__list">
			<?php foreach ( (array) $data['licenses'] as $releasecart_license ) : ?>
				<li
					class="releasecart-license-manager__card"
					data-releasecart-license
					data-license-id="<?php echo esc_attr( (string) $releasecart_license['id'] ); ?>"
				>
					<div class="releasecart-license-manager__header">
						<span class="releasecart-license-manager__product"><?php echo esc_html( $releasecart_license['product_name'] ); ?></span>
						<span class="releasecart-license-manager__status releasecart-badge releasecart-license-manager__status--<?php echo esc_attr( $releasecart_license['status_slug'] ); ?>">
							<?php echo esc_html( $releasecart_license['status_label'] ); ?>
						</span>
					</div>

					<?php if ( $data['show_key'] ) : ?>
						<p class="releasecart-license-manager__key-row">
							<span class="releasecart-license-manager__key-label"><?php esc_html_e( 'License key', 'releasecart' ); ?></span>
							<code class="releasecart-license-manager__key"><?php echo esc_html( $releasecart_license['license_key'] ); ?></code>
						</p>
					<?php endif; ?>

					<dl class="releasecart-license-manager__meta">
						<div class="releasecart-license-manager__meta-item">
							<dt><?php esc_html_e( 'Expires', 'releasecart' ); ?></dt>
							<dd><?php echo esc_html( $releasecart_license['expiry_label'] ); ?></dd>
						</div>
						<div class="releasecart-license-manager__meta-item">
							<dt><?php esc_html_e( 'Active sites', 'releasecart' ); ?></dt>
							<dd class="releasecart-license-manager__usage">
								<span class="releasecart-license-manager__usage-count" data-releasecart-usage-count><?php echo esc_html( (string) $releasecart_license['activation_count'] ); ?></span>
								<span class="releasecart-license-manager__usage-sep" aria-hidden="true">/</span>
								<span class="releasecart-license-manager__usage-limit"><?php echo esc_html( $releasecart_license['activation_limit_label'] ); ?></span>
							</dd>
						</div>
					</dl>

					<?php if ( $data['show_activations'] ) : ?>
						<div class="releasecart-license-manager__activations">
							<?php if ( array() !== $releasecart_license['activations'] ) : ?>
								<ul class="releasecart-license-manager__sites">
									<?php foreach ( $releasecart_license['activations'] as $releasecart_activation ) : ?>
										<li class="releasecart-license-manager__site" data-releasecart-activation>
											<span class="releasecart-license-manager__site-url"><?php echo esc_html( $releasecart_activation['site_url'] ); ?></span>
											<button
												type="button"
												class="releasecart-license-manager__deactivate releasecart-button releasecart-button--secondary releasecart-button--small"
												data-releasecart-deactivate
												data-license-key="<?php echo esc_attr( $releasecart_license['license_key'] ); ?>"
												data-site-url="<?php echo esc_attr( $releasecart_activation['site_url'] ); ?>"
											>
												<?php esc_html_e( 'Deactivate', 'releasecart' ); ?>
											</button>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php else : ?>
								<p class="releasecart-license-manager__no-sites"><?php esc_html_e( 'No sites are activated yet.', 'releasecart' ); ?></p>
							<?php endif; ?>

							<?php if ( $releasecart_license['can_activate'] ) : ?>
								<?php $releasecart_input_id = 'releasecart-license-manager-site-' . (int) $releasecart_license['id']; ?>
								<form
									class="releasecart-license-manager__activate-form"
									data-releasecart-activate-form
									data-license-key="<?php echo esc_attr( $releasecart_license['license_key'] ); ?>"
								>
									<label class="releasecart-license-manager__activate-label" for="<?php echo esc_attr( $releasecart_input_id ); ?>">
										<?php esc_html_e( 'Activate a new site', 'releasecart' ); ?>
									</label>
									<input
										type="url"
										id="<?php echo esc_attr( $releasecart_input_id ); ?>"
										class="releasecart-license-manager__activate-input releasecart-input"
										name="site_url"
										placeholder="https://example.com"
										required
										aria-required="true"
										data-releasecart-site-input
									>
									<button type="submit" class="releasecart-license-manager__activate releasecart-button releasecart-button--primary">
										<?php esc_html_e( 'Activate', 'releasecart' ); ?>
									</button>
								</form>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>

	<?php endif; ?>
</div>

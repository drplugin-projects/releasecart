<?php
/**
 * Downloads list template.
 *
 * Theme override: {theme}/releasecart/components/downloads-list.php.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array<string, mixed> $data {
 *     @type string $state View state.
 *     @type string $title Optional heading.
 *     @type string $login_url Login link.
 *     @type bool $show_updates Show updates.
 *     @type array<int, array<string, mixed>> $downloads Download entries.
 *     @type array<int, array<string, mixed>> $updates Update entries.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="releasecart-component releasecart-downloads-list">
	<?php if ( '' !== $data['title'] ) : ?>
		<h2 class="releasecart-downloads-list__title"><?php echo esc_html( $data['title'] ); ?></h2>
	<?php endif; ?>

	<?php if ( 'logged_out' === $data['state'] ) : ?>

		<p class="releasecart-downloads-list__prompt">
			<?php echo esc_html__( 'Please log in to view your downloads.', 'releasecart' ); ?>
		</p>
		<a class="releasecart-downloads-list__login" href="<?php echo esc_url( $data['login_url'] ); ?>">
			<?php echo esc_html__( 'Log in', 'releasecart' ); ?>
		</a>

	<?php elseif ( 'empty' === $data['state'] ) : ?>

		<p class="releasecart-downloads-list__empty">
			<?php echo esc_html__( 'You have no downloads yet.', 'releasecart' ); ?>
		</p>

	<?php else : ?>

		<?php if ( array() !== $data['downloads'] ) : ?>
			<ul class="releasecart-downloads-list__products">
				<?php foreach ( $data['downloads'] as $releasecart_entry ) : ?>
					<li class="releasecart-downloads-list__product">
						<h3 class="releasecart-downloads-list__product-name">
							<?php echo esc_html( (string) $releasecart_entry['product_name'] ); ?>
						</h3>
						<ul class="releasecart-downloads-list__files">
							<?php foreach ( $releasecart_entry['files'] as $releasecart_file ) : ?>
								<li class="releasecart-downloads-list__file">
									<a
										class="releasecart-downloads-list__file-link"
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
		<?php endif; ?>

		<?php if ( $data['show_updates'] && array() !== $data['updates'] ) : ?>
			<section class="releasecart-downloads-list__updates">
				<h3 class="releasecart-downloads-list__updates-title">
					<?php echo esc_html__( 'Updates', 'releasecart' ); ?>
				</h3>
				<ul class="releasecart-downloads-list__update-items">
					<?php foreach ( $data['updates'] as $releasecart_update ) : ?>
						<li class="releasecart-downloads-list__update">
							<span class="releasecart-downloads-list__update-product">
								<?php echo esc_html( (string) $releasecart_update['product_name'] ); ?>
							</span>
							<span class="releasecart-downloads-list__update-version">
								<?php
								printf(
									/* translators: %s: a release version number, e.g. 1.4.0. */
									esc_html__( 'Version %s', 'releasecart' ),
									esc_html( (string) $releasecart_update['version'] )
								);
								?>
							</span>
							<a
								class="releasecart-downloads-list__update-link"
								href="<?php echo esc_url( (string) $releasecart_update['download_url'] ); ?>"
								download
							>
								<?php echo esc_html__( 'Download update', 'releasecart' ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

	<?php endif; ?>
</div>

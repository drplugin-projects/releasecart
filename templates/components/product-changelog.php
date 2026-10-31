<?php
/**
 * Product changelog component template.
 *
 * Release HTML is sanitized before this template uses it.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array{
 *     releases: array<int, array{version: string, date: string|null, html: string, compatibility: array<int, array{label: string, value: string}>}>,
 *     show_version_heading: bool
 * } $data View data supplied by the component.
 */

defined( 'ABSPATH' ) || exit;

$releasecart_releases     = ( isset( $data['releases'] ) && is_array( $data['releases'] ) ) ? $data['releases'] : array();
$releasecart_show_heading = ! empty( $data['show_version_heading'] );

if ( array() === $releasecart_releases ) {
	return;
}
?>
<div class="releasecart-component releasecart-product-changelog">
	<?php foreach ( $releasecart_releases as $releasecart_release ) : ?>
		<?php
		$releasecart_version = isset( $releasecart_release['version'] ) ? (string) $releasecart_release['version'] : '';
		$releasecart_date    = ( isset( $releasecart_release['date'] ) && '' !== (string) $releasecart_release['date'] ) ? (string) $releasecart_release['date'] : null;
		$releasecart_notes   = isset( $releasecart_release['html'] ) ? (string) $releasecart_release['html'] : '';
		$releasecart_compat  = ( isset( $releasecart_release['compatibility'] ) && is_array( $releasecart_release['compatibility'] ) ) ? $releasecart_release['compatibility'] : array();
		?>
		<section class="releasecart-product-changelog__release">
			<?php if ( $releasecart_show_heading || null !== $releasecart_date ) : ?>
				<header class="releasecart-product-changelog__header">
					<?php if ( $releasecart_show_heading && '' !== $releasecart_version ) : ?>
						<h3 class="releasecart-product-changelog__version">
							<?php
							/* translators: %s: a release version number, e.g. 1.4.0. */
							echo esc_html( sprintf( __( 'Version %s', 'releasecart' ), $releasecart_version ) );
							?>
						</h3>
					<?php endif; ?>
					<?php if ( null !== $releasecart_date ) : ?>
						<span class="releasecart-product-changelog__date"><?php echo esc_html( $releasecart_date ); ?></span>
					<?php endif; ?>
				</header>
			<?php endif; ?>

			<?php if ( '' !== $releasecart_notes ) : ?>
				<div class="releasecart-product-changelog__notes">
					<?php
					// The notes can come from the object cache, so they are filtered again here.
					echo wp_kses_post( $releasecart_notes );
					?>
				</div>
			<?php endif; ?>

			<?php if ( array() !== $releasecart_compat ) : ?>
				<p class="releasecart-product-changelog__compat">
					<?php foreach ( $releasecart_compat as $releasecart_compat_item ) : ?>
						<span class="releasecart-product-changelog__compat-item">
							<?php
							/* translators: 1: a label, 2: its value. */
							echo esc_html( sprintf( __( '%1$s: %2$s', 'releasecart' ), (string) $releasecart_compat_item['label'], (string) $releasecart_compat_item['value'] ) );
							?>
						</span>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>
		</section>
	<?php endforeach; ?>
</div>

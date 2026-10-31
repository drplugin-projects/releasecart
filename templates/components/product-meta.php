<?php
/**
 * Product meta template.
 *
 * Theme override: {theme}/releasecart/components/product-meta.php.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array{
 *     categories: array<int, array{name: string, url: string|null}>,
 *     tags: array<int, array{name: string, url: string|null}>,
 *     version: string|null,
 *     updated: string|null
 * } $data View data.
 */

defined( 'ABSPATH' ) || exit;

$releasecart_categories = ( isset( $data['categories'] ) && is_array( $data['categories'] ) ) ? $data['categories'] : array();
$releasecart_tags       = ( isset( $data['tags'] ) && is_array( $data['tags'] ) ) ? $data['tags'] : array();
$releasecart_version    = ( isset( $data['version'] ) && '' !== (string) $data['version'] ) ? (string) $data['version'] : null;
$releasecart_updated    = ( isset( $data['updated'] ) && '' !== (string) $data['updated'] ) ? (string) $data['updated'] : null;

$releasecart_has_terms   = array() !== $releasecart_categories || array() !== $releasecart_tags;
$releasecart_has_release = null !== $releasecart_version || null !== $releasecart_updated;

if ( ! $releasecart_has_terms && ! $releasecart_has_release ) {
	return;
}

/**
 * Render one taxonomy row.
 *
 * @param string $label Row label.
 * @param string $kind Row modifier.
 * @param array<int, array{name: string, url: string|null}> $terms Term rows.
 * @return void
 */
$releasecart_render_terms = static function ( string $label, string $kind, array $terms ): void {
	?>
	<div class="releasecart-product-meta__row releasecart-product-meta__row--<?php echo esc_attr( $kind ); ?>">
		<span class="releasecart-product-meta__label"><?php echo esc_html( $label ); ?></span>
		<span class="releasecart-product-meta__terms">
			<?php foreach ( $terms as $releasecart_term ) : ?>
				<?php if ( isset( $releasecart_term['url'] ) && '' !== (string) $releasecart_term['url'] ) : ?>
					<a class="releasecart-product-meta__term" href="<?php echo esc_url( (string) $releasecart_term['url'] ); ?>"><?php echo esc_html( (string) $releasecart_term['name'] ); ?></a>
				<?php else : ?>
					<span class="releasecart-product-meta__term"><?php echo esc_html( (string) $releasecart_term['name'] ); ?></span>
				<?php endif; ?>
			<?php endforeach; ?>
		</span>
	</div>
	<?php
};
?>
<div class="releasecart-component releasecart-product-meta">
	<?php
	if ( array() !== $releasecart_categories ) {
		$releasecart_render_terms( __( 'Categories', 'releasecart' ), 'categories', $releasecart_categories );
	}

	if ( array() !== $releasecart_tags ) {
		$releasecart_render_terms( __( 'Tags', 'releasecart' ), 'tags', $releasecart_tags );
	}
	?>

	<?php if ( $releasecart_has_release ) : ?>
		<div class="releasecart-product-meta__row releasecart-product-meta__row--release">
			<?php if ( null !== $releasecart_version ) : ?>
				<span class="releasecart-product-meta__version">
					<?php
					/* translators: %s: a release version number, e.g. 1.4.0. */
					echo esc_html( sprintf( __( 'Version %s', 'releasecart' ), $releasecart_version ) );
					?>
				</span>
			<?php endif; ?>
			<?php if ( null !== $releasecart_updated ) : ?>
				<span class="releasecart-product-meta__updated">
					<?php
					/* translators: %s: the date of the latest published release. */
					echo esc_html( sprintf( __( 'Updated %s', 'releasecart' ), $releasecart_updated ) );
					?>
				</span>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>

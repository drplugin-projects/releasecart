<?php
/**
 * Sale price pair.
 *
 * The screen-reader sentence announces both formatted prices once.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var string $releasecart_was Formatted original price.
 * @var string $releasecart_now Formatted sale price.
 * @var string $releasecart_note Price note, or '' for none.
 */

defined( 'ABSPATH' ) || exit;

$releasecart_was  = (string) $releasecart_was;
$releasecart_now  = (string) $releasecart_now;
$releasecart_note = isset( $releasecart_note ) ? (string) $releasecart_note : '';

/* translators: 1: original price, 2: discounted price. */
$releasecart_sr = sprintf( __( 'Original price was %1$s, discounted price is %2$s.', 'releasecart' ), $releasecart_was, $releasecart_now );

// The note trails the assistive sentence, so a screen reader hears the sentence
// then the note once. With no note the pair is unchanged.
?>
<span class="releasecart-price releasecart-price--discounted">
	<del aria-hidden="true"><span class="releasecart-price__was"><?php echo esc_html( $releasecart_was ); ?></span></del>
	<ins><span class="releasecart-price__now"><?php echo esc_html( $releasecart_now ); ?></span></ins>
	<span class="screen-reader-text"><?php echo esc_html( $releasecart_sr ); ?></span>
<?php
if ( '' !== $releasecart_note ) {
	printf( ' <span class="releasecart-price__note">%s</span>', esc_html( $releasecart_note ) );
}
?>
</span>

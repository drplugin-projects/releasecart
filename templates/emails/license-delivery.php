<?php
/**
 * License delivery email body.
 *
 * Theme override: {theme}/releasecart/emails/license-delivery.php.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array<string, mixed> $data {
 *     @type string $product_name Product name.
 *     @type string $license_key License key.
 *     @type string $expires_at Expiry date.
 *     @type array<int, array<string, mixed>> $downloads Download entries.
 * }
 */

defined( 'ABSPATH' ) || exit;

$releasecart_product   = (string) ( $data['product_name'] ?? '' );
$releasecart_key       = (string) ( $data['license_key'] ?? '' );
$releasecart_expires   = (string) ( $data['expires_at'] ?? '' );
$releasecart_downloads = is_array( $data['downloads'] ?? null ) ? $data['downloads'] : array();
?>
<h2 style="margin:0 0 16px; font-size:18px; font-weight:600; color:#1f2933;">
	<?php esc_html_e( 'Your license key', 'releasecart' ); ?>
</h2>

<p style="margin:0 0 24px; color:#52606d;">
	<?php
	printf(
		/* translators: %s: the product name. */
		esc_html__( 'Here is your license key for %s.', 'releasecart' ),
		esc_html( $releasecart_product )
	);
	?>
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; border-collapse:collapse; margin-bottom:24px;">
	<tbody>
		<tr>
			<td style="padding:10px 12px; background-color:#f8fafc; border:1px solid #e4e7eb;">
				<span style="display:block; color:#52606d; font-size:14px; margin-bottom:4px;"><?php echo esc_html( $releasecart_product ); ?></span>
				<code style="font-family:'SFMono-Regular',Consolas,'Liberation Mono',Menlo,monospace; font-size:15px; color:#1f2933; word-break:break-all;"><?php echo esc_html( $releasecart_key ); ?></code>
			</td>
		</tr>
	</tbody>
</table>

<?php if ( '' !== $releasecart_expires ) : ?>
	<p style="margin:0 0 24px; color:#52606d;">
		<?php
		printf(
			/* translators: %s: the license expiry date. */
			esc_html__( 'This license is valid until %s.', 'releasecart' ),
			esc_html( $releasecart_expires )
		);
		?>
	</p>
<?php endif; ?>

<?php if ( array() !== $releasecart_downloads ) : ?>
	<h3 style="margin:0 0 12px; font-size:16px; font-weight:600; color:#1f2933;">
		<?php esc_html_e( 'Your downloads', 'releasecart' ); ?>
	</h3>
	<p style="margin:0 0 12px; color:#7b8794; font-size:13px;">
		<?php esc_html_e( 'These links are valid for a limited time. Save your files somewhere safe.', 'releasecart' ); ?>
	</p>
	<?php foreach ( $releasecart_downloads as $releasecart_entry ) : ?>
		<p style="margin:0 0 6px; color:#1f2933; font-weight:600;"><?php echo esc_html( (string) ( $releasecart_entry['product_name'] ?? '' ) ); ?></p>
		<ul style="margin:0 0 16px; padding-left:20px;">
			<?php foreach ( ( is_array( $releasecart_entry['files'] ?? null ) ? $releasecart_entry['files'] : array() ) as $releasecart_file ) : ?>
				<li style="margin-bottom:4px;">
					<a href="<?php echo esc_url( (string) ( $releasecart_file['download_url'] ?? '' ) ); ?>" style="color:#2563eb; text-decoration:underline;">
						<?php echo esc_html( (string) ( $releasecart_file['label'] ?? '' ) ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endforeach; ?>
<?php endif; ?>

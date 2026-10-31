<?php
/**
 * Purchase receipt email body.
 *
 * Theme override: {theme}/releasecart/emails/purchase-receipt.php.
 * Show keys in the receipt.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array<string, mixed> $data {
 *     @type string $store_name Store name.
 *     @type array $order Order data.
 *     @type array<int, array<string, mixed>> $items Line items.
 *     @type string $subtotal Formatted subtotal.
 *     @type array|null $discount Discount data.
 *     @type string $total Formatted total.
 *     @type array<int, array<string, string>> $licenses License rows.
 *     @type array<int, array<string, mixed>> $downloads Download entries.
 * }
 */

defined( 'ABSPATH' ) || exit;

$releasecart_order     = is_array( $data['order'] ?? null ) ? $data['order'] : array();
$releasecart_reference = (string) ( $releasecart_order['reference'] ?? '' );
$releasecart_date      = (string) ( $releasecart_order['date'] ?? '' );
$releasecart_items     = is_array( $data['items'] ?? null ) ? $data['items'] : array();
$releasecart_discount  = is_array( $data['discount'] ?? null ) ? $data['discount'] : null;
$releasecart_licenses  = is_array( $data['licenses'] ?? null ) ? $data['licenses'] : array();
$releasecart_downloads = is_array( $data['downloads'] ?? null ) ? $data['downloads'] : array();
?>
<h2 style="margin:0 0 16px; font-size:18px; font-weight:600; color:#1f2933;">
	<?php esc_html_e( 'Thank you for your purchase', 'releasecart' ); ?>
</h2>

<p style="margin:0 0 24px; color:#52606d;">
	<?php
	printf(
		/* translators: 1: the order reference number, 2: the order date. */
		esc_html__( 'Here is your receipt for order #%1$s placed on %2$s.', 'releasecart' ),
		esc_html( $releasecart_reference ),
		esc_html( $releasecart_date )
	);
	?>
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; border-collapse:collapse; margin-bottom:24px;">
	<thead>
		<tr>
			<th align="left" style="padding:8px 0; border-bottom:2px solid #e4e7eb; font-size:13px; text-transform:uppercase; letter-spacing:0.04em; color:#7b8794;"><?php esc_html_e( 'Item', 'releasecart' ); ?></th>
			<th align="center" style="padding:8px 0; border-bottom:2px solid #e4e7eb; font-size:13px; text-transform:uppercase; letter-spacing:0.04em; color:#7b8794;"><?php esc_html_e( 'Qty', 'releasecart' ); ?></th>
			<th align="right" style="padding:8px 0; border-bottom:2px solid #e4e7eb; font-size:13px; text-transform:uppercase; letter-spacing:0.04em; color:#7b8794;"><?php esc_html_e( 'Total', 'releasecart' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $releasecart_items as $releasecart_item ) : ?>
			<tr>
				<td align="left" style="padding:12px 0; border-bottom:1px solid #f0f1f3; color:#1f2933;"><?php echo esc_html( (string) ( $releasecart_item['name'] ?? '' ) ); ?></td>
				<td align="center" style="padding:12px 0; border-bottom:1px solid #f0f1f3; color:#52606d;"><?php echo esc_html( (string) ( $releasecart_item['quantity'] ?? '' ) ); ?></td>
				<td align="right" style="padding:12px 0; border-bottom:1px solid #f0f1f3; color:#1f2933;"><?php echo esc_html( (string) ( $releasecart_item['total'] ?? '' ) ); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
	<tfoot>
		<tr>
			<td align="right" colspan="2" style="padding:12px 8px 4px 0; color:#52606d;"><?php esc_html_e( 'Subtotal', 'releasecart' ); ?></td>
			<td align="right" style="padding:12px 0 4px; color:#1f2933;"><?php echo esc_html( (string) ( $data['subtotal'] ?? '' ) ); ?></td>
		</tr>
		<?php if ( null !== $releasecart_discount ) : ?>
			<tr>
				<td align="right" colspan="2" style="padding:4px 8px 4px 0; color:#52606d;">
					<?php
					printf(
						/* translators: %s: the discount code the buyer applied. */
						esc_html__( 'Discount (%s)', 'releasecart' ),
						esc_html( (string) ( $releasecart_discount['code'] ?? '' ) )
					);
					?>
				</td>
				<td align="right" style="padding:4px 0; color:#1f2933;">&minus;<?php echo esc_html( (string) ( $releasecart_discount['amount'] ?? '' ) ); ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<td align="right" colspan="2" style="padding:8px 8px 0 0; font-weight:600; color:#1f2933;"><?php esc_html_e( 'Order total', 'releasecart' ); ?></td>
			<td align="right" style="padding:8px 0 0; font-weight:600; color:#1f2933;"><?php echo esc_html( (string) ( $data['total'] ?? '' ) ); ?></td>
		</tr>
	</tfoot>
</table>

<?php if ( array() !== $releasecart_licenses ) : ?>
	<h3 style="margin:0 0 12px; font-size:16px; font-weight:600; color:#1f2933;">
		<?php esc_html_e( 'Your license keys', 'releasecart' ); ?>
	</h3>
	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; border-collapse:collapse; margin-bottom:24px;">
		<tbody>
			<?php foreach ( $releasecart_licenses as $releasecart_license ) : ?>
				<tr>
					<td style="padding:10px 12px; background-color:#f8fafc; border:1px solid #e4e7eb;">
						<span style="display:block; color:#52606d; font-size:14px; margin-bottom:4px;"><?php echo esc_html( (string) ( $releasecart_license['product_name'] ?? '' ) ); ?></span>
						<code style="font-family:'SFMono-Regular',Consolas,'Liberation Mono',Menlo,monospace; font-size:15px; color:#1f2933; word-break:break-all;"><?php echo esc_html( (string) ( $releasecart_license['license_key'] ?? '' ) ); ?></code>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
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

<?php
/**
 * Product screenshots template.
 *
 * Uses the shared gallery markup for the block and product details view.
 *
 * @package DrPlugin\WP\ReleaseCart
 *
 * @var array{screenshots: array<int, array{full: string, thumb: string, alt: string, caption: string}>} $data View data.
 */

defined( 'ABSPATH' ) || exit;

$releasecart_screenshots = ( isset( $data['screenshots'] ) && is_array( $data['screenshots'] ) ) ? $data['screenshots'] : array();

require RELEASECART_PATH . 'templates/components/partials/product-screenshots-grid.php';

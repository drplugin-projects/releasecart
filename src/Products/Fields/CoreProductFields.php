<?php
/**
 * The product fields and panels ReleaseCart registers itself.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Products\Fields;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

/**
 * Core product editor field and panel definitions.
 *
 * Content fields use product columns. Media fields use settings.
 *
 * @since 1.0.0
 */
final class CoreProductFields {

	/**
	 * Core field definitions in editor order.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, array<string, mixed>> The built-in fields.
	 */
	public function fields(): array {
		return array(
			array(
				'id'      => 'overview',
				'panel'   => 'content',
				'type'    => 'rich_text',
				'label'   => __( 'Overview', 'releasecart' ),
				'help'    => __( 'A short rich-text summary shown near the top of the product page.', 'releasecart' ),
				'storage' => 'column',
			),
			array(
				'id'      => 'description',
				'panel'   => 'content',
				'type'    => 'rich_text',
				'label'   => __( 'Full description', 'releasecart' ),
				'help'    => __( 'The long description with formatting, shown on the product and pricing pages.', 'releasecart' ),
				'storage' => 'column',
			),
			array(
				'id'    => 'main_image',
				'panel' => 'media',
				'type'  => 'media',
				'label' => __( 'Main image', 'releasecart' ),
				'help'  => __( 'The primary product image, from the media library.', 'releasecart' ),
			),
			array(
				'id'    => 'thumbnail',
				'panel' => 'media',
				'type'  => 'media',
				'label' => __( 'Thumbnail', 'releasecart' ),
				'help'  => __( 'A smaller image for product grids and listings.', 'releasecart' ),
			),
			array(
				'id'    => 'og_image',
				'panel' => 'media',
				'type'  => 'media',
				'label' => __( 'Social share image', 'releasecart' ),
				'help'  => __( 'Shown when the product page is shared on social networks.', 'releasecart' ),
			),
			array(
				'id'    => 'screenshots',
				'panel' => 'media',
				'type'  => 'media_gallery',
				'label' => __( 'Screenshots', 'releasecart' ),
				'help'  => __( 'A gallery of product screenshots, from the media library.', 'releasecart' ),
			),
		);
	}

	/**
	 * Core editor panels.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, array<string, string>> The built-in panels as `{id, label}` rows.
	 */
	public function panels(): array {
		return array(
			array(
				'id'    => 'content',
				'label' => __( 'Content', 'releasecart' ),
			),
			array(
				'id'    => 'media',
				'label' => __( 'Media', 'releasecart' ),
			),
		);
	}
}

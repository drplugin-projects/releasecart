<?php
/**
 * The curated Elementor style controls, each writing a design token.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Frontend\Adapters\Elementor;

defined( 'ABSPATH' ) || exit;

/**
 * Maps shared design tokens to Elementor style controls.
 *
 * Elementor scopes the generated CSS to each widget wrapper.
 *
 * @since 1.0.0
 */
class StyleControlMap {

	/**
	 * Slider control type string, mirroring \Elementor\Controls_Manager::SLIDER.
	 *
	 * @var string
	 */
	private const TYPE_SLIDER = 'slider';

	/**
	 * The style controls, keyed by control id.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, array<string, mixed>> Elementor control args per control id.
	 */
	public function controls(): array {
		return array(
			'releasecart_color_primary'  => array(
				'label'     => __( 'Primary color', 'releasecart' ),
				'type'      => ControlMap::TYPE_COLOR,
				'selectors' => array( '{{WRAPPER}}' => '--releasecart-color-primary: {{VALUE}};' ),
			),
			'releasecart_color_text'     => array(
				'label'     => __( 'Text color', 'releasecart' ),
				'type'      => ControlMap::TYPE_COLOR,
				'selectors' => array( '{{WRAPPER}}' => '--releasecart-color-text: {{VALUE}};' ),
			),
			'releasecart_color_surface'  => array(
				'label'     => __( 'Surface color', 'releasecart' ),
				'type'      => ControlMap::TYPE_COLOR,
				'selectors' => array( '{{WRAPPER}}' => '--releasecart-color-surface: {{VALUE}};' ),
			),
			'releasecart_radius'         => array(
				'label'      => __( 'Corner radius', 'releasecart' ),
				'type'       => self::TYPE_SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array( '{{WRAPPER}}' => '--releasecart-radius: {{SIZE}}{{UNIT}};' ),
			),
			'releasecart_font_size_base' => array(
				'label'      => __( 'Base font size', 'releasecart' ),
				'type'       => self::TYPE_SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array( '{{WRAPPER}}' => '--releasecart-font-size-base: {{SIZE}}{{UNIT}};' ),
			),
		);
	}
}

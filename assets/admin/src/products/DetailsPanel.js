/**
 * Details panel for the product editor.
 *
 * Keep the saved permalink visible while a slug change is unsaved.
 */

/**
 * External dependencies
 */
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, TextControl } from '@wordpress/components';

/**
 * Render the details panel.
 *
 * @param {Object}      props             Component props.
 * @param {Object}      props.form        The current product form state.
 * @param {Function}    props.update      Update one top-level form field.
 * @param {Element}     props.fieldPanels The registered product-field panels node.
 * @param {boolean}     props.isNew       Whether this is an unsaved product.
 * @param {string|null} props.permalink   The saved product's storefront URL, or
 *                                        null when there is none to link to.
 * @return {Element} The details panel element.
 */
export default function DetailsPanel( {
	form,
	update,
	fieldPanels,
	isNew,
	permalink,
} ) {
	// Reset this state when the editor loads a different product.
	const [ editingSlug, setEditingSlug ] = useState( false );

	const hasLink = ! isNew && !! permalink;
	const showSlug = ! hasLink || editingSlug;

	const slugField = (
		<TextControl
			__nextHasNoMarginBottom
			__next40pxDefaultSize
			label={ __( 'Slug', 'releasecart' ) }
			value={ form.slug }
			help={
				isNew
					? __(
							'Leave empty to generate one from the name.',
							'releasecart'
					  )
					: __(
							'Leave empty to keep the current slug.',
							'releasecart'
					  )
			}
			onChange={ ( value ) => update( 'slug', value ) }
		/>
	);

	return (
		<div className="releasecart-panel">
			<div className="releasecart-form-stack">
				<TextControl
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					label={ __( 'Name', 'releasecart' ) }
					value={ form.name }
					onChange={ ( value ) => update( 'name', value ) }
				/>
				<div className="releasecart-permalink">
					{ hasLink && (
						<p className="releasecart-permalink__line">
							<span className="releasecart-permalink__label">
								{ __( 'Permalink:', 'releasecart' ) }
							</span>
							<a
								className="releasecart-permalink__url"
								href={ permalink }
								target="_blank"
								rel="noreferrer"
							>
								{ permalink }
							</a>
							{ ! editingSlug && (
								<Button
									variant="link"
									onClick={ () => setEditingSlug( true ) }
								>
									{ __( 'Edit', 'releasecart' ) }
								</Button>
							) }
						</p>
					) }
					{ showSlug && slugField }
					{ ! hasLink && (
						<p className="releasecart-permalink__note">
							{ isNew
								? __(
										'Save the product to get a shareable link.',
										'releasecart'
								  )
								: __(
										'Set a product details page under Settings to get a shareable link.',
										'releasecart'
								  ) }
						</p>
					) }
				</div>
			</div>
			{ fieldPanels }
		</div>
	);
}

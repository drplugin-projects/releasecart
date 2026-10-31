/**
 * Publish sidebar for the product editor.
 *
 * Use the shared form callbacks for all product fields.
 */

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	Button,
	SelectControl,
	ToggleControl,
	TextControl,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import CategoryTreePanel from './CategoryTreePanel';
import TermsPanel from './TermsPanel';

/**
 * Render the publish sidebar.
 *
 * @param {Object}   props              Component props.
 * @param {Object}   props.form         The current product form state.
 * @param {Function} props.update       Update one top-level form field.
 * @param {Function} props.onToggleTerm Add or remove a term id in a taxonomy's selection.
 * @param {boolean}  props.isSaving     Whether a save is in flight.
 * @param {Function} props.onSave       Submit the form.
 * @param {Element}  props.mediaPanels  The registered media panel node, or a falsy value when none.
 * @return {Element} The sidebar element.
 */
export default function PublishSidebar( {
	form,
	update,
	onToggleTerm,
	isSaving,
	onSave,
	mediaPanels,
} ) {
	return (
		<aside className="releasecart-product-edit__sidebar">
			<div className="releasecart-panel releasecart-product-edit__box">
				<h2>{ __( 'Publish', 'releasecart' ) }</h2>
				<div className="releasecart-form-stack">
					<SelectControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Status', 'releasecart' ) }
						value={ form.status }
						options={ [
							{
								label: __( 'Draft', 'releasecart' ),
								value: 'draft',
							},
							{
								label: __( 'Published', 'releasecart' ),
								value: 'published',
							},
							{
								label: __( 'Archived', 'releasecart' ),
								value: 'archived',
							},
						] }
						onChange={ ( value ) => update( 'status', value ) }
					/>
					<div className="releasecart-form-actions">
						<Button
							variant="primary"
							isBusy={ isSaving }
							disabled={ isSaving }
							onClick={ onSave }
						>
							{ __( 'Save product', 'releasecart' ) }
						</Button>
					</div>
				</div>
			</div>

			<div className="releasecart-panel releasecart-product-edit__box">
				<h2>{ __( 'Product type', 'releasecart' ) }</h2>
				<div className="releasecart-form-stack">
					<SelectControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Type', 'releasecart' ) }
						value={ form.type }
						options={ [
							{
								label: __( 'Digital', 'releasecart' ),
								value: 'digital',
							},
							{
								label: __( 'Bundle', 'releasecart' ),
								value: 'bundle',
							},
						] }
						onChange={ ( value ) => update( 'type', value ) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Licensed product', 'releasecart' ) }
						checked={ form.is_licensed }
						onChange={ ( value ) => update( 'is_licensed', value ) }
					/>
					{ form.is_licensed && (
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Update slug', 'releasecart' ) }
							value={ form.update_slug }
							help={ __(
								'The plugin/theme folder slug used by update checks.',
								'releasecart'
							) }
							onChange={ ( value ) =>
								update( 'update_slug', value )
							}
						/>
					) }
				</div>
			</div>

			<CategoryTreePanel
				selectedIds={ form.categories }
				onAdd={ ( id ) => onToggleTerm( 'categories', id, true ) }
				onRemove={ ( id ) => onToggleTerm( 'categories', id, false ) }
			/>

			<TermsPanel
				selectedIds={ form.tags }
				onAdd={ ( id ) => onToggleTerm( 'tags', id, true ) }
				onRemove={ ( id ) => onToggleTerm( 'tags', id, false ) }
			/>

			{ mediaPanels && (
				<div className="releasecart-panel releasecart-product-edit__box releasecart-product-edit__box--media">
					{ mediaPanels }
				</div>
			) }
		</aside>
	);
}

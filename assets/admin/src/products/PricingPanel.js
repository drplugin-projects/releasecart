/**
 * Pricing panel for the product editor.
 *
 * Product saves include the complete price-tier list. Extensions add their own
 * controls to each tier card through the price tier registry.
 */

/**
 * External dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { Button, TextControl, SelectControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import EmptyState from '../components/EmptyState';
import { toInt, blankPrice } from './productForm';
import { parseLicenseLength, formatLicenseLength } from './licenseLength';
import { usePriceTierExtensions } from './priceTierExtensions';

/**
 * Render the pricing panel.
 *
 * @param {Object}   props          Component props.
 * @param {Object}   props.form     The current product form state.
 * @param {Function} props.setForm  The form state setter.
 * @param {string}   props.currency The store currency code.
 * @return {Element} The pricing panel element.
 */
export default function PricingPanel( { form, setForm, currency } ) {
	const extensions = usePriceTierExtensions();

	/**
	 * Merge changes into the price row at a given index.
	 *
	 * @param {number}          index   The price row index.
	 * @param {Object|Function} changes The fields to merge, or a function of the
	 *                                  current row that returns them.
	 * @return {void}
	 */
	const mergePrice = ( index, changes ) =>
		setForm( ( prev ) => {
			const prices = prev.prices.slice();
			const current = prices[ index ];
			const patch =
				typeof changes === 'function' ? changes( current ) : changes;
			prices[ index ] = { ...current, ...patch };
			return { ...prev, prices };
		} );

	/**
	 * Update one field of the price row at a given index.
	 *
	 * @param {number} index The price row index.
	 * @param {string} key   The price field.
	 * @param {*}      value The new value.
	 * @return {void}
	 */
	const updatePrice = ( index, key, value ) =>
		mergePrice( index, { [ key ]: value } );

	/**
	 * Replace the product settings with what an updater returns.
	 *
	 * @param {Function} updater Receives the current settings, returns the new ones.
	 * @return {void}
	 */
	const updateSettings = ( updater ) =>
		setForm( ( prev ) => ( {
			...prev,
			settings: updater( prev.settings || {} ),
		} ) );

	// Store singular duration units but show plural labels.
	const licenseUnitOptions = [
		{ label: __( 'Days', 'releasecart' ), value: 'day' },
		{ label: __( 'Weeks', 'releasecart' ), value: 'week' },
		{ label: __( 'Months', 'releasecart' ), value: 'month' },
		{ label: __( 'Years', 'releasecart' ), value: 'year' },
	];

	/**
	 * Render the license-length control for one tier.
	 *
	 * @param {Object} price The price row.
	 * @param {number} index The price row index.
	 * @return {Element} The control element.
	 */
	const renderLicenseLength = ( price, index ) => {
		const raw = price.license_length;
		const parsed = parseLicenseLength( raw );
		// An empty value asks the merchant to pick a length.
		const isPrompt = raw === '';

		// Preserve an unrecognised value until the merchant replaces it.
		if ( parsed.mode === 'custom' && ! isPrompt ) {
			return (
				<div className="releasecart-license-length">
					<span className="releasecart-license-length__label">
						{ __( 'License length', 'releasecart' ) }
					</span>
					<p className="releasecart-license-length__custom">
						{ sprintf(
							/* translators: %s: the stored license-length value. */
							__( 'Custom: %s', 'releasecart' ),
							parsed.raw
						) }
					</p>
					<Button
						variant="secondary"
						onClick={ () =>
							updatePrice(
								index,
								'license_length',
								formatLicenseLength( 1, 'month' )
							)
						}
					>
						{ __( 'Replace', 'releasecart' ) }
					</Button>
				</div>
			);
		}

		const mode = isPrompt ? 'prompt' : parsed.mode;
		const modeOptions = [
			...( isPrompt
				? [
						{
							label: __( 'Select a length', 'releasecart' ),
							value: 'prompt',
						},
				  ]
				: [] ),
			{ label: __( 'Lifetime', 'releasecart' ), value: 'lifetime' },
			{ label: __( 'Duration', 'releasecart' ), value: 'duration' },
		];

		/**
		 * Set the selected license-length mode.
		 *
		 * @param {string} value The selected mode.
		 * @return {void}
		 */
		const onModeChange = ( value ) => {
			if ( value === 'lifetime' ) {
				updatePrice( index, 'license_length', null );
			} else if ( value === 'duration' ) {
				updatePrice(
					index,
					'license_length',
					formatLicenseLength( 1, 'month' )
				);
			}
		};

		return (
			<div className="releasecart-license-length">
				<SelectControl
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					label={ __( 'License length', 'releasecart' ) }
					value={ mode }
					options={ modeOptions }
					onChange={ onModeChange }
				/>
				{ mode === 'duration' && (
					<div className="releasecart-license-length__duration">
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							type="number"
							label={ __( 'Length', 'releasecart' ) }
							value={ String( parsed.count ) }
							onChange={ ( value ) =>
								updatePrice(
									index,
									'license_length',
									formatLicenseLength(
										Math.max( 1, toInt( value ) ),
										parsed.unit
									)
								)
							}
						/>
						<SelectControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Unit', 'releasecart' ) }
							value={ parsed.unit }
							options={ licenseUnitOptions }
							onChange={ ( value ) =>
								updatePrice(
									index,
									'license_length',
									formatLicenseLength( parsed.count, value )
								)
							}
						/>
					</div>
				) }
			</div>
		);
	};

	const addPriceButton = (
		<Button
			variant="secondary"
			onClick={ () =>
				setForm( ( prev ) => ( {
					...prev,
					prices: [ ...prev.prices, blankPrice() ],
				} ) )
			}
		>
			{ __( 'Add price', 'releasecart' ) }
		</Button>
	);

	return (
		<div className="releasecart-panel">
			{ form.prices.length === 0 && (
				<EmptyState
					className="releasecart-empty-state--panel"
					message={ __(
						'No prices yet. A product needs at least one price before it can be bought.',
						'releasecart'
					) }
					action={ addPriceButton }
				/>
			) }
			{ form.prices.map( ( price, index ) => (
				<fieldset key={ index } className="releasecart-price-row">
					<legend className="releasecart-price-row__legend">
						{ sprintf(
							/* translators: %d: the position of this price in the list, starting at 1. */
							__( 'Price %d', 'releasecart' ),
							index + 1
						) }
					</legend>
					<div className="releasecart-form-stack">
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Price name', 'releasecart' ) }
							value={ price.name }
							onChange={ ( value ) =>
								updatePrice( index, 'name', value )
							}
						/>
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							type="number"
							label={ sprintf(
								/* translators: %s: the store's currency code, for example USD. */
								__( 'Amount (%s)', 'releasecart' ),
								currency
							) }
							value={ price.amount }
							onChange={ ( value ) =>
								updatePrice( index, 'amount', value )
							}
						/>
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							type="number"
							label={ __(
								'Activation limit (0 = unlimited)',
								'releasecart'
							) }
							value={ String( price.activation_limit ) }
							onChange={ ( value ) =>
								updatePrice(
									index,
									'activation_limit',
									toInt( value )
								)
							}
						/>
						{ renderLicenseLength( price, index ) }
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							type="number"
							label={ __( 'Minimum quantity', 'releasecart' ) }
							value={ String( price.min_quantity ) }
							onChange={ ( value ) =>
								updatePrice(
									index,
									'min_quantity',
									toInt( value )
								)
							}
						/>
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							type="number"
							label={ __(
								'Maximum quantity (0 = unlimited)',
								'releasecart'
							) }
							value={ String( price.max_quantity ) }
							onChange={ ( value ) =>
								updatePrice(
									index,
									'max_quantity',
									toInt( value )
								)
							}
						/>
						{ extensions.map( ( { id, component: Extension } ) => (
							<Extension
								key={ id }
								price={ price }
								index={ index }
								prices={ form.prices }
								settings={ form.settings || {} }
								updatePrice={ ( changes ) =>
									mergePrice( index, changes )
								}
								updateSettings={ updateSettings }
							/>
						) ) }
						<SelectControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Price status', 'releasecart' ) }
							value={ price.status }
							options={ [
								{
									label: __( 'Active', 'releasecart' ),
									value: 'active',
								},
								{
									label: __( 'Archived', 'releasecart' ),
									value: 'archived',
								},
							] }
							onChange={ ( value ) =>
								updatePrice( index, 'status', value )
							}
						/>
					</div>
					<div className="releasecart-form-actions">
						<Button
							variant="secondary"
							isDestructive
							onClick={ () =>
								setForm( ( prev ) => ( {
									...prev,
									prices: prev.prices.filter(
										( item, position ) => position !== index
									),
								} ) )
							}
						>
							{ __( 'Remove price', 'releasecart' ) }
						</Button>
					</div>
				</fieldset>
			) ) }
			{ form.prices.length > 0 && (
				<div className="releasecart-form-actions">
					{ addPriceButton }
				</div>
			) }
		</div>
	);
}

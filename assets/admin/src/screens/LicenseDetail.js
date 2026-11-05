/**
 * Show a license and its activations.
 */

/**
 * External dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Button, Modal, Notice, TextControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { del, get } from '../api/client';
import ScreenHeader from '../components/ScreenHeader';
import { SkeletonForm } from '../components/Skeleton';
import StatusBadge from '../components/StatusBadge';
import { joinList } from '../utils/list';
import { firstReadPending } from '../utils/listState';

/**
 * Return link for all screen states.
 *
 * @type {{href: string, label: string}}
 */
const BACK_TO_LICENSES = {
	href: '#/licenses',
	label: __( 'Back to licenses', 'releasecart' ),
};

/**
 * License status labels.
 *
 * @type {Object<string, string>}
 */
const STATUS_LABELS = {
	active: __( 'Active', 'releasecart' ),
	expired: __( 'Expired', 'releasecart' ),
	disabled: __( 'Disabled', 'releasecart' ),
};

/**
 * Activation status labels.
 *
 * @type {Object<string, string>}
 */
const ACTIVATION_STATUS_LABELS = {
	active: __( 'Active', 'releasecart' ),
	deactivated: __( 'Deactivated', 'releasecart' ),
};

/**
 * Join the environment a client reported into one line, such as
 * "php: 8.2.20, wp: 6.9.1". The keys are whatever the client sent.
 *
 * @param {?Object} environment The stored environment map, or null.
 * @return {string} The joined pairs, or an empty string.
 */
function formatEnvironment( environment ) {
	if ( ! environment || 'object' !== typeof environment ) {
		return '';
	}

	return joinList(
		Object.entries( environment )
			.filter( ( [ , value ] ) => '' !== String( value ).trim() )
			.map( ( [ key, value ] ) => `${ key }: ${ value }` )
	);
}

/**
 * Build the react-query cache key for a single license.
 *
 * @param {string} id The license id, from the route param.
 * @return {Array} The query key.
 */
function licenseQueryKey( id ) {
	return [ 'license', id ];
}

/**
 * Format a date as stable YYYY-MM-DD text.
 *
 * @param {?string} dateString The ISO date-time string, or a falsy value for
 *                             "never happened" / "never expires".
 * @param {string}  [fallback] The label to show for a falsy date string.
 * @return {string} The formatted date, or the fallback label.
 */
function formatDate( dateString, fallback = __( 'Never', 'releasecart' ) ) {
	if ( ! dateString ) {
		return fallback;
	}
	const date = new Date( dateString );
	if ( Number.isNaN( date.getTime() ) ) {
		return dateString;
	}
	return date.toISOString().slice( 0, 10 );
}

/**
 * Format activation usage with an unlimited fallback.
 *
 * @param {number}  count The current activation count.
 * @param {?number} limit The activation limit, or a falsy value for
 *                        unlimited.
 * @return {string} The formatted usage string, e.g. "2 / 3".
 */
function formatUsage( count, limit ) {
	return sprintf(
		/* translators: 1: number of activations used, 2: the activation limit (or an infinity symbol for unlimited). */
		__( '%1$d / %2$s', 'releasecart' ),
		count,
		limit ? String( limit ) : '∞'
	);
}

/**
 * Render the license detail screen.
 *
 * @param {Object} props        Component props.
 * @param {Object} props.params The route params; `params.id` is the license
 *                              id from the `#/licenses/:id` hash.
 * @return {Element} The license detail screen element.
 */
export default function LicenseDetail( { params } ) {
	const licenseId = params.id;
	const queryClient = useQueryClient();
	const [ confirmActivationId, setConfirmActivationId ] = useState( null );
	const [ copied, setCopied ] = useState( false );

	const licenseQuery = useQuery( {
		queryKey: licenseQueryKey( licenseId ),
		queryFn: () => get( `/licenses/${ licenseId }` ),
	} );
	const { data, isError, error } = licenseQuery;

	const deactivateMutation = useMutation( {
		/**
		 * Deactivate a single activation.
		 *
		 * @param {number} activationId The activation id to deactivate.
		 * @return {Promise<Object>} The refreshed license detail payload.
		 */
		mutationFn: ( activationId ) =>
			del( `/licenses/${ licenseId }/activations/${ activationId }` ),
		onSuccess: ( refreshedDetail ) => {
			// Reuse the returned detail and mark it stale for later reads.
			queryClient.setQueryData(
				licenseQueryKey( licenseId ),
				refreshedDetail
			);
			queryClient.invalidateQueries( {
				queryKey: licenseQueryKey( licenseId ),
				refetchType: 'none',
			} );
			// The list needs a new activation count.
			queryClient.invalidateQueries( { queryKey: [ 'licenses' ] } );
			setConfirmActivationId( null );
		},
	} );

	/**
	 * Copy the full license key to the clipboard.
	 *
	 * @return {void}
	 */
	function handleCopy() {
		navigator.clipboard.writeText( data.license_key );
		setCopied( true );
	}

	// Do not read license data until the first request has a usable result.
	if ( firstReadPending( licenseQuery ) || ( ! isError && ! data ) ) {
		return (
			<section className="releasecart-admin-screen">
				<ScreenHeader
					title={ __( 'License', 'releasecart' ) }
					backLink={ BACK_TO_LICENSES }
				/>
				<SkeletonForm fields={ 5 } />
			</section>
		);
	}

	if ( isError ) {
		return (
			<section className="releasecart-admin-screen">
				<ScreenHeader
					title={ __( 'License', 'releasecart' ) }
					backLink={ BACK_TO_LICENSES }
				/>
				<Notice
					status={ error.status === 404 ? 'warning' : 'error' }
					isDismissible={ false }
				>
					{ error.status === 404
						? __( 'License not found.', 'releasecart' )
						: error.message }
				</Notice>
			</section>
		);
	}

	return (
		<section className="releasecart-admin-screen">
			<ScreenHeader
				title={ __( 'License', 'releasecart' ) }
				backLink={ BACK_TO_LICENSES }
			/>

			{ /* Keep the key and metadata in one panel. */ }
			<div className="releasecart-panel">
				<div className="releasecart-license-key-field">
					<TextControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'License key', 'releasecart' ) }
						value={ data.license_key }
						readOnly
						className="releasecart-mono"
					/>
					{ /* Match the key field height. */ }
					<Button
						variant="secondary"
						__next40pxDefaultSize
						onClick={ handleCopy }
					>
						{ copied
							? __( 'Copied', 'releasecart' )
							: __( 'Copy', 'releasecart' ) }
					</Button>
				</div>

				<dl className="releasecart-license-meta">
					<div>
						<dt>{ __( 'Product', 'releasecart' ) }</dt>
						<dd>
							{ /* Use the id when the product no longer exists. */ }
							{ data.product_name ||
								sprintf(
									/* translators: %d: a numeric record id. */
									__( '#%d', 'releasecart' ),
									data.product_id
								) }
						</dd>
					</div>
					{ data.bundle_product_id > 0 && (
						<div>
							<dt>{ __( 'Bundle', 'releasecart' ) }</dt>
							<dd>
								{ data.bundle_product_name ||
									sprintf(
										/* translators: %d: a numeric record id. */
										__( '#%d', 'releasecart' ),
										data.bundle_product_id
									) }
							</dd>
						</div>
					) }
					<div>
						<dt>{ __( 'Status', 'releasecart' ) }</dt>
						<dd>
							<StatusBadge
								status={ data.status }
								label={
									STATUS_LABELS[ data.status ] || data.status
								}
							/>
						</dd>
					</div>
					<div>
						<dt>{ __( 'Expires', 'releasecart' ) }</dt>
						<dd>{ formatDate( data.expires_at ) }</dd>
					</div>
					<div>
						<dt>{ __( 'Activations', 'releasecart' ) }</dt>
						<dd>
							{ formatUsage(
								data.activation_count,
								data.activation_limit
							) }
						</dd>
					</div>
					<div>
						<dt>{ __( 'Order', 'releasecart' ) }</dt>
						<dd>
							<a href={ `#/orders/${ data.order_id }` }>
								{ sprintf(
									/* translators: %d: a numeric record id. */
									__( '#%d', 'releasecart' ),
									data.order_id
								) }
							</a>
						</dd>
					</div>
					<div>
						<dt>{ __( 'Customer', 'releasecart' ) }</dt>
						<dd>
							{ /* Use the id when the customer record no longer exists. */ }
							{ data.customer_email ? (
								<a
									href={ `#/customers?search=${ encodeURIComponent(
										data.customer_email
									) }` }
								>
									{ data.customer_email }
								</a>
							) : (
								sprintf(
									/* translators: %d: a numeric record id. */
									__( '#%d', 'releasecart' ),
									data.customer_id
								)
							) }
						</dd>
					</div>
				</dl>
			</div>

			<h2>{ __( 'Activations', 'releasecart' ) }</h2>
			<table className="releasecart-activations-table wp-list-table widefat striped">
				<thead>
					<tr>
						<th>{ __( 'Site', 'releasecart' ) }</th>
						<th>{ __( 'Status', 'releasecart' ) }</th>
						<th>{ __( 'Activated', 'releasecart' ) }</th>
						<th>{ __( 'Deactivated', 'releasecart' ) }</th>
						<th>{ __( 'Last checked', 'releasecart' ) }</th>
						<th>{ __( 'Actions', 'releasecart' ) }</th>
					</tr>
				</thead>
				<tbody>
					{ data.activations.length === 0 && (
						<tr>
							<td colSpan={ 6 }>
								{ __( 'No activations yet.', 'releasecart' ) }
							</td>
						</tr>
					) }
					{ data.activations.map( ( activation ) => {
						const environment = formatEnvironment(
							activation.environment
						);

						return (
							<tr key={ activation.id }>
								<td>
									{ activation.site_url }
									{ environment && (
										<div className="description">
											{ environment }
										</div>
									) }
								</td>
								<td>
									<StatusBadge
										status={ activation.status }
										label={
											ACTIVATION_STATUS_LABELS[
												activation.status
											] || activation.status
										}
									/>
								</td>
								<td>
									{ formatDate( activation.activated_at ) }
								</td>
								<td>
									{ formatDate(
										activation.deactivated_at,
										'-'
									) }
								</td>
								<td>
									{ formatDate(
										activation.last_checked_at,
										__( 'Never', 'releasecart' )
									) }
								</td>
								<td>
									{ activation.status === 'active' && (
										<Button
											variant="secondary"
											isDestructive
											onClick={ () =>
												setConfirmActivationId(
													activation.id
												)
											}
										>
											{ __(
												'Deactivate',
												'releasecart'
											) }
										</Button>
									) }
								</td>
							</tr>
						);
					} ) }
				</tbody>
			</table>

			{ confirmActivationId !== null && (
				<Modal
					className="releasecart-modal releasecart-modal--confirm"
					title={ __( 'Deactivate this activation?', 'releasecart' ) }
					onRequestClose={ () => setConfirmActivationId( null ) }
				>
					<p>
						{ __(
							'This frees up the activation slot right away, so the customer can activate their license elsewhere.',
							'releasecart'
						) }
					</p>

					{ deactivateMutation.isError && (
						<Notice status="error" isDismissible={ false }>
							{ deactivateMutation.error.message }
						</Notice>
					) }

					<div className="releasecart-modal-actions">
						<Button
							variant="tertiary"
							disabled={ deactivateMutation.isPending }
							onClick={ () => setConfirmActivationId( null ) }
						>
							{ __( 'Cancel', 'releasecart' ) }
						</Button>
						<Button
							variant="primary"
							isDestructive
							isBusy={ deactivateMutation.isPending }
							disabled={ deactivateMutation.isPending }
							onClick={ () =>
								deactivateMutation.mutate( confirmActivationId )
							}
						>
							{ __( 'Yes, deactivate', 'releasecart' ) }
						</Button>
					</div>
				</Modal>
			) }
		</section>
	);
}

/**
 * Route registry entry for the license detail screen.
 *
 * @type {{path: string, title: string, capability: string, component: Function}}
 */
export const route = {
	path: '#/licenses/:id',
	title: __( 'License', 'releasecart' ),
	capability: 'manage_releasecart',
	component: LicenseDetail,
};

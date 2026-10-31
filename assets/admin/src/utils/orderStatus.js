/**
 * The human labels for an order status.
 *
 * Shared labels for order statuses.
 */

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Map an order status slug to its human label.
 *
 * Unknown statuses use their raw slug.
 *
 * @param {string} status The order status slug.
 * @return {string} The translated label, or the slug itself.
 */
export function orderStatusLabel( status ) {
	const map = {
		pending: __( 'Pending', 'releasecart' ),
		completed: __( 'Completed', 'releasecart' ),
		failed: __( 'Failed', 'releasecart' ),
		abandoned: __( 'Abandoned', 'releasecart' ),
		refunded: __( 'Refunded', 'releasecart' ),
		partially_refunded: __( 'Partially refunded', 'releasecart' ),
	};
	return map[ status ] || status;
}

/**
 * Loading placeholders for admin screens.
 *
 * CSS reserves each variant's height to prevent layout shifts.
 */

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * A list of n indexes, for rendering n identical bars.
 *
 * @param {number} n How many.
 * @return {number[]} The indexes, from zero.
 */
function times( n ) {
	return Array.from( { length: Math.max( 0, n ) }, ( _value, i ) => i );
}

/**
 * Wrap a skeleton in an accessible loading region.
 *
 * @param {Object}    props           Component props.
 * @param {string}    props.variant   The variant modifier suffix.
 * @param {boolean}   [props.panel]   Reserve the panel height.
 * @param {boolean}   [props.compact] Use the short reserve above other content.
 * @param {Element[]} props.children  The bars.
 * @return {Element} The skeleton region.
 */
function Region( { variant, panel, compact, children } ) {
	const classes = [
		'releasecart-skeleton',
		`releasecart-skeleton--${ variant }`,
		panel ? 'releasecart-skeleton--panel' : '',
		compact ? `releasecart-skeleton--${ variant }-compact` : '',
	]
		.filter( Boolean )
		.join( ' ' );

	return (
		<div className={ classes } role="status" data-testid="skeleton">
			<span className="screen-reader-text">
				{ __( 'Loading', 'releasecart' ) }
			</span>
			{ children }
		</div>
	);
}

/**
 * Render text placeholder bars.
 *
 * @param {number} lines How many lines.
 * @return {Element[]} The bars.
 */
function lineBars( lines ) {
	return times( lines ).map( ( i ) => (
		<div
			key={ i }
			className={
				i === lines - 1
					? 'releasecart-skeleton__line releasecart-skeleton__line--short'
					: 'releasecart-skeleton__line'
			}
		/>
	) );
}

/**
 * Render a text placeholder at list height.
 *
 * @param {Object} props         Component props.
 * @param {number} [props.lines] How many lines. Defaults to three.
 * @return {Element} The skeleton.
 */
export function SkeletonText( { lines = 3 } ) {
	return <Region variant="text">{ lineBars( lines ) }</Region>;
}

/**
 * Lines of text at the height a sidebar or settings panel reserves.
 *
 * @param {Object} props         Component props.
 * @param {number} [props.lines] How many lines. Defaults to three.
 * @return {Element} The skeleton.
 */
export function SkeletonPanel( { lines = 3 } ) {
	return (
		<Region variant="text" panel>
			{ lineBars( lines ) }
		</Region>
	);
}

/**
 * Render a table placeholder without table semantics.
 *
 * @param {Object} props           Component props.
 * @param {number} [props.rows]    How many body rows. Defaults to five.
 * @param {number} [props.columns] How many columns. Defaults to four.
 * @return {Element} The skeleton.
 */
export function SkeletonTable( { rows = 5, columns = 4 } ) {
	const cells = ( rowKey ) =>
		times( columns ).map( ( i ) => (
			<div
				key={ `${ rowKey }-${ i }` }
				className="releasecart-skeleton__cell"
			/>
		) );

	return (
		<Region variant="table">
			<div className="releasecart-skeleton__row releasecart-skeleton__row--head">
				{ cells( 'head' ) }
			</div>
			{ times( rows ).map( ( i ) => (
				<div key={ i } className="releasecart-skeleton__row">
					{ cells( i ) }
				</div>
			) ) }
		</Region>
	);
}

/**
 * A stack of labelled fields.
 *
 * @param {Object} props          Component props.
 * @param {number} [props.fields] How many fields. Defaults to four.
 * @return {Element} The skeleton.
 */
export function SkeletonForm( { fields = 4 } ) {
	return (
		<Region variant="form">
			{ times( fields ).map( ( i ) => (
				<div key={ i } className="releasecart-skeleton__field">
					<div className="releasecart-skeleton__label" />
					<div className="releasecart-skeleton__control" />
				</div>
			) ) }
		</Region>
	);
}

/**
 * Render summary-card placeholders.
 *
 * @param {Object}  props           Component props.
 * @param {number}  [props.cards]   How many cards. Defaults to four.
 * @param {boolean} [props.compact] Reserve only the row's own height, not the
 *                                  list reserve. Defaults to false.
 * @return {Element} The skeleton.
 */
export function SkeletonCards( { cards = 4, compact = false } ) {
	return (
		<Region variant="cards" compact={ compact }>
			{ times( cards ).map( ( i ) => (
				<div key={ i } className="releasecart-skeleton__card">
					<div className="releasecart-skeleton__label" />
					<div className="releasecart-skeleton__value" />
				</div>
			) ) }
		</Region>
	);
}

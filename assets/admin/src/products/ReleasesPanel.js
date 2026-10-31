/**
 * Manage a product's releases and packages.
 */

/**
 * External dependencies
 */
import { useState, useRef, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
	Button,
	TextControl,
	TextareaControl,
	Notice,
} from '@wordpress/components';
// Multipart uploads use api-fetch, not the JSON client.
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import { get, post, put, del, getFile, saveBlob } from '../api/client';
import { useReleaseRowExtensions } from '../app/releaseRowExtensions';
import EmptyState from '../components/EmptyState';
import { SkeletonPanel } from '../components/Skeleton';
import StatusBadge from '../components/StatusBadge';
import { firstReadPending } from '../utils/listState';
import { formatBytes } from '../utils/formatBytes';

/**
 * Normalize errors from multipart requests.
 *
 * @param {*} error The raw rejected value.
 * @return {{code: string, message: string, errors: (Object|null)}} The error.
 */
function readApiError( error ) {
	if ( ! error || typeof error !== 'object' ) {
		return {
			code: 'error',
			message: error
				? String( error )
				: __( 'Upload failed.', 'releasecart' ),
			errors: null,
		};
	}
	const data = error.data && typeof error.data === 'object' ? error.data : {};
	return {
		code: typeof error.code === 'string' ? error.code : 'error',
		message:
			typeof error.message === 'string' && error.message !== ''
				? error.message
				: __( 'Something went wrong.', 'releasecart' ),
		errors: data.errors || null,
	};
}

/**
 * How long the "Copied" checksum confirmation stays up, in milliseconds.
 *
 * @type {number}
 */
const COPIED_RESET_MS = 2000;

/**
 * Copy text with the fallback for insecure wp-admin pages.
 *
 * @param {string} value The text to copy.
 * @return {boolean} True if the legacy copy command executed.
 */
function legacyCopy( value ) {
	if ( typeof document === 'undefined' || ! document.execCommand ) {
		return false;
	}
	const textarea = document.createElement( 'textarea' );
	textarea.value = value;
	textarea.setAttribute( 'readonly', '' );
	textarea.style.position = 'absolute';
	textarea.style.left = '-9999px';
	document.body.appendChild( textarea );
	textarea.select();
	let copied = false;
	try {
		copied = document.execCommand( 'copy' );
	} catch {
		copied = false;
	}
	document.body.removeChild( textarea );
	return copied;
}

/**
 * Map a release status to its human-readable label.
 *
 * @param {string} status The raw release status.
 * @return {string} The translated label.
 */
function releaseStatusLabel( status ) {
	switch ( status ) {
		case 'published':
			return __( 'Published', 'releasecart' );
		case 'yanked':
			return __( 'Yanked', 'releasecart' );
		default:
			return __( 'Draft', 'releasecart' );
	}
}

/**
 * The blank state for the release upload/edit form.
 *
 * @return {Object} A fresh release form.
 */
function blankRelease() {
	return {
		version: '',
		changelog: '',
		requires_wp: '',
		tested_up_to: '',
		requires_php: '',
	};
}

/**
 * Append the scalar release fields plus the package file to a FormData body.
 *
 * @param {Object} form The release form values.
 * @param {File}   file The package zip.
 * @return {FormData} The multipart body.
 */
function releaseFormData( form, file ) {
	const body = new FormData();
	// Let the browser set the multipart boundary.
	body.append( 'package', file );
	body.append( 'version', form.version );
	body.append( 'changelog', form.changelog );
	body.append( 'requires_wp', form.requires_wp );
	body.append( 'tested_up_to', form.tested_up_to );
	body.append( 'requires_php', form.requires_php );
	return body;
}

/**
 * Render the release list and editor.
 *
 * Each release row also renders the components extensions registered.
 *
 * @param {Object}          props           Component props.
 * @param {(number|string)} props.productId The product whose releases to manage.
 * @return {Element} The releases panel element.
 */
export default function ReleasesPanel( { productId } ) {
	const queryClient = useQueryClient();
	const extensions = useReleaseRowExtensions();
	const base = `/products/${ productId }/releases`;
	// apiFetch needs the plugin namespace for multipart requests.
	const restBase = `/releasecart/v1${ base }`;

	const releasesQuery = useQuery( {
		queryKey: [ 'releases', productId ],
		queryFn: () => get( base ),
	} );

	const [ upload, setUpload ] = useState( blankRelease );
	const [ uploadFile, setUploadFile ] = useState( null );
	const [ uploadError, setUploadError ] = useState( null );

	const [ editingId, setEditingId ] = useState( null );
	const [ editForm, setEditForm ] = useState( blankRelease );
	const [ editFile, setEditFile ] = useState( null );

	// Per-release publish error state, keyed by release id.
	const [ publishErrors, setPublishErrors ] = useState( {} );

	// Track downloads and errors by release id.
	const [ downloadingId, setDownloadingId ] = useState( null );
	const [ downloadErrors, setDownloadErrors ] = useState( {} );

	// Clear the copied notice and its timer on unmount.
	const [ copiedId, setCopiedId ] = useState( null );
	const copyTimer = useRef( null );
	useEffect(
		() => () => {
			if ( copyTimer.current ) {
				clearTimeout( copyTimer.current );
			}
		},
		[]
	);

	// Track each row because a shared mutation only remembers the latest call.
	const [ busyIds, setBusyIds ] = useState( {
		publish: [],
		yank: [],
		delete: [],
	} );

	/**
	 * Mark one release as having a write of the given kind in flight.
	 *
	 * @param {string} action One of `publish`, `yank` or `delete`.
	 * @param {number} id     The release id the write was issued for.
	 * @return {void}
	 */
	const markBusy = ( action, id ) => {
		setBusyIds( ( prev ) => ( {
			...prev,
			[ action ]: prev[ action ].concat( id ),
		} ) );
	};

	/**
	 * Clear a row guard after its write settles.
	 *
	 * @param {string} action One of `publish`, `yank` or `delete`.
	 * @param {number} id     The release id the write was issued for.
	 * @return {void}
	 */
	const clearBusy = ( action, id ) => {
		setBusyIds( ( prev ) => ( {
			...prev,
			[ action ]: prev[ action ].filter( ( entry ) => entry !== id ),
		} ) );
	};

	/**
	 * Refresh releases before the row guard clears.
	 *
	 * @return {Promise<void>} Resolves once the refetch settles.
	 */
	const invalidate = () =>
		queryClient.invalidateQueries( {
			queryKey: [ 'releases', productId ],
		} );

	const uploadMutation = useMutation( {
		mutationFn: async () => {
			try {
				return await apiFetch( {
					path: restBase,
					method: 'POST',
					body: releaseFormData( upload, uploadFile ),
				} );
			} catch ( error ) {
				throw readApiError( error );
			}
		},
		onSuccess: () => {
			setUpload( blankRelease() );
			setUploadFile( null );
			setUploadError( null );
			return invalidate();
		},
		onError: ( error ) => setUploadError( error ),
	} );

	const editMutation = useMutation( {
		mutationFn: async () => {
			const release = ( releasesQuery.data || [] ).find(
				( item ) => item.id === editingId
			);
			const fully = release && release.status === 'draft';
			// Any release can replace its package. Only live versions are locked.
			if ( editFile ) {
				try {
					// PHP parses multipart fields only on POST. Route it as PUT.
					return await apiFetch( {
						path: `${ restBase }/${ editingId }`,
						method: 'POST',
						headers: { 'X-HTTP-Method-Override': 'PUT' },
						body: releaseFormData( editForm, editFile ),
					} );
				} catch ( error ) {
					throw readApiError( error );
				}
			}
			// Live releases keep their version but can change other metadata.
			const changes = {
				changelog: editForm.changelog,
				requires_wp: editForm.requires_wp,
				tested_up_to: editForm.tested_up_to,
				requires_php: editForm.requires_php,
			};
			if ( fully ) {
				changes.version = editForm.version;
			}
			return put( `${ base }/${ editingId }`, changes );
		},
		onSuccess: () => {
			setEditingId( null );
			setEditFile( null );
			return invalidate();
		},
	} );

	const deleteMutation = useMutation( {
		mutationFn: ( id ) => del( `${ base }/${ id }` ),
		onMutate: ( id ) => {
			markBusy( 'delete', id );
		},
		onSuccess: invalidate,
		onSettled: ( data, error, id ) => {
			clearBusy( 'delete', id );
		},
	} );

	const publishMutation = useMutation( {
		mutationFn: ( { id, override } ) =>
			post( `${ base }/${ id }/publish`, { override: !! override } ),
		onMutate: ( { id } ) => {
			markBusy( 'publish', id );
		},
		onSuccess: ( data, variables ) => {
			setPublishErrors( ( prev ) => ( {
				...prev,
				[ variables.id ]: undefined,
			} ) );
			return invalidate();
		},
		onError: ( error, variables ) =>
			setPublishErrors( ( prev ) => ( {
				...prev,
				[ variables.id ]: error,
			} ) ),
		onSettled: ( data, error, variables ) => {
			clearBusy( 'publish', variables.id );
		},
	} );

	const yankMutation = useMutation( {
		mutationFn: ( id ) => post( `${ base }/${ id }/yank`, {} ),
		onMutate: ( id ) => {
			markBusy( 'yank', id );
		},
		onSuccess: invalidate,
		onSettled: ( data, error, id ) => {
			clearBusy( 'yank', id );
		},
	} );

	/**
	 * Open the inline editor for a release.
	 *
	 * @param {Object} release The release to edit.
	 * @return {void}
	 */
	const startEdit = ( release ) => {
		setEditingId( release.id );
		setEditFile( null );
		setEditForm( {
			version: release.version || '',
			changelog: release.changelog || '',
			requires_wp: release.requires_wp || '',
			tested_up_to: release.tested_up_to || '',
			requires_php: release.requires_php || '',
		} );
	};

	/**
	 * Download a package under the server-provided name.
	 *
	 * @param {Object} release The release whose package to download.
	 * @return {Promise<void>}
	 */
	const downloadPackage = async ( release ) => {
		setDownloadingId( release.id );
		setDownloadErrors( ( prev ) => ( { ...prev, [ release.id ]: null } ) );
		try {
			const { blob } = await getFile(
				`${ base }/${ release.id }/package`
			);
			saveBlob( blob, release.package_filename );
		} catch ( error ) {
			setDownloadErrors( ( prev ) => ( {
				...prev,
				[ release.id ]:
					error && error.message
						? error.message
						: __(
								'The package could not be downloaded.',
								'releasecart'
						  ),
			} ) );
		} finally {
			setDownloadingId( null );
		}
	};

	/**
	 * Copy the full checksum and show a short confirmation.
	 *
	 * @param {Object} release The release whose checksum to copy.
	 * @return {Promise<void>}
	 */
	const copyChecksum = async ( release ) => {
		const value = release.checksum_sha256 || '';
		if ( value === '' ) {
			return;
		}
		try {
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				await navigator.clipboard.writeText( value );
			} else if ( ! legacyCopy( value ) ) {
				return;
			}
		} catch {
			// Try the legacy path when the async API refuses.
			if ( ! legacyCopy( value ) ) {
				return;
			}
		}
		if ( copyTimer.current ) {
			clearTimeout( copyTimer.current );
		}
		setCopiedId( release.id );
		copyTimer.current = setTimeout(
			() => setCopiedId( null ),
			COPIED_RESET_MS
		);
	};

	/**
	 * Delete a release after confirming.
	 *
	 * @param {number} id The release id.
	 * @return {void}
	 */
	const confirmDelete = ( id ) => {
		// eslint-disable-next-line no-alert
		const confirmed = window.confirm(
			__( 'Delete this release? This cannot be undone.', 'releasecart' )
		);
		if ( confirmed ) {
			deleteMutation.mutate( id );
		}
	};

	/**
	 * Save the edit after confirming a live package replacement.
	 *
	 * @return {void}
	 */
	const saveEdit = () => {
		const release = ( releasesQuery.data || [] ).find(
			( item ) => item.id === editingId
		);
		if ( release && release.status !== 'draft' && editFile ) {
			// eslint-disable-next-line no-alert
			const confirmed = window.confirm(
				__(
					'Replace the package for this release? Sites that have already updated keep the current build; new update checks serve the replacement under the same version.',
					'releasecart'
				)
			);
			if ( ! confirmed ) {
				return;
			}
		}
		editMutation.mutate();
	};

	const releases = Array.isArray( releasesQuery.data )
		? releasesQuery.data
		: [];

	return (
		<div className="releasecart-panel releasecart-releases">
			<div
				role="status"
				aria-live="polite"
				className="screen-reader-text"
			>
				{ null !== copiedId
					? __( 'SHA-256 checksum copied.', 'releasecart' )
					: '' }
			</div>
			<h2>{ __( 'Upload a new release', 'releasecart' ) }</h2>
			{ uploadError && (
				<Notice status="error" isDismissible={ false }>
					{ uploadError.message }
				</Notice>
			) }
			<TextControl
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				label={ __( 'Version', 'releasecart' ) }
				value={ upload.version }
				onChange={ ( value ) =>
					setUpload( ( prev ) => ( { ...prev, version: value } ) )
				}
			/>
			<div className="releasecart-file-field">
				<span>{ __( 'Package (.zip)', 'releasecart' ) }</span>
				<input
					type="file"
					accept=".zip"
					aria-label={ __( 'Package (.zip)', 'releasecart' ) }
					onChange={ ( event ) =>
						setUploadFile(
							event.target.files && event.target.files[ 0 ]
								? event.target.files[ 0 ]
								: null
						)
					}
				/>
			</div>
			<TextareaControl
				__nextHasNoMarginBottom
				label={ __( 'Changelog', 'releasecart' ) }
				value={ upload.changelog }
				onChange={ ( value ) =>
					setUpload( ( prev ) => ( { ...prev, changelog: value } ) )
				}
			/>
			<TextControl
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				label={ __( 'Requires WordPress', 'releasecart' ) }
				value={ upload.requires_wp }
				onChange={ ( value ) =>
					setUpload( ( prev ) => ( { ...prev, requires_wp: value } ) )
				}
			/>
			<TextControl
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				label={ __( 'Tested up to', 'releasecart' ) }
				value={ upload.tested_up_to }
				onChange={ ( value ) =>
					setUpload( ( prev ) => ( {
						...prev,
						tested_up_to: value,
					} ) )
				}
			/>
			<TextControl
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				label={ __( 'Requires PHP', 'releasecart' ) }
				value={ upload.requires_php }
				onChange={ ( value ) =>
					setUpload( ( prev ) => ( {
						...prev,
						requires_php: value,
					} ) )
				}
			/>
			<Button
				variant="primary"
				disabled={
					! uploadFile || ! upload.version || uploadMutation.isPending
				}
				onClick={ () => uploadMutation.mutate() }
			>
				{ __( 'Upload release', 'releasecart' ) }
			</Button>

			<h2>{ __( 'Releases', 'releasecart' ) }</h2>
			{ firstReadPending( releasesQuery ) && <SkeletonPanel /> }
			{ releasesQuery.isError && (
				<Notice status="error" isDismissible={ false }>
					{ releasesQuery.error.message }
				</Notice>
			) }
			{ /* Show empty state only after a successful read. */ }
			{ ! firstReadPending( releasesQuery ) &&
				! releasesQuery.isError &&
				releases.length === 0 && (
					<EmptyState
						className="releasecart-empty-state--panel"
						message={ __(
							'No releases yet. Upload one above to start serving updates.',
							'releasecart'
						) }
					/>
				) }

			<ul className="releasecart-release-list">
				{ releases.map( ( release ) => {
					const isEditing = editingId === release.id;
					const fully = release.status === 'draft';
					const publishError = publishErrors[ release.id ];
					const publishing = busyIds.publish.includes( release.id );
					const yanking = busyIds.yank.includes( release.id );
					const deleting = busyIds.delete.includes( release.id );
					// Each write action below stays busy and disabled from the
					// click until the refreshed list arrives, which on a slow
					// host is seconds rather than milliseconds. A merchant who
					// clicks the same action again inside that window would
					// otherwise send a second request, and publish and yank are
					// a compare-and-swap on release status, so the second one
					// loses: the controller maps the failed transition to a 409
					// `releasecart_conflict`, which the panel renders as "Reload
					// and try again" over a write that in fact succeeded.
					//
					// Switching tabs and back inside that window clears the
					// guard: `busyIds` is this panel's state and the TabPanel
					// unmounts the panel, while the request carries on in the
					// mutation cache. Any in-flight guard held in component
					// state has that hole.
					//
					// `__experimentalIsFocusable` is what keeps that from
					// costing keyboard users their place: it swaps the native
					// `disabled` attribute for `aria-disabled` and overrides
					// the click handler, so a button that disables itself as a
					// result of being activated stays focusable instead of
					// dropping focus to the document body and sending the next
					// Tab back to the top of the page.
					//
					// WordPress maps it to the stable `accessibleWhenDisabled`
					// through `useDeprecatedProps`. The admin screens still use
					// the experimental name everywhere; switch them together.
					// The jest Button stub models neither prop, so nothing in
					// the unit suite would notice if this were dropped; it was
					// checked in the browser instead.
					return (
						<li
							key={ release.id }
							className="releasecart-release-row"
						>
							<div className="releasecart-release-head">
								<strong>{ release.version }</strong>
								<StatusBadge
									status={ release.status }
									label={ releaseStatusLabel(
										release.status
									) }
								/>
								{ extensions.map(
									( { id, component: Extension } ) => (
										<Extension
											key={ id }
											release={ release }
											productId={ productId }
										/>
									)
								) }
							</div>

							<div className="releasecart-release-package">
								<span className="releasecart-release-package__name">
									{ release.package_filename }
								</span>
								<span className="releasecart-release-package__size">
									{ formatBytes( release.filesize ) }
								</span>
								<span className="releasecart-release-package__checksum-label">
									{ __( 'SHA-256', 'releasecart' ) }
								</span>
								<button
									type="button"
									className={
										'releasecart-release-package__checksum' +
										( copiedId === release.id
											? ' is-copied'
											: '' )
									}
									title={ release.checksum_sha256 || '' }
									aria-label={ __(
										'Copy the SHA-256 checksum',
										'releasecart'
									) }
									onClick={ () => copyChecksum( release ) }
								>
									{ release.checksum_sha256 }
								</button>
								{ copiedId === release.id && (
									<span
										className="releasecart-release-package__copied"
										aria-hidden="true"
									>
										{ __( 'Copied', 'releasecart' ) }
									</span>
								) }
								<Button
									variant="secondary"
									isBusy={ downloadingId === release.id }
									disabled={ downloadingId === release.id }
									__experimentalIsFocusable
									onClick={ () => downloadPackage( release ) }
								>
									{ __( 'Download', 'releasecart' ) }
								</Button>
							</div>

							{ downloadErrors[ release.id ] && (
								<Notice status="error" isDismissible={ false }>
									{ downloadErrors[ release.id ] }
								</Notice>
							) }

							{ ! isEditing && (
								<div className="releasecart-release-actions">
									<Button
										variant="secondary"
										onClick={ () => startEdit( release ) }
									>
										{ __( 'Edit', 'releasecart' ) }
									</Button>
									{ release.status === 'draft' && (
										<Button
											variant="primary"
											isBusy={ publishing }
											disabled={ publishing }
											__experimentalIsFocusable
											onClick={ () =>
												publishMutation.mutate( {
													id: release.id,
													override: false,
												} )
											}
										>
											{ __( 'Publish', 'releasecart' ) }
										</Button>
									) }
									{ release.status === 'published' && (
										<Button
											variant="secondary"
											isBusy={ yanking }
											disabled={ yanking }
											__experimentalIsFocusable
											onClick={ () =>
												yankMutation.mutate(
													release.id
												)
											}
										>
											{ __( 'Yank', 'releasecart' ) }
										</Button>
									) }
									{ ( release.status === 'draft' ||
										release.status === 'yanked' ) && (
										<Button
											variant="secondary"
											isDestructive
											isBusy={ deleting }
											disabled={ deleting }
											__experimentalIsFocusable
											onClick={ () =>
												confirmDelete( release.id )
											}
										>
											{ __( 'Delete', 'releasecart' ) }
										</Button>
									) }
								</div>
							) }

							{ publishError && (
								<Notice status="error" isDismissible={ false }>
									<p>{ publishError.message }</p>
									{ publishError.code ===
									'releasecart_conflict' ? (
										<p>
											{ __(
												'Please reload the page and try again.',
												'releasecart'
											) }
										</p>
									) : (
										<Button
											variant="primary"
											isBusy={ publishing }
											disabled={ publishing }
											__experimentalIsFocusable
											onClick={ () =>
												publishMutation.mutate( {
													id: release.id,
													override: true,
												} )
											}
										>
											{ __(
												'Publish anyway',
												'releasecart'
											) }
										</Button>
									) }
								</Notice>
							) }

							{ isEditing && (
								<div className="releasecart-release-edit">
									<TextControl
										__nextHasNoMarginBottom
										__next40pxDefaultSize
										label={ __( 'Version', 'releasecart' ) }
										value={ editForm.version }
										disabled={ ! fully }
										onChange={ ( value ) =>
											setEditForm( ( prev ) => ( {
												...prev,
												version: value,
											} ) )
										}
									/>
									<div className="releasecart-file-field">
										<span>
											{ __(
												'Replace package (.zip)',
												'releasecart'
											) }
										</span>
										<input
											type="file"
											accept=".zip"
											aria-label={ __(
												'Replace package (.zip)',
												'releasecart'
											) }
											onChange={ ( event ) =>
												setEditFile(
													event.target.files &&
														event.target.files[ 0 ]
														? event.target
																.files[ 0 ]
														: null
												)
											}
										/>
									</div>
									<TextareaControl
										__nextHasNoMarginBottom
										label={ __(
											'Changelog',
											'releasecart'
										) }
										value={ editForm.changelog }
										onChange={ ( value ) =>
											setEditForm( ( prev ) => ( {
												...prev,
												changelog: value,
											} ) )
										}
									/>
									<TextControl
										__nextHasNoMarginBottom
										__next40pxDefaultSize
										label={ __(
											'Requires WordPress',
											'releasecart'
										) }
										value={ editForm.requires_wp }
										onChange={ ( value ) =>
											setEditForm( ( prev ) => ( {
												...prev,
												requires_wp: value,
											} ) )
										}
									/>
									<TextControl
										__nextHasNoMarginBottom
										__next40pxDefaultSize
										label={ __(
											'Tested up to',
											'releasecart'
										) }
										value={ editForm.tested_up_to }
										onChange={ ( value ) =>
											setEditForm( ( prev ) => ( {
												...prev,
												tested_up_to: value,
											} ) )
										}
									/>
									<TextControl
										__nextHasNoMarginBottom
										__next40pxDefaultSize
										label={ __(
											'Requires PHP',
											'releasecart'
										) }
										value={ editForm.requires_php }
										onChange={ ( value ) =>
											setEditForm( ( prev ) => ( {
												...prev,
												requires_php: value,
											} ) )
										}
									/>
									{ ! fully && (
										<p>
											{ __(
												'Only the version is locked once a release is published or yanked.',
												'releasecart'
											) }
										</p>
									) }
									<Button
										variant="primary"
										isBusy={ editMutation.isPending }
										disabled={ editMutation.isPending }
										__experimentalIsFocusable
										onClick={ saveEdit }
									>
										{ __( 'Save changes', 'releasecart' ) }
									</Button>
									<Button
										variant="secondary"
										onClick={ () => setEditingId( null ) }
									>
										{ __( 'Cancel', 'releasecart' ) }
									</Button>
								</div>
							) }
						</li>
					);
				} ) }
			</ul>
		</div>
	);
}

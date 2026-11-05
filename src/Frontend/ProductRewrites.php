<?php
/**
 * Registers and flushes the product details pretty rewrite.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Frontend;

use DrPlugin\WP\ReleaseCart\Core\Hookable;

defined( 'ABSPATH' ) || exit;

/**
 * Registers product detail URLs and flushes rewrites only when needed.
 *
 * A product slug directly below the details page always resolves as a product.
 *
 * @since 1.0.0
 */
class ProductRewrites implements Hookable {

	/**
	 * The option holding the last-flushed page signature.
	 *
	 * @var string
	 */
	public const SIGNATURE_OPTION = 'releasecart_product_rewrite_signature';

	/**
	 * The deferred one-time rewrite flush flag.
	 *
	 * @var string
	 */
	public const FLUSH_FLAG = 'releasecart_pending_rewrite_flush';

	/**
	 * The signature used when there is no published details page.
	 *
	 * @var string
	 */
	private const NO_PAGE_SIGNATURE = 'none';

	/**
	 * The resolver for the configured details page.
	 *
	 * @var PageResolver
	 */
	private PageResolver $pages;

	/**
	 * Wire the page resolver.
	 *
	 * @param PageResolver $pages The storefront page resolver.
	 */
	public function __construct( PageResolver $pages ) {
		$this->pages = $pages;
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_filter( 'query_vars', array( $this, 'registerQueryVar' ) );
		// After PageProvisioner (init priority 20) so a freshly provisioned
		// details page is already wired when the rule is built.
		add_action( 'init', array( $this, 'registerRule' ), 30 );
		add_action( 'wp_loaded', array( $this, 'maybeFlush' ) );
		add_action( 'save_post_page', array( $this, 'onPageSaved' ) );
	}

	/**
	 * Register the `releasecart_product` query var so WordPress carries the slug
	 * through to the request context.
	 *
	 * @param array<int, string> $vars The registered public query vars.
	 * @return array<int, string>
	 */
	public function registerQueryVar( array $vars ): array {
		$vars[] = ProductUrlProvider::QUERY_VAR;

		return $vars;
	}

	/**
	 * Register the pretty rewrite rule when a published details page exists.
	 *
	 * @return void
	 */
	public function registerRule(): void {
		$id = $this->pages->pageId( PageResolver::PRODUCT );

		if ( ! $this->isPublishedPage( $id ) ) {
			return;
		}

		$uri = (string) get_page_uri( $id );

		if ( '' === $uri ) {
			return;
		}

		add_rewrite_rule(
			'^' . preg_quote( $uri, '#' ) . '/([^/]+)/?$',
			'index.php?page_id=' . $id . '&' . ProductUrlProvider::QUERY_VAR . '=$matches[1]',
			'top'
		);
	}

	/**
	 * Flush rewrites for a deferred flag or changed page signature.
	 *
	 * @return void
	 */
	public function maybeFlush(): void {
		$signature = $this->currentSignature();

		if ( get_option( self::FLUSH_FLAG ) ) {
			delete_option( self::FLUSH_FLAG );
			$this->flush( $signature );

			return;
		}

		if ( get_option( self::SIGNATURE_OPTION ) !== $signature ) {
			$this->flush( $signature );
		}
	}

	/**
	 * Queue a rewrite flush when the details page changes.
	 *
	 * @param int $postId The saved post id.
	 * @return void
	 */
	public function onPageSaved( $postId ): void {
		$postId = (int) $postId;

		if ( wp_is_post_revision( $postId ) ) {
			return;
		}

		if ( $this->pages->isPage( PageResolver::PRODUCT, $postId ) ) {
			update_option( self::FLUSH_FLAG, true );
		}
	}

	/**
	 * Clear the stored signature and remove the product rule from the stored rules.
	 *
	 * The rule is still registered in the deactivation request, so a plain flush
	 * would store it again. The filter keeps it out of this flush and any later one
	 * in this request.
	 *
	 * @since 1.0.0
	 * @since 1.0.1 Removes the product rule.
	 *
	 * @return void
	 */
	public static function clearOnDeactivate(): void {
		delete_option( self::SIGNATURE_OPTION );

		add_filter( 'rewrite_rules_array', array( self::class, 'withoutProductRule' ) );
		flush_rewrite_rules( false );
	}

	/**
	 * Drop the product rule from a rewrite rule set.
	 *
	 * @since 1.0.1
	 *
	 * @param mixed $rules Rewrite rules, regex to query. Another filter may have broken the value.
	 * @return mixed The rules without the product rule, or the value as given when it is not an array.
	 */
	public static function withoutProductRule( $rules ) {
		if ( ! is_array( $rules ) ) {
			return $rules;
		}

		$marker = '&' . ProductUrlProvider::QUERY_VAR . '=';

		return array_filter(
			$rules,
			static fn ( $query ): bool => ! str_contains( (string) $query, $marker )
		);
	}

	/**
	 * Get the current details page signature.
	 *
	 * @return string
	 */
	private function currentSignature(): string {
		$id = $this->pages->pageId( PageResolver::PRODUCT );

		if ( ! $this->isPublishedPage( $id ) ) {
			return self::NO_PAGE_SIGNATURE;
		}

		return md5( (string) get_page_uri( $id ) . '|' . $id );
	}

	/**
	 * Whether a page id resolves to a published page.
	 *
	 * @param int $id The page id.
	 * @return bool
	 */
	private function isPublishedPage( int $id ): bool {
		return $id > 0 && 'publish' === get_post_status( $id );
	}

	/**
	 * Soft-flush rewrites and save the current signature.
	 *
	 * @param string $signature The signature just flushed for.
	 * @return void
	 */
	private function flush( string $signature ): void {
		flush_rewrite_rules( false );
		update_option( self::SIGNATURE_OPTION, $signature );
	}
}

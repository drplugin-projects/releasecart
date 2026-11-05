<?php
/**
 * GDPR personal-data exporter for ReleaseCart customers.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Privacy;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

use DrPlugin\WP\ReleaseCart\Customers\Customer;
use DrPlugin\WP\ReleaseCart\Customers\CustomerRepository;
use DrPlugin\WP\ReleaseCart\Database\LogRepository;
use DrPlugin\WP\ReleaseCart\Licensing\Activation;
use DrPlugin\WP\ReleaseCart\Licensing\ActivationRepository;
use DrPlugin\WP\ReleaseCart\Licensing\License;
use DrPlugin\WP\ReleaseCart\Licensing\LicenseRepository;
use DrPlugin\WP\ReleaseCart\Orders\Order;
use DrPlugin\WP\ReleaseCart\Orders\OrderRepository;
use DrPlugin\WP\ReleaseCart\Payments\Stripe\StripeCustomers;
use DrPlugin\WP\ReleaseCart\REST\CheckoutController;

/**
 * Export ReleaseCart personal data for the WordPress exporter.
 *
 * Orders and downloads are paged. Profile, licenses and activations are on page one.
 *
 * @since 1.0.0
 */
class PersonalDataExporter {

	/**
	 * The core metadata type the order-meta table is mapped under.
	 *
	 * @var string
	 */
	private const ORDER_META_TYPE = 'releasecart_order';

	/**
	 * The export group id for the customer profile.
	 *
	 * @var string
	 */
	private const GROUP_CUSTOMER = 'releasecart-customer';

	/**
	 * The export group id for orders.
	 *
	 * @var string
	 */
	private const GROUP_ORDERS = 'releasecart-orders';

	/**
	 * The export group id for licenses.
	 *
	 * @var string
	 */
	private const GROUP_LICENSES = 'releasecart-licenses';

	/**
	 * The export group id for activations.
	 *
	 * @var string
	 */
	private const GROUP_ACTIVATIONS = 'releasecart-activations';

	/**
	 * The export group id for download log rows.
	 *
	 * @var string
	 */
	private const GROUP_DOWNLOADS = 'releasecart-downloads';

	/**
	 * How many orders and download rows to export per page.
	 *
	 * @var int
	 */
	private const PER_PAGE = 100;

	/**
	 * Wire the exporter with the reads it needs.
	 *
	 * @since 1.0.0
	 *
	 * @param CustomerRepository   $customers   Customer profile reads.
	 * @param OrderRepository      $orders      Order reads and the customer's order-id set.
	 * @param LicenseRepository    $licenses    License reads.
	 * @param ActivationRepository $activations Activation reads.
	 * @param LogRepository        $logs        Download log reads.
	 */
	public function __construct(
		private readonly CustomerRepository $customers,
		private readonly OrderRepository $orders,
		private readonly LicenseRepository $licenses,
		private readonly ActivationRepository $activations,
		private readonly LogRepository $logs
	) {
	}

	/**
	 * The friendly name WordPress shows for this exporter in the export report.
	 *
	 * @since 1.0.0
	 *
	 * @return string The translated exporter name.
	 */
	public function friendlyName(): string {
		return __( 'ReleaseCart store data', 'releasecart' );
	}

	/**
	 * Export one page of a customer's ReleaseCart data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $email The email address the request is for.
	 * @param int    $page  The 1-based page number WordPress requests.
	 * @return array{data: array<int, array<string, mixed>>, done: bool} The core exporter response.
	 */
	public function export( string $email, int $page = 1 ): array {
		$page     = max( 1, $page );
		$customer = $this->customers->findByEmail( $email );

		if ( null === $customer || null === $customer->id() ) {
			return array(
				'data' => array(),
				'done' => true,
			);
		}

		$customerId = (int) $customer->id();
		$data       = array();

		// The profile and the bounded license/activation sets go out on page one.
		if ( 1 === $page ) {
			$data[] = $this->customerGroup( $customer );

			foreach ( $this->licenses->findByCustomerId( $customerId ) as $license ) {
				$data[] = $this->licenseGroup( $license );

				foreach ( $this->activations->listForLicense( (int) $license->id() ) as $activation ) {
					$data[] = $this->activationGroup( (int) $license->id(), $activation );
				}
			}
		}

		// Orders and download rows are the growing streams, so they are paged.
		$orders = $this->orders->query(
			array(
				'customer_id' => $customerId,
				'page'        => $page,
				'per_page'    => self::PER_PAGE,
				'orderby'     => 'id',
				'order'       => 'ASC',
			)
		);

		foreach ( $orders as $order ) {
			$data[] = $this->orderGroup( $order );
		}

		$downloads = $this->logs->findDownloadsForOrders( $this->orders->idsForCustomer( $customerId ), $page, self::PER_PAGE );

		foreach ( $downloads as $row ) {
			$data[] = $this->downloadGroup( $row );
		}

		// Package/update downloads key off the customer's licenses, not orders.
		$licenseIds = array_map(
			static fn ( $license ): int => (int) $license->id(),
			$this->licenses->findByCustomerId( $customerId )
		);

		$packageDownloads = $this->logs->findDownloadsForLicenses( $licenseIds, $page, self::PER_PAGE );

		foreach ( $packageDownloads as $row ) {
			$data[] = $this->downloadGroup( $row );
		}

		// The request is complete once no paged stream filled a page.
		$done = count( $orders ) < self::PER_PAGE
			&& count( $downloads ) < self::PER_PAGE
			&& count( $packageDownloads ) < self::PER_PAGE;

		return array(
			'data' => $data,
			'done' => $done,
		);
	}

	/**
	 * Build the customer-profile export group.
	 *
	 * @since 1.0.0
	 *
	 * @param Customer $customer The customer.
	 * @return array<string, mixed> The export group.
	 */
	private function customerGroup( Customer $customer ): array {
		$items = array(
			array(
				'name'  => __( 'Email', 'releasecart' ),
				'value' => $customer->email(),
			),
		);

		if ( null !== $customer->name() ) {
			$items[] = array(
				'name'  => __( 'Name', 'releasecart' ),
				'value' => $customer->name(),
			);
		}

		if ( null !== $customer->firstName() ) {
			$items[] = array(
				'name'  => __( 'First name', 'releasecart' ),
				'value' => $customer->firstName(),
			);
		}

		if ( null !== $customer->lastName() ) {
			$items[] = array(
				'name'  => __( 'Last name', 'releasecart' ),
				'value' => $customer->lastName(),
			);
		}

		return $this->group(
			self::GROUP_CUSTOMER,
			__( 'ReleaseCart customer profile', 'releasecart' ),
			'releasecart-customer-' . $customer->id(),
			$items
		);
	}

	/**
	 * Build an order export group.
	 *
	 * @since 1.0.0
	 * @since 1.0.1 Adds the billing country.
	 *
	 * @param Order $order The order.
	 * @return array<string, mixed> The export group.
	 */
	private function orderGroup( Order $order ): array {
		$items = array(
			array(
				'name'  => __( 'Order', 'releasecart' ),
				'value' => $order->uuid(),
			),
			array(
				'name'  => __( 'Email', 'releasecart' ),
				'value' => $order->email(),
			),
			array(
				'name'  => __( 'Status', 'releasecart' ),
				'value' => $order->status()->value,
			),
			array(
				'name'  => __( 'Total', 'releasecart' ),
				/* translators: 1: order total in minor units, 2: uppercased currency code. */
				'value' => sprintf( __( '%1$d (%2$s, in minor units)', 'releasecart' ), $order->total(), strtoupper( $order->currency() ) ),
			),
			array(
				'name'  => __( 'Date', 'releasecart' ),
				'value' => (string) $order->createdAt(),
			),
		);

		if ( null !== $order->ipAddress() ) {
			$items[] = array(
				'name'  => __( 'IP address', 'releasecart' ),
				'value' => $order->ipAddress(),
			);
		}

		if ( null !== $order->country() && '' !== $order->country() ) {
			$items[] = array(
				'name'  => __( 'Billing country', 'releasecart' ),
				'value' => $order->country(),
			);
		}

		$items = array_merge( $items, $this->orderMetaItems( (int) $order->id() ) );

		return $this->group(
			self::GROUP_ORDERS,
			__( 'ReleaseCart orders', 'releasecart' ),
			'releasecart-order-' . $order->id(),
			$items
		);
	}

	/**
	 * Read the buyer PII order-meta the checkout collected, as labelled rows.
	 *
	 * The billing address is stored as an array, so it is flattened into one row
	 * per filled part. The consent time is retained through erasure but still
	 * belongs in a portability export.
	 *
	 * @since 1.0.0
	 *
	 * @param int $orderId The order id.
	 * @return array<int, array<string, string>> The name/value rows, possibly empty.
	 */
	private function orderMetaItems( int $orderId ): array {
		$items   = array();
		$address = get_metadata( self::ORDER_META_TYPE, $orderId, CheckoutController::ADDRESS_META, true );

		if ( is_array( $address ) ) {
			$labels = array(
				'address_1' => __( 'Billing address', 'releasecart' ),
				'address_2' => __( 'Billing address (line 2)', 'releasecart' ),
				'city'      => __( 'Billing city', 'releasecart' ),
				'state'     => __( 'Billing state or region', 'releasecart' ),
				'postcode'  => __( 'Billing postcode', 'releasecart' ),
			);

			foreach ( $labels as $key => $label ) {
				$value = isset( $address[ $key ] ) ? trim( (string) $address[ $key ] ) : '';

				if ( '' !== $value ) {
					$items[] = array(
						'name'  => $label,
						'value' => $value,
					);
				}
			}
		}

		$scalarLabels = array(
			CheckoutController::FIRST_NAME_META => __( 'First name', 'releasecart' ),
			CheckoutController::LAST_NAME_META  => __( 'Last name', 'releasecart' ),
			CheckoutController::COMPANY_META    => __( 'Company', 'releasecart' ),
			CheckoutController::TAX_ID_META     => __( 'Tax ID', 'releasecart' ),
			CheckoutController::TERMS_META      => __( 'Terms accepted', 'releasecart' ),
			StripeCustomers::META_KEYS['live']  => __( 'Stripe customer ID', 'releasecart' ),
			StripeCustomers::META_KEYS['test']  => __( 'Stripe customer ID (test mode)', 'releasecart' ),
		);

		foreach ( $scalarLabels as $metaKey => $label ) {
			$value = trim( (string) get_metadata( self::ORDER_META_TYPE, $orderId, $metaKey, true ) );

			if ( '' !== $value ) {
				$items[] = array(
					'name'  => $label,
					'value' => $value,
				);
			}
		}

		return $items;
	}

	/**
	 * Build a license export group.
	 *
	 * @since 1.0.0
	 *
	 * @param License $license The license.
	 * @return array<string, mixed> The export group.
	 */
	private function licenseGroup( License $license ): array {
		$items = array(
			array(
				'name'  => __( 'License key', 'releasecart' ),
				'value' => $license->licenseKey(),
			),
			array(
				'name'  => __( 'Status', 'releasecart' ),
				'value' => $license->status()->value,
			),
			array(
				'name'  => __( 'Expires', 'releasecart' ),
				'value' => null === $license->expiresAt() ? __( 'Never', 'releasecart' ) : $license->expiresAt()->format( 'Y-m-d H:i:s' ),
			),
		);

		return $this->group(
			self::GROUP_LICENSES,
			__( 'ReleaseCart licenses', 'releasecart' ),
			'releasecart-license-' . $license->id(),
			$items
		);
	}

	/**
	 * Build an activation export group.
	 *
	 * @since 1.0.0
	 * @since 1.0.1 Adds the reported environment and the device fingerprint.
	 *
	 * @param int        $licenseId  The owning license id.
	 * @param Activation $activation The activation.
	 * @return array<string, mixed> The export group.
	 */
	private function activationGroup( int $licenseId, Activation $activation ): array {
		$items = array(
			array(
				'name'  => __( 'Site URL', 'releasecart' ),
				'value' => $activation->siteUrl(),
			),
			array(
				'name'  => __( 'Status', 'releasecart' ),
				'value' => $activation->status()->value,
			),
			array(
				'name'  => __( 'Activated', 'releasecart' ),
				'value' => null === $activation->activatedAt() ? '' : $activation->activatedAt()->format( 'Y-m-d H:i:s' ),
			),
		);

		$environment = $this->environmentLine( $activation->environment() );

		if ( '' !== $environment ) {
			$items[] = array(
				'name'  => __( 'Reported environment', 'releasecart' ),
				'value' => $environment,
			);
		}

		if ( null !== $activation->fingerprint() && '' !== $activation->fingerprint() ) {
			$items[] = array(
				'name'  => __( 'Device ID', 'releasecart' ),
				'value' => $activation->fingerprint(),
			);
		}

		return $this->group(
			self::GROUP_ACTIVATIONS,
			__( 'ReleaseCart license activations', 'releasecart' ),
			'releasecart-activation-' . $licenseId . '-' . $activation->id(),
			$items
		);
	}

	/**
	 * Write the reported environment as one `key: value` list, as the license screen does.
	 *
	 * @since 1.0.1
	 *
	 * @param array<string, mixed>|null $environment The stored report, or null.
	 * @return string The line, or '' when nothing was reported.
	 */
	private function environmentLine( ?array $environment ): string {
		$parts = array();

		foreach ( (array) $environment as $key => $value ) {
			if ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
				$parts[] = $key . ': ' . $value;
			}
		}

		return implode( wp_get_list_item_separator(), $parts );
	}

	/**
	 * Build a download-log export group.
	 *
	 * @since 1.0.0
	 *
	 * @param object $row A `releasecart_logs` download row.
	 * @return array<string, mixed> The export group.
	 */
	private function downloadGroup( object $row ): array {
		$items = array(
			array(
				'name'  => __( 'Event', 'releasecart' ),
				'value' => (string) $row->message,
			),
			array(
				'name'  => __( 'Date', 'releasecart' ),
				'value' => (string) $row->created_at,
			),
		);

		if ( null !== $row->ip_address && '' !== $row->ip_address ) {
			$items[] = array(
				'name'  => __( 'IP address', 'releasecart' ),
				'value' => (string) $row->ip_address,
			);
		}

		return $this->group(
			self::GROUP_DOWNLOADS,
			__( 'ReleaseCart downloads', 'releasecart' ),
			'releasecart-download-' . (int) $row->id,
			$items
		);
	}

	/**
	 * Assemble one export group in core's response shape.
	 *
	 * @since 1.0.0
	 *
	 * @param string                            $groupId The stable group id.
	 * @param string                            $label   The translated group label.
	 * @param string                            $itemId  The unique item id within the group.
	 * @param array<int, array<string, string>> $items   The name/value data pairs.
	 * @return array<string, mixed> The export group.
	 */
	private function group( string $groupId, string $label, string $itemId, array $items ): array {
		return array(
			'group_id'    => $groupId,
			'group_label' => $label,
			'item_id'     => $itemId,
			'data'        => $items,
		);
	}
}

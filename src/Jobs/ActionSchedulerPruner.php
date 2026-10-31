<?php
/**
 * Daily job that keeps the bundled Action Scheduler tables from growing without bound.
 *
 * @package DrPlugin\WP\ReleaseCart
 */

declare(strict_types=1);

namespace DrPlugin\WP\ReleaseCart\Jobs;

// Prevent direct file access.
defined( 'ABSPATH' ) || exit;

use ActionScheduler;
use ActionScheduler_Store;
use DrPlugin\WP\ReleaseCart\Core\Hookable;
use DrPlugin\WP\ReleaseCart\Database\Logger;
use Exception;
use Throwable;

/**
 * Daily job that prunes ReleaseCart's own rows from the Action Scheduler queue.
 *
 * Other plugins share the queue, so only the `releasecart` group is touched.
 *
 * @since 1.0.0
 */
final class ActionSchedulerPruner implements Hookable {

	/**
	 * The daily prune job hook.
	 *
	 * @var string
	 */
	public const PRUNE_HOOK = 'releasecart_job_prune_action_scheduler';

	/**
	 * How long finished actions are kept, in seconds (seven days).
	 *
	 * @var int
	 */
	private const RETENTION = 7 * 86400;

	/**
	 * Action rows deleted per status in one batch.
	 *
	 * @var int
	 */
	private const BATCH_SIZE = 100;

	/**
	 * Maximum delete batches per run.
	 *
	 * @var int
	 */
	private const MAX_BATCHES = 20;

	/**
	 * Wire the pruner with the audit logger.
	 *
	 * @since 1.0.0
	 *
	 * @param Logger                     $logger The `_logs` audit writer (failures are logged, never thrown).
	 * @param ActionScheduler_Store|null $store  The queue store, or null for the active one.
	 */
	public function __construct( private readonly Logger $logger, private readonly ?ActionScheduler_Store $store = null ) {
	}

	/**
	 * Register the job handler and the init-time self-scheduler.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( self::PRUNE_HOOK, array( $this, 'onPruneJob' ) );
		add_action( 'init', array( $this, 'scheduleJobs' ) );
	}

	/**
	 * Schedule the daily prune once through Action Scheduler.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function scheduleJobs(): void {
		if ( false === as_next_scheduled_action( self::PRUNE_HOOK, array(), 'releasecart' ) ) {
			// The trailing `true` asks Action Scheduler to enforce uniqueness at
			// claim time, so two concurrent first requests cannot both schedule
			// the recurring prune.
			as_schedule_recurring_action( time(), 86400, self::PRUNE_HOOK, array(), 'releasecart', true );
		}
	}

	/**
	 * Prune ReleaseCart's completed and canceled actions older than the retention.
	 *
	 * Stop at the batch cap. Log failures instead of stopping cron.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function onPruneJob(): void {
		$failed = 0;

		try {
			$store  = $this->store ?? ActionScheduler::store();
			$cutoff = as_get_datetime_object( '@' . ( time() - self::RETENTION ) );
			$batch  = 0;

			foreach ( array( ActionScheduler_Store::STATUS_COMPLETE, ActionScheduler_Store::STATUS_CANCELED ) as $status ) {
				while ( $batch < self::MAX_BATCHES ) {
					++$batch;

					$ids = $store->query_actions(
						array(
							'group'            => 'releasecart',
							'status'           => $status,
							'modified'         => $cutoff,
							'modified_compare' => '<=',
							'per_page'         => self::BATCH_SIZE,
							'orderby'          => 'none',
						)
					);

					$batchFailed = 0;

					foreach ( $ids as $id ) {
						try {
							$store->delete_action( (int) $id );
						} catch ( Exception $failure ) {
							// Usually another process deleted it first. Go on with the rest.
							++$batchFailed;
						}
					}

					$failed += $batchFailed;

					// A short batch means nothing older is left. A batch where no
					// row could be deleted would only come back the same.
					if ( count( $ids ) < self::BATCH_SIZE || count( $ids ) === $batchFailed ) {
						break;
					}
				}
			}
		} catch ( Throwable $failure ) {
			$this->logger->log(
				'error',
				sprintf( 'Action Scheduler prune failed: %s', $failure->getMessage() )
			);
		}

		if ( $failed > 0 ) {
			$this->logger->log(
				'error',
				sprintf( 'Action Scheduler prune could not delete %d %s.', $failed, 1 === $failed ? 'action' : 'actions' )
			);
		}
	}
}

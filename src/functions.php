<?php

use WP_Queue\Job;
use WP_Queue\Queue;

if ( ! function_exists( 'wp_queue' ) ) {
	/**
	 * Queue single job.
	 *
	 * @param Job $job
	 * @param int $delay Delay in seconds.
	 *
	 * @return bool|int
	 */
	function wp_queue( Job $job, $delay = 0 ) {
		$result = Queue::get_instance()->connection()->push( $job, $delay );

		do_action( 'wp_queue_push', $job, $delay );

		return $result;
	}
}

if ( ! function_exists( 'wp_queue_batch' ) ) {
	/**
	 * Queue multiple jobs.
	 *
	 * @param array $jobs  Array of \WP_Queue\Job jobs.
	 * @param int   $delay Delay in seconds.
	 */
	function wp_queue_batch( $jobs, $delay = 0 ) {
		foreach ( $jobs as $job ) {
			wp_queue( $job, $delay );
		}
	}
}
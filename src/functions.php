<?php

use WP_Queue\DatabaseQueue;
use WP_Queue\Job;

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
		global $wpdb;
		$queue = new DatabaseQueue( $wpdb );

		return $queue->push( $job, $delay );
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
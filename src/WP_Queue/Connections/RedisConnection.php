<?php

namespace WP_Queue\Connections;

use Exception;
use WP_Queue\Job;

/**
 * An incomplete example of how a new ConnectionInterface could be set up
 * for storing queue jobs.
 *
 * Please see the DatabaseConnection class for a complete working implementation.
 */
class RedisConnection implements ConnectionInterface {

	/**
	 * Push a job onto the queue.
	 *
	 * @param Job $job
	 * @param int $delay
	 *
	 * @return bool|int
	 */
	public function push( Job $job, $delay = 0 ) {
		return false;
	}

	/**
	 * Retrieve a job from the queue.
	 *
	 * @return bool|Job
	 */
	public function pop() {
		return false;
	}

	/**
	 * Delete a job from the queue.
	 *
	 * @param Job $job
	 *
	 * @return bool
	 */
	public function delete( $job ) {
		return false;
	}

	/**
	 * Release a job back onto the queue.
	 *
	 * @param Job $job
	 *
	 * @return bool
	 */
	public function release( Job $job ) {
		return false;
	}

	/**
	 * Push a job onto the failure queue.
	 *
	 * @param Job       $job
	 * @param Exception $exception
	 *
	 * @return bool
	 */
	public function failure( $job, Exception $exception ) {
		return false;
	}

	/**
	 * Get total jobs in the queue.
	 *
	 * @return int
	 */
	public function jobs() {
		return 0;
	}

	/**
	 * Get total jobs in the failures queue.
	 *
	 * @return int
	 */
	public function failed_jobs() {
		return 0;
	}
}

<?php

namespace WP_Queue;

abstract class Queue {

	/**
	 * Push a job onto the queue.
	 *
	 * @param Job $job
	 * @param int $delay
	 *
	 * @return bool|int
	 */
	abstract public function push( Job $job, $delay = 0 );

	/**
	 * Retrieve a job from the queue.
	 *
	 * @return bool|Job
	 */
	abstract public function pop();

	/**
	 * Reserve a job in the queue.
	 *
	 * @param int $id
	 */
	abstract protected function reserve( $id );

	/**
	 * Vitalize Job with latest data.
	 *
	 * @param mixed $raw_job
	 *
	 * @return Job
	 */
	abstract protected function vitalize_job( $raw_job );

	/**
	 * Get MySQL datetime.
	 *
	 * @param int $offset Seconds, can pass negative int.
	 *
	 * @return string
	 */
	protected function datetime( $offset = 0 ) {
		$timestamp = time() + $offset;

		return gmdate( 'Y-m-d H:i:s', $timestamp );
	}

}
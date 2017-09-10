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
	 * Delete a job from the queue.
	 *
	 * @param Job $job
	 */
	abstract public function delete( $job );

	/**
	 * Release a job back onto the queue.
	 *
	 * @param Job $job
	 */
	abstract public function release( $job );

	/**
	 * Push a job onto the failure queue.
	 *
	 * @param Job $job
	 */
	abstract public function failure( $job );

	/**
	 * Reserve a job in the queue.
	 *
	 * @param Job $job
	 */
	abstract protected function reserve( $job );

	/**
	 * Vitalize Job with latest data.
	 *
	 * @param mixed $raw_job
	 *
	 * @return Job
	 */
	abstract protected function vitalize_job( $raw_job );

}
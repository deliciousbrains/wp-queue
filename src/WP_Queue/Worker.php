<?php

namespace WP_Queue;

use Exception;

class Worker {

	/**
	 * @var Queue
	 */
	private $queue;

	/**
	 * Worker constructor.
	 *
	 * @param Queue $queue
	 */
	public function __construct( $queue ) {
		$this->queue = $queue;
	}

	/**
	 * Process a job on the queue.
	 *
	 * @return bool
	 */
	public function process() {
		$job = $this->queue->pop();

		if ( ! $job ) {
			return false;
		}

		try {
			$job->handle();
		} catch ( Exception $e ) {
			$job->release();
		}

		if ( $job->released() ) {
			$this->queue->release( $job );
		} else {
			$this->queue->delete( $job );
		}

		return true;
	}

}
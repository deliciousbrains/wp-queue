<?php

namespace WP_Queue;

use Exception;

class Worker {

	/**
	 * @var Queue
	 */
	private $queue;

	/**
	 * @var int
	 */
	private $attempts;

	/**
	 * Worker constructor.
	 *
	 * @param Queue $queue
	 * @param int   $attempts
	 */
	public function __construct( $queue, $attempts = 3 ) {
		$this->queue    = $queue;
		$this->attempts = $attempts;
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
			$job->release( $e );
		}

		if ( $job->released() && $job->attempts() >= $this->attempts ) {
			$this->queue->failure( $job );
		} else if ( $job->released() ) {
			$this->queue->release( $job );
		} else {
			$this->queue->delete( $job );
		}

		return true;
	}

}
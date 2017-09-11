<?php

namespace WP_Queue;

use Exception;
use WP_Queue\Connections\ConnectionInterface;

class Worker {

	/**
	 * @var ConnectionInterface
	 */
	private $connection;

	/**
	 * @var int
	 */
	private $attempts;

	/**
	 * Worker constructor.
	 *
	 * @param ConnectionInterface $connection
	 * @param int                 $attempts
	 */
	public function __construct( $connection, $attempts = 3 ) {
		$this->connection = $connection;
		$this->attempts   = $attempts;
	}

	/**
	 * Process a job on the queue.
	 *
	 * @return bool
	 */
	public function process() {
		$job = $this->connection->pop();

		if ( ! $job ) {
			return false;
		}

		try {
			$job->handle();
		} catch ( Exception $e ) {
			$job->release( $e );
		}

		if ( $job->released() && $job->attempts() >= $this->attempts ) {
			$this->connection->failure( $job );
		} else if ( $job->released() ) {
			$this->connection->release( $job );
		} else {
			$this->connection->delete( $job );
		}

		return true;
	}

}
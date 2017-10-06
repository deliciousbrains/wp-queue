<?php

namespace WP_Queue;

use WP_Queue\Connections\ConnectionInterface;

class Queue {

	/**
	 * @var ConnectionInterface
	 */
	protected $connection;

	/**
	 * @var string
	 */
	protected $identifier;

	/**
	 * Queue constructor.
	 *
	 * @param ConnectionInterface $connection
	 * @param string              $identifier
	 */
	public function __construct( ConnectionInterface $connection, $identifier ) {
		$this->connection = $connection;
		$this->identifier = $identifier;
	}

	/**
	 * Push a job onto the queue;
	 *
	 * @param Job $job
	 * @param int $delay
	 *
	 * @return bool|int
	 */
	public function push( Job $job, $delay = 0 ) {
		return $this->connection->push( $job, $delay );
	}

	/**
	 * Create a cron worker.
	 *
	 * @param int $attempts
	 */
	public function cron( $attempts = 3 ) {
		$cron = new Cron( $this->identifier, $this->worker( $attempts ) );

		if ( $cron->is_enabled() ) {
			$cron->init();
		}
	}

	/**
	 * Create a new worker.
	 *
	 * @param int $attempts
	 *
	 * @return Worker
	 */
	public function worker( $attempts ) {
		return new Worker( $this->connection, $attempts );
	}
}
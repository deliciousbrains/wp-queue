<?php

namespace WP_Queue;

use WP_Queue\Connections\ConnectionInterface;

class Queue {

	/**
	 * @var ConnectionInterface
	 */
	protected $connection;

	/**
	 * Queue constructor.
	 *
	 * @param ConnectionInterface $connection
	 */
	public function __construct( ConnectionInterface $connection ) {
		$this->connection = $connection;
	}

	/**
	 * Init Queue class.
	 */
	public function init() {
		if ( method_exists( $this->connection, 'init' ) ) {
			$this->connection->init();
		}

		$attempts = add_filter( 'wp_queue_cron_attempts', 3 );
		$cron     = new Cron( $this->worker( $attempts ) );

		if ( $cron->is_enabled() ) {
			$cron->init();
		}
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
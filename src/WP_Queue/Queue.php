<?php

namespace WP_Queue;

use WP_CLI;
use WP_Queue\Connections\ConnectionInterface;
use WP_Queue\Connections\DatabaseConnection;

class Queue {

	/**
	 * @var Queue
	 */
	protected static $instance;

	/**
	 * @var ConnectionInterface
	 */
	protected $connection;

	/**
	 * Make this class a singleton.
	 *
	 * Use this instead of __construct().
	 *
	 * @param null|ConnectionInterface $connection
	 *
	 * @return Queue
	 */
	public static function get_instance( $connection = null ) {
		if ( ! isset( static::$instance ) && ! ( self::$instance instanceof Queue ) ) {
			static::$instance = new Queue();
			static::$instance->init( $connection );
		}

		return static::$instance;
	}

	/**
	 * Init Queue class.
	 *
	 * @param null|ConnectionInterface $connection
	 */
	protected function init( $connection ) {
		if ( is_null( $connection ) ) {
			global $wpdb;
			$connection = new DatabaseConnection( $wpdb );
		}

		$this->connection = $connection;
	}

	/**
	 * Add WP CLI command.
	 */
	public function add_cli_command() {
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}

		WP_CLI::add_command( 'queue', '\WP_Queue\Command' );

		return $this;
	}

	/**
	 * Register the cron Worker.
	 */
	public function register_cron_worker() {
		$attempts = add_filter( 'wp_queue_cron_attempts', 3 );
		$cron     = new Cron( $this->worker( $attempts ) );

		$cron->init();

		return $this;
	}

	/**
	 * Get queue connection.
	 *
	 * @return ConnectionInterface
	 */
	public function connection() {
		return $this->connection;
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

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * class via the `new` operator from outside of this class.
	 */
	protected function __construct() {}

	/**
	 * As this class is a singleton it should not be clone-able.
	 */
	protected function __clone() {}

	/**
	 * As this class is a singleton it should not be able to be unserialized.
	 */
	protected function __wakeup() {}
}
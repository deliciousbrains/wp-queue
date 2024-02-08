<?php

namespace WP_Queue;

use Exception;
use WP_Queue\Connections\ConnectionInterface;
use WP_Queue\Connections\DatabaseConnection;
use WP_Queue\Connections\RedisConnection;
use WP_Queue\Connections\SyncConnection;
use WP_Queue\Exceptions\ConnectionNotFoundException;

class QueueManager {

	/**
	 * @var array
	 */
	protected static $instances = [];

	/**
	 * Resolve a Queue instance for required connection.
	 *
	 * @param string $connection
	 * @param array  $allowed_job_classes Job classes that may be handled, default any Job subclass.
	 *
	 * @return Queue
	 * @throws Exception
	 */
	public static function resolve( $connection, array $allowed_job_classes = [] ) {
		if ( isset( static::$instances[ $connection ] ) ) {
			return static::$instances[ $connection ];
		}

		return static::build( $connection, $allowed_job_classes );
	}

	/**
	 * Build a queue instance.
	 *
	 * @param string $connection
	 * @param array  $allowed_job_classes Job classes that may be handled, default any Job subclass.
	 *
	 * @return Queue
	 * @throws Exception
	 */
	protected static function build( $connection, array $allowed_job_classes = [] ) {
		$connections = static::connections( $allowed_job_classes );

		if ( empty( $connections[ $connection ] ) ) {
			throw new ConnectionNotFoundException();
		}

		static::$instances[ $connection ] = new Queue( $connections[ $connection ] );

		return static::$instances[ $connection ];
	}

	/**
	 * Get available connections.
	 *
	 * It's strongly recommended to override this function and provide a unique
	 * set of connections for your plugin, and unique filter name if filtering
	 * is desired.
	 *
	 * @param array $allowed_job_classes Job classes that may be handled, default any Job subclass.
	 *
	 * @return array
	 */
	protected static function connections( array $allowed_job_classes = [] ) {
		$connections = [
			'database' => new DatabaseConnection( $GLOBALS['wpdb'], $allowed_job_classes ),
			'redis'    => new RedisConnection(),
			'sync'     => new SyncConnection(),
		];

		/**
		 * Filter the available connections.
		 *
		 * @param ConnectionInterface[] $connections Associative array of connections.
		 */
		return apply_filters( 'wp_queue_connections', $connections );
	}
}

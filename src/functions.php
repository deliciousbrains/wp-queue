<?php

use WP_Queue\Queue;
use WP_Queue\QueueManager;

if ( ! function_exists( 'wp_queue' ) ) {
	/**
	 * Return Queue instance.
	 *
	 * @param string $connection
	 *
	 * @return Queue
	 */
	function wp_queue( $connection = 'database' ) {
		return QueueManager::resolve( $connection );
	}
}
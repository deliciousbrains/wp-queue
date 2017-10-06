<?php

use WP_Queue\Queue;

if ( ! function_exists( 'wp_queue' ) ) {
	/**
	 * Return Queue instance.
	 *
	 * @return Queue
	 */
	function wp_queue() {
		static $queue = null;

		if ( is_null( $queue ) ) {
			global $wpdb;
			$connection = apply_filters( 'wp_queue_connection', new WP_Queue\Connections\DatabaseConnection( $wpdb ) );
			$queue      = new WP_Queue\Queue( $connection );

			$queue->init();
		}

		return $queue;
	}
}
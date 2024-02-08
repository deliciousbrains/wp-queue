<?php

use WP_Queue\Queue;
use WP_Queue\QueueManager;

if ( ! function_exists( 'wp_queue' ) ) {
	/**
	 * Return Queue instance.
	 *
	 * @param string $connection
	 * @param array  $allowed_job_classes Job classes that may be handled. Default, any Job subclass.
	 *
	 * @return Queue
	 * @throws Exception
	 */
	function wp_queue( $connection = '', array $allowed_job_classes = [] ) {
		if ( empty( $connection ) ) {
			$connection = apply_filters( 'wp_queue_default_connection', 'database' );
		}

		return QueueManager::resolve( $connection, $allowed_job_classes );
	}
}

if ( ! function_exists( 'wp_queue_install_tables' ) ) {
	/**
	 * Install database tables
	 */
	function wp_queue_install_tables() {
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$wpdb->hide_errors();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$wpdb->prefix}queue_jobs (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				job longtext NOT NULL,
				attempts tinyint(3) NOT NULL DEFAULT 0,
				reserved_at datetime DEFAULT NULL,
				available_at datetime NOT NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id)
				) $charset_collate;";

		dbDelta( $sql );

		$sql = "CREATE TABLE {$wpdb->prefix}queue_failures (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				job longtext NOT NULL,
				error text DEFAULT NULL,
				failed_at datetime NOT NULL,
				PRIMARY KEY  (id)
				) $charset_collate;";

		dbDelta( $sql );
	}
}

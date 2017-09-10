<?php

namespace WP_Queue;

use WP_CLI;
use WP_CLI_Command;

class Command extends WP_CLI_Command {

	/**
	 * Install jobs database table.
	 */
	public function install() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		global $wpdb;
		$wpdb->hide_errors();

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$wpdb->prefix}jobs (
				id bigint(20) NOT NULL AUTO_INCREMENT,
                job longtext NOT NULL,
                attempts tinyint(3) NOT NULL DEFAULT 0,
                reserved_at datetime DEFAULT NULL,
                available_at datetime NOT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id)
				) $charset_collate;";

		dbDelta( $sql );

		WP_CLI::success( "Table {$wpdb->prefix}jobs created." );
	}

	/**
	 * Run a single job.
	 */
	public function run() {
		global $wpdb;
		$queue  = new DatabaseQueue( $wpdb );
		$worker = new Worker( $queue );

		if ( $worker->process() ) {
			WP_CLI::success( 'Job processed.' );
		} else {
			WP_CLI::success( 'No jobs to process.' );
		}
	}

}
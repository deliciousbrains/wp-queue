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

		WP_CLI::success( "Table {$wpdb->prefix}queue_jobs created." );

		$sql = "CREATE TABLE {$wpdb->prefix}queue_failures (
				id bigint(20) NOT NULL AUTO_INCREMENT,
                job longtext NOT NULL,
                error text DEFAULT NULL,
                failed_at datetime NOT NULL,
                PRIMARY KEY  (id)
				) $charset_collate;";

		dbDelta( $sql );

		WP_CLI::success( "Table {$wpdb->prefix}queue_failures created." );
	}

	/**
	 * Run a single job.
	 *
	 * ## OPTIONS
	 *
	 * [--attempts=<attempts>]
	 * : The number of times to attempt a job.
	 * ---
	 * default: 3
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp queue run --attempts=3
	 */
	public function run( $args, $assoc_args ) {
		global $wpdb;
		$queue  = new DatabaseQueue( $wpdb );
		$worker = new Worker( $queue, $assoc_args['attempts'] );

		if ( $worker->process() ) {
			WP_CLI::success( 'Job processed.' );
		} else {
			WP_CLI::success( 'No jobs to process.' );
		}
	}

	/**
	 * Start a queue worker.
	 *
	 * ## OPTIONS
	 *
	 * [--attempts=<attempts>]
	 * : The number of times to attempt a job.
	 * ---
	 * default: 3
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp queue work --attempts=3
	 */
	public function work( $args, $assoc_args ) {
		global $wpdb;
		$queue  = new DatabaseQueue( $wpdb );
		$worker = new Worker( $queue, $assoc_args['attempts'] );

		while ( true ) {
			if ( $worker->process() ) {
				WP_CLI::success( 'Job processed.' );
			} else {
				sleep( 5 );
			}
		}
	}

	/**
	 * Show queue status.
	 */
	public function status() {
		global $wpdb;
		$queue = new DatabaseQueue( $wpdb );

		WP_CLI::log( $queue->jobs() . ' jobs in the queue' );
		WP_CLI::log( $queue->failed_jobs() . ' failed jobs in the queue' );
	}

}
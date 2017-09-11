<?php

namespace WP_Queue;

class Cron {

	/**
	 * @var Cron
	 */
	protected static $instance;

	/**
	 * Timestamp of when processing the queue started.
	 *
	 * @var int
	 */
	protected $start_time;

	/**
	 * Make this class a singleton.
	 *
	 * Use this instead of __construct().
	 *
	 * @return Cron
	 */
	public static function get_instance() {
		if ( ! isset( static::$instance ) && ! ( self::$instance instanceof Cron ) ) {
			static::$instance = new Cron();
		}

		static::$instance->init();

		return static::$instance;
	}

	/**
	 * Init cron class.
	 */
	protected function init() {
		add_filter( 'cron_schedules', array( $this, 'schedule_cron' ) );
		add_action( 'wp_queue_cron_worker', array( $this, 'cron_worker' ) );

		if ( ! wp_next_scheduled( 'wp_queue_cron_worker' ) ) {
			// Schedule health check
			wp_schedule_event( time(), 'wp_queue_cron_interval', 'wp_queue_cron_worker' );
		}
	}

	/**
	 * Add 5 minutes to cron schedules.
	 *
	 * @param array $schedules
	 *
	 * @return array
	 */
	public function schedule_cron( $schedules ) {
		$interval = apply_filters( 'wp_queue_cron_interval', 5 );

		$schedules['wp_queue_cron_interval'] = array(
			'interval' => MINUTE_IN_SECONDS * $interval,
			'display'  => sprintf( __( 'Every %d Minutes' ), $interval ),
		);

		return $schedules;
	}

	/**
	 * Process any jobs in the queue.
	 */
	public function cron_worker() {
		global $wpdb;
		$queue    = new DatabaseQueue( $wpdb );
		$attempts = add_filter( 'wp_queue_cron_attempts', 3 );
		$worker   = new Worker( $queue, $attempts );

		$this->start_time = time();

		while ( ! $this->time_exceeded() && ! $this->memory_exceeded() ) {
			if ( ! $worker->process() ) {
				break;
			}
		}
	}

	/**
	 * Memory exceeded
	 *
	 * Ensures the worker process never exceeds 80%
	 * of the maximum allowed PHP memory.
	 *
	 * @return bool
	 */
	protected function memory_exceeded() {
		$memory_limit   = $this->get_memory_limit() * 0.8; // 80% of max memory
		$current_memory = memory_get_usage( true );
		$return         = false;

		if ( $current_memory >= $memory_limit ) {
			$return = true;
		}

		return apply_filters( 'wp_queue_cron_memory_exceeded', $return );
	}

	/**
	 * Get memory limit.
	 *
	 * @return int
	 */
	protected function get_memory_limit() {
		if ( function_exists( 'ini_get' ) ) {
			$memory_limit = ini_get( 'memory_limit' );
		} else {
			$memory_limit = '256M';
		}

		if ( ! $memory_limit || -1 == $memory_limit ) {
			// Unlimited, set to 1GB
			$memory_limit = '1000M';
		}

		return intval( $memory_limit ) * 1024 * 1024;
	}

	/**
	 * Time exceeded
	 *
	 * Ensures the worker never exceeds a sensible time limit (20s by default).
	 * A timeout limit of 30s is common on shared hosting.
	 *
	 * @return bool
	 */
	protected function time_exceeded() {
		$finish = $this->start_time + apply_filters( 'wp_queue_cron_time_limit', 20 ); // 20 seconds
		$return = false;

		if ( time() >= $finish ) {
			$return = true;
		}

		return apply_filters( 'wp_queue_cron_time_exceeded', $return );
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
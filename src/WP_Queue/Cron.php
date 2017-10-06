<?php

namespace WP_Queue;

class Cron {

	/**
	 * @var Worker
	 */
	protected $worker;

	/**
	 * Timestamp of when processing the queue started.
	 *
	 * @var int
	 */
	protected $start_time;

	/**
	 * Cron constructor.
	 *
	 * @param Worker $worker
	 */
	public function __construct( $worker ) {
		$this->worker = $worker;
	}

	/**
	 * Is the cron queue worker enabled?
	 *
	 * @return bool
	 */
	public function is_enabled() {
		if ( defined( 'DISABLE_WP_QUEUE_CRON' ) && DISABLE_WP_QUEUE_CRON ) {
			return false;
		}

		return true;
	}

	/**
	 * Init cron class.
	 */
	public function init() {
		add_filter( 'cron_schedules', array( $this, 'schedule_cron' ) );
		add_action( 'wp_queue_worker', array( $this, 'cron_worker' ) );

		if ( ! wp_next_scheduled( 'wp_queue_worker' ) ) {
			// Schedule health check
			wp_schedule_event( time(), 'wp_queue_cron_interval', 'wp_queue_worker' );
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
		$this->start_time = time();

		while ( ! $this->time_exceeded() && ! $this->memory_exceeded() ) {
			if ( ! $this->worker->process() ) {
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
}
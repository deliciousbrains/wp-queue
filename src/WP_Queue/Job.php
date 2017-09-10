<?php

namespace WP_Queue;

use Carbon\Carbon;

abstract class Job {

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var int
	 */
	protected $attempts;

	/**
	 * @var Carbon
	 */
	protected $reserved_at;

	/**
	 * @var Carbon
	 */
	protected $available_at;

	/**
	 * @var Carbon
	 */
	protected $created_at;

	/**
	 *
	 */
	abstract public function handle();

	/**
	 * Get job ID.
	 *
	 * @return int
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * Set job ID.
	 *
	 * @param int $id
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Get job attempts;
	 *
	 * @return int
	 */
	public function attempts() {
		return $this->attempts;
	}

	/**
	 * Set job attempts.
	 *
	 * @param int $attempts
	 */
	public function set_attempts( $attempts ) {
		$this->attempts = $attempts;
	}

	/**
	 * Get reserved at date.
	 *
	 * @return Carbon
	 */
	public function reserved_at() {
		return $this->reserved_at;
	}

	/**
	 * Set reserved at date.
	 *
	 * @param Carbon $reserved_at
	 */
	public function set_reserved_at( Carbon $reserved_at ) {
		$this->reserved_at = $reserved_at;
	}

	/**
	 * Get available at date.
	 *
	 * @return Carbon
	 */
	public function available_at() {
		return $this->available_at;
	}

	/**
	 * Set available at date.
	 *
	 * @param Carbon $available_at
	 */
	public function set_available_at( Carbon $available_at ) {
		$this->available_at = $available_at;
	}

	/**
	 * Get created at date.
	 *
	 * @return Carbon
	 */
	public function created_at() {
		return $this->created_at;
	}

	/**
	 * Set created at date.
	 *
	 * @param Carbon $created_at
	 */
	public function set_created_at( Carbon $created_at ) {
		$this->created_at = $created_at;
	}

	/**
	 * Determine which properties should be serialized.
	 *
	 * @return array
	 */
	public function __sleep() {
		$props = get_object_vars( $this );

		unset( $props['id'], $props['attempts'], $props['reserved_at'], $props['available_at'], $props['created_at'] );

		return array_keys( $props );
	}

}
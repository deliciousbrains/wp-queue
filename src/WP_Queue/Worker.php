<?php

namespace WP_Queue;

use Exception;
use WP_Queue\Connections\ConnectionInterface;
use WP_Queue\Exceptions\WorkerAttemptsExceededException;

class Worker
{

	/**
	 * @var ConnectionInterface
	 */
	protected $connection;

	/**
	 * @var int
	 */
	protected $attempts;

	/**
	 * Worker constructor.
	 *
	 * @param ConnectionInterface $connection
	 * @param int                 $attempts
	 */
	public function __construct($connection, $attempts = 3)
	{
		$this->connection = $connection;
		$this->attempts   = $attempts;
	}

	/**
	 * Process a job on the queue.
	 *
	 * @return bool
	 */
	public function process()
	{
		$job = $this->connection->pop();

		if (! $job) {
			return false;
		}

		$exception = null;

		try {
			$job->handle();
		} catch (Exception $exception) {
			// Check if this is the final allowed attempt
			if ($job->attempts() + 1 >= $this->attempts) {
				$job->fail();
			} else {
				$job->release();
			}
		}

		// Handle non-exception failures
		if (! $job->released() && ! $job->failed() && $job->attempts() >= $this->attempts) {
			if (empty($exception)) {
				$exception = new WorkerAttemptsExceededException();
			}
			$job->fail();
		}

		if ($job->failed()) {
			$this->connection->failure($job, $exception);
		} elseif ($job->released()) {
			$this->connection->release($job);
		} else {
			$this->connection->delete($job);
		}

		return true;
	}
}

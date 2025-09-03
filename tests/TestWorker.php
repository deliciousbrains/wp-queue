<?php

use PHPUnit\Framework\TestCase;
use WP_Queue\Connections\ConnectionInterface;
use WP_Queue\Job;
use WP_Queue\Worker;

class TestWorker extends TestCase
{

	public function setUp(): void
	{
		WP_Mock::setUp();
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
	}

	public function test_process_success()
	{
		$connection = Mockery::spy(ConnectionInterface::class);
		$job        = Mockery::spy(Job::class);
		$connection->shouldReceive('pop')->once()->andReturn($job);

		$worker = new Worker($connection);
		$this->assertTrue($worker->process());
	}

	public function test_process_fail()
	{
		$connection = Mockery::spy(ConnectionInterface::class);
		$job        = Mockery::spy(Job::class);
		$connection->shouldReceive('pop')->once()->andReturn(false);

		$worker = new Worker($connection);
		$this->assertFalse($worker->process());
	}

	public function test_exception_within_attempt_limit_releases_job()
	{
		$connection = Mockery::spy(ConnectionInterface::class);
		$job        = Mockery::spy(Job::class);

		$connection->shouldReceive('pop')->once()->andReturn($job);
		$job->shouldReceive('handle')->once()->andThrow(new Exception('Test exception'));
		$job->shouldReceive('attempts')->andReturn(2); // Under limit of 3
		$job->shouldReceive('release')->once();
		$job->shouldReceive('released')->andReturn(true);
		$job->shouldReceive('failed')->andReturn(false);
		$connection->shouldReceive('release')->once();

		$worker = new Worker($connection, 3);
		$this->assertTrue($worker->process());
	}

	public function test_exception_at_attempt_limit_fails_job()
	{
		$connection = Mockery::spy(ConnectionInterface::class);
		$job        = Mockery::spy(Job::class);

		$connection->shouldReceive('pop')->once()->andReturn($job);
		$job->shouldReceive('handle')->once()->andThrow(new Exception('Test exception'));
		$job->shouldReceive('attempts')->andReturn(3); // At limit of 3
		$job->shouldReceive('fail')->once();
		$job->shouldReceive('failed')->andReturn(true);
		$connection->shouldReceive('failure')->once();

		$worker = new Worker($connection, 3);
		$this->assertTrue($worker->process());
	}
}

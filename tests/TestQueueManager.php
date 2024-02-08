<?php

use PHPUnit\Framework\TestCase;
use WP_Queue\Exceptions\ConnectionNotFoundException;
use WP_Queue\Job;
use WP_Queue\Queue;
use WP_Queue\QueueManager;

class TestQueueManager extends TestCase {

	public function setUp(): void {
		WP_Mock::setUp();

		global $wpdb;
		$wpdb         = Mockery::mock( 'WPDB' );
		$wpdb->prefix = 'wp_';
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function test_resolve() {
		$queue = QueueManager::resolve( 'database' );
		$this->assertInstanceOf( Queue::class, $queue );
		$queue = QueueManager::resolve( 'database', [] );
		$this->assertInstanceOf( Queue::class, $queue );
		$queue = QueueManager::resolve( 'database', [ TestJob::class ] );
		$this->assertInstanceOf( Queue::class, $queue );
	}

	public function test_resolve_exception() {
		$this->expectException( ConnectionNotFoundException::class );
		QueueManager::resolve( 'wibble' );
	}
}

if ( ! class_exists( 'TestJob' ) ) {
	class TestJob extends Job {
		public function handle() {
		}
	}
}

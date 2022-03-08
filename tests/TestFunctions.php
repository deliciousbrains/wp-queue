<?php

use PHPUnit\Framework\TestCase;
use WP_Queue\Queue;

class TestFunctions extends TestCase {

	public function setUp() : void {
		WP_Mock::setUp();

		global $wpdb;
		$wpdb = Mockery::mock( 'WPDB' );;
		$wpdb->prefix = "wp_";
	}

	public function tearDown() : void {
		WP_Mock::tearDown();
	}

	public function test_wp_queue() {
		$this->assertInstanceOf( Queue::class, wp_queue() );
	}
}

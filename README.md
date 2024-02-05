# WP Queue

[![Total Downloads](https://poser.pugx.org/deliciousbrains/wp-queue/downloads)](https://packagist.org/packages/deliciousbrains/wp-queue)
[![Latest Stable Version](https://poser.pugx.org/deliciousbrains/wp-queue/v/stable)](https://packagist.org/packages/deliciousbrains/wp-queue)
[![License](https://poser.pugx.org/deliciousbrains/wp-queue/license)](https://packagist.org/packages/deliciousbrains/wp-queue)

Job queues for WordPress.

## Install

The recommended way to install this library in your project is by loading it through Composer:

```shell
composer require deliciousbrains/wp-queue
```

It is highly recommended to prefix wrap the library class files using [PHP-Scoper](https://packagist.org/packages/humbug/php-scoper), to prevent collisions with other projects using this same library.

## Prerequisites

WP_Queue requires PHP __7.3+__.

The following database tables need to be created:

```sql
CREATE TABLE {$wpdb->prefix}queue_jobs (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    job longtext NOT NULL,
    attempts tinyint(3) NOT NULL DEFAULT 0,
    reserved_at datetime DEFAULT NULL,
    available_at datetime NOT NULL,
    created_at datetime NOT NULL,
    PRIMARY KEY (id)
);
```

```sql
CREATE TABLE {$wpdb->prefix}queue_failures (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    job longtext NOT NULL,
    error text DEFAULT NULL,
    failed_at datetime NOT NULL,
    PRIMARY KEY (id)
);
```

Alternatively, you can call the `wp_queue_install_tables()` helper function to install the tables. If using WP_Queue in a plugin you may opt to call the helper from within your `register_activation_hook`.

## Jobs

Job classes should extend the `WP_Queue\Job` class and normally only contain a `handle` method which is called when the job is processed by the queue worker. Any data required by the job should be passed to the constructor and assigned to a public property. This data will remain available once the job is retrieved from the queue. Let's look at an example job class:

```php
<?php

use WP_Queue\Job;

class Subscribe_User_Job extends Job {

	/**
	 * @var int
	 */
	public $user_id;

	/**
	 * Subscribe_User_Job constructor.
	 *
	 * @param int $user_id
	 */
	public function __construct( $user_id ) {
		$this->user_id = $user_id;
	}

	/**
	 * Handle job logic.
	 */
	public function handle() {
		$user = get_user_by( 'ID', $this->user_id );

		// Process the user...
	}

}
```

## Dispatching Jobs

Jobs can be pushed to the queue like so:

```php
wp_queue()->push( new Subscribe_User_Job( 12345 ) );
```

You can create delayed jobs by passing an optional second parameter to the `push` method. This job will be delayed by 60 minutes:

```php
wp_queue()->push( new Subscribe_User_Job( 12345 ), 3600 );
```

## Cron Worker

Jobs need to be processed by a queue worker. You can start a cron worker like so, which piggy backs onto WP cron:

```php
wp_queue()->cron();
```

You can also specify the number of times a job should be attempted before being marked as a failure.

```php
wp_queue()->cron( 3 );
```

## Restricting Allowed Job Classes

The queue will handle any subclass of `WP_Queue\Job`. For better security,
it is strongly recommended that the `DatabaseConnection` be instantiated with a list
of allowed `Job` subclasses that are expected to be handled.

You can do that by passing in an array of `Job` subclasses when the `Queue` sets
up its database connection, or by having a database connection that only handles
certain `Job` subclasses.

```php
class Connection extends DatabaseConnection {
    public function __construct( $wpdb, array $allowed_job_classes = [] ) {
        // If a connection is always dealing with the same Jobs,
        // you could explicitly set the allowed job classes here
        // rather than pass them in.
        if ( empty( $allowed_job_classes ) ) {
            $allowed_job_classes = [ Subscribe_User_Job::class ];
        }

        parent::__construct( $wpdb, $allowed_job_classes );

        $this->jobs_table     = $wpdb->base_prefix . 'myplugin_subs_jobs';
        $this->failures_table = $wpdb->base_prefix . 'myplugin_subs_failures';
    }
}

class Subscribe_User_Queue extends Queue {
    public function __construct() {
        global $wpdb;

        // Set up custom database queue, with list of allowed job classes.
        parent::__construct( new Connection( $wpdb, [ Subscribe_User_Job::class ] ) );

        // Other set up stuff ...
    }
}

class MyPlugin {
    /**
     * @var Subscribe_User_Queue
     */
    private $queue;

    public function __construct() {
        // Part of bring-up ...
        $this->queue = new Subscribe_User_Queue();
        
        // Other stuff ...
    }

    protected function subscribe_user( $user_id ) {
        $this->queue->push( new Subscribe_User_Job( $user_id ) );
    }

    /**
     * Triggered by cron or background process etc.
     *
     * @return bool
     */
    protected function process_queue_job() {
        return $this->queue->worker()->process();
    }
}
```

## Local Development

When developing locally you may want jobs processed instantly, instead of them being pushed to the queue. This can be useful for debugging jobs via Xdebug. Add the following filter to use the `sync` connection.

```php
add_filter( 'wp_queue_default_connection', function() {
	return 'sync';
} );
```

## Contributing

Contributions are welcome via Pull Requests, but please do raise an issue before
working on anything to discuss the change if there isn't already an issue. If there
is an approved issue you'd like to tackle, please post a comment on it to let people know
you're going to have a go at it so that effort isn't wasted through duplicated work.

### Unit & Style Tests

When working on the library, please add unit tests to the appropriate file in the
`tests` directory that cover your changes.

#### Setting Up

We use the standard WordPress test libraries for running unit tests.

Please run the following command to set up the libraries:

```shell
bin/install-wp-tests.sh db_name db_user db_pass
```

Substitute `db_name`, `db_user` and `db_pass` as appropriate.

Please be aware that running the unit tests is a **destructive operation**, *database
tables will be cleared*, so please use a database name dedicated to running unit tests.
The standard database name usually used by the WordPress community is `wordpress_test`, e.g.

```shell
bin/install-wp-tests.sh wordpress_test root root
```

Please refer to the [Initialize the testing environment locally](https://make.wordpress.org/cli/handbook/misc/plugin-unit-tests/#3-initialize-the-testing-environment-locally)
section of the WordPress Handbook's [Plugin Integration Tests](https://make.wordpress.org/cli/handbook/misc/plugin-unit-tests/)
entry should you run into any issues.

#### Running Unit Tests

To run the unit tests, simply run:

```shell
make test-unit
```

If the `composer` dependencies aren't in place, they'll be automatically installed first.

#### Running Style Tests

It's important that the code in the library use a consistent style to aid in quickly
understanding it, and to avoid some common issues. `PHP_Code_Sniffer` is used with
mostly standard WordPress rules to help check for consistency.

To run the style tests, simply run:

```shell
make test-style
```

If the `composer` dependencies aren't in place, they'll be automatically installed first.

#### Running All Tests

To make things super simple, just run the following to run all tests:

```shell
make
```

If the `composer` dependencies aren't in place, they'll be automatically installed first.

### Creating a PR

When creating a PR, please make sure to mention which GitHub issue is being resolved
at the top of the description, e.g.:

`Resolves #123`

The unit and style tests will be run automatically, the PR will not be eligible for
merge unless they pass, and the branch is up-to-date with `master`.

## License

WP Queue is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

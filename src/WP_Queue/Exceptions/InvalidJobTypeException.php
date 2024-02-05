<?php

namespace WP_Queue\Exceptions;

use Exception;

/**
 * Exception for when job data includes an unrecognized class.
 */
class InvalidJobTypeException extends Exception {
}

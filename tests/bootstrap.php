<?php
/**
 * PHPUnit bootstrap file
 */

// Composer autoloader
$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// Check which test suite we're running
$suite = getenv('TESTSUITE') ?: 'unit';

if ($suite === 'unit') {
    // Unit tests - mock WordPress functions with Brain\Monkey
    require_once __DIR__ . '/bootstrap-unit.php';
} else {
    // Integration tests - load WordPress test framework
    require_once __DIR__ . '/bootstrap-integration.php';
}

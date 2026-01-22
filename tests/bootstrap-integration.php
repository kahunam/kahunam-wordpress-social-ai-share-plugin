<?php
/**
 * Integration tests bootstrap - Loads WordPress test framework
 *
 * Requires WP_TESTS_DIR environment variable pointing to WordPress test library
 * or wp-env running locally.
 */

$_tests_dir = getenv('WP_TESTS_DIR');

if (!$_tests_dir) {
    // Try common locations
    $locations = [
        '/tmp/wordpress-tests-lib',
        dirname(__DIR__) . '/vendor/wp-phpunit/wp-phpunit',
        getenv('HOME') . '/.wp-tests/wordpress-tests-lib',
    ];

    foreach ($locations as $location) {
        if (file_exists($location . '/includes/functions.php')) {
            $_tests_dir = $location;
            break;
        }
    }
}

if (!$_tests_dir || !file_exists($_tests_dir . '/includes/functions.php')) {
    echo "WordPress test library not found. Set WP_TESTS_DIR environment variable.\n";
    echo "For local development, consider using wp-env: https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/\n";
    exit(1);
}

// Give access to tests_add_filter() function
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested
 */
function _manually_load_plugin() {
    require dirname(__DIR__) . '/ai-share-buttons.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WP testing environment
require $_tests_dir . '/includes/bootstrap.php';

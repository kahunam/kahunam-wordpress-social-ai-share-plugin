<?php
/**
 * Unit tests bootstrap - Uses Brain\Monkey to mock WordPress
 */

use Brain\Monkey;

// Define WordPress constants
define('ABSPATH', '/tmp/wordpress/');
define('KAAIS_VERSION', '2.0.0');
define('KAAIS_PATH', dirname(__DIR__) . '/');
define('KAAIS_URL', 'http://example.com/wp-content/plugins/ai-share-buttons/');

// Mock WordPress functions before loading plugin
Monkey\setUp();

// Common WordPress function mocks
Monkey\Functions\stubs([
    'plugin_dir_path' => function($file) {
        return dirname($file) . '/';
    },
    'plugin_dir_url' => function($file) {
        return 'http://example.com/wp-content/plugins/' . basename(dirname($file)) . '/';
    },
    'get_option' => function($key, $default = false) {
        return $default;
    },
    'add_option' => '__return_true',
    'update_option' => '__return_true',
    'delete_option' => '__return_true',
    'wp_parse_args' => function($args, $defaults) {
        return array_merge($defaults, $args);
    },
    'sanitize_text_field' => function($str) {
        return trim(strip_tags($str));
    },
    'sanitize_textarea_field' => function($str) {
        return trim(strip_tags($str));
    },
    'sanitize_key' => function($key) {
        return preg_replace('/[^a-z0-9_\-]/', '', strtolower($key));
    },
    'sanitize_title' => function($title) {
        return preg_replace('/[^a-z0-9\-]/', '-', strtolower($title));
    },
    'esc_html' => function($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    },
    'esc_attr' => function($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    },
    'esc_url' => function($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    },
    'esc_url_raw' => function($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    },
    'esc_textarea' => function($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    },
    '__' => function($text, $domain = 'default') {
        return $text;
    },
    'esc_html__' => function($text, $domain = 'default') {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    },
    'esc_attr__' => function($text, $domain = 'default') {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    },
    'get_the_ID' => function() {
        return 1;
    },
    'get_permalink' => function($post_id = null) {
        return 'http://example.com/test-post/';
    },
    'get_the_title' => function($post_id = null) {
        return 'Test Post Title';
    },
    'is_singular' => '__return_true',
    'get_post_type' => function() {
        return 'post';
    },
    'apply_filters' => function($tag, $value, ...$args) {
        return $value;
    },
    'do_action' => function($tag, ...$args) {
        // No-op for unit tests
    },
    'add_filter' => '__return_true',
    'add_action' => '__return_true',
    'add_shortcode' => '__return_true',
    'shortcode_atts' => function($defaults, $atts, $shortcode = '') {
        return array_merge($defaults, (array) $atts);
    },
    'register_activation_hook' => '__return_true',
    'is_admin' => '__return_false',
]);

// Load plugin functions (not the full plugin to avoid hooks)
require_once dirname(__DIR__) . '/ai-share-buttons.php';

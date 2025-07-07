<?php
/**
 * Plugin Name: Test AI Share Buttons Activation
 * Description: Test version to debug activation issues
 * Version: 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Simple activation test
register_activation_hook(__FILE__, 'test_ai_share_activation');

function test_ai_share_activation() {
    // Create a test option to verify activation works
    update_option('ai_share_test_activation', 'Plugin activated successfully at ' . current_time('mysql'));
}

// Add admin notice to show activation status
add_action('admin_notices', 'test_ai_share_admin_notice');

function test_ai_share_admin_notice() {
    $test_option = get_option('ai_share_test_activation');
    if ($test_option) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($test_option); ?></p>
        </div>
        <?php
    }
}
<?php
/**
 * Uninstall script for AI-Enhanced Share Buttons
 *
 * This file is executed when the plugin is uninstalled
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if we should delete data
$settings = get_option('ai_share_buttons_settings');
if (isset($settings['delete_data_on_uninstall']) && $settings['delete_data_on_uninstall']) {
    // Delete options
    delete_option('ai_share_buttons_settings');
    delete_option('ai_share_buttons_networks');
    delete_option('ai_share_buttons_prompts');
    
    // Drop tables
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ai_share_clicks");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ai_share_analytics");
    
    // Remove upload directory
    $upload_dir = wp_upload_dir();
    $plugin_upload_dir = $upload_dir['basedir'] . '/ai-share-buttons';
    if (is_dir($plugin_upload_dir)) {
        ai_share_buttons_remove_directory($plugin_upload_dir);
    }
}

// Clear scheduled hooks
wp_clear_scheduled_hook('ai_share_buttons_daily_cleanup');

/**
 * Recursively remove directory
 */
function ai_share_buttons_remove_directory($dir) {
    if (!is_dir($dir)) {
        return;
    }
    
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            ai_share_buttons_remove_directory($path);
        } else {
            unlink($path);
        }
    }
    
    rmdir($dir);
}
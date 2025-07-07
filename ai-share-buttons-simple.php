<?php
/**
 * Plugin Name: AI-Enhanced Share Buttons (Simple)
 * Plugin URI: https://yourwebsite.com/ai-share-buttons
 * Description: Enhanced share buttons with integrated AI services - Simple version for debugging
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: ai-share-buttons
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AI_SHARE_BUTTONS_VERSION', '1.0.0');
define('AI_SHARE_BUTTONS_FILE', __FILE__);
define('AI_SHARE_BUTTONS_PATH', plugin_dir_path(__FILE__));
define('AI_SHARE_BUTTONS_URL', plugin_dir_url(__FILE__));
define('AI_SHARE_BUTTONS_BASENAME', plugin_basename(__FILE__));

// Main plugin class
class AI_Share_Buttons_Simple {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Add shortcode
        add_shortcode('ai_share_buttons', array($this, 'shortcode'));
        
        // Add menu
        add_action('admin_menu', array($this, 'add_menu'));
    }
    
    public function shortcode($atts) {
        return '<div class="ai-share-buttons">Share buttons will appear here</div>';
    }
    
    public function add_menu() {
        add_menu_page(
            'AI Share Buttons',
            'AI Share Buttons',
            'manage_options',
            'ai-share-buttons',
            array($this, 'admin_page'),
            'dashicons-share',
            30
        );
    }
    
    public function admin_page() {
        echo '<div class="wrap">';
        echo '<h1>AI Share Buttons</h1>';
        echo '<p>Plugin is working! This is the admin page.</p>';
        echo '</div>';
    }
}

// Activation hook
register_activation_hook(__FILE__, 'ai_share_buttons_activate_simple');
function ai_share_buttons_activate_simple() {
    // Set a simple option to verify activation
    update_option('ai_share_buttons_activated', true);
}

// Initialize plugin
add_action('plugins_loaded', function() {
    AI_Share_Buttons_Simple::get_instance();
});

// Test admin notice
add_action('admin_notices', function() {
    if (get_option('ai_share_buttons_activated')) {
        echo '<div class="notice notice-success"><p>AI Share Buttons activated successfully!</p></div>';
        delete_option('ai_share_buttons_activated');
    }
});
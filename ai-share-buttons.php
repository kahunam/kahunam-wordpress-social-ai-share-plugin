<?php
/**
 * Plugin Name: AI-Enhanced Share Buttons
 * Plugin URI: https://github.com/kahunam-wordpress-social-ai-share-plugin
 * Description: Enhanced share buttons with integrated AI services for content analysis and sharing optimization
 * Version: 1.0.0
 * Author: Kahunam
 * Author URI: https://kahunam.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-share-buttons
 * Domain Path: /languages
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

// Only load files after WordPress is ready
function ai_share_buttons_load_files() {
    $required_files = array(
        'includes/class-ai-share-buttons.php',
        'includes/class-ai-share-buttons-installer.php', 
        'includes/class-ai-share-buttons-admin.php',
        'includes/class-ai-share-buttons-frontend.php',
        'includes/class-ai-share-buttons-analytics.php',
        'includes/functions.php'
    );

    foreach ($required_files as $file) {
        $file_path = AI_SHARE_BUTTONS_PATH . $file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }
}

// Load files early but after WordPress core is loaded
add_action('plugins_loaded', 'ai_share_buttons_load_files', 5);

// Plugin activation hook - delay until plugins_loaded to ensure class exists
function ai_share_buttons_activate() {
    require_once AI_SHARE_BUTTONS_PATH . 'includes/class-ai-share-buttons-installer.php';
    AI_Share_Buttons_Installer::activate();
}
register_activation_hook(__FILE__, 'ai_share_buttons_activate');

// Plugin deactivation hook
function ai_share_buttons_deactivate() {
    require_once AI_SHARE_BUTTONS_PATH . 'includes/class-ai-share-buttons-installer.php';
    AI_Share_Buttons_Installer::deactivate();
}
register_deactivation_hook(__FILE__, 'ai_share_buttons_deactivate');

// Initialize the plugin
function ai_share_buttons_init() {
    // Make sure our classes are loaded
    if (!class_exists('AI_Share_Buttons')) {
        return;
    }
    
    // Initialize main plugin class
    $plugin = AI_Share_Buttons::get_instance();
    $plugin->init();
}
add_action('plugins_loaded', 'ai_share_buttons_init', 10);

// Load text domain
function ai_share_buttons_load_textdomain() {
    load_plugin_textdomain('ai-share-buttons', false, dirname(AI_SHARE_BUTTONS_BASENAME) . '/languages');
}
add_action('init', 'ai_share_buttons_load_textdomain');

// Global function for manual placement is defined in includes/functions.php
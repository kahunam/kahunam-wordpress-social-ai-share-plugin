<?php
/**
 * Plugin Name: AI-Enhanced Share Buttons
 * Plugin URI: https://yourwebsite.com/ai-share-buttons
 * Description: Enhanced share buttons with integrated AI services for content analysis and sharing optimization
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * Text Domain: ai-share-buttons
 * Domain Path: /languages
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

// Include required files
require_once AI_SHARE_BUTTONS_PATH . 'includes/class-ai-share-buttons.php';
require_once AI_SHARE_BUTTONS_PATH . 'includes/class-ai-share-buttons-installer.php';
require_once AI_SHARE_BUTTONS_PATH . 'includes/class-ai-share-buttons-admin.php';
require_once AI_SHARE_BUTTONS_PATH . 'includes/class-ai-share-buttons-frontend.php';
require_once AI_SHARE_BUTTONS_PATH . 'includes/class-ai-share-buttons-analytics.php';
require_once AI_SHARE_BUTTONS_PATH . 'includes/functions.php';

// Plugin activation hook
register_activation_hook(__FILE__, array('AI_Share_Buttons_Installer', 'activate'));

// Plugin deactivation hook
register_deactivation_hook(__FILE__, array('AI_Share_Buttons_Installer', 'deactivate'));

// Plugin uninstall hook
register_uninstall_hook(__FILE__, array('AI_Share_Buttons_Installer', 'uninstall'));

// Initialize the plugin
function ai_share_buttons_init() {
    // Load plugin text domain
    load_plugin_textdomain('ai-share-buttons', false, dirname(AI_SHARE_BUTTONS_BASENAME) . '/languages');
    
    // Initialize main plugin class
    $plugin = AI_Share_Buttons::get_instance();
    $plugin->init();
}
add_action('plugins_loaded', 'ai_share_buttons_init');

// Global function for manual placement
function ai_share_buttons($args = array()) {
    $plugin = AI_Share_Buttons::get_instance();
    return $plugin->render_buttons($args);
}
<?php
/**
 * Plugin Name: AI-Enhanced Share Buttons
 * Plugin URI: https://yourwebsite.com/ai-share-buttons
 * Description: Enhanced share buttons with integrated AI services for content analysis and sharing optimization
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
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

// Include required files with error checking
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
    } else {
        if (function_exists('wp_die')) {
            wp_die(sprintf(__('Required file missing: %s', 'ai-share-buttons'), $file));
        } else {
            die('Required file missing: ' . $file);
        }
    }
}

// Plugin activation hook
register_activation_hook(__FILE__, array('AI_Share_Buttons_Installer', 'activate'));

// Plugin deactivation hook
register_deactivation_hook(__FILE__, array('AI_Share_Buttons_Installer', 'deactivate'));

// Plugin uninstall hook
register_uninstall_hook(__FILE__, array('AI_Share_Buttons_Installer', 'uninstall'));

// Initialize the plugin
function ai_share_buttons_init() {
    // Initialize main plugin class
    $plugin = AI_Share_Buttons::get_instance();
    $plugin->init();
}
add_action('plugins_loaded', 'ai_share_buttons_init');

// Load text domain
function ai_share_buttons_load_textdomain() {
    load_plugin_textdomain('ai-share-buttons', false, dirname(AI_SHARE_BUTTONS_BASENAME) . '/languages');
}
add_action('init', 'ai_share_buttons_load_textdomain');

// Global function for manual placement
if (!function_exists('ai_share_buttons')) {
    function ai_share_buttons($args = array()) {
        $plugin = AI_Share_Buttons::get_instance();
        echo $plugin->render_buttons($args);
    }
}
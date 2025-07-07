<?php
/**
 * Plugin installer and database setup
 */

class AI_Share_Buttons_Installer {
    
    public static function activate() {
        // Create database tables
        self::create_tables();
        
        // Set default options
        self::set_default_options();
        
        // Create upload directory for custom icons
        self::create_upload_directory();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public static function deactivate() {
        // Clean up scheduled events
        wp_clear_scheduled_hook('ai_share_buttons_daily_cleanup');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public static function uninstall() {
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
                self::remove_directory($plugin_upload_dir);
            }
        }
    }
    
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Click tracking table
        $clicks_table = $wpdb->prefix . 'ai_share_clicks';
        $sql_clicks = "CREATE TABLE $clicks_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            post_id bigint(20) unsigned NOT NULL,
            service_id varchar(50) NOT NULL,
            prompt_id varchar(50) DEFAULT NULL,
            user_ip varchar(45) DEFAULT NULL,
            user_agent text,
            referrer varchar(255) DEFAULT NULL,
            click_time datetime NOT NULL,
            response_time decimal(5,3) DEFAULT NULL,
            success tinyint(1) DEFAULT 1,
            PRIMARY KEY (id),
            KEY post_service (post_id, service_id),
            KEY click_time (click_time),
            KEY service_id (service_id)
        ) $charset_collate;";
        
        // Analytics summary table
        $analytics_table = $wpdb->prefix . 'ai_share_analytics';
        $sql_analytics = "CREATE TABLE $analytics_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            date_recorded date NOT NULL,
            service_id varchar(50) NOT NULL,
            prompt_id varchar(50) DEFAULT NULL,
            total_clicks int(11) DEFAULT 0,
            unique_clicks int(11) DEFAULT 0,
            avg_response_time decimal(5,3) DEFAULT NULL,
            success_rate decimal(5,2) DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY date_service_prompt (date_recorded, service_id, prompt_id),
            KEY date_recorded (date_recorded)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_clicks);
        dbDelta($sql_analytics);
    }
    
    private static function set_default_options() {
        $plugin = AI_Share_Buttons::get_instance();
        
        // Only set options if they don't exist
        if (get_option('ai_share_buttons_settings') === false) {
            add_option('ai_share_buttons_settings', $plugin->get_settings());
        }
        
        if (get_option('ai_share_buttons_networks') === false) {
            add_option('ai_share_buttons_networks', $plugin->get_networks());
        }
        
        if (get_option('ai_share_buttons_prompts') === false) {
            add_option('ai_share_buttons_prompts', $plugin->get_prompts());
        }
        
        // Schedule daily cleanup
        if (!wp_next_scheduled('ai_share_buttons_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'ai_share_buttons_daily_cleanup');
        }
    }
    
    private static function create_upload_directory() {
        $upload_dir = wp_upload_dir();
        $plugin_upload_dir = $upload_dir['basedir'] . '/ai-share-buttons';
        $icons_dir = $plugin_upload_dir . '/icons';
        
        if (!file_exists($plugin_upload_dir)) {
            wp_mkdir_p($plugin_upload_dir);
        }
        
        if (!file_exists($icons_dir)) {
            wp_mkdir_p($icons_dir);
        }
        
        // Add index.php for security
        $index_content = '<?php // Silence is golden';
        file_put_contents($plugin_upload_dir . '/index.php', $index_content);
        file_put_contents($icons_dir . '/index.php', $index_content);
    }
    
    private static function remove_directory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                self::remove_directory($path);
            } else {
                unlink($path);
            }
        }
        
        rmdir($dir);
    }
}
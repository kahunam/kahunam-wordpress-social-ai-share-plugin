<?php
/**
 * Analytics and click tracking
 */

class AI_Share_Buttons_Analytics {
    
    private $plugin;
    
    public function __construct() {
        $this->plugin = AI_Share_Buttons::get_instance();
    }
    
    public function init() {
        // Schedule cleanup
        add_action('ai_share_buttons_daily_cleanup', array($this, 'daily_cleanup'));
        
        // AJAX handlers for analytics
        add_action('wp_ajax_ai_share_get_analytics', array($this, 'ajax_get_analytics'));
        add_action('wp_ajax_ai_share_export_analytics', array($this, 'ajax_export_analytics'));
    }
    
    public function track_click($post_id, $service_id, $prompt_id = null) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'ai_share_clicks';
        
        // Get user data
        $user_ip = $this->get_user_ip();
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
        $referrer = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '';
        
        // Insert click record
        $result = $wpdb->insert(
            $table,
            array(
                'post_id' => $post_id,
                'service_id' => $service_id,
                'prompt_id' => $prompt_id,
                'user_ip' => $user_ip,
                'user_agent' => $user_agent,
                'referrer' => $referrer,
                'click_time' => current_time('mysql'),
                'success' => 1
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d')
        );
        
        if ($result) {
            // Update analytics summary
            $this->update_analytics_summary($service_id, $prompt_id);
        }
        
        return $result;
    }
    
    private function update_analytics_summary($service_id, $prompt_id = null) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'ai_share_analytics';
        $today = current_time('Y-m-d');
        
        // Check if record exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE date_recorded = %s AND service_id = %s AND " . 
            ($prompt_id ? "prompt_id = %s" : "prompt_id IS NULL"),
            $today, $service_id, $prompt_id
        ));
        
        if ($existing) {
            // Update existing record
            $wpdb->update(
                $table,
                array(
                    'total_clicks' => $existing->total_clicks + 1
                ),
                array(
                    'id' => $existing->id
                ),
                array('%d'),
                array('%d')
            );
        } else {
            // Insert new record
            $wpdb->insert(
                $table,
                array(
                    'date_recorded' => $today,
                    'service_id' => $service_id,
                    'prompt_id' => $prompt_id,
                    'total_clicks' => 1,
                    'unique_clicks' => 1
                ),
                array('%s', '%s', '%s', '%d', '%d')
            );
        }
    }
    
    public function get_analytics_data($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'date_start' => date('Y-m-d', strtotime('-30 days')),
            'date_end' => current_time('Y-m-d'),
            'service_id' => '',
            'prompt_id' => '',
            'group_by' => 'day',
            'limit' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table = $wpdb->prefix . 'ai_share_analytics';
        
        // Build query
        $where = array();
        $where[] = $wpdb->prepare("date_recorded >= %s", $args['date_start']);
        $where[] = $wpdb->prepare("date_recorded <= %s", $args['date_end']);
        
        if (!empty($args['service_id'])) {
            $where[] = $wpdb->prepare("service_id = %s", $args['service_id']);
        }
        
        if (!empty($args['prompt_id'])) {
            $where[] = $wpdb->prepare("prompt_id = %s", $args['prompt_id']);
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Group by clause
        $group_by = '';
        if ($args['group_by'] === 'service') {
            $group_by = 'GROUP BY service_id';
        } elseif ($args['group_by'] === 'prompt') {
            $group_by = 'GROUP BY service_id, prompt_id';
        }
        
        // Query
        $query = "SELECT * FROM $table WHERE $where_clause $group_by ORDER BY date_recorded DESC";
        
        if ($args['limit'] > 0) {
            $query .= $wpdb->prepare(" LIMIT %d", $args['limit']);
        }
        
        return $wpdb->get_results($query);
    }
    
    public function get_top_services($limit = 10, $days = 30) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'ai_share_analytics';
        $date_start = date('Y-m-d', strtotime("-$days days"));
        
        $query = $wpdb->prepare(
            "SELECT service_id, SUM(total_clicks) as clicks 
            FROM $table 
            WHERE date_recorded >= %s 
            GROUP BY service_id 
            ORDER BY clicks DESC 
            LIMIT %d",
            $date_start,
            $limit
        );
        
        return $wpdb->get_results($query);
    }
    
    public function get_top_prompts($limit = 10, $days = 30) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'ai_share_analytics';
        $date_start = date('Y-m-d', strtotime("-$days days"));
        
        $query = $wpdb->prepare(
            "SELECT prompt_id, service_id, SUM(total_clicks) as clicks 
            FROM $table 
            WHERE date_recorded >= %s AND prompt_id IS NOT NULL
            GROUP BY prompt_id, service_id 
            ORDER BY clicks DESC 
            LIMIT %d",
            $date_start,
            $limit
        );
        
        return $wpdb->get_results($query);
    }
    
    public function get_click_trends($days = 30) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'ai_share_analytics';
        $date_start = date('Y-m-d', strtotime("-$days days"));
        
        $query = $wpdb->prepare(
            "SELECT date_recorded, SUM(total_clicks) as clicks 
            FROM $table 
            WHERE date_recorded >= %s 
            GROUP BY date_recorded 
            ORDER BY date_recorded ASC",
            $date_start
        );
        
        return $wpdb->get_results($query);
    }
    
    public function daily_cleanup() {
        global $wpdb;
        
        $settings = $this->plugin->get_settings();
        
        // Only run if analytics is enabled
        if (!$settings['enable_analytics']) {
            return;
        }
        
        // Clean up old click records (keep last 90 days)
        $clicks_table = $wpdb->prefix . 'ai_share_clicks';
        $cutoff_date = date('Y-m-d H:i:s', strtotime('-90 days'));
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $clicks_table WHERE click_time < %s",
            $cutoff_date
        ));
        
        // Update unique clicks count in analytics
        $this->update_unique_clicks();
    }
    
    private function update_unique_clicks() {
        global $wpdb;
        
        $clicks_table = $wpdb->prefix . 'ai_share_clicks';
        $analytics_table = $wpdb->prefix . 'ai_share_analytics';
        
        // Get yesterday's date
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        // Update unique clicks for yesterday
        $query = "
            UPDATE $analytics_table a
            SET unique_clicks = (
                SELECT COUNT(DISTINCT user_ip)
                FROM $clicks_table c
                WHERE DATE(c.click_time) = a.date_recorded
                AND c.service_id = a.service_id
                AND (
                    (a.prompt_id IS NULL AND c.prompt_id IS NULL)
                    OR (a.prompt_id = c.prompt_id)
                )
            )
            WHERE a.date_recorded = %s
        ";
        
        $wpdb->query($wpdb->prepare($query, $yesterday));
    }
    
    private function get_user_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }
    
    public function ajax_get_analytics() {
        check_ajax_referer('ai_share_buttons_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'overview';
        $days = isset($_POST['days']) ? intval($_POST['days']) : 30;
        
        $data = array();
        
        switch ($type) {
            case 'overview':
                $data['trends'] = $this->get_click_trends($days);
                $data['top_services'] = $this->get_top_services(10, $days);
                $data['top_prompts'] = $this->get_top_prompts(10, $days);
                break;
                
            case 'services':
                $data['services'] = $this->get_top_services(50, $days);
                break;
                
            case 'prompts':
                $data['prompts'] = $this->get_top_prompts(50, $days);
                break;
        }
        
        wp_send_json_success($data);
    }
    
    public function ajax_export_analytics() {
        check_ajax_referer('ai_share_buttons_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'csv';
        $date_start = isset($_POST['date_start']) ? sanitize_text_field($_POST['date_start']) : date('Y-m-d', strtotime('-30 days'));
        $date_end = isset($_POST['date_end']) ? sanitize_text_field($_POST['date_end']) : current_time('Y-m-d');
        
        $data = $this->get_analytics_data(array(
            'date_start' => $date_start,
            'date_end' => $date_end
        ));
        
        if ($format === 'csv') {
            $csv_data = $this->generate_csv($data);
            
            wp_send_json_success(array(
                'filename' => 'ai-share-analytics-' . date('Y-m-d') . '.csv',
                'data' => $csv_data
            ));
        } else {
            wp_send_json_error('Invalid export format');
        }
    }
    
    private function generate_csv($data) {
        $csv = "Date,Service,Prompt,Total Clicks,Unique Clicks\n";
        
        foreach ($data as $row) {
            $csv .= sprintf(
                "%s,%s,%s,%d,%d\n",
                $row->date_recorded,
                $row->service_id,
                $row->prompt_id ?: 'N/A',
                $row->total_clicks,
                $row->unique_clicks
            );
        }
        
        return $csv;
    }
}
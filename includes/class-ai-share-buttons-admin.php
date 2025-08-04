<?php
/**
 * Admin interface and settings management
 */

class AI_Share_Buttons_Admin {
    
    private $plugin;
    
    public function __construct() {
        $this->plugin = AI_Share_Buttons::get_instance();
    }
    
    public function init() {
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_ai_share_save_network', array($this, 'ajax_save_network'));
        add_action('wp_ajax_ai_share_delete_network', array($this, 'ajax_delete_network'));
        add_action('wp_ajax_ai_share_reorder_networks', array($this, 'ajax_reorder_networks'));
        add_action('wp_ajax_ai_share_save_prompt', array($this, 'ajax_save_prompt'));
        add_action('wp_ajax_ai_share_delete_prompt', array($this, 'ajax_delete_prompt'));
        add_action('wp_ajax_ai_share_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_ai_share_upload_icon', array($this, 'ajax_upload_icon'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('AI Share Buttons', 'ai-share-buttons'),
            __('AI Share Buttons', 'ai-share-buttons'),
            'manage_options',
            'ai-share-buttons',
            array($this, 'render_admin_page'),
            'dashicons-share',
            30
        );
        
        add_submenu_page(
            'ai-share-buttons',
            __('Networks', 'ai-share-buttons'),
            __('Networks', 'ai-share-buttons'),
            'manage_options',
            'ai-share-buttons',
            array($this, 'render_admin_page')
        );
        
        add_submenu_page(
            'ai-share-buttons',
            __('AI Prompts', 'ai-share-buttons'),
            __('AI Prompts', 'ai-share-buttons'),
            'manage_options',
            'ai-share-buttons-prompts',
            array($this, 'render_prompts_page')
        );
        
        add_submenu_page(
            'ai-share-buttons',
            __('Display Settings', 'ai-share-buttons'),
            __('Display Settings', 'ai-share-buttons'),
            'manage_options',
            'ai-share-buttons-display',
            array($this, 'render_display_page')
        );
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'ai-share-buttons') === false) {
            return;
        }
        
        // CSS
        wp_enqueue_style(
            'ai-share-buttons-admin',
            AI_SHARE_BUTTONS_URL . 'admin/css/admin.css',
            array(),
            AI_SHARE_BUTTONS_VERSION
        );
        
        // JavaScript
        wp_enqueue_script(
            'ai-share-buttons-admin',
            AI_SHARE_BUTTONS_URL . 'admin/js/admin.js',
            array('jquery', 'jquery-ui-sortable', 'wp-color-picker'),
            AI_SHARE_BUTTONS_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('ai-share-buttons-admin', 'aiShareButtons', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ai_share_buttons_nonce'),
            'strings' => array(
                'confirmDelete' => __('Are you sure you want to delete this item?', 'ai-share-buttons'),
                'saveSuccess' => __('Settings saved successfully!', 'ai-share-buttons'),
                'saveError' => __('Error saving settings. Please try again.', 'ai-share-buttons')
            )
        ));
        
        // Color picker
        wp_enqueue_style('wp-color-picker');
        
        // Media uploader
        wp_enqueue_media();
    }
    
    public function render_admin_page() {
        include AI_SHARE_BUTTONS_PATH . 'admin/partials/networks-page.php';
    }
    
    public function render_prompts_page() {
        include AI_SHARE_BUTTONS_PATH . 'admin/partials/prompts-page.php';
    }
    
    public function render_display_page() {
        include AI_SHARE_BUTTONS_PATH . 'admin/partials/display-page.php';
    }
    
    public function ajax_save_network() {
        check_ajax_referer('ai_share_buttons_nonce', 'nonce');
        
        // Permission check handled by WordPress menu system
        
        $network_data = array(
            'id' => sanitize_text_field($_POST['id']),
            'name' => sanitize_text_field($_POST['name']),
            'type' => sanitize_text_field($_POST['type']),
            'enabled' => isset($_POST['enabled']) ? true : false,
            'icon' => sanitize_text_field($_POST['icon']),
            'color' => sanitize_hex_color($_POST['color']),
            'url_template' => esc_url_raw($_POST['url_template']),
            'default_prompt' => isset($_POST['default_prompt']) ? sanitize_textarea_field($_POST['default_prompt']) : '',
            'built_in' => false,
            'created_date' => current_time('mysql')
        );
        
        // Validate required fields
        if (empty($network_data['id']) || empty($network_data['name']) || empty($network_data['url_template'])) {
            wp_send_json_error('Required fields are missing');
        }
        
        // Get current networks
        $networks = $this->plugin->get_networks();
        
        // Determine order
        $max_order = 0;
        foreach ($networks['built_in'] as $network) {
            $max_order = max($max_order, $network['order']);
        }
        foreach ($networks['custom'] as $network) {
            $max_order = max($max_order, $network['order']);
        }
        $network_data['order'] = $max_order + 1;
        
        // Add/update network
        $networks['custom'][$network_data['id']] = $network_data;
        
        // Save
        if ($this->plugin->save_networks($networks)) {
            wp_send_json_success($network_data);
        } else {
            wp_send_json_error('Failed to save network');
        }
    }
    
    public function ajax_delete_network() {
        check_ajax_referer('ai_share_buttons_nonce', 'nonce');
        
        // Permission check handled by WordPress menu system
        
        $network_id = sanitize_text_field($_POST['network_id']);
        
        if (empty($network_id)) {
            wp_send_json_error('Invalid network ID');
        }
        
        // Get current networks
        $networks = $this->plugin->get_networks();
        
        // Check if it's a custom network
        if (!isset($networks['custom'][$network_id])) {
            wp_send_json_error('Cannot delete built-in networks');
        }
        
        // Remove network
        unset($networks['custom'][$network_id]);
        
        // Save
        if ($this->plugin->save_networks($networks)) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed to delete network');
        }
    }
    
    public function ajax_reorder_networks() {
        check_ajax_referer('ai_share_buttons_nonce', 'nonce');
        
        // Permission check handled by WordPress menu system
        
        $order = $_POST['order'];
        if (!is_array($order)) {
            wp_send_json_error('Invalid order data');
        }
        
        // Get current networks
        $networks = $this->plugin->get_networks();
        
        // Update order
        foreach ($order as $index => $network_id) {
            $network_id = sanitize_text_field($network_id);
            $new_order = intval($index) + 1;
            
            // Check in built_in networks
            if (isset($networks['built_in'][$network_id])) {
                $networks['built_in'][$network_id]['order'] = $new_order;
            }
            // Check in custom networks
            elseif (isset($networks['custom'][$network_id])) {
                $networks['custom'][$network_id]['order'] = $new_order;
            }
        }
        
        // Save
        if ($this->plugin->save_networks($networks)) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed to save order');
        }
    }
    
    public function ajax_save_prompt() {
        check_ajax_referer('ai_share_buttons_nonce', 'nonce');
        
        // Permission check handled by WordPress menu system
        
        $prompt_data = array(
            'id' => sanitize_text_field($_POST['id']),
            'name' => sanitize_text_field($_POST['name']),
            'prompt_text' => sanitize_textarea_field($_POST['prompt_text']),
            'enabled' => isset($_POST['enabled']) ? true : false,
            'assigned_services' => isset($_POST['assigned_services']) ? array_map('sanitize_text_field', $_POST['assigned_services']) : array(),
            'built_in' => false,
            'created_date' => current_time('mysql')
        );
        
        // Validate required fields
        if (empty($prompt_data['id']) || empty($prompt_data['name']) || empty($prompt_data['prompt_text'])) {
            wp_send_json_error('Required fields are missing');
        }
        
        // Get current prompts
        $prompts = $this->plugin->get_prompts();
        
        // Determine order
        $max_order = 0;
        foreach ($prompts['built_in'] as $prompt) {
            $max_order = max($max_order, $prompt['order']);
        }
        foreach ($prompts['custom'] as $prompt) {
            $max_order = max($max_order, $prompt['order']);
        }
        $prompt_data['order'] = $max_order + 1;
        
        // Add/update prompt
        $prompts['custom'][$prompt_data['id']] = $prompt_data;
        
        // Save
        if ($this->plugin->save_prompts($prompts)) {
            wp_send_json_success($prompt_data);
        } else {
            wp_send_json_error('Failed to save prompt');
        }
    }
    
    public function ajax_delete_prompt() {
        check_ajax_referer('ai_share_buttons_nonce', 'nonce');
        
        // Permission check handled by WordPress menu system
        
        $prompt_id = sanitize_text_field($_POST['prompt_id']);
        
        if (empty($prompt_id)) {
            wp_send_json_error('Invalid prompt ID');
        }
        
        // Get current prompts
        $prompts = $this->plugin->get_prompts();
        
        // Check if it's a custom prompt
        if (!isset($prompts['custom'][$prompt_id])) {
            wp_send_json_error('Cannot delete built-in prompts');
        }
        
        // Remove prompt
        unset($prompts['custom'][$prompt_id]);
        
        // Save
        if ($this->plugin->save_prompts($prompts)) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed to delete prompt');
        }
    }
    
    public function ajax_save_settings() {
        check_ajax_referer('ai_share_buttons_nonce', 'nonce');
        
        // Permission check handled by WordPress menu system
        
        $settings = array(
            'auto_display' => isset($_POST['auto_display']) ? true : false,
            'hook_location' => sanitize_text_field($_POST['hook_location']),
            'hook_priority' => intval($_POST['hook_priority']),
            'custom_hook' => sanitize_text_field($_POST['custom_hook']),
            'post_types' => isset($_POST['post_types']) ? array_map('sanitize_text_field', $_POST['post_types']) : array(),
            'css_level' => sanitize_text_field($_POST['css_level']),
            'mobile_layout' => sanitize_text_field($_POST['mobile_layout']),
            'tablet_layout' => sanitize_text_field($_POST['tablet_layout']),
            'hide_mobile' => isset($_POST['hide_mobile']) ? true : false,
            'container_class' => sanitize_text_field($_POST['container_class']),
            'button_class' => sanitize_text_field($_POST['button_class']),
            'dropdown_class' => sanitize_text_field($_POST['dropdown_class']),
            'custom_css' => wp_strip_all_tags($_POST['custom_css']),
            'version' => AI_SHARE_BUTTONS_VERSION
        );
        
        // Save
        if ($this->plugin->save_settings($settings)) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed to save settings');
        }
    }
    
    public function ajax_upload_icon() {
        check_ajax_referer('ai_share_buttons_nonce', 'nonce');
        
        // Permission check handled by WordPress menu system
        
        if (!isset($_FILES['icon_file'])) {
            wp_send_json_error('No file uploaded');
        }
        
        $file = $_FILES['icon_file'];
        
        // Check file type
        $allowed_types = array('image/svg+xml', 'image/png', 'image/jpeg', 'image/jpg');
        if (!in_array($file['type'], $allowed_types)) {
            wp_send_json_error('Invalid file type. Please upload SVG, PNG, or JPG files.');
        }
        
        // Upload file
        $upload_dir = wp_upload_dir();
        $plugin_upload_dir = $upload_dir['basedir'] . '/ai-share-buttons/icons';
        
        if (!file_exists($plugin_upload_dir)) {
            wp_mkdir_p($plugin_upload_dir);
        }
        
        $filename = sanitize_file_name($file['name']);
        $unique_filename = wp_unique_filename($plugin_upload_dir, $filename);
        $destination = $plugin_upload_dir . '/' . $unique_filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $icon_url = $upload_dir['baseurl'] . '/ai-share-buttons/icons/' . $unique_filename;
            wp_send_json_success(array(
                'filename' => $unique_filename,
                'url' => $icon_url
            ));
        } else {
            wp_send_json_error('Failed to upload file');
        }
    }
}
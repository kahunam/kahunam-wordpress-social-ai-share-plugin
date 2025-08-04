<?php
/**
 * Frontend display and functionality
 */

class AI_Share_Buttons_Frontend {
    
    private $plugin;
    private $settings;
    
    public function __construct() {
        $this->plugin = AI_Share_Buttons::get_instance();
        $this->settings = $this->plugin->get_settings();
    }
    
    public function init() {
        // Enqueue frontend assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        
        // Also check for shortcode and load assets if needed
        add_action('wp', array($this, 'check_for_shortcode'));
        
        // Auto display hooks
        if ($this->settings['auto_display']) {
            $hook = $this->settings['hook_location'];
            $priority = $this->settings['hook_priority'];
            
            if ($hook === 'custom' && !empty($this->settings['custom_hook'])) {
                $hook = $this->settings['custom_hook'];
            }
            
            if ($hook === 'the_content') {
                add_filter('the_content', array($this, 'auto_display_buttons'), $priority);
            } else {
                add_action($hook, array($this, 'auto_display_buttons_action'), $priority);
            }
        }
    }
    
    public function check_for_shortcode() {
        global $post;
        
        if ($post && has_shortcode($post->post_content, 'ai_share_buttons')) {
            $this->force_enqueue_assets();
        }
    }
    
    private function force_enqueue_assets() {
        global $post;
        
        // CSS
        if ($this->settings['css_level'] !== 'none') {
            wp_enqueue_style(
                'ai-share-buttons-frontend',
                AI_SHARE_BUTTONS_URL . 'assets/css/frontend.css',
                array(),
                AI_SHARE_BUTTONS_VERSION
            );
            
            // Add custom CSS if any
            if (!empty($this->settings['custom_css'])) {
                wp_add_inline_style('ai-share-buttons-frontend', $this->settings['custom_css']);
            }
        }
        
        // JavaScript
        wp_enqueue_script(
            'ai-share-buttons-frontend',
            AI_SHARE_BUTTONS_URL . 'assets/js/frontend.js',
            array('jquery'),
            AI_SHARE_BUTTONS_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('ai-share-buttons-frontend', 'aiShareButtonsFront', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ai_share_buttons_nonce'),
            'postId' => $post ? $post->ID : 0,
            'strings' => array(
                'copySuccess' => __('Link copied to clipboard!', 'ai-share-buttons'),
                'copyError' => __('Failed to copy link', 'ai-share-buttons')
            )
        ));
    }
    
    public function enqueue_frontend_assets() {
        // Enqueue on singular posts/pages or if shortcode is being used
        global $post;
        
        if (is_singular()) {
            // Check if current post type is enabled
            $post_type = get_post_type();
            if (!in_array($post_type, $this->settings['post_types'])) {
                return;
            }
        } elseif (!$post || !has_shortcode($post->post_content, 'ai_share_buttons')) {
            // If not singular and no shortcode, don't load
            return;
        }
        
        // CSS
        if ($this->settings['css_level'] !== 'none') {
            wp_enqueue_style(
                'ai-share-buttons-frontend',
                AI_SHARE_BUTTONS_URL . 'assets/css/frontend.css',
                array(),
                AI_SHARE_BUTTONS_VERSION
            );
            
            // Add custom CSS if any
            if (!empty($this->settings['custom_css'])) {
                wp_add_inline_style('ai-share-buttons-frontend', $this->settings['custom_css']);
            }
        }
        
        // JavaScript
        wp_enqueue_script(
            'ai-share-buttons-frontend',
            AI_SHARE_BUTTONS_URL . 'assets/js/frontend.js',
            array('jquery'),
            AI_SHARE_BUTTONS_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('ai-share-buttons-frontend', 'aiShareButtonsFront', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ai_share_buttons_nonce'),
            'postId' => get_the_ID(),
            'strings' => array(
                'copySuccess' => __('Link copied to clipboard!', 'ai-share-buttons'),
                'copyError' => __('Failed to copy link', 'ai-share-buttons')
            )
        ));
    }
    
    public function auto_display_buttons($content = '') {
        // Check if we should display
        if (!is_singular() || !in_the_loop() || !is_main_query()) {
            return $content;
        }
        
        // Check post type
        $post_type = get_post_type();
        if (!in_array($post_type, $this->settings['post_types'])) {
            return $content;
        }
        
        // Get buttons HTML
        $buttons_html = $this->render_buttons();
        
        // Add to content
        if ($this->settings['hook_location'] === 'the_content') {
            // Prepend or append based on settings
            return $buttons_html . $content . $buttons_html;
        }
        
        return $content;
    }
    
    public function auto_display_buttons_action() {
        echo $this->render_buttons();
    }
    
    public function render_buttons($args = array()) {
        // Get post ID from args or current post
        $post_id = isset($args['post_id']) ? intval($args['post_id']) : get_the_ID();
        
        // If still no post ID, try to get from global post
        if (!$post_id) {
            global $post;
            $post_id = $post ? $post->ID : 0;
        }
        
        // Return empty if no valid post ID
        if (!$post_id) {
            return '';
        }
        
        // Get networks and prompts
        $networks = $this->plugin->get_networks();
        $prompts = $this->plugin->get_prompts();
        
        // Get enabled networks sorted by order
        $enabled_networks = array();
        
        // Merge built-in and custom networks
        $all_networks = array_merge($networks['built_in'], $networks['custom']);
        
        // Filter and sort
        foreach ($all_networks as $network) {
            if ($network['enabled']) {
                $enabled_networks[] = $network;
            }
        }
        
        // Sort by order
        usort($enabled_networks, function($a, $b) {
            return $a['order'] - $b['order'];
        });
        
        // Start output buffering
        ob_start();
        ?>
        <div class="<?php echo esc_attr($this->settings['container_class']); ?>" data-post-id="<?php echo esc_attr($post_id); ?>">
            <?php foreach ($enabled_networks as $network): ?>
                <?php $this->render_button($network, $prompts, $post_id); ?>
            <?php endforeach; ?>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    private function render_button($network, $prompts, $post_id = null) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        $button_class = $this->settings['button_class'];
        $network_type = $network['type'];
        $network_id = $network['id'];
        
        // Get icon URL
        $icon_url = $this->get_icon_url($network['icon']);
        
        // Special handling for utility buttons
        if ($network_type === 'utility') {
            $this->render_utility_button($network, $icon_url, $post_id);
            return;
        }
        
        // Get share URL
        if ($network_type === 'ai') {
            // For AI services, we'll use JavaScript to handle prompt selection
            $share_url = '#';
            $onclick = 'return false;';
        } else {
            $share_url = $this->build_share_url($network['url_template'], '', $post_id);
            $onclick = '';
        }
        
        // Check if this service has prompts
        $has_prompts = false;
        $service_prompts = array();
        
        if ($network_type === 'ai') {
            foreach ($prompts['built_in'] as $prompt) {
                if ($prompt['enabled'] && in_array($network_id, $prompt['assigned_services'])) {
                    $has_prompts = true;
                    $service_prompts[] = $prompt;
                }
            }
            foreach ($prompts['custom'] as $prompt) {
                if ($prompt['enabled'] && in_array($network_id, $prompt['assigned_services'])) {
                    $has_prompts = true;
                    $service_prompts[] = $prompt;
                }
            }
            
            // Sort prompts by order
            usort($service_prompts, function($a, $b) {
                return $a['order'] - $b['order'];
            });
        }
        ?>
        <div class="<?php echo esc_attr($button_class); ?> <?php echo esc_attr($button_class . '-' . $network_id); ?> <?php echo $has_prompts ? 'has-dropdown' : ''; ?>" 
             data-network="<?php echo esc_attr($network_id); ?>"
             data-type="<?php echo esc_attr($network_type); ?>">
            <a href="<?php echo esc_url($share_url); ?>" 
               class="<?php echo esc_attr($button_class); ?>-link"
               target="_blank"
               rel="noopener noreferrer"
               onclick="<?php echo $onclick; ?>"
               style="background-color: <?php echo esc_attr($network['color']); ?>">
                <span class="<?php echo esc_attr($button_class); ?>-icon">
                    <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($network['name']); ?>" width="20" height="20">
                </span>
                <span class="<?php echo esc_attr($button_class); ?>-text">
                    <?php echo esc_html($network['name']); ?>
                </span>
            </a>
            
            <?php if ($has_prompts): ?>
                <div class="<?php echo esc_attr($this->settings['dropdown_class']); ?>" style="display: none;">
                    <div class="<?php echo esc_attr($this->settings['dropdown_class']); ?>-inner">
                        <?php foreach ($service_prompts as $prompt): ?>
                            <a href="#" 
                               class="<?php echo esc_attr($this->settings['dropdown_class']); ?>-item"
                               data-prompt-id="<?php echo esc_attr($prompt['id']); ?>"
                               data-prompt-text="<?php echo esc_attr($prompt['prompt_text']); ?>"
                               data-url-template="<?php echo esc_attr($network['url_template']); ?>">
                                <?php echo esc_html($prompt['name']); ?>
                            </a>
                        <?php endforeach; ?>
                        
                        <?php if (!empty($network['default_prompt'])): ?>
                            <div class="<?php echo esc_attr($this->settings['dropdown_class']); ?>-divider"></div>
                            <a href="#" 
                               class="<?php echo esc_attr($this->settings['dropdown_class']); ?>-item"
                               data-prompt-id="default"
                               data-prompt-text="<?php echo esc_attr($network['default_prompt']); ?>"
                               data-url-template="<?php echo esc_attr($network['url_template']); ?>">
                                <?php _e('Default prompt', 'ai-share-buttons'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    private function render_utility_button($network, $icon_url, $post_id = null) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        $button_class = $this->settings['button_class'];
        $network_id = $network['id'];
        
        if ($network_id === 'copy'): ?>
            <div class="<?php echo esc_attr($button_class); ?> <?php echo esc_attr($button_class . '-' . $network_id); ?>" 
                 data-network="<?php echo esc_attr($network_id); ?>"
                 data-type="utility">
                <a href="#" 
                   class="<?php echo esc_attr($button_class); ?>-link"
                   onclick="return aiShareButtons.copyLink(this);"
                   style="background-color: <?php echo esc_attr($network['color']); ?>">
                    <span class="<?php echo esc_attr($button_class); ?>-icon">
                        <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($network['name']); ?>" width="20" height="20">
                    </span>
                    <span class="<?php echo esc_attr($button_class); ?>-text">
                        <?php echo esc_html($network['name']); ?>
                    </span>
                </a>
            </div>
        <?php elseif ($network_id === 'print'): ?>
            <div class="<?php echo esc_attr($button_class); ?> <?php echo esc_attr($button_class . '-' . $network_id); ?>" 
                 data-network="<?php echo esc_attr($network_id); ?>"
                 data-type="utility">
                <a href="#" 
                   class="<?php echo esc_attr($button_class); ?>-link"
                   onclick="window.print(); return false;"
                   style="background-color: <?php echo esc_attr($network['color']); ?>">
                    <span class="<?php echo esc_attr($button_class); ?>-icon">
                        <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($network['name']); ?>" width="20" height="20">
                    </span>
                    <span class="<?php echo esc_attr($button_class); ?>-text">
                        <?php echo esc_html($network['name']); ?>
                    </span>
                </a>
            </div>
        <?php elseif ($network_id === 'email'): ?>
            <div class="<?php echo esc_attr($button_class); ?> <?php echo esc_attr($button_class . '-' . $network_id); ?>" 
                 data-network="<?php echo esc_attr($network_id); ?>"
                 data-type="utility">
                <a href="<?php echo esc_url($this->build_share_url($network['url_template'], '', $post_id)); ?>" 
                   class="<?php echo esc_attr($button_class); ?>-link"
                   style="background-color: <?php echo esc_attr($network['color']); ?>">
                    <span class="<?php echo esc_attr($button_class); ?>-icon">
                        <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($network['name']); ?>" width="20" height="20">
                    </span>
                    <span class="<?php echo esc_attr($button_class); ?>-text">
                        <?php echo esc_html($network['name']); ?>
                    </span>
                </a>
            </div>
        <?php endif;
    }
    
    private function get_icon_url($icon_filename) {
        // Check if it's a custom uploaded icon
        if (strpos($icon_filename, 'http') === 0) {
            return $icon_filename;
        }
        
        // Check in uploads directory first
        $upload_dir = wp_upload_dir();
        $custom_icon_path = $upload_dir['basedir'] . '/ai-share-buttons/icons/' . $icon_filename;
        $custom_icon_url = $upload_dir['baseurl'] . '/ai-share-buttons/icons/' . $icon_filename;
        
        if (file_exists($custom_icon_path)) {
            return $custom_icon_url;
        }
        
        // Fall back to plugin's icon directory
        return AI_SHARE_BUTTONS_URL . 'assets/icons/' . $icon_filename;
    }
    
    private function build_share_url($template, $prompt_text = '', $post_id = null) {
        $processed = $this->plugin->process_template_variables($template, $post_id);
        
        // Handle encoded prompt
        if (!empty($prompt_text) && strpos($processed, '{encoded_prompt}') !== false) {
            $prompt_processed = $this->plugin->process_template_variables($prompt_text, $post_id);
            $encoded_prompt = urlencode($prompt_processed);
            $processed = str_replace('{encoded_prompt}', $encoded_prompt, $processed);
        }
        
        return $processed;
    }
    
    public function build_ai_prompt_url($network_id, $prompt_id) {
        $networks = $this->plugin->get_networks();
        $prompts = $this->plugin->get_prompts();
        
        // Find network
        $network = null;
        if (isset($networks['built_in'][$network_id])) {
            $network = $networks['built_in'][$network_id];
        } elseif (isset($networks['custom'][$network_id])) {
            $network = $networks['custom'][$network_id];
        }
        
        if (!$network) {
            return '#';
        }
        
        // Find prompt text
        $prompt_text = '';
        if ($prompt_id === 'default' && !empty($network['default_prompt'])) {
            $prompt_text = $network['default_prompt'];
        } else {
            // Search in prompts
            if (isset($prompts['built_in'][$prompt_id])) {
                $prompt_text = $prompts['built_in'][$prompt_id]['prompt_text'];
            } elseif (isset($prompts['custom'][$prompt_id])) {
                $prompt_text = $prompts['custom'][$prompt_id]['prompt_text'];
            }
        }
        
        if (empty($prompt_text)) {
            return '#';
        }
        
        return $this->build_share_url($network['url_template'], $prompt_text);
    }
}
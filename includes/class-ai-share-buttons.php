<?php
/**
 * Main plugin class
 */

class AI_Share_Buttons {
    
    private static $instance = null;
    private $admin;
    private $frontend;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Constructor is private for singleton pattern
    }
    
    public function init() {
        // Load dependencies
        $this->load_dependencies();
        
        // Initialize hooks
        $this->init_hooks();
        
        // Ensure default data is set
        $this->ensure_default_data();
        
        // Initialize components
        if (is_admin()) {
            $this->admin = new AI_Share_Buttons_Admin();
            $this->admin->init();
        } else {
            $this->frontend = new AI_Share_Buttons_Frontend();
            $this->frontend->init();
        }
    }
    
    private function load_dependencies() {
        // Dependencies are loaded in main plugin file
    }
    
    private function init_hooks() {
        // Register shortcode
        add_shortcode('ai_share_buttons', array($this, 'shortcode_handler'));
        
        // Register widget
        add_action('widgets_init', array($this, 'register_widget'));
    }
    
    private function ensure_default_data() {
        // Ensure networks have default data
        $networks = get_option('ai_share_buttons_networks');
        if (empty($networks) || empty($networks['built_in'])) {
            $default_networks = self::get_default_networks();
            update_option('ai_share_buttons_networks', $default_networks);
        }
        
        // Ensure prompts have default data
        $prompts = get_option('ai_share_buttons_prompts');
        if (empty($prompts) || empty($prompts['built_in'])) {
            $default_prompts = self::get_default_prompts();
            update_option('ai_share_buttons_prompts', $default_prompts);
        }
    }
    
    public function shortcode_handler($atts) {
        $args = shortcode_atts(array(
            'services' => '',
            'style' => 'default',
            'align' => 'left',
            'post_id' => get_the_ID()
        ), $atts);
        
        // Ensure frontend is initialized
        if (!$this->frontend) {
            $this->frontend = new AI_Share_Buttons_Frontend();
            $this->frontend->init();
        }
        
        return $this->render_buttons($args);
    }
    
    public function register_widget() {
        // Widget registration will be implemented
    }
    
    public function render_buttons($args = array()) {
        if (!$this->frontend) {
            $this->frontend = new AI_Share_Buttons_Frontend();
            $this->frontend->init();
        }
        
        return $this->frontend->render_buttons($args);
    }
    
    public function get_networks() {
        $networks = get_option('ai_share_buttons_networks', self::get_default_networks());
        return $networks;
    }
    
    public function save_networks($networks) {
        $sanitized = $this->sanitize_networks($networks);
        return update_option('ai_share_buttons_networks', $sanitized);
    }
    
    public function get_prompts() {
        $prompts = get_option('ai_share_buttons_prompts', self::get_default_prompts());
        return $prompts;
    }
    
    public function save_prompts($prompts) {
        $sanitized = $this->sanitize_prompts($prompts);
        return update_option('ai_share_buttons_prompts', $sanitized);
    }
    
    public function get_settings() {
        $settings = get_option('ai_share_buttons_settings', self::get_default_settings());
        return $settings;
    }
    
    public function save_settings($settings) {
        $sanitized = $this->sanitize_settings($settings);
        return update_option('ai_share_buttons_settings', $sanitized);
    }
    
    private static function get_default_networks() {
        return array(
            'built_in' => array(
                'chatgpt' => array(
                    'id' => 'chatgpt',
                    'name' => 'ChatGPT',
                    'type' => 'ai',
                    'enabled' => true,
                    'order' => 1,
                    'icon' => 'chatgpt.svg',
                    'color' => '#10a37f',
                    'url_template' => 'https://chat.openai.com/?q={encoded_prompt}',
                    'default_prompt' => 'Visit this URL and summarize this post for me: {POST_URL}',
                    'built_in' => true
                ),
                'perplexity' => array(
                    'id' => 'perplexity',
                    'name' => 'Perplexity',
                    'type' => 'ai',
                    'enabled' => true,
                    'order' => 2,
                    'icon' => 'perplexity.svg',
                    'color' => '#6f42c1',
                    'url_template' => 'https://www.perplexity.ai/search/new?q={encoded_prompt}',
                    'default_prompt' => 'Visit this URL and summarize the post for me: {POST_URL}',
                    'built_in' => true
                ),
                'gemini' => array(
                    'id' => 'gemini',
                    'name' => 'Gemini',
                    'type' => 'ai',
                    'enabled' => true,
                    'order' => 3,
                    'icon' => 'gemini.svg',
                    'color' => '#4285F4',
                    'url_template' => 'https://gemini.google.com/app?q={encoded_prompt}',
                    'default_prompt' => 'Summarize this post: {POST_URL}',
                    'built_in' => true
                ),
                'claude' => array(
                    'id' => 'claude',
                    'name' => 'Claude',
                    'type' => 'ai',
                    'enabled' => true,
                    'order' => 4,
                    'icon' => 'claude.svg',
                    'color' => '#D4A373',
                    'url_template' => 'https://claude.ai/new?q={encoded_prompt}',
                    'default_prompt' => 'Visit this URL and summarize this post: {POST_URL}',
                    'built_in' => true
                ),
                'grok' => array(
                    'id' => 'grok',
                    'name' => 'Grok',
                    'type' => 'ai',
                    'enabled' => true,
                    'order' => 5,
                    'icon' => 'grok.svg',
                    'color' => '#1c1c1e',
                    'url_template' => 'https://x.com/i/grok?text={encoded_prompt}',
                    'default_prompt' => 'Summarize this URL: {POST_URL}',
                    'built_in' => true
                ),
                'facebook' => array(
                    'id' => 'facebook',
                    'name' => 'Facebook',
                    'type' => 'social',
                    'enabled' => false,
                    'order' => 6,
                    'icon' => 'facebook.svg',
                    'color' => '#1877F2',
                    'url_template' => 'https://www.facebook.com/sharer/sharer.php?u={POST_URL}',
                    'built_in' => true
                ),
                'twitter' => array(
                    'id' => 'twitter',
                    'name' => 'X',
                    'type' => 'social',
                    'enabled' => true,
                    'order' => 7,
                    'icon' => 'twitter.svg',
                    'color' => '#000000',
                    'url_template' => 'https://x.com/intent/tweet?text={POST_TITLE}&url={POST_URL}',
                    'built_in' => true
                ),
                'linkedin' => array(
                    'id' => 'linkedin',
                    'name' => 'LinkedIn',
                    'type' => 'social',
                    'enabled' => true,
                    'order' => 8,
                    'icon' => 'linkedin.svg',
                    'color' => '#0077b5',
                    'url_template' => 'https://www.linkedin.com/sharing/share-offsite/?url={POST_URL}',
                    'built_in' => true
                ),
                'whatsapp' => array(
                    'id' => 'whatsapp',
                    'name' => 'WhatsApp',
                    'type' => 'social',
                    'enabled' => true,
                    'order' => 9,
                    'icon' => 'whatsapp.svg',
                    'color' => '#25D366',
                    'url_template' => 'https://wa.me/?text={POST_TITLE}%20-%20{POST_URL}',
                    'built_in' => true
                ),
                'telegram' => array(
                    'id' => 'telegram',
                    'name' => 'Telegram',
                    'type' => 'social',
                    'enabled' => true,
                    'order' => 10,
                    'icon' => 'telegram.svg',
                    'color' => '#0088cc',
                    'url_template' => 'https://t.me/share/url?url={POST_URL}&text={POST_TITLE}',
                    'built_in' => true
                ),
                'reddit' => array(
                    'id' => 'reddit',
                    'name' => 'Reddit',
                    'type' => 'social',
                    'enabled' => true,
                    'order' => 11,
                    'icon' => 'reddit.svg',
                    'color' => '#FF4500',
                    'url_template' => 'https://reddit.com/submit?url={POST_URL}&title={POST_TITLE}',
                    'built_in' => true
                ),
                'pinterest' => array(
                    'id' => 'pinterest',
                    'name' => 'Pinterest',
                    'type' => 'social',
                    'enabled' => true,
                    'order' => 12,
                    'icon' => 'pinterest.svg',
                    'color' => '#BD081C',
                    'url_template' => 'https://pinterest.com/pin/create/button/?url={POST_URL}&description={POST_TITLE}',
                    'built_in' => true
                ),
                'email' => array(
                    'id' => 'email',
                    'name' => 'Email',
                    'type' => 'utility',
                    'enabled' => true,
                    'order' => 13,
                    'icon' => 'email.svg',
                    'color' => '#7f7f7f',
                    'url_template' => 'mailto:?subject={POST_TITLE}&body={POST_URL}',
                    'built_in' => true
                ),
                'copy' => array(
                    'id' => 'copy',
                    'name' => 'Copy Link',
                    'type' => 'utility',
                    'enabled' => true,
                    'order' => 14,
                    'icon' => 'copy.svg',
                    'color' => '#7f7f7f',
                    'url_template' => '#',
                    'built_in' => true
                ),
                'print' => array(
                    'id' => 'print',
                    'name' => 'Print',
                    'type' => 'utility',
                    'enabled' => true,
                    'order' => 15,
                    'icon' => 'print.svg',
                    'color' => '#7f7f7f',
                    'url_template' => '#',
                    'built_in' => true
                )
            ),
            'custom' => array()
        );
    }
    
    private static function get_default_prompts() {
        return array(
            'built_in' => array(
                'summarize' => array(
                    'id' => 'summarize',
                    'name' => 'Summarize this article',
                    'prompt_text' => 'Visit this URL and summarize this post for me: {POST_URL}',
                    'enabled' => true,
                    'order' => 1,
                    'assigned_services' => array('chatgpt', 'perplexity', 'gemini', 'claude', 'grok'),
                    'built_in' => true
                ),
                'extract_points' => array(
                    'id' => 'extract_points',
                    'name' => 'Extract key stats and bullet points',
                    'prompt_text' => 'Extract key statistics and bullet points from: {POST_URL}',
                    'enabled' => true,
                    'order' => 2,
                    'assigned_services' => array('chatgpt', 'perplexity', 'gemini', 'claude'),
                    'built_in' => true
                ),
                'presentation_slides' => array(
                    'id' => 'presentation_slides',
                    'name' => 'Turn into presentation slides',
                    'prompt_text' => 'Convert this article into presentation slide format: {POST_URL}',
                    'enabled' => true,
                    'order' => 3,
                    'assigned_services' => array('chatgpt', 'gemini', 'claude'),
                    'built_in' => true
                ),
                'fact_check' => array(
                    'id' => 'fact_check',
                    'name' => 'Fact-check with latest information',
                    'prompt_text' => 'Fact check the claims in this article with latest information: {POST_URL}',
                    'enabled' => true,
                    'order' => 4,
                    'assigned_services' => array('perplexity', 'grok'),
                    'built_in' => true
                ),
                'eli5' => array(
                    'id' => 'eli5',
                    'name' => 'Explain like I\'m 5',
                    'prompt_text' => 'Explain this article in simple terms that a 5-year-old could understand: {POST_URL}',
                    'enabled' => true,
                    'order' => 5,
                    'assigned_services' => array('chatgpt', 'claude', 'gemini'),
                    'built_in' => true
                ),
                'discussion_questions' => array(
                    'id' => 'discussion_questions',
                    'name' => 'Generate discussion questions',
                    'prompt_text' => 'Create thought-provoking discussion questions about this article: {POST_URL}',
                    'enabled' => true,
                    'order' => 6,
                    'assigned_services' => array('chatgpt', 'claude', 'gemini'),
                    'built_in' => true
                ),
                'related_topics' => array(
                    'id' => 'related_topics',
                    'name' => 'Find related topics',
                    'prompt_text' => 'Suggest related topics and subjects for further exploration based on: {POST_URL}',
                    'enabled' => true,
                    'order' => 7,
                    'assigned_services' => array('perplexity', 'chatgpt', 'claude'),
                    'built_in' => true
                )
            ),
            'custom' => array()
        );
    }
    
    private static function get_default_settings() {
        return array(
            'auto_display' => true,
            'hook_location' => 'the_content',
            'hook_priority' => 10,
            'custom_hook' => '',
            'post_types' => array('post', 'page'),
            'css_level' => 'full',
            'mobile_layout' => 'horizontal_scroll',
            'tablet_layout' => 'stack',
            'hide_mobile' => false,
            'container_class' => 'ai-share-buttons',
            'button_class' => 'ai-share-button',
            'dropdown_class' => 'ai-prompt-dropdown',
            'custom_css' => '',
            'version' => AI_SHARE_BUTTONS_VERSION
        );
    }
    
    private function sanitize_networks($networks) {
        $sanitized = array('built_in' => array(), 'custom' => array());
        
        foreach ($networks as $type => $type_networks) {
            if (!in_array($type, array('built_in', 'custom'))) {
                continue;
            }
            
            foreach ($type_networks as $id => $network) {
                $sanitized[$type][$id] = array(
                    'id' => sanitize_text_field($network['id']),
                    'name' => sanitize_text_field($network['name']),
                    'type' => sanitize_text_field($network['type']),
                    'enabled' => (bool) $network['enabled'],
                    'order' => intval($network['order']),
                    'icon' => sanitize_text_field($network['icon']),
                    'color' => sanitize_hex_color($network['color']) ?: '#000000',
                    'url_template' => esc_url_raw($network['url_template']),
                    'default_prompt' => isset($network['default_prompt']) ? sanitize_textarea_field($network['default_prompt']) : '',
                    'built_in' => (bool) $network['built_in']
                );
                
                if ($type === 'custom' && isset($network['created_date'])) {
                    $sanitized[$type][$id]['created_date'] = sanitize_text_field($network['created_date']);
                }
            }
        }
        
        return $sanitized;
    }
    
    private function sanitize_prompts($prompts) {
        $sanitized = array('built_in' => array(), 'custom' => array());
        
        foreach ($prompts as $type => $type_prompts) {
            if (!in_array($type, array('built_in', 'custom'))) {
                continue;
            }
            
            foreach ($type_prompts as $id => $prompt) {
                $sanitized[$type][$id] = array(
                    'id' => sanitize_text_field($prompt['id']),
                    'name' => sanitize_text_field($prompt['name']),
                    'prompt_text' => sanitize_textarea_field($prompt['prompt_text']),
                    'enabled' => (bool) $prompt['enabled'],
                    'order' => intval($prompt['order']),
                    'assigned_services' => array_map('sanitize_text_field', $prompt['assigned_services']),
                    'built_in' => (bool) $prompt['built_in']
                );
                
                if ($type === 'custom' && isset($prompt['created_date'])) {
                    $sanitized[$type][$id]['created_date'] = sanitize_text_field($prompt['created_date']);
                }
            }
        }
        
        return $sanitized;
    }
    
    private function sanitize_settings($settings) {
        return array(
            'auto_display' => (bool) $settings['auto_display'],
            'hook_location' => sanitize_text_field($settings['hook_location']),
            'hook_priority' => intval($settings['hook_priority']),
            'custom_hook' => sanitize_text_field($settings['custom_hook']),
            'post_types' => array_map('sanitize_text_field', $settings['post_types']),
            'css_level' => sanitize_text_field($settings['css_level']),
            'mobile_layout' => sanitize_text_field($settings['mobile_layout']),
            'tablet_layout' => sanitize_text_field($settings['tablet_layout']),
            'hide_mobile' => (bool) $settings['hide_mobile'],
            'container_class' => sanitize_text_field($settings['container_class']),
            'button_class' => sanitize_text_field($settings['button_class']),
            'dropdown_class' => sanitize_text_field($settings['dropdown_class']),
            'custom_css' => wp_strip_all_tags($settings['custom_css']),
            'version' => sanitize_text_field($settings['version'])
        );
    }
    
    public function process_template_variables($template, $post_id = null) {
        if (!$post_id) {
            global $post;
            if ($post) {
                $post_id = $post->ID;
            }
        }
        
        if (!$post_id) {
            return $template;
        }
        
        // Get post data
        $post_obj = get_post($post_id);
        if (!$post_obj) {
            return $template;
        }
        
        // Prepare variables - limit content to prevent memory issues
        $content = wp_strip_all_tags($post_obj->post_content);
        if (strlen($content) > 5000) {
            $content = substr($content, 0, 5000) . '...';
        }
        
        $variables = array(
            '{POST_URL}' => get_permalink($post_id),
            '{POST_TITLE}' => get_the_title($post_id),
            '{POST_EXCERPT}' => get_the_excerpt($post_id),
            '{POST_CONTENT}' => $content,
            '{SITE_NAME}' => get_bloginfo('name'),
            '{SITE_URL}' => home_url(),
            '{AUTHOR_NAME}' => get_the_author_meta('display_name', $post_obj->post_author),
            '{POST_DATE}' => get_the_date('', $post_id),
            '{POST_CATEGORY}' => strip_tags(get_the_category_list(', ', '', $post_id)),
            '{POST_TAGS}' => strip_tags(get_the_tag_list('', ', ', '', $post_id)),
            '{ENCODED_URL}' => urlencode(get_permalink($post_id)),
            '{ENCODED_TITLE}' => urlencode(get_the_title($post_id)),
            '{ENCODED_EXCERPT}' => urlencode(get_the_excerpt($post_id))
        );
        
        // Replace variables
        foreach ($variables as $variable => $value) {
            $template = str_replace($variable, $value, $template);
        }
        
        // Special handling for encoded_prompt
        if (strpos($template, '{encoded_prompt}') !== false) {
            // This will be handled by the frontend when a specific prompt is selected
            $template = str_replace('{encoded_prompt}', '', $template);
        }
        
        return $template;
    }
}
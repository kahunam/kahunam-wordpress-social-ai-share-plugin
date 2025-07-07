<?php
/**
 * Helper functions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the plugin instance
 */
function ai_share_buttons() {
    return AI_Share_Buttons::get_instance();
}

/**
 * Get a specific network by ID
 */
function ai_share_get_network($network_id) {
    $plugin = ai_share_buttons();
    $networks = $plugin->get_networks();
    
    if (isset($networks['built_in'][$network_id])) {
        return $networks['built_in'][$network_id];
    } elseif (isset($networks['custom'][$network_id])) {
        return $networks['custom'][$network_id];
    }
    
    return null;
}

/**
 * Get a specific prompt by ID
 */
function ai_share_get_prompt($prompt_id) {
    $plugin = ai_share_buttons();
    $prompts = $plugin->get_prompts();
    
    if (isset($prompts['built_in'][$prompt_id])) {
        return $prompts['built_in'][$prompt_id];
    } elseif (isset($prompts['custom'][$prompt_id])) {
        return $prompts['custom'][$prompt_id];
    }
    
    return null;
}

/**
 * Check if a network is enabled
 */
function ai_share_is_network_enabled($network_id) {
    $network = ai_share_get_network($network_id);
    return $network && $network['enabled'];
}

/**
 * Get all enabled networks
 */
function ai_share_get_enabled_networks() {
    $plugin = ai_share_buttons();
    $networks = $plugin->get_networks();
    $enabled = array();
    
    // Merge all networks
    $all_networks = array_merge($networks['built_in'], $networks['custom']);
    
    // Filter enabled ones
    foreach ($all_networks as $network) {
        if ($network['enabled']) {
            $enabled[] = $network;
        }
    }
    
    // Sort by order
    usort($enabled, function($a, $b) {
        return $a['order'] - $b['order'];
    });
    
    return $enabled;
}

/**
 * Get prompts for a specific service
 */
function ai_share_get_service_prompts($service_id) {
    $plugin = ai_share_buttons();
    $prompts = $plugin->get_prompts();
    $service_prompts = array();
    
    // Check built-in prompts
    foreach ($prompts['built_in'] as $prompt) {
        if ($prompt['enabled'] && in_array($service_id, $prompt['assigned_services'])) {
            $service_prompts[] = $prompt;
        }
    }
    
    // Check custom prompts
    foreach ($prompts['custom'] as $prompt) {
        if ($prompt['enabled'] && in_array($service_id, $prompt['assigned_services'])) {
            $service_prompts[] = $prompt;
        }
    }
    
    // Sort by order
    usort($service_prompts, function($a, $b) {
        return $a['order'] - $b['order'];
    });
    
    return $service_prompts;
}

/**
 * Process template variables for current post
 */
function ai_share_process_template($template, $post_id = null) {
    $plugin = ai_share_buttons();
    return $plugin->process_template_variables($template, $post_id);
}

/**
 * Get the share URL for a network
 */
function ai_share_get_share_url($network_id, $prompt_id = null) {
    $network = ai_share_get_network($network_id);
    if (!$network) {
        return '#';
    }
    
    $template = $network['url_template'];
    
    // If AI service with prompt
    if ($network['type'] === 'ai' && $prompt_id) {
        $prompt = ai_share_get_prompt($prompt_id);
        if ($prompt) {
            $prompt_text = ai_share_process_template($prompt['prompt_text']);
            $template = str_replace('{encoded_prompt}', urlencode($prompt_text), $template);
        }
    }
    
    return ai_share_process_template($template);
}

/**
 * Check if current page should display share buttons
 */
function ai_share_should_display() {
    $plugin = ai_share_buttons();
    $settings = $plugin->get_settings();
    
    // Check if auto display is enabled
    if (!$settings['auto_display']) {
        return false;
    }
    
    // Check if singular
    if (!is_singular()) {
        return false;
    }
    
    // Check post type
    $post_type = get_post_type();
    if (!in_array($post_type, $settings['post_types'])) {
        return false;
    }
    
    return true;
}

/**
 * Get available template variables
 */
function ai_share_get_template_variables() {
    return array(
        '{POST_URL}' => __('The URL of the current post', 'ai-share-buttons'),
        '{POST_TITLE}' => __('The title of the current post', 'ai-share-buttons'),
        '{POST_EXCERPT}' => __('The excerpt of the current post', 'ai-share-buttons'),
        '{POST_CONTENT}' => __('The content of the current post (plain text)', 'ai-share-buttons'),
        '{SITE_NAME}' => __('The name of your website', 'ai-share-buttons'),
        '{SITE_URL}' => __('The URL of your website', 'ai-share-buttons'),
        '{AUTHOR_NAME}' => __('The name of the post author', 'ai-share-buttons'),
        '{POST_DATE}' => __('The publication date of the post', 'ai-share-buttons'),
        '{POST_CATEGORY}' => __('The categories of the post', 'ai-share-buttons'),
        '{POST_TAGS}' => __('The tags of the post', 'ai-share-buttons'),
        '{ENCODED_URL}' => __('URL-encoded post URL', 'ai-share-buttons'),
        '{ENCODED_TITLE}' => __('URL-encoded post title', 'ai-share-buttons'),
        '{ENCODED_EXCERPT}' => __('URL-encoded post excerpt', 'ai-share-buttons'),
        '{encoded_prompt}' => __('The selected AI prompt (AI services only)', 'ai-share-buttons')
    );
}

/**
 * Sanitize hex color
 */
if (!function_exists('sanitize_hex_color')) {
    function sanitize_hex_color($color) {
        if ('' === $color) {
            return '';
        }
        
        // 3 or 6 hex digits, or the empty string.
        if (preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color)) {
            return $color;
        }
        
        return null;
    }
}
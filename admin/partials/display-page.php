<?php
/**
 * Display settings page
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$plugin = AI_Share_Buttons::get_instance();
$settings = $plugin->get_settings();

// Get available post types
$post_types = get_post_types(array('public' => true), 'objects');
?>

<div class="wrap ai-share-buttons-admin">
    <div class="ai-share-buttons-header">
        <h1><?php _e('AI Share Buttons - Display Settings', 'ai-share-buttons'); ?></h1>
        <p><?php _e('Configure how and where the share buttons appear on your site.', 'ai-share-buttons'); ?></p>
    </div>
    
    <div class="ai-share-tabs">
        <h2 class="nav-tab-wrapper">
            <a href="<?php echo admin_url('admin.php?page=ai-share-buttons'); ?>" class="nav-tab"><?php _e('Networks', 'ai-share-buttons'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=ai-share-buttons-prompts'); ?>" class="nav-tab"><?php _e('AI Prompts', 'ai-share-buttons'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=ai-share-buttons-display'); ?>" class="nav-tab nav-tab-active"><?php _e('Display Settings', 'ai-share-buttons'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=ai-share-buttons-analytics'); ?>" class="nav-tab"><?php _e('Analytics', 'ai-share-buttons'); ?></a>
        </h2>
    </div>
    
    <form id="ai-share-settings-form" method="post">
        <div class="ai-share-section">
            <h2><?php _e('Display Options', 'ai-share-buttons'); ?></h2>
            
            <table class="ai-share-form-table">
                <tr>
                    <th><?php _e('Auto Display', 'ai-share-buttons'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   name="auto_display" 
                                   value="1" 
                                   <?php checked($settings['auto_display']); ?>>
                            <?php _e('Automatically display share buttons on posts and pages', 'ai-share-buttons'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th><?php _e('Hook Location', 'ai-share-buttons'); ?></th>
                    <td>
                        <select id="hook_location" name="hook_location" class="ai-share-select">
                            <option value="the_content" <?php selected($settings['hook_location'], 'the_content'); ?>>
                                <?php _e('Before and after content', 'ai-share-buttons'); ?>
                            </option>
                            <option value="wp_head" <?php selected($settings['hook_location'], 'wp_head'); ?>>
                                <?php _e('Header (requires manual positioning)', 'ai-share-buttons'); ?>
                            </option>
                            <option value="custom" <?php selected($settings['hook_location'], 'custom'); ?>>
                                <?php _e('Custom hook', 'ai-share-buttons'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                
                <tr id="custom-hook-row" style="<?php echo $settings['hook_location'] !== 'custom' ? 'display:none;' : ''; ?>">
                    <th><?php _e('Custom Hook Name', 'ai-share-buttons'); ?></th>
                    <td>
                        <input type="text" 
                               name="custom_hook" 
                               class="ai-share-input" 
                               value="<?php echo esc_attr($settings['custom_hook']); ?>"
                               placeholder="e.g., my_theme_after_title">
                    </td>
                </tr>
                
                <tr>
                    <th><?php _e('Hook Priority', 'ai-share-buttons'); ?></th>
                    <td>
                        <input type="number" 
                               name="hook_priority" 
                               class="small-text" 
                               value="<?php echo esc_attr($settings['hook_priority']); ?>"
                               min="1" 
                               max="999">
                        <p class="description">
                            <?php _e('Default: 10. Lower numbers = higher priority', 'ai-share-buttons'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th><?php _e('Post Types', 'ai-share-buttons'); ?></th>
                    <td>
                        <?php foreach ($post_types as $post_type): ?>
                            <label style="display: block; margin-bottom: 5px;">
                                <input type="checkbox" 
                                       name="post_types[]" 
                                       value="<?php echo esc_attr($post_type->name); ?>"
                                       <?php checked(in_array($post_type->name, $settings['post_types'])); ?>>
                                <?php echo esc_html($post_type->label); ?>
                            </label>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="ai-share-section">
            <h2><?php _e('Styling Options', 'ai-share-buttons'); ?></h2>
            
            <table class="ai-share-form-table">
                <tr>
                    <th><?php _e('CSS Level', 'ai-share-buttons'); ?></th>
                    <td>
                        <select name="css_level" class="ai-share-select">
                            <option value="full" <?php selected($settings['css_level'], 'full'); ?>>
                                <?php _e('Full styles (recommended)', 'ai-share-buttons'); ?>
                            </option>
                            <option value="minimal" <?php selected($settings['css_level'], 'minimal'); ?>>
                                <?php _e('Minimal styles', 'ai-share-buttons'); ?>
                            </option>
                            <option value="none" <?php selected($settings['css_level'], 'none'); ?>>
                                <?php _e('No styles (custom CSS only)', 'ai-share-buttons'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th><?php _e('Mobile Layout', 'ai-share-buttons'); ?></th>
                    <td>
                        <select name="mobile_layout" class="ai-share-select">
                            <option value="horizontal_scroll" <?php selected($settings['mobile_layout'], 'horizontal_scroll'); ?>>
                                <?php _e('Horizontal scroll', 'ai-share-buttons'); ?>
                            </option>
                            <option value="stack" <?php selected($settings['mobile_layout'], 'stack'); ?>>
                                <?php _e('Stack vertically', 'ai-share-buttons'); ?>
                            </option>
                            <option value="collapse" <?php selected($settings['mobile_layout'], 'collapse'); ?>>
                                <?php _e('Collapse to menu', 'ai-share-buttons'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th><?php _e('Tablet Layout', 'ai-share-buttons'); ?></th>
                    <td>
                        <select name="tablet_layout" class="ai-share-select">
                            <option value="default" <?php selected($settings['tablet_layout'], 'default'); ?>>
                                <?php _e('Same as desktop', 'ai-share-buttons'); ?>
                            </option>
                            <option value="stack" <?php selected($settings['tablet_layout'], 'stack'); ?>>
                                <?php _e('Stack vertically', 'ai-share-buttons'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th><?php _e('Hide on Mobile', 'ai-share-buttons'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   name="hide_mobile" 
                                   value="1" 
                                   <?php checked($settings['hide_mobile']); ?>>
                            <?php _e('Hide share buttons on mobile devices', 'ai-share-buttons'); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="ai-share-section">
            <h2><?php _e('Advanced Options', 'ai-share-buttons'); ?></h2>
            
            <table class="ai-share-form-table">
                <tr>
                    <th><?php _e('Container Class', 'ai-share-buttons'); ?></th>
                    <td>
                        <input type="text" 
                               name="container_class" 
                               class="ai-share-input" 
                               value="<?php echo esc_attr($settings['container_class']); ?>">
                        <p class="description">
                            <?php _e('CSS class for the buttons container', 'ai-share-buttons'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th><?php _e('Button Class', 'ai-share-buttons'); ?></th>
                    <td>
                        <input type="text" 
                               name="button_class" 
                               class="ai-share-input" 
                               value="<?php echo esc_attr($settings['button_class']); ?>">
                        <p class="description">
                            <?php _e('CSS class for individual buttons', 'ai-share-buttons'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th><?php _e('Dropdown Class', 'ai-share-buttons'); ?></th>
                    <td>
                        <input type="text" 
                               name="dropdown_class" 
                               class="ai-share-input" 
                               value="<?php echo esc_attr($settings['dropdown_class']); ?>">
                        <p class="description">
                            <?php _e('CSS class for AI prompt dropdowns', 'ai-share-buttons'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th><?php _e('Custom CSS', 'ai-share-buttons'); ?></th>
                    <td>
                        <textarea name="custom_css" 
                                  class="ai-share-textarea" 
                                  rows="10"
                                  style="font-family: monospace;"><?php echo esc_textarea($settings['custom_css']); ?></textarea>
                        <p class="description">
                            <?php _e('Add custom CSS to style the share buttons', 'ai-share-buttons'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th><?php _e('Enable Analytics', 'ai-share-buttons'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   name="enable_analytics" 
                                   value="1" 
                                   <?php checked($settings['enable_analytics']); ?>>
                            <?php _e('Track button clicks and usage analytics', 'ai-share-buttons'); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="ai-share-section">
            <h2><?php _e('Manual Placement', 'ai-share-buttons'); ?></h2>
            
            <p><?php _e('You can manually place share buttons using these methods:', 'ai-share-buttons'); ?></p>
            
            <h3><?php _e('Shortcode', 'ai-share-buttons'); ?></h3>
            <code>[ai_share_buttons]</code>
            <p class="description"><?php _e('Add this shortcode to any post or page', 'ai-share-buttons'); ?></p>
            
            <h3><?php _e('PHP Function', 'ai-share-buttons'); ?></h3>
            <code>&lt;?php ai_share_buttons(); ?&gt;</code>
            <p class="description"><?php _e('Add this to your theme template files', 'ai-share-buttons'); ?></p>
            
            <h3><?php _e('Widget', 'ai-share-buttons'); ?></h3>
            <p><?php _e('Go to Appearance > Widgets and add the "AI Share Buttons" widget', 'ai-share-buttons'); ?></p>
        </div>
        
        <p class="submit">
            <button type="submit" class="ai-share-button">
                <?php _e('Save Settings', 'ai-share-buttons'); ?>
            </button>
        </p>
    </form>
</div>
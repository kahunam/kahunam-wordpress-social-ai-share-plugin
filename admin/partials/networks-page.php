<?php
/**
 * Networks management page
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$plugin = AI_Share_Buttons::get_instance();
$networks = $plugin->get_networks();

// Merge and sort all networks
$all_networks = array_merge($networks['built_in'], $networks['custom']);
usort($all_networks, function($a, $b) {
    return $a['order'] - $b['order'];
});
?>

<div class="wrap ai-share-buttons-admin">
    <div class="ai-share-buttons-header">
        <h1><?php _e('AI Share Buttons - Networks', 'ai-share-buttons'); ?></h1>
        <p><?php _e('Manage social networks and AI services for sharing.', 'ai-share-buttons'); ?></p>
    </div>
    
    <div class="ai-share-tabs">
        <h2 class="nav-tab-wrapper">
            <a href="<?php echo admin_url('admin.php?page=ai-share-buttons'); ?>" class="nav-tab nav-tab-active"><?php _e('Networks', 'ai-share-buttons'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=ai-share-buttons-prompts'); ?>" class="nav-tab"><?php _e('AI Prompts', 'ai-share-buttons'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=ai-share-buttons-display'); ?>" class="nav-tab"><?php _e('Display Settings', 'ai-share-buttons'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=ai-share-buttons-analytics'); ?>" class="nav-tab"><?php _e('Analytics', 'ai-share-buttons'); ?></a>
        </h2>
    </div>
    
    <div class="ai-share-section">
        <h2><?php _e('Social Networks & AI Services', 'ai-share-buttons'); ?></h2>
        
        <div class="ai-share-actions">
            <button class="ai-share-button" id="add-new-network"><?php _e('Add New Network', 'ai-share-buttons'); ?></button>
        </div>
        
        <ul id="ai-share-networks-list" class="ai-share-networks-list">
            <?php foreach ($all_networks as $network): ?>
                <li class="ai-share-network-item <?php echo !$network['enabled'] ? 'disabled' : ''; ?>" 
                    data-network-id="<?php echo esc_attr($network['id']); ?>"
                    data-network='<?php echo esc_attr(json_encode($network)); ?>'>
                    
                    <span class="network-drag-handle dashicons dashicons-menu"></span>
                    
                    <div class="network-icon" style="background-color: <?php echo esc_attr($network['color']); ?>">
                        <?php 
                        $icon_url = AI_SHARE_BUTTONS_URL . 'assets/icons/' . $network['icon'];
                        // Check if it's a full URL (custom icon)
                        if (strpos($network['icon'], 'http') === 0) {
                            $icon_url = $network['icon'];
                        }
                        ?>
                        <img src="<?php echo esc_url($icon_url); ?>" 
                             alt="<?php echo esc_attr($network['name']); ?>">
                    </div>
                    
                    <div class="network-info">
                        <div class="network-name"><?php echo esc_html($network['name']); ?></div>
                        <div class="network-type type-<?php echo esc_attr($network['type']); ?>">
                            <?php echo ucfirst($network['type']); ?>
                            <?php if (!$network['built_in']): ?>
                                <span class="custom-badge">(Custom)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="network-actions">
                        <label class="network-enable-toggle">
                            <input type="checkbox" 
                                   class="network-enable-toggle" 
                                   data-network-id="<?php echo esc_attr($network['id']); ?>"
                                   <?php checked($network['enabled']); ?>>
                            <?php _e('Enabled', 'ai-share-buttons'); ?>
                        </label>
                        
                        <?php if (!$network['built_in']): ?>
                            <button class="ai-share-button secondary edit-network">
                                <?php _e('Edit', 'ai-share-buttons'); ?>
                            </button>
                            <button class="ai-share-button danger delete-network">
                                <?php _e('Delete', 'ai-share-buttons'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<!-- Network Modal -->
<div id="network-modal" class="ai-share-modal">
    <div class="ai-share-modal-content">
        <div class="ai-share-modal-header">
            <h2 id="network-modal-title"><?php _e('Add New Network', 'ai-share-buttons'); ?></h2>
            <button class="ai-share-modal-close">&times;</button>
        </div>
        
        <form id="network-form" method="post">
            <div class="ai-share-modal-body">
                <input type="hidden" id="network-id" name="id">
                
                <table class="ai-share-form-table">
                    <tr>
                        <th><?php _e('Network Name', 'ai-share-buttons'); ?></th>
                        <td>
                            <input type="text" 
                                   id="network-name" 
                                   name="name" 
                                   class="ai-share-input" 
                                   required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><?php _e('Network Type', 'ai-share-buttons'); ?></th>
                        <td>
                            <select id="network-type" name="type" class="ai-share-select">
                                <option value="social"><?php _e('Social Network', 'ai-share-buttons'); ?></option>
                                <option value="ai"><?php _e('AI Service', 'ai-share-buttons'); ?></option>
                                <option value="utility"><?php _e('Utility', 'ai-share-buttons'); ?></option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><?php _e('Icon', 'ai-share-buttons'); ?></th>
                        <td>
                            <div class="ai-share-icon-upload">
                                <div id="network-icon-preview" class="ai-share-icon-preview"></div>
                                <input type="hidden" id="network-icon" name="icon">
                                <button type="button" 
                                        id="upload-icon-button" 
                                        class="ai-share-button secondary">
                                    <?php _e('Upload Icon', 'ai-share-buttons'); ?>
                                </button>
                                <p class="description">
                                    <?php _e('Recommended: SVG or PNG, 64x64px', 'ai-share-buttons'); ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><?php _e('Button Color', 'ai-share-buttons'); ?></th>
                        <td>
                            <input type="text" 
                                   id="network-color" 
                                   name="color" 
                                   class="ai-share-color-picker" 
                                   value="#0073aa">
                        </td>
                    </tr>
                    
                    <tr>
                        <th><?php _e('Share URL Template', 'ai-share-buttons'); ?></th>
                        <td>
                            <input type="url" 
                                   id="network-url-template" 
                                   name="url_template" 
                                   class="ai-share-input" 
                                   required>
                            
                            <div class="url-builder-helper">
                                <h4><?php _e('Available Variables:', 'ai-share-buttons'); ?></h4>
                                <div class="template-variables">
                                    <span class="template-variable">{POST_URL}</span>
                                    <span class="template-variable">{POST_TITLE}</span>
                                    <span class="template-variable">{ENCODED_URL}</span>
                                    <span class="template-variable">{ENCODED_TITLE}</span>
                                    <span class="template-variable">{encoded_prompt}</span>
                                </div>
                                <p class="description">
                                    <?php _e('Example: https://example.com/share?url={POST_URL}&title={POST_TITLE}', 'ai-share-buttons'); ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <tr class="ai-service-only">
                        <th><?php _e('Default Prompt', 'ai-share-buttons'); ?></th>
                        <td>
                            <textarea id="network-default-prompt" 
                                      name="default_prompt" 
                                      class="ai-share-textarea"
                                      placeholder="<?php _e('Optional default prompt for AI services', 'ai-share-buttons'); ?>"></textarea>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="ai-share-modal-footer">
                <button type="button" class="ai-share-button secondary ai-share-modal-close">
                    <?php _e('Cancel', 'ai-share-buttons'); ?>
                </button>
                <button type="submit" class="ai-share-button">
                    <?php _e('Save Network', 'ai-share-buttons'); ?>
                </button>
            </div>
        </form>
    </div>
</div>
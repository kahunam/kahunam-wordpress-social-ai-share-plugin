<?php
/**
 * AI Prompts management page
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$plugin = AI_Share_Buttons::get_instance();
$prompts = $plugin->get_prompts();
$networks = $plugin->get_networks();

// Get AI services for assignment
$ai_services = array();
foreach ($networks['built_in'] as $network) {
    if ($network['type'] === 'ai') {
        $ai_services[$network['id']] = $network['name'];
    }
}
foreach ($networks['custom'] as $network) {
    if ($network['type'] === 'ai') {
        $ai_services[$network['id']] = $network['name'];
    }
}

// Merge and sort all prompts
$all_prompts = array_merge($prompts['built_in'], $prompts['custom']);
usort($all_prompts, function($a, $b) {
    return $a['order'] - $b['order'];
});
?>

<div class="wrap ai-share-buttons-admin">
    <div class="ai-share-buttons-header">
        <h1><?php _e('AI Share Buttons - Prompts', 'ai-share-buttons'); ?></h1>
        <p><?php _e('Manage AI prompts that appear in dropdown menus.', 'ai-share-buttons'); ?></p>
    </div>
    
    <div class="ai-share-tabs">
        <h2 class="nav-tab-wrapper">
            <a href="<?php echo admin_url('admin.php?page=ai-share-buttons'); ?>" class="nav-tab"><?php _e('Networks', 'ai-share-buttons'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=ai-share-buttons-prompts'); ?>" class="nav-tab nav-tab-active"><?php _e('AI Prompts', 'ai-share-buttons'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=ai-share-buttons-display'); ?>" class="nav-tab"><?php _e('Display Settings', 'ai-share-buttons'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=ai-share-buttons-analytics'); ?>" class="nav-tab"><?php _e('Analytics', 'ai-share-buttons'); ?></a>
        </h2>
    </div>
    
    <div class="ai-share-section">
        <h2><?php _e('AI Prompts Management', 'ai-share-buttons'); ?></h2>
        
        <div class="ai-share-actions">
            <button class="ai-share-button" id="add-new-prompt"><?php _e('Add New Prompt', 'ai-share-buttons'); ?></button>
        </div>
        
        <ul id="ai-share-prompts-list" class="ai-share-prompts-list">
            <?php foreach ($all_prompts as $prompt): ?>
                <li class="ai-share-prompt-item <?php echo !$prompt['enabled'] ? 'disabled' : ''; ?>" 
                    data-prompt-id="<?php echo esc_attr($prompt['id']); ?>"
                    data-prompt='<?php echo esc_attr(json_encode($prompt)); ?>'>
                    
                    <span class="prompt-drag-handle dashicons dashicons-menu"></span>
                    
                    <div class="prompt-content">
                        <div class="prompt-name"><?php echo esc_html($prompt['name']); ?></div>
                        <div class="prompt-text"><?php echo esc_html($prompt['prompt_text']); ?></div>
                        <div class="prompt-services">
                            <?php foreach ($prompt['assigned_services'] as $service_id): ?>
                                <?php if (isset($ai_services[$service_id])): ?>
                                    <span class="prompt-service-tag">
                                        <?php echo esc_html($ai_services[$service_id]); ?>
                                    </span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="prompt-actions">
                        <label class="prompt-enable-toggle">
                            <input type="checkbox" 
                                   class="prompt-enable-toggle" 
                                   data-prompt-id="<?php echo esc_attr($prompt['id']); ?>"
                                   <?php checked($prompt['enabled']); ?>>
                            <?php _e('Enabled', 'ai-share-buttons'); ?>
                        </label>
                        
                        <button class="ai-share-button secondary edit-prompt">
                            <?php _e('Edit', 'ai-share-buttons'); ?>
                        </button>
                        
                        <?php if (!$prompt['built_in']): ?>
                            <button class="ai-share-button danger delete-prompt">
                                <?php _e('Delete', 'ai-share-buttons'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<!-- Prompt Modal -->
<div id="prompt-modal" class="ai-share-modal">
    <div class="ai-share-modal-content">
        <div class="ai-share-modal-header">
            <h2 id="prompt-modal-title"><?php _e('Add New Prompt', 'ai-share-buttons'); ?></h2>
            <button class="ai-share-modal-close">&times;</button>
        </div>
        
        <form id="prompt-form" method="post">
            <div class="ai-share-modal-body">
                <input type="hidden" id="prompt-id" name="id">
                
                <table class="ai-share-form-table">
                    <tr>
                        <th><?php _e('Prompt Name', 'ai-share-buttons'); ?></th>
                        <td>
                            <input type="text" 
                                   id="prompt-name" 
                                   name="name" 
                                   class="ai-share-input" 
                                   required>
                            <p class="description">
                                <?php _e('This name will appear in the dropdown menu', 'ai-share-buttons'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><?php _e('Prompt Text', 'ai-share-buttons'); ?></th>
                        <td>
                            <textarea id="prompt-text" 
                                      name="prompt_text" 
                                      class="ai-share-textarea"
                                      required></textarea>
                            
                            <div class="url-builder-helper">
                                <h4><?php _e('Available Variables:', 'ai-share-buttons'); ?></h4>
                                <div class="template-variables">
                                    <span class="template-variable">{POST_URL}</span>
                                    <span class="template-variable">{POST_TITLE}</span>
                                    <span class="template-variable">{POST_EXCERPT}</span>
                                    <span class="template-variable">{POST_CONTENT}</span>
                                    <span class="template-variable">{SITE_NAME}</span>
                                    <span class="template-variable">{AUTHOR_NAME}</span>
                                </div>
                                <p class="description">
                                    <?php _e('Example: Summarize this article for me: {POST_URL}', 'ai-share-buttons'); ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><?php _e('Assign to Services', 'ai-share-buttons'); ?></th>
                        <td>
                            <div class="ai-services-checkboxes">
                                <?php foreach ($ai_services as $service_id => $service_name): ?>
                                    <label>
                                        <input type="checkbox" 
                                               name="assigned_services[]" 
                                               value="<?php echo esc_attr($service_id); ?>">
                                        <?php echo esc_html($service_name); ?>
                                    </label><br>
                                <?php endforeach; ?>
                            </div>
                            <p class="description">
                                <?php _e('Select which AI services should show this prompt', 'ai-share-buttons'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="ai-share-modal-footer">
                <button type="button" class="ai-share-button secondary ai-share-modal-close">
                    <?php _e('Cancel', 'ai-share-buttons'); ?>
                </button>
                <button type="submit" class="ai-share-button">
                    <?php _e('Save Prompt', 'ai-share-buttons'); ?>
                </button>
            </div>
        </form>
    </div>
</div>
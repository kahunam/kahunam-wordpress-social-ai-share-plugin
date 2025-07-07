<?php
/**
 * Analytics page
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$plugin = AI_Share_Buttons::get_instance();
$analytics = new AI_Share_Buttons_Analytics();

// Get date range
$days = isset($_GET['days']) ? intval($_GET['days']) : 30;

// Get analytics data
$click_trends = $analytics->get_click_trends($days);
$top_services = $analytics->get_top_services(10, $days);
$top_prompts = $analytics->get_top_prompts(10, $days);

// Calculate totals
$total_clicks = 0;
foreach ($click_trends as $day) {
    $total_clicks += $day->clicks;
}

// Get networks for display names
$networks = $plugin->get_networks();
$all_networks = array_merge($networks['built_in'], $networks['custom']);
$network_names = array();
foreach ($all_networks as $network) {
    $network_names[$network['id']] = $network['name'];
}

// Get prompts for display names
$prompts = $plugin->get_prompts();
$all_prompts = array_merge($prompts['built_in'], $prompts['custom']);
$prompt_names = array();
foreach ($all_prompts as $prompt) {
    $prompt_names[$prompt['id']] = $prompt['name'];
}
?>

<div class="wrap ai-share-buttons-admin">
    <div class="ai-share-buttons-header">
        <h1><?php _e('AI Share Buttons - Analytics', 'ai-share-buttons'); ?></h1>
        <p><?php _e('View sharing statistics and usage data.', 'ai-share-buttons'); ?></p>
    </div>
    
    <div class="ai-share-tabs">
        <h2 class="nav-tab-wrapper">
            <a href="<?php echo admin_url('admin.php?page=ai-share-buttons'); ?>" class="nav-tab"><?php _e('Networks', 'ai-share-buttons'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=ai-share-buttons-prompts'); ?>" class="nav-tab"><?php _e('AI Prompts', 'ai-share-buttons'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=ai-share-buttons-display'); ?>" class="nav-tab"><?php _e('Display Settings', 'ai-share-buttons'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=ai-share-buttons-analytics'); ?>" class="nav-tab nav-tab-active"><?php _e('Analytics', 'ai-share-buttons'); ?></a>
        </h2>
    </div>
    
    <div class="ai-share-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2><?php _e('Analytics Overview', 'ai-share-buttons'); ?></h2>
            
            <div>
                <label for="analytics-range"><?php _e('Date Range:', 'ai-share-buttons'); ?></label>
                <select id="analytics-range" class="ai-share-select">
                    <option value="7" <?php selected($days, 7); ?>><?php _e('Last 7 days', 'ai-share-buttons'); ?></option>
                    <option value="30" <?php selected($days, 30); ?>><?php _e('Last 30 days', 'ai-share-buttons'); ?></option>
                    <option value="90" <?php selected($days, 90); ?>><?php _e('Last 90 days', 'ai-share-buttons'); ?></option>
                </select>
            </div>
        </div>
        
        <div class="analytics-summary">
            <div class="analytics-card">
                <h3><?php _e('Total Clicks', 'ai-share-buttons'); ?></h3>
                <div class="number"><?php echo number_format($total_clicks); ?></div>
            </div>
            
            <div class="analytics-card">
                <h3><?php _e('Daily Average', 'ai-share-buttons'); ?></h3>
                <div class="number"><?php echo number_format($total_clicks / max($days, 1), 1); ?></div>
            </div>
            
            <div class="analytics-card">
                <h3><?php _e('Top Service', 'ai-share-buttons'); ?></h3>
                <div class="number">
                    <?php 
                    if (!empty($top_services)) {
                        $top_service = $top_services[0];
                        echo isset($network_names[$top_service->service_id]) 
                            ? esc_html($network_names[$top_service->service_id]) 
                            : esc_html($top_service->service_id);
                    } else {
                        echo '-';
                    }
                    ?>
                </div>
            </div>
            
            <div class="analytics-card">
                <h3><?php _e('Active Services', 'ai-share-buttons'); ?></h3>
                <div class="number"><?php echo count($top_services); ?></div>
            </div>
        </div>
    </div>
    
    <div class="ai-share-section">
        <h2><?php _e('Click Trends', 'ai-share-buttons'); ?></h2>
        
        <div class="analytics-chart" id="clicks-chart">
            <?php if (!empty($click_trends)): ?>
                <canvas id="clicks-canvas" width="400" height="100"></canvas>
                <script>
                    // Simple chart data for demonstration
                    var clickData = <?php echo json_encode(array_map(function($item) {
                        return array(
                            'date' => $item->date_recorded,
                            'clicks' => intval($item->clicks)
                        );
                    }, $click_trends)); ?>;
                </script>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 40px;">
                    <?php _e('No data available for the selected period.', 'ai-share-buttons'); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="ai-share-section">
        <h2><?php _e('Top Services', 'ai-share-buttons'); ?></h2>
        
        <?php if (!empty($top_services)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Service', 'ai-share-buttons'); ?></th>
                        <th style="width: 100px;"><?php _e('Clicks', 'ai-share-buttons'); ?></th>
                        <th style="width: 100px;"><?php _e('Percentage', 'ai-share-buttons'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top_services as $service): ?>
                        <tr>
                            <td>
                                <?php 
                                echo isset($network_names[$service->service_id]) 
                                    ? esc_html($network_names[$service->service_id]) 
                                    : esc_html($service->service_id);
                                ?>
                            </td>
                            <td><?php echo number_format($service->clicks); ?></td>
                            <td>
                                <?php 
                                $percentage = $total_clicks > 0 ? ($service->clicks / $total_clicks * 100) : 0;
                                echo number_format($percentage, 1) . '%';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?php _e('No service data available.', 'ai-share-buttons'); ?></p>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($top_prompts)): ?>
        <div class="ai-share-section">
            <h2><?php _e('Top AI Prompts', 'ai-share-buttons'); ?></h2>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Prompt', 'ai-share-buttons'); ?></th>
                        <th><?php _e('Service', 'ai-share-buttons'); ?></th>
                        <th style="width: 100px;"><?php _e('Clicks', 'ai-share-buttons'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top_prompts as $prompt): ?>
                        <tr>
                            <td>
                                <?php 
                                echo isset($prompt_names[$prompt->prompt_id]) 
                                    ? esc_html($prompt_names[$prompt->prompt_id]) 
                                    : esc_html($prompt->prompt_id);
                                ?>
                            </td>
                            <td>
                                <?php 
                                echo isset($network_names[$prompt->service_id]) 
                                    ? esc_html($network_names[$prompt->service_id]) 
                                    : esc_html($prompt->service_id);
                                ?>
                            </td>
                            <td><?php echo number_format($prompt->clicks); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <div class="ai-share-section">
        <h2><?php _e('Export Data', 'ai-share-buttons'); ?></h2>
        
        <p><?php _e('Export analytics data for further analysis.', 'ai-share-buttons'); ?></p>
        
        <form id="export-form" style="display: inline-block;">
            <label><?php _e('Date Range:', 'ai-share-buttons'); ?></label>
            <input type="date" id="export-date-start" value="<?php echo date('Y-m-d', strtotime("-$days days")); ?>">
            <span> - </span>
            <input type="date" id="export-date-end" value="<?php echo date('Y-m-d'); ?>">
            
            <label style="margin-left: 20px;"><?php _e('Format:', 'ai-share-buttons'); ?></label>
            <select id="export-format">
                <option value="csv"><?php _e('CSV', 'ai-share-buttons'); ?></option>
            </select>
            
            <button type="button" id="export-analytics" class="ai-share-button secondary" style="margin-left: 20px;">
                <?php _e('Export', 'ai-share-buttons'); ?>
            </button>
        </form>
    </div>
</div>
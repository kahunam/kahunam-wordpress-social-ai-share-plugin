<?php
/**
 * Admin settings page
 */

if (!defined('ABSPATH')) {
    exit;
}

class KAAIS_Settings {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function add_menu() {
        add_options_page(
            __('AI Share Buttons', 'kaais'),
            __('AI Share Buttons', 'kaais'),
            'manage_options',
            'kaais-settings',
            [$this, 'render_page']
        );
    }

    public function register_settings() {
        register_setting('kaais_settings_group', 'kaais_settings', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
            'default' => kaais_get_defaults(),
        ]);
    }

    public function sanitize_settings($input) {
        $defaults = kaais_get_defaults();
        $sanitized = [];

        // AI Platforms (with order)
        $sanitized['ai_platforms'] = [];
        $ai_platforms = kaais_get_ai_platforms();
        foreach (array_keys($ai_platforms) as $id) {
            $sanitized['ai_platforms'][$id] = !empty($input['ai_platforms'][$id]);
        }

        // Platform order
        $sanitized['platform_order'] = [];
        if (!empty($input['platform_order'])) {
            $sanitized['platform_order'] = array_map('sanitize_key', explode(',', $input['platform_order']));
        }

        // Social Networks
        $sanitized['social_networks'] = [];
        $social_networks = kaais_get_social_networks();
        foreach (array_keys($social_networks) as $id) {
            $sanitized['social_networks'][$id] = !empty($input['social_networks'][$id]);
        }

        // Custom Networks
        $sanitized['custom_networks'] = [];
        if (!empty($input['custom_networks']) && is_array($input['custom_networks'])) {
            foreach ($input['custom_networks'] as $custom) {
                if (empty($custom['name'])) {
                    continue;
                }
                $sanitized['custom_networks'][] = [
                    'name' => sanitize_text_field($custom['name']),
                    'icon' => esc_url_raw($custom['icon']),
                    'url_template' => esc_url_raw($custom['url_template']),
                ];
            }
        }

        // Prompts
        $sanitized['prompts'] = [];
        if (!empty($input['prompts']) && is_array($input['prompts'])) {
            foreach ($input['prompts'] as $label => $text) {
                $clean_label = sanitize_text_field($label);
                if (!empty($clean_label)) {
                    $sanitized['prompts'][$clean_label] = sanitize_textarea_field($text);
                }
            }
        }
        if (empty($sanitized['prompts'])) {
            $sanitized['prompts'] = $defaults['prompts'];
        }

        // Display settings
        $sanitized['auto_insert'] = !empty($input['auto_insert']);
        $sanitized['post_types'] = [];
        if (!empty($input['post_types']) && is_array($input['post_types'])) {
            $sanitized['post_types'] = array_map('sanitize_key', $input['post_types']);
        }
        $sanitized['position'] = in_array($input['position'] ?? '', ['before', 'after', 'both'], true)
            ? $input['position']
            : 'after';

        // Layout
        $sanitized['layout'] = in_array($input['layout'] ?? '', ['inline', 'stacked', 'divider', 'stacked-divider'], true)
            ? $input['layout']
            : 'inline';
        $sanitized['show_labels'] = !empty($input['show_labels']);
        $sanitized['disable_css'] = !empty($input['disable_css']);

        // Labels
        $sanitized['ai_label'] = sanitize_text_field($input['ai_label'] ?? $defaults['ai_label']);
        $sanitized['social_label'] = sanitize_text_field($input['social_label'] ?? $defaults['social_label']);

        return $sanitized;
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'settings_page_kaais-settings') {
            return;
        }

        // WordPress media library
        wp_enqueue_media();

        // jQuery UI for sortable
        wp_enqueue_script('jquery-ui-sortable');

        // Admin JS
        wp_enqueue_script(
            'kaais-admin',
            KAAIS_URL . 'assets/js/kaais-admin.js',
            ['jquery', 'jquery-ui-sortable'],
            KAAIS_VERSION,
            true
        );

        wp_localize_script('kaais-admin', 'kaaisAdmin', [
            'mediaTitle' => __('Select Icon', 'kaais'),
            'mediaButton' => __('Use this icon', 'kaais'),
        ]);

        // Admin CSS
        wp_add_inline_style('wp-admin', $this->get_admin_css());
    }

    private function get_admin_css() {
        return '
            .kaais-settings { max-width: 800px; }
            .kaais-settings h2 { margin-top: 2em; padding-top: 1em; border-top: 1px solid #ccc; }
            .kaais-settings h2:first-of-type { margin-top: 0; padding-top: 0; border-top: none; }

            /* Platform list with icons */
            .kaais-platform-list { margin: 1em 0; }
            .kaais-platform-item {
                display: flex;
                align-items: center;
                gap: 0.75em;
                padding: 0.6em 0.75em;
                background: #f9f9f9;
                border: 1px solid #e0e0e0;
                border-radius: 4px;
                margin-bottom: 4px;
                cursor: move;
            }
            .kaais-platform-item:hover { background: #f0f0f0; }
            .kaais-platform-item.ui-sortable-helper { box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
            .kaais-platform-item.ui-sortable-placeholder {
                visibility: visible !important;
                background: #e8f4fc;
                border: 2px dashed #2271b1;
            }
            .kaais-platform-item .drag-handle {
                color: #999;
                cursor: move;
            }
            .kaais-platform-item .platform-icon {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 24px;
                height: 24px;
                flex-shrink: 0;
            }
            .kaais-platform-item .platform-icon svg {
                width: 20px;
                height: 20px;
            }
            .kaais-platform-item .platform-name { flex: 1; font-weight: 500; }
            .kaais-platform-item input[type="checkbox"] { margin: 0; }

            /* Custom network form */
            .kaais-custom-network {
                background: #f9f9f9;
                padding: 1em;
                margin-bottom: 1em;
                border-radius: 4px;
                border: 1px solid #e0e0e0;
            }
            .kaais-custom-network .field-row {
                display: flex;
                gap: 0.5em;
                margin-bottom: 0.5em;
                align-items: center;
            }
            .kaais-custom-network .field-row:last-child { margin-bottom: 0; }
            .kaais-custom-network input[type="text"],
            .kaais-custom-network input[type="url"] { flex: 1; }
            .kaais-custom-network .icon-preview {
                width: 32px;
                height: 32px;
                border: 1px solid #ddd;
                border-radius: 4px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #fff;
                flex-shrink: 0;
            }
            .kaais-custom-network .icon-preview img {
                max-width: 24px;
                max-height: 24px;
            }
            .kaais-custom-network .button-link-delete { color: #b32d2e; }
            .kaais-add-network { margin-top: 1em; }

            /* Prompts */
            .kaais-prompt { margin-bottom: 1.5em; }
            .kaais-prompt label { display: block; font-weight: 600; margin-bottom: 0.25em; }
            .kaais-prompt textarea { width: 100%; }

            /* Layout preview */
            .kaais-layout-options { display: flex; gap: 1em; flex-wrap: wrap; margin: 1em 0; }
            .kaais-layout-option {
                border: 2px solid #ddd;
                border-radius: 8px;
                padding: 1em;
                cursor: pointer;
                text-align: center;
                min-width: 120px;
            }
            .kaais-layout-option:hover { border-color: #999; }
            .kaais-layout-option.selected { border-color: #2271b1; background: #f0f7fc; }
            .kaais-layout-option input { display: none; }
            .kaais-layout-option .preview {
                height: 40px;
                margin-bottom: 0.5em;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 4px;
            }
            .kaais-layout-option .preview-dot {
                width: 8px;
                height: 8px;
                background: #666;
                border-radius: 2px;
            }
            .kaais-layout-option .preview-line {
                width: 100%;
                height: 1px;
                background: #ccc;
                margin: 4px 0;
            }
            .kaais-layout-option .label { font-size: 12px; color: #666; }
        ';
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $settings = kaais_get_settings();
        $ai_platforms = kaais_get_ai_platforms();
        $social_networks = kaais_get_social_networks();
        $post_types = get_post_types(['public' => true], 'objects');

        // Get platform order or use default
        $platform_order = $settings['platform_order'] ?? [];
        if (empty($platform_order)) {
            $platform_order = array_keys($ai_platforms);
        }
        // Add any missing platforms
        foreach (array_keys($ai_platforms) as $id) {
            if (!in_array($id, $platform_order)) {
                $platform_order[] = $id;
            }
        }

        ?>
        <div class="wrap kaais-settings">
            <h1><?php esc_html_e('AI Share Buttons', 'kaais'); ?></h1>

            <form method="post" action="options.php">
                <?php settings_fields('kaais_settings_group'); ?>

                <h2><?php esc_html_e('AI Platforms', 'kaais'); ?></h2>
                <p class="description"><?php esc_html_e('Select and reorder AI platforms. Drag to change order.', 'kaais'); ?></p>

                <div class="kaais-platform-list" id="kaais-ai-platforms">
                    <?php foreach ($platform_order as $id) :
                        if (!isset($ai_platforms[$id])) continue;
                        $platform = $ai_platforms[$id];
                    ?>
                        <div class="kaais-platform-item" data-id="<?php echo esc_attr($id); ?>">
                            <span class="drag-handle dashicons dashicons-menu"></span>
                            <span class="platform-icon"><?php echo $platform['icon']; ?></span>
                            <span class="platform-name"><?php echo esc_html($platform['name']); ?></span>
                            <input type="checkbox"
                                   name="kaais_settings[ai_platforms][<?php echo esc_attr($id); ?>]"
                                   value="1"
                                   <?php checked(!empty($settings['ai_platforms'][$id])); ?>>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="kaais_settings[platform_order]" id="kaais-platform-order" value="<?php echo esc_attr(implode(',', $platform_order)); ?>">

                <h2><?php esc_html_e('Custom AI Networks', 'kaais'); ?></h2>
                <p class="description"><?php esc_html_e('Add custom AI platforms. Use {prompt} in the URL where the encoded prompt should go.', 'kaais'); ?></p>

                <div id="kaais-custom-networks">
                    <?php
                    $custom_networks = $settings['custom_networks'] ?? [];
                    if (!empty($custom_networks)) :
                        foreach ($custom_networks as $index => $custom) :
                    ?>
                        <div class="kaais-custom-network">
                            <div class="field-row">
                                <input type="text"
                                       name="kaais_settings[custom_networks][<?php echo $index; ?>][name]"
                                       value="<?php echo esc_attr($custom['name']); ?>"
                                       placeholder="<?php esc_attr_e('Platform name', 'kaais'); ?>"
                                       class="regular-text">
                            </div>
                            <div class="field-row">
                                <span class="icon-preview">
                                    <?php if (!empty($custom['icon'])) : ?>
                                        <img src="<?php echo esc_url($custom['icon']); ?>" alt="">
                                    <?php endif; ?>
                                </span>
                                <input type="url"
                                       name="kaais_settings[custom_networks][<?php echo $index; ?>][icon]"
                                       value="<?php echo esc_url($custom['icon']); ?>"
                                       placeholder="<?php esc_attr_e('Icon URL', 'kaais'); ?>"
                                       class="regular-text kaais-icon-url">
                                <button type="button" class="button kaais-select-icon">
                                    <?php esc_html_e('Select', 'kaais'); ?>
                                </button>
                            </div>
                            <div class="field-row">
                                <input type="url"
                                       name="kaais_settings[custom_networks][<?php echo $index; ?>][url_template]"
                                       value="<?php echo esc_url($custom['url_template']); ?>"
                                       placeholder="<?php esc_attr_e('URL template, e.g. https://ai.com/?q={prompt}', 'kaais'); ?>"
                                       class="large-text">
                            </div>
                            <div class="field-row">
                                <button type="button" class="button-link button-link-delete kaais-remove-network">
                                    <?php esc_html_e('Remove', 'kaais'); ?>
                                </button>
                            </div>
                        </div>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </div>

                <template id="kaais-network-template">
                    <div class="kaais-custom-network">
                        <div class="field-row">
                            <input type="text"
                                   name="kaais_settings[custom_networks][__INDEX__][name]"
                                   placeholder="<?php esc_attr_e('Platform name', 'kaais'); ?>"
                                   class="regular-text">
                        </div>
                        <div class="field-row">
                            <span class="icon-preview"></span>
                            <input type="url"
                                   name="kaais_settings[custom_networks][__INDEX__][icon]"
                                   placeholder="<?php esc_attr_e('Icon URL', 'kaais'); ?>"
                                   class="regular-text kaais-icon-url">
                            <button type="button" class="button kaais-select-icon">
                                <?php esc_html_e('Select', 'kaais'); ?>
                            </button>
                        </div>
                        <div class="field-row">
                            <input type="url"
                                   name="kaais_settings[custom_networks][__INDEX__][url_template]"
                                   placeholder="<?php esc_attr_e('URL template, e.g. https://ai.com/?q={prompt}', 'kaais'); ?>"
                                   class="large-text">
                        </div>
                        <div class="field-row">
                            <button type="button" class="button-link button-link-delete kaais-remove-network">
                                <?php esc_html_e('Remove', 'kaais'); ?>
                            </button>
                        </div>
                    </div>
                </template>

                <button type="button" class="button kaais-add-network" id="kaais-add-network">
                    <?php esc_html_e('+ Add Custom AI Platform', 'kaais'); ?>
                </button>

                <h2><?php esc_html_e('Social Networks', 'kaais'); ?></h2>
                <p class="description"><?php esc_html_e('Select which social sharing buttons to display.', 'kaais'); ?></p>

                <div class="kaais-platform-list">
                    <?php foreach ($social_networks as $id => $network) : ?>
                        <div class="kaais-platform-item" style="cursor: default;">
                            <span class="platform-icon"><?php echo $network['icon']; ?></span>
                            <span class="platform-name"><?php echo esc_html($network['name']); ?></span>
                            <input type="checkbox"
                                   name="kaais_settings[social_networks][<?php echo esc_attr($id); ?>]"
                                   value="1"
                                   <?php checked(!empty($settings['social_networks'][$id])); ?>>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h2><?php esc_html_e('Prompts', 'kaais'); ?></h2>
                <p class="description"><?php esc_html_e('Edit the prompts shown in AI platform dropdown menus. Use {url} where the post URL should appear.', 'kaais'); ?></p>

                <?php foreach ($settings['prompts'] as $label => $text) : ?>
                    <div class="kaais-prompt">
                        <label><?php echo esc_html($label); ?></label>
                        <textarea name="kaais_settings[prompts][<?php echo esc_attr($label); ?>]"
                                  rows="2"
                                  class="large-text"><?php echo esc_textarea($text); ?></textarea>
                    </div>
                <?php endforeach; ?>

                <h2><?php esc_html_e('Layout', 'kaais'); ?></h2>
                <p class="description"><?php esc_html_e('Choose how the buttons are displayed.', 'kaais'); ?></p>

                <div class="kaais-layout-options">
                    <label class="kaais-layout-option <?php echo ($settings['layout'] ?? 'inline') === 'inline' ? 'selected' : ''; ?>">
                        <input type="radio" name="kaais_settings[layout]" value="inline" <?php checked($settings['layout'] ?? 'inline', 'inline'); ?>>
                        <div class="preview">
                            <span class="preview-dot"></span>
                            <span class="preview-dot"></span>
                            <span class="preview-dot"></span>
                            <span class="preview-dot"></span>
                        </div>
                        <span class="label"><?php esc_html_e('Inline', 'kaais'); ?></span>
                    </label>

                    <label class="kaais-layout-option <?php echo ($settings['layout'] ?? '') === 'stacked' ? 'selected' : ''; ?>">
                        <input type="radio" name="kaais_settings[layout]" value="stacked" <?php checked($settings['layout'] ?? '', 'stacked'); ?>>
                        <div class="preview" style="flex-direction: column;">
                            <span style="font-size: 10px; color: #999;">Label</span>
                            <div style="display: flex; gap: 4px;">
                                <span class="preview-dot"></span>
                                <span class="preview-dot"></span>
                                <span class="preview-dot"></span>
                            </div>
                        </div>
                        <span class="label"><?php esc_html_e('Stacked', 'kaais'); ?></span>
                    </label>

                    <label class="kaais-layout-option <?php echo ($settings['layout'] ?? '') === 'divider' ? 'selected' : ''; ?>">
                        <input type="radio" name="kaais_settings[layout]" value="divider" <?php checked($settings['layout'] ?? '', 'divider'); ?>>
                        <div class="preview" style="flex-direction: column; width: 100%;">
                            <div style="display: flex; gap: 4px;">
                                <span class="preview-dot"></span>
                                <span class="preview-dot"></span>
                            </div>
                            <span class="preview-line"></span>
                            <div style="display: flex; gap: 4px;">
                                <span class="preview-dot"></span>
                                <span class="preview-dot"></span>
                            </div>
                        </div>
                        <span class="label"><?php esc_html_e('With Divider', 'kaais'); ?></span>
                    </label>

                    <label class="kaais-layout-option <?php echo ($settings['layout'] ?? '') === 'stacked-divider' ? 'selected' : ''; ?>">
                        <input type="radio" name="kaais_settings[layout]" value="stacked-divider" <?php checked($settings['layout'] ?? '', 'stacked-divider'); ?>>
                        <div class="preview" style="flex-direction: column; width: 100%;">
                            <span style="font-size: 10px; color: #999;">Label</span>
                            <div style="display: flex; gap: 4px;">
                                <span class="preview-dot"></span>
                                <span class="preview-dot"></span>
                            </div>
                            <span class="preview-line"></span>
                            <span style="font-size: 10px; color: #999;">Label</span>
                            <div style="display: flex; gap: 4px;">
                                <span class="preview-dot"></span>
                                <span class="preview-dot"></span>
                            </div>
                        </div>
                        <span class="label"><?php esc_html_e('Stacked + Divider', 'kaais'); ?></span>
                    </label>
                </div>

                <h2><?php esc_html_e('Display Settings', 'kaais'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Automatic placement', 'kaais'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox"
                                       name="kaais_settings[auto_insert]"
                                       value="1"
                                       <?php checked($settings['auto_insert']); ?>>
                                <?php esc_html_e('Automatically add share buttons to posts', 'kaais'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('When enabled, buttons will appear on your posts without needing to add them manually.', 'kaais'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Post types', 'kaais'); ?></th>
                        <td>
                            <?php foreach ($post_types as $pt) : ?>
                                <label style="display: block; margin-bottom: 0.25em;">
                                    <input type="checkbox"
                                           name="kaais_settings[post_types][]"
                                           value="<?php echo esc_attr($pt->name); ?>"
                                           <?php checked(in_array($pt->name, $settings['post_types'], true)); ?>>
                                    <?php echo esc_html($pt->label); ?>
                                </label>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Position', 'kaais'); ?></th>
                        <td>
                            <select name="kaais_settings[position]">
                                <option value="after" <?php selected($settings['position'], 'after'); ?>>
                                    <?php esc_html_e('After content', 'kaais'); ?>
                                </option>
                                <option value="before" <?php selected($settings['position'], 'before'); ?>>
                                    <?php esc_html_e('Before content', 'kaais'); ?>
                                </option>
                                <option value="both" <?php selected($settings['position'], 'both'); ?>>
                                    <?php esc_html_e('Before and after', 'kaais'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Show labels', 'kaais'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox"
                                       name="kaais_settings[show_labels]"
                                       value="1"
                                       <?php checked($settings['show_labels'] ?? true); ?>>
                                <?php esc_html_e('Show "Explore with AI" and "Share" labels', 'kaais'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Custom labels', 'kaais'); ?></th>
                        <td>
                            <input type="text"
                                   name="kaais_settings[ai_label]"
                                   value="<?php echo esc_attr($settings['ai_label']); ?>"
                                   class="regular-text"
                                   placeholder="<?php esc_attr_e('Explore with AI', 'kaais'); ?>">
                            <p class="description"><?php esc_html_e('AI section label', 'kaais'); ?></p>
                            <input type="text"
                                   name="kaais_settings[social_label]"
                                   value="<?php echo esc_attr($settings['social_label']); ?>"
                                   class="regular-text"
                                   style="margin-top: 0.5em;"
                                   placeholder="<?php esc_attr_e('Share', 'kaais'); ?>">
                            <p class="description"><?php esc_html_e('Social section label', 'kaais'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Disable plugin CSS', 'kaais'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox"
                                       name="kaais_settings[disable_css]"
                                       value="1"
                                       <?php checked($settings['disable_css']); ?>>
                                <?php esc_html_e('Use your own styles instead', 'kaais'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('For theme developers who want complete control over styling.', 'kaais'); ?></p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <hr>
            <h2><?php esc_html_e('Manual Placement', 'kaais'); ?></h2>
            <p><?php esc_html_e('If automatic placement is disabled, use one of these methods:', 'kaais'); ?></p>
            <p><strong><?php esc_html_e('Shortcode:', 'kaais'); ?></strong> <code>[kaais_share_buttons]</code></p>
            <p><strong><?php esc_html_e('PHP:', 'kaais'); ?></strong> <code>&lt;?php kaais_share_buttons(); ?&gt;</code></p>
        </div>
        <?php
    }
}

new KAAIS_Settings();

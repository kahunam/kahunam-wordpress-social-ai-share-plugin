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
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
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

        // AI Platforms
        $sanitized['ai_platforms'] = [];
        $ai_platforms = kaais_get_ai_platforms();
        foreach (array_keys($ai_platforms) as $id) {
            $sanitized['ai_platforms'][$id] = !empty($input['ai_platforms'][$id]);
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
        $sanitized['disable_css'] = !empty($input['disable_css']);

        // Labels
        $sanitized['ai_label'] = sanitize_text_field($input['ai_label'] ?? $defaults['ai_label']);
        $sanitized['social_label'] = sanitize_text_field($input['social_label'] ?? $defaults['social_label']);

        return $sanitized;
    }

    public function enqueue_admin_styles($hook) {
        if ($hook !== 'settings_page_kaais-settings') {
            return;
        }

        wp_add_inline_style('wp-admin', '
            .kaais-settings { max-width: 800px; }
            .kaais-settings h2 { margin-top: 2em; padding-top: 1em; border-top: 1px solid #ccc; }
            .kaais-settings h2:first-of-type { margin-top: 0; padding-top: 0; border-top: none; }
            .kaais-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 0.5em; margin: 1em 0; }
            .kaais-grid label { display: flex; align-items: center; gap: 0.5em; padding: 0.5em; background: #f9f9f9; border-radius: 4px; }
            .kaais-grid label:hover { background: #f0f0f0; }
            .kaais-prompt { margin-bottom: 1.5em; }
            .kaais-prompt label { display: block; font-weight: 600; margin-bottom: 0.25em; }
            .kaais-prompt textarea { width: 100%; }
            .kaais-custom-network { background: #f9f9f9; padding: 1em; margin-bottom: 1em; border-radius: 4px; }
            .kaais-custom-network input { margin-bottom: 0.5em; }
            .kaais-custom-network .button-link-delete { color: #b32d2e; }
            .kaais-add-network { margin-top: 1em; }
        ');
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $settings = kaais_get_settings();
        $ai_platforms = kaais_get_ai_platforms();
        $social_networks = kaais_get_social_networks();
        $post_types = get_post_types(['public' => true], 'objects');

        ?>
        <div class="wrap kaais-settings">
            <h1><?php esc_html_e('AI Share Buttons', 'kaais'); ?></h1>

            <form method="post" action="options.php">
                <?php settings_fields('kaais_settings_group'); ?>

                <h2><?php esc_html_e('AI Platforms', 'kaais'); ?></h2>
                <p class="description"><?php esc_html_e('Select which AI platforms to display.', 'kaais'); ?></p>
                <div class="kaais-grid">
                    <?php foreach ($ai_platforms as $id => $platform) : ?>
                        <label>
                            <input type="checkbox"
                                   name="kaais_settings[ai_platforms][<?php echo esc_attr($id); ?>]"
                                   value="1"
                                   <?php checked(!empty($settings['ai_platforms'][$id])); ?>>
                            <?php echo esc_html($platform['name']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <h2><?php esc_html_e('Custom Networks', 'kaais'); ?></h2>
                <p class="description"><?php esc_html_e('Add custom AI platforms. Use {prompt} in the URL template where the encoded prompt should go.', 'kaais'); ?></p>

                <div id="kaais-custom-networks">
                    <?php
                    $custom_networks = $settings['custom_networks'] ?? [];
                    if (!empty($custom_networks)) :
                        foreach ($custom_networks as $index => $custom) :
                    ?>
                        <div class="kaais-custom-network">
                            <input type="text"
                                   name="kaais_settings[custom_networks][<?php echo $index; ?>][name]"
                                   value="<?php echo esc_attr($custom['name']); ?>"
                                   placeholder="<?php esc_attr_e('Name', 'kaais'); ?>"
                                   class="regular-text">
                            <input type="url"
                                   name="kaais_settings[custom_networks][<?php echo $index; ?>][icon]"
                                   value="<?php echo esc_url($custom['icon']); ?>"
                                   placeholder="<?php esc_attr_e('Icon URL (optional)', 'kaais'); ?>"
                                   class="regular-text">
                            <input type="url"
                                   name="kaais_settings[custom_networks][<?php echo $index; ?>][url_template]"
                                   value="<?php echo esc_url($custom['url_template']); ?>"
                                   placeholder="<?php esc_attr_e('URL template with {prompt}', 'kaais'); ?>"
                                   class="large-text">
                            <button type="button" class="button-link button-link-delete kaais-remove-network">
                                <?php esc_html_e('Remove', 'kaais'); ?>
                            </button>
                        </div>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </div>

                <template id="kaais-network-template">
                    <div class="kaais-custom-network">
                        <input type="text"
                               name="kaais_settings[custom_networks][__INDEX__][name]"
                               placeholder="<?php esc_attr_e('Name', 'kaais'); ?>"
                               class="regular-text">
                        <input type="url"
                               name="kaais_settings[custom_networks][__INDEX__][icon]"
                               placeholder="<?php esc_attr_e('Icon URL (optional)', 'kaais'); ?>"
                               class="regular-text">
                        <input type="url"
                               name="kaais_settings[custom_networks][__INDEX__][url_template]"
                               placeholder="<?php esc_attr_e('URL template with {prompt}', 'kaais'); ?>"
                               class="large-text">
                        <button type="button" class="button-link button-link-delete kaais-remove-network">
                            <?php esc_html_e('Remove', 'kaais'); ?>
                        </button>
                    </div>
                </template>

                <button type="button" class="button kaais-add-network" id="kaais-add-network">
                    <?php esc_html_e('+ Add Custom Network', 'kaais'); ?>
                </button>

                <h2><?php esc_html_e('Social Networks', 'kaais'); ?></h2>
                <p class="description"><?php esc_html_e('Select which social networks to display.', 'kaais'); ?></p>
                <div class="kaais-grid">
                    <?php foreach ($social_networks as $id => $network) : ?>
                        <label>
                            <input type="checkbox"
                                   name="kaais_settings[social_networks][<?php echo esc_attr($id); ?>]"
                                   value="1"
                                   <?php checked(!empty($settings['social_networks'][$id])); ?>>
                            <?php echo esc_html($network['name']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <h2><?php esc_html_e('Prompts', 'kaais'); ?></h2>
                <p class="description"><?php esc_html_e('Edit the prompts shown in AI platform dropdowns. Use {url} where the post URL should appear.', 'kaais'); ?></p>

                <?php foreach ($settings['prompts'] as $label => $text) : ?>
                    <div class="kaais-prompt">
                        <label><?php echo esc_html($label); ?></label>
                        <textarea name="kaais_settings[prompts][<?php echo esc_attr($label); ?>]"
                                  rows="2"
                                  class="large-text"><?php echo esc_textarea($text); ?></textarea>
                    </div>
                <?php endforeach; ?>

                <h2><?php esc_html_e('Display Settings', 'kaais'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Auto-insert', 'kaais'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox"
                                       name="kaais_settings[auto_insert]"
                                       value="1"
                                       <?php checked($settings['auto_insert']); ?>>
                                <?php esc_html_e('Automatically add buttons to post content', 'kaais'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Post Types', 'kaais'); ?></th>
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
                        <th scope="row"><?php esc_html_e('Labels', 'kaais'); ?></th>
                        <td>
                            <input type="text"
                                   name="kaais_settings[ai_label]"
                                   value="<?php echo esc_attr($settings['ai_label']); ?>"
                                   class="regular-text"
                                   placeholder="<?php esc_attr_e('AI section label', 'kaais'); ?>">
                            <p class="description"><?php esc_html_e('Label shown above AI buttons', 'kaais'); ?></p>
                            <input type="text"
                                   name="kaais_settings[social_label]"
                                   value="<?php echo esc_attr($settings['social_label']); ?>"
                                   class="regular-text"
                                   style="margin-top: 0.5em;"
                                   placeholder="<?php esc_attr_e('Social section label', 'kaais'); ?>">
                            <p class="description"><?php esc_html_e('Label shown above social buttons', 'kaais'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Disable CSS', 'kaais'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox"
                                       name="kaais_settings[disable_css]"
                                       value="1"
                                       <?php checked($settings['disable_css']); ?>>
                                <?php esc_html_e('Do not load plugin styles (for theme developers)', 'kaais'); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <hr>
            <h2><?php esc_html_e('Usage', 'kaais'); ?></h2>
            <p><strong><?php esc_html_e('Shortcode:', 'kaais'); ?></strong> <code>[kaais_share_buttons]</code></p>
            <p><strong><?php esc_html_e('PHP:', 'kaais'); ?></strong> <code>&lt;?php kaais_share_buttons(); ?&gt;</code></p>
        </div>

        <script>
        (function() {
            var container = document.getElementById('kaais-custom-networks');
            var template = document.getElementById('kaais-network-template');
            var addBtn = document.getElementById('kaais-add-network');
            var index = <?php echo count($custom_networks); ?>;

            addBtn.addEventListener('click', function() {
                var html = template.innerHTML.replace(/__INDEX__/g, index++);
                container.insertAdjacentHTML('beforeend', html);
            });

            container.addEventListener('click', function(e) {
                if (e.target.classList.contains('kaais-remove-network')) {
                    e.target.closest('.kaais-custom-network').remove();
                }
            });
        })();
        </script>
        <?php
    }
}

new KAAIS_Settings();

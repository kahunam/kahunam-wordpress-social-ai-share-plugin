<?php
/**
 * Integration tests for settings functionality
 *
 * These tests require WordPress test framework.
 * Run with: TESTSUITE=integration vendor/bin/phpunit --testsuite integration
 */

class SettingsIntegrationTest extends WP_UnitTestCase {

    /**
     * Set up before each test
     */
    public function setUp(): void {
        parent::setUp();
        // Clear any existing settings
        delete_option('kaais_settings');
    }

    /**
     * Clean up after each test
     */
    public function tearDown(): void {
        delete_option('kaais_settings');
        parent::tearDown();
    }

    /**
     * Test plugin activation creates default settings
     */
    public function test_activation_creates_defaults(): void {
        // Simulate activation
        kaais_activate();

        $settings = get_option('kaais_settings');

        $this->assertIsArray($settings);
        $this->assertArrayHasKey('ai_platforms', $settings);
        $this->assertArrayHasKey('social_networks', $settings);
        $this->assertArrayHasKey('prompts', $settings);
    }

    /**
     * Test settings can be saved and retrieved
     */
    public function test_settings_persist(): void {
        $custom_settings = kaais_get_defaults();
        $custom_settings['ai_platforms']['perplexity'] = true;
        $custom_settings['social_networks']['twitter'] = true;

        update_option('kaais_settings', $custom_settings);

        $retrieved = kaais_get_settings();

        $this->assertTrue($retrieved['ai_platforms']['perplexity']);
        $this->assertTrue($retrieved['social_networks']['twitter']);
    }

    /**
     * Test custom networks are saved correctly
     */
    public function test_custom_networks_save(): void {
        $settings = kaais_get_defaults();
        $settings['custom_networks'] = [
            [
                'name' => 'Test AI',
                'icon' => 'https://example.com/icon.svg',
                'url_template' => 'https://test-ai.com/?q={prompt}',
            ],
        ];

        update_option('kaais_settings', $settings);

        $retrieved = kaais_get_settings();

        $this->assertCount(1, $retrieved['custom_networks']);
        $this->assertEquals('Test AI', $retrieved['custom_networks'][0]['name']);
    }

    /**
     * Test prompts can be modified
     */
    public function test_prompts_can_be_modified(): void {
        $settings = kaais_get_defaults();
        $settings['prompts']['Key takeaways'] = 'Custom prompt text with {url}';

        update_option('kaais_settings', $settings);

        $retrieved = kaais_get_settings();

        $this->assertEquals(
            'Custom prompt text with {url}',
            $retrieved['prompts']['Key takeaways']
        );
    }

    /**
     * Test kaais_get_settings merges with defaults
     */
    public function test_get_settings_merges_defaults(): void {
        // Save partial settings
        update_option('kaais_settings', [
            'ai_platforms' => ['chatgpt' => false],
        ]);

        $settings = kaais_get_settings();

        // Should have ChatGPT disabled
        $this->assertFalse($settings['ai_platforms']['chatgpt']);

        // But other defaults should exist
        $this->assertArrayHasKey('social_networks', $settings);
        $this->assertArrayHasKey('prompts', $settings);
    }

    /**
     * Test display settings are saved correctly
     */
    public function test_display_settings_save(): void {
        $settings = kaais_get_defaults();
        $settings['auto_insert'] = true;
        $settings['post_types'] = ['post', 'page', 'custom_type'];
        $settings['position'] = 'before';
        $settings['disable_css'] = true;

        update_option('kaais_settings', $settings);

        $retrieved = kaais_get_settings();

        $this->assertTrue($retrieved['auto_insert']);
        $this->assertEquals(['post', 'page', 'custom_type'], $retrieved['post_types']);
        $this->assertEquals('before', $retrieved['position']);
        $this->assertTrue($retrieved['disable_css']);
    }

    /**
     * Test labels can be customized
     */
    public function test_custom_labels(): void {
        $settings = kaais_get_defaults();
        $settings['ai_label'] = 'Ask AI';
        $settings['social_label'] = 'Share this';

        update_option('kaais_settings', $settings);

        $retrieved = kaais_get_settings();

        $this->assertEquals('Ask AI', $retrieved['ai_label']);
        $this->assertEquals('Share this', $retrieved['social_label']);
    }

    /**
     * Test advanced settings can be saved
     */
    public function test_advanced_settings_save(): void {
        $settings = kaais_get_defaults();
        $settings['content_priority'] = 50;
        $settings['wrapper_class'] = 'my-wrapper';
        $settings['css_loading'] = 'singular';
        $settings['dropdown_z_index'] = 100;

        update_option('kaais_settings', $settings);

        $retrieved = kaais_get_settings();

        $this->assertEquals(50, $retrieved['content_priority']);
        $this->assertEquals('my-wrapper', $retrieved['wrapper_class']);
        $this->assertEquals('singular', $retrieved['css_loading']);
        $this->assertEquals(100, $retrieved['dropdown_z_index']);
    }

    /**
     * Test layout settings can be saved
     */
    public function test_layout_settings_save(): void {
        $settings = kaais_get_defaults();
        $settings['layout'] = 'stacked-divider';
        $settings['show_labels'] = false;
        $settings['platform_order'] = ['claude', 'chatgpt', 'gemini', 'grok', 'perplexity'];

        update_option('kaais_settings', $settings);

        $retrieved = kaais_get_settings();

        $this->assertEquals('stacked-divider', $retrieved['layout']);
        $this->assertFalse($retrieved['show_labels']);
        $this->assertEquals(['claude', 'chatgpt', 'gemini', 'grok', 'perplexity'], $retrieved['platform_order']);
    }

    /**
     * Test content priority has valid default
     */
    public function test_content_priority_default(): void {
        kaais_activate();

        $settings = kaais_get_settings();

        $this->assertEquals(20, $settings['content_priority']);
    }

    /**
     * Test z-index has valid default
     */
    public function test_dropdown_zindex_default(): void {
        kaais_activate();

        $settings = kaais_get_settings();

        $this->assertEquals(10, $settings['dropdown_z_index']);
    }
}

<?php
/**
 * Unit tests for default settings and platform definitions
 */

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

class DefaultsTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test that defaults contain required keys
     */
    public function test_defaults_has_required_keys(): void {
        $defaults = kaais_get_defaults();

        $this->assertArrayHasKey('ai_platforms', $defaults);
        $this->assertArrayHasKey('social_networks', $defaults);
        $this->assertArrayHasKey('custom_networks', $defaults);
        $this->assertArrayHasKey('prompts', $defaults);
        $this->assertArrayHasKey('auto_insert', $defaults);
        $this->assertArrayHasKey('post_types', $defaults);
        $this->assertArrayHasKey('position', $defaults);
        $this->assertArrayHasKey('disable_css', $defaults);
        $this->assertArrayHasKey('ai_label', $defaults);
        $this->assertArrayHasKey('social_label', $defaults);
    }

    /**
     * Test default AI platforms are configured correctly
     */
    public function test_default_ai_platforms(): void {
        $defaults = kaais_get_defaults();
        $ai_platforms = $defaults['ai_platforms'];

        // Should have 5 AI platforms
        $this->assertCount(5, $ai_platforms);

        // Check enabled by default
        $this->assertTrue($ai_platforms['chatgpt']);
        $this->assertTrue($ai_platforms['claude']);
        $this->assertTrue($ai_platforms['gemini']);
        $this->assertTrue($ai_platforms['grok']);

        // Check disabled by default
        $this->assertFalse($ai_platforms['perplexity']);
    }

    /**
     * Test default social networks are all disabled
     */
    public function test_default_social_networks_disabled(): void {
        $defaults = kaais_get_defaults();
        $social = $defaults['social_networks'];

        // All should be disabled by default
        foreach ($social as $network => $enabled) {
            $this->assertFalse($enabled, "Social network '$network' should be disabled by default");
        }
    }

    /**
     * Test default prompts exist and contain {url} placeholder
     */
    public function test_default_prompts(): void {
        $defaults = kaais_get_defaults();
        $prompts = $defaults['prompts'];

        // Should have 4 prompts
        $this->assertCount(4, $prompts);

        // Each prompt should contain {url} placeholder
        foreach ($prompts as $label => $text) {
            $this->assertStringContainsString(
                '{url}',
                $text,
                "Prompt '$label' should contain {url} placeholder"
            );
        }

        // Check specific prompts exist
        $this->assertArrayHasKey('Key takeaways', $prompts);
        $this->assertArrayHasKey('Explain principles', $prompts);
        $this->assertArrayHasKey('Create action plan', $prompts);
        $this->assertArrayHasKey('Future perspectives', $prompts);
    }

    /**
     * Test custom networks defaults to empty array
     */
    public function test_custom_networks_default_empty(): void {
        $defaults = kaais_get_defaults();

        $this->assertIsArray($defaults['custom_networks']);
        $this->assertEmpty($defaults['custom_networks']);
    }

    /**
     * Test default display settings
     */
    public function test_default_display_settings(): void {
        $defaults = kaais_get_defaults();

        $this->assertFalse($defaults['auto_insert']);
        $this->assertEquals(['post'], $defaults['post_types']);
        $this->assertEquals('after', $defaults['position']);
        $this->assertFalse($defaults['disable_css']);
    }

    /**
     * Test default labels
     */
    public function test_default_labels(): void {
        $defaults = kaais_get_defaults();

        $this->assertEquals('Explore with AI', $defaults['ai_label']);
        $this->assertEquals('Share', $defaults['social_label']);
    }

    /**
     * Test defaults contain advanced settings keys
     */
    public function test_defaults_has_advanced_settings(): void {
        $defaults = kaais_get_defaults();

        $this->assertArrayHasKey('content_priority', $defaults);
        $this->assertArrayHasKey('wrapper_class', $defaults);
        $this->assertArrayHasKey('css_loading', $defaults);
        $this->assertArrayHasKey('dropdown_z_index', $defaults);
    }

    /**
     * Test default advanced settings values
     */
    public function test_default_advanced_settings_values(): void {
        $defaults = kaais_get_defaults();

        $this->assertEquals(20, $defaults['content_priority']);
        $this->assertEquals('', $defaults['wrapper_class']);
        $this->assertEquals('always', $defaults['css_loading']);
        $this->assertEquals(10, $defaults['dropdown_z_index']);
    }

    /**
     * Test defaults contain layout settings
     */
    public function test_defaults_has_layout_settings(): void {
        $defaults = kaais_get_defaults();

        $this->assertArrayHasKey('layout', $defaults);
        $this->assertArrayHasKey('show_labels', $defaults);
        $this->assertArrayHasKey('platform_order', $defaults);
    }

    /**
     * Test default layout settings values
     */
    public function test_default_layout_values(): void {
        $defaults = kaais_get_defaults();

        $this->assertEquals('inline', $defaults['layout']);
        $this->assertTrue($defaults['show_labels']);
        $this->assertEquals([], $defaults['platform_order']);
    }
}

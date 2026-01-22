<?php
/**
 * Unit tests for button rendering
 */

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

class RenderTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();

        // Set up common mocks
        Functions\stubs([
            'get_the_ID' => function() { return 1; },
            'get_permalink' => function($id = null) { return 'http://example.com/test-post/'; },
            'get_the_title' => function($id = null) { return 'Test Post Title'; },
            'apply_filters' => function($tag, $value, ...$args) { return $value; },
            'do_action' => function() {},
            'esc_html' => function($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); },
            'esc_attr' => function($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); },
            'esc_url' => function($url) { return $url; },
            '__' => function($text) { return $text; },
            'wp_parse_args' => function($args, $defaults) { return array_merge($defaults, $args); },
            'sanitize_html_class' => function($class) { return $class; },
            'absint' => function($val) { return abs(intval($val)); },
        ]);
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test render returns empty when no post ID
     */
    public function test_render_returns_empty_without_post_id(): void {
        // Override the stub to return 0/false for no post context
        Functions\when('get_the_ID')->justReturn(0);
        Functions\expect('get_option')->andReturn(kaais_get_defaults());

        $output = kaais_render_buttons(null);

        $this->assertEmpty($output);
    }

    /**
     * Test render returns empty when nothing enabled
     */
    public function test_render_returns_empty_when_nothing_enabled(): void {
        // Mock settings with everything disabled
        Functions\expect('get_option')->andReturn([
            'ai_platforms' => [
                'chatgpt' => false,
                'claude' => false,
                'gemini' => false,
                'grok' => false,
                'perplexity' => false,
            ],
            'social_networks' => [
                'twitter' => false,
                'linkedin' => false,
                'reddit' => false,
                'facebook' => false,
                'whatsapp' => false,
                'email' => false,
            ],
            'custom_networks' => [],
            'prompts' => kaais_get_defaults()['prompts'],
            'ai_label' => 'Explore with AI',
            'social_label' => 'Share',
        ]);

        $output = kaais_render_buttons(1);

        $this->assertEmpty($output);
    }

    /**
     * Test render contains main container
     */
    public function test_render_contains_main_container(): void {
        Functions\expect('get_option')->andReturn(kaais_get_defaults());

        $output = kaais_render_buttons(1);

        $this->assertStringContainsString('class="kaais"', $output);
    }

    /**
     * Test render contains AI section when AI enabled
     */
    public function test_render_contains_ai_section(): void {
        Functions\expect('get_option')->andReturn(kaais_get_defaults());

        $output = kaais_render_buttons(1);

        $this->assertStringContainsString('class="kaais__ai"', $output);
        $this->assertStringContainsString('Explore with AI', $output);
    }

    /**
     * Test render contains dropdown for each enabled AI platform
     */
    public function test_render_contains_ai_dropdowns(): void {
        Functions\expect('get_option')->andReturn(kaais_get_defaults());

        $output = kaais_render_buttons(1);

        // Check for enabled platforms
        $this->assertStringContainsString('data-platform="chatgpt"', $output);
        $this->assertStringContainsString('data-platform="claude"', $output);
        $this->assertStringContainsString('data-platform="gemini"', $output);
        $this->assertStringContainsString('data-platform="grok"', $output);

        // Check perplexity is NOT present (disabled by default)
        $this->assertStringNotContainsString('data-platform="perplexity"', $output);
    }

    /**
     * Test render contains prompt menu items
     */
    public function test_render_contains_prompt_items(): void {
        Functions\expect('get_option')->andReturn(kaais_get_defaults());

        $output = kaais_render_buttons(1);

        $this->assertStringContainsString('Key takeaways', $output);
        $this->assertStringContainsString('Explain principles', $output);
        $this->assertStringContainsString('Create action plan', $output);
        $this->assertStringContainsString('Future perspectives', $output);
    }

    /**
     * Test render does not contain social section when disabled
     */
    public function test_render_excludes_social_when_disabled(): void {
        Functions\expect('get_option')->andReturn(kaais_get_defaults());

        $output = kaais_render_buttons(1);

        // Social is disabled by default
        $this->assertStringNotContainsString('class="kaais__social"', $output);
    }

    /**
     * Test render contains social section when enabled
     */
    public function test_render_contains_social_when_enabled(): void {
        $settings = kaais_get_defaults();
        $settings['social_networks']['twitter'] = true;
        $settings['social_networks']['linkedin'] = true;

        Functions\expect('get_option')->andReturn($settings);

        $output = kaais_render_buttons(1);

        $this->assertStringContainsString('class="kaais__social"', $output);
        $this->assertStringContainsString('Share on X', $output);
        $this->assertStringContainsString('Share on LinkedIn', $output);
    }

    /**
     * Test render contains proper accessibility attributes
     */
    public function test_render_has_accessibility_attributes(): void {
        Functions\expect('get_option')->andReturn(kaais_get_defaults());

        $output = kaais_render_buttons(1);

        $this->assertStringContainsString('aria-label=', $output);
        $this->assertStringContainsString('aria-expanded="false"', $output);
        $this->assertStringContainsString('aria-haspopup="menu"', $output);
        $this->assertStringContainsString('role="menu"', $output);
        $this->assertStringContainsString('role="menuitem"', $output);
    }

    /**
     * Test render contains target="_blank" for external links
     */
    public function test_render_has_external_link_attributes(): void {
        Functions\expect('get_option')->andReturn(kaais_get_defaults());

        $output = kaais_render_buttons(1);

        $this->assertStringContainsString('target="_blank"', $output);
        $this->assertStringContainsString('rel="noopener noreferrer"', $output);
    }

    /**
     * Test render URL encodes prompts
     */
    public function test_render_encodes_prompts(): void {
        Functions\expect('get_option')->andReturn(kaais_get_defaults());

        $output = kaais_render_buttons(1);

        // URLs should contain encoded text (no spaces, special chars encoded)
        $this->assertStringContainsString('chat.openai.com/?q=', $output);

        // Should not have unencoded spaces in prompt URLs
        preg_match_all('/href="([^"]+chat\.openai\.com[^"]+)"/', $output, $matches);
        foreach ($matches[1] as $url) {
            // After the ?q= parameter, spaces should be encoded
            $query_part = substr($url, strpos($url, '?q=') + 3);
            $this->assertStringNotContainsString(' ', $query_part, "URL should not contain unencoded spaces");
        }
    }

    /**
     * Test render replaces {url} in prompts
     */
    public function test_render_replaces_url_placeholder(): void {
        Functions\expect('get_option')->andReturn(kaais_get_defaults());

        $output = kaais_render_buttons(1);

        // The post URL should appear in the rendered URLs (encoded)
        $this->assertStringContainsString(
            rawurlencode('http://example.com/test-post/'),
            $output
        );
    }

    /**
     * Test render applies stacked layout class
     */
    public function test_render_applies_stacked_layout(): void {
        $settings = kaais_get_defaults();
        $settings['layout'] = 'stacked';
        Functions\expect('get_option')->andReturn($settings);

        $output = kaais_render_buttons(1);

        $this->assertStringContainsString('kaais--stacked', $output);
    }

    /**
     * Test render applies divider layout class
     */
    public function test_render_applies_divider_layout(): void {
        $settings = kaais_get_defaults();
        $settings['layout'] = 'divider';
        Functions\expect('get_option')->andReturn($settings);

        $output = kaais_render_buttons(1);

        $this->assertStringContainsString('kaais--divider', $output);
    }

    /**
     * Test render applies stacked-divider layout classes
     */
    public function test_render_applies_stacked_divider_layout(): void {
        $settings = kaais_get_defaults();
        $settings['layout'] = 'stacked-divider';
        Functions\expect('get_option')->andReturn($settings);

        $output = kaais_render_buttons(1);

        $this->assertStringContainsString('kaais--stacked', $output);
        $this->assertStringContainsString('kaais--divider', $output);
    }

    /**
     * Test render includes custom wrapper class
     */
    public function test_render_includes_wrapper_class(): void {
        $settings = kaais_get_defaults();
        $settings['wrapper_class'] = 'my-custom-class';

        Functions\expect('get_option')->andReturn($settings);
        Functions\expect('sanitize_html_class')
            ->andReturnUsing(function($class) { return $class; });

        $output = kaais_render_buttons(1);

        $this->assertStringContainsString('my-custom-class', $output);
    }

    /**
     * Test render adds z-index data attribute when non-default
     */
    public function test_render_adds_zindex_data_when_custom(): void {
        $settings = kaais_get_defaults();
        $settings['dropdown_z_index'] = 50;
        Functions\expect('get_option')->andReturn($settings);

        $output = kaais_render_buttons(1);

        $this->assertStringContainsString('data-zindex="50"', $output);
    }

    /**
     * Test render does not add z-index data when default
     */
    public function test_render_omits_zindex_data_when_default(): void {
        $settings = kaais_get_defaults();
        $settings['dropdown_z_index'] = 10; // default
        Functions\expect('get_option')->andReturn($settings);

        $output = kaais_render_buttons(1);

        $this->assertStringNotContainsString('data-zindex', $output);
    }

    /**
     * Test render hides labels when show_labels is false
     */
    public function test_render_hides_labels_when_disabled(): void {
        $settings = kaais_get_defaults();
        $settings['show_labels'] = false;
        Functions\expect('get_option')->andReturn($settings);

        $output = kaais_render_buttons(1);

        $this->assertStringContainsString('kaais__label--hidden', $output);
    }

    /**
     * Test render shows labels when show_labels is true
     */
    public function test_render_shows_labels_when_enabled(): void {
        $settings = kaais_get_defaults();
        $settings['show_labels'] = true;
        Functions\expect('get_option')->andReturn($settings);

        $output = kaais_render_buttons(1);

        // Should have label class but not hidden modifier
        $this->assertStringContainsString('kaais__label', $output);
        $this->assertStringNotContainsString('kaais__label--hidden', $output);
    }
}

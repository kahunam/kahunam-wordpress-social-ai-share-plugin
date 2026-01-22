<?php
/**
 * Integration tests for shortcode functionality
 *
 * Run with: TESTSUITE=integration vendor/bin/phpunit --testsuite integration
 */

class ShortcodeIntegrationTest extends WP_UnitTestCase {

    protected $post_id;

    public function setUp(): void {
        parent::setUp();

        // Create a test post
        $this->post_id = $this->factory->post->create([
            'post_title' => 'Test Post for Shortcode',
            'post_content' => 'Test content',
            'post_status' => 'publish',
        ]);

        // Reset settings to defaults
        delete_option('kaais_settings');
        update_option('kaais_settings', kaais_get_defaults());
    }

    public function tearDown(): void {
        wp_delete_post($this->post_id, true);
        delete_option('kaais_settings');
        parent::tearDown();
    }

    /**
     * Test shortcode is registered
     */
    public function test_shortcode_registered(): void {
        $this->assertTrue(shortcode_exists('kaais_share_buttons'));
    }

    /**
     * Test shortcode renders output
     */
    public function test_shortcode_renders(): void {
        // Set global post
        global $post;
        $post = get_post($this->post_id);
        setup_postdata($post);

        $output = do_shortcode('[kaais_share_buttons]');

        $this->assertStringContainsString('class="kaais"', $output);
        $this->assertStringContainsString('kaais__ai', $output);

        wp_reset_postdata();
    }

    /**
     * Test shortcode accepts post_id attribute
     */
    public function test_shortcode_accepts_post_id(): void {
        $output = do_shortcode('[kaais_share_buttons post_id="' . $this->post_id . '"]');

        $this->assertStringContainsString('class="kaais"', $output);
    }

    /**
     * Test shortcode renders AI platform buttons
     */
    public function test_shortcode_renders_ai_platforms(): void {
        global $post;
        $post = get_post($this->post_id);
        setup_postdata($post);

        $output = do_shortcode('[kaais_share_buttons]');

        // Default enabled platforms
        $this->assertStringContainsString('data-platform="chatgpt"', $output);
        $this->assertStringContainsString('data-platform="claude"', $output);
        $this->assertStringContainsString('data-platform="gemini"', $output);
        $this->assertStringContainsString('data-platform="grok"', $output);

        wp_reset_postdata();
    }

    /**
     * Test shortcode includes post URL in prompts
     */
    public function test_shortcode_includes_post_url(): void {
        global $post;
        $post = get_post($this->post_id);
        setup_postdata($post);

        $output = do_shortcode('[kaais_share_buttons]');

        $post_url = get_permalink($this->post_id);
        $encoded_url = rawurlencode($post_url);

        $this->assertStringContainsString($encoded_url, $output);

        wp_reset_postdata();
    }

    /**
     * Test shortcode respects disabled platforms
     */
    public function test_shortcode_respects_settings(): void {
        // Disable all AI platforms
        $settings = kaais_get_defaults();
        $settings['ai_platforms'] = array_fill_keys(array_keys($settings['ai_platforms']), false);
        $settings['social_networks']['twitter'] = true;
        update_option('kaais_settings', $settings);

        global $post;
        $post = get_post($this->post_id);
        setup_postdata($post);

        $output = do_shortcode('[kaais_share_buttons]');

        // Should not have AI section
        $this->assertStringNotContainsString('kaais__ai', $output);

        // Should have social section
        $this->assertStringContainsString('kaais__social', $output);

        wp_reset_postdata();
    }
}

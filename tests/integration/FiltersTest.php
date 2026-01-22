<?php
/**
 * Integration tests for filters and hooks
 *
 * Run with: TESTSUITE=integration vendor/bin/phpunit --testsuite integration
 */

class FiltersIntegrationTest extends WP_UnitTestCase {

    protected $post_id;

    public function setUp(): void {
        parent::setUp();

        $this->post_id = $this->factory->post->create([
            'post_title' => 'Filter Test Post',
            'post_content' => 'Test content',
            'post_status' => 'publish',
        ]);

        delete_option('kaais_settings');
        update_option('kaais_settings', kaais_get_defaults());
    }

    public function tearDown(): void {
        wp_delete_post($this->post_id, true);
        delete_option('kaais_settings');

        // Remove any filters we added
        remove_all_filters('kaais_ai_platforms');
        remove_all_filters('kaais_social_networks');
        remove_all_filters('kaais_prompts');
        remove_all_filters('kaais_output');

        parent::tearDown();
    }

    /**
     * Test kaais_ai_platforms filter can add platforms
     */
    public function test_ai_platforms_filter_add(): void {
        add_filter('kaais_ai_platforms', function($platforms) {
            $platforms['custom_ai'] = [
                'name' => 'Custom AI',
                'url' => 'https://custom-ai.com/?q={prompt}',
                'icon' => '<svg></svg>',
            ];
            return $platforms;
        });

        $platforms = kaais_get_ai_platforms();

        $this->assertArrayHasKey('custom_ai', $platforms);
        $this->assertEquals('Custom AI', $platforms['custom_ai']['name']);
    }

    /**
     * Test kaais_ai_platforms filter can remove platforms
     */
    public function test_ai_platforms_filter_remove(): void {
        add_filter('kaais_ai_platforms', function($platforms) {
            unset($platforms['perplexity']);
            return $platforms;
        });

        $platforms = kaais_get_ai_platforms();

        $this->assertArrayNotHasKey('perplexity', $platforms);
    }

    /**
     * Test kaais_social_networks filter can add networks
     */
    public function test_social_networks_filter_add(): void {
        add_filter('kaais_social_networks', function($networks) {
            $networks['mastodon'] = [
                'name' => 'Mastodon',
                'url' => 'https://mastodon.social/share?text={title}&url={url}',
                'icon' => '<svg></svg>',
            ];
            return $networks;
        });

        $networks = kaais_get_social_networks();

        $this->assertArrayHasKey('mastodon', $networks);
        $this->assertEquals('Mastodon', $networks['mastodon']['name']);
    }

    /**
     * Test kaais_prompts filter can modify prompts
     */
    public function test_prompts_filter_modify(): void {
        add_filter('kaais_prompts', function($prompts) {
            $prompts['Custom Prompt'] = 'Custom prompt text {url}';
            unset($prompts['Future perspectives']);
            return $prompts;
        });

        global $post;
        $post = get_post($this->post_id);
        setup_postdata($post);

        $output = kaais_render_buttons($this->post_id);

        $this->assertStringContainsString('Custom Prompt', $output);
        $this->assertStringNotContainsString('Future perspectives', $output);

        wp_reset_postdata();
    }

    /**
     * Test kaais_output filter can modify final HTML
     */
    public function test_output_filter(): void {
        add_filter('kaais_output', function($html, $post_id) {
            return '<div class="custom-wrapper">' . $html . '</div>';
        }, 10, 2);

        global $post;
        $post = get_post($this->post_id);
        setup_postdata($post);

        $output = kaais_render_buttons($this->post_id);

        $this->assertStringStartsWith('<div class="custom-wrapper">', $output);
        $this->assertStringEndsWith('</div>', $output);

        wp_reset_postdata();
    }

    /**
     * Test kaais_output filter receives post_id
     */
    public function test_output_filter_receives_post_id(): void {
        $received_post_id = null;

        add_filter('kaais_output', function($html, $post_id) use (&$received_post_id) {
            $received_post_id = $post_id;
            return $html;
        }, 10, 2);

        global $post;
        $post = get_post($this->post_id);
        setup_postdata($post);

        kaais_render_buttons($this->post_id);

        $this->assertEquals($this->post_id, $received_post_id);

        wp_reset_postdata();
    }

    /**
     * Test action hooks fire in correct order
     */
    public function test_action_hooks_order(): void {
        $hook_order = [];

        add_action('kaais_before_buttons', function() use (&$hook_order) {
            $hook_order[] = 'before_buttons';
        });

        add_action('kaais_before_ai_section', function() use (&$hook_order) {
            $hook_order[] = 'before_ai';
        });

        add_action('kaais_after_ai_section', function() use (&$hook_order) {
            $hook_order[] = 'after_ai';
        });

        add_action('kaais_after_buttons', function() use (&$hook_order) {
            $hook_order[] = 'after_buttons';
        });

        global $post;
        $post = get_post($this->post_id);
        setup_postdata($post);

        kaais_render_buttons($this->post_id);

        $expected = ['before_buttons', 'before_ai', 'after_ai', 'after_buttons'];
        $this->assertEquals($expected, $hook_order);

        wp_reset_postdata();
    }

    /**
     * Test social section hooks only fire when social enabled
     */
    public function test_social_hooks_only_when_enabled(): void {
        $social_hook_fired = false;

        add_action('kaais_before_social_section', function() use (&$social_hook_fired) {
            $social_hook_fired = true;
        });

        global $post;
        $post = get_post($this->post_id);
        setup_postdata($post);

        // Social disabled by default
        kaais_render_buttons($this->post_id);

        $this->assertFalse($social_hook_fired, 'Social hook should not fire when social disabled');

        // Enable social
        $settings = kaais_get_settings();
        $settings['social_networks']['twitter'] = true;
        update_option('kaais_settings', $settings);

        kaais_render_buttons($this->post_id);

        $this->assertTrue($social_hook_fired, 'Social hook should fire when social enabled');

        wp_reset_postdata();
    }
}

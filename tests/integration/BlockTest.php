<?php
/**
 * Integration tests for Gutenberg block registration
 *
 * Run with: TESTSUITE=integration vendor/bin/phpunit --testsuite integration
 */

class BlockIntegrationTest extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        delete_option('kaais_settings');
    }

    public function tearDown(): void {
        delete_option('kaais_settings');
        parent::tearDown();
    }

    /**
     * Test block is registered
     */
    public function test_block_is_registered(): void {
        // Trigger block registration
        do_action('init');

        $registered = WP_Block_Type_Registry::get_instance()->is_registered('kaais/share-buttons');

        // Block should be registered if build files exist
        $build_exists = file_exists(KAAIS_PATH . 'build/blocks/share-buttons/block.json');

        if ($build_exists) {
            $this->assertTrue($registered, 'Block should be registered when build files exist');
        } else {
            $this->assertFalse($registered, 'Block should not be registered when build files missing');
        }
    }

    /**
     * Test block has correct attributes
     */
    public function test_block_has_correct_name(): void {
        $build_exists = file_exists(KAAIS_PATH . 'build/blocks/share-buttons/block.json');

        if (!$build_exists) {
            $this->markTestSkipped('Block build files not present');
        }

        do_action('init');

        $registry = WP_Block_Type_Registry::get_instance();
        $block = $registry->get_registered('kaais/share-buttons');

        $this->assertNotNull($block);
        $this->assertEquals('kaais/share-buttons', $block->name);
    }

    /**
     * Test block renders using PHP callback
     */
    public function test_block_renders_via_php(): void {
        $build_exists = file_exists(KAAIS_PATH . 'build/blocks/share-buttons/block.json');

        if (!$build_exists) {
            $this->markTestSkipped('Block build files not present');
        }

        // Create a test post
        $post_id = $this->factory->post->create([
            'post_title' => 'Block Test Post',
            'post_content' => '<!-- wp:kaais/share-buttons /-->',
            'post_status' => 'publish',
        ]);

        // Enable default platforms
        update_option('kaais_settings', kaais_get_defaults());

        // Go to the post
        $this->go_to(get_permalink($post_id));

        global $post;
        $post = get_post($post_id);
        setup_postdata($post);

        // Render the block
        $output = do_blocks($post->post_content);

        // Should contain share buttons
        $this->assertStringContainsString('kaais', $output);

        wp_reset_postdata();
        wp_delete_post($post_id, true);
    }

    /**
     * Test block returns empty when no platforms enabled
     */
    public function test_block_returns_empty_when_nothing_enabled(): void {
        $build_exists = file_exists(KAAIS_PATH . 'build/blocks/share-buttons/block.json');

        if (!$build_exists) {
            $this->markTestSkipped('Block build files not present');
        }

        // Create a test post
        $post_id = $this->factory->post->create([
            'post_title' => 'Block Test Post',
            'post_content' => '<!-- wp:kaais/share-buttons /-->',
            'post_status' => 'publish',
        ]);

        // Disable all platforms
        $settings = kaais_get_defaults();
        $settings['ai_platforms'] = [
            'chatgpt' => false,
            'claude' => false,
            'gemini' => false,
            'grok' => false,
            'perplexity' => false,
        ];
        update_option('kaais_settings', $settings);

        $this->go_to(get_permalink($post_id));

        global $post;
        $post = get_post($post_id);
        setup_postdata($post);

        $output = do_blocks($post->post_content);

        // Should not contain share buttons container
        $this->assertStringNotContainsString('class="kaais"', $output);

        wp_reset_postdata();
        wp_delete_post($post_id, true);
    }

    /**
     * Test block in widget category
     */
    public function test_block_in_widgets_category(): void {
        $build_exists = file_exists(KAAIS_PATH . 'build/blocks/share-buttons/block.json');

        if (!$build_exists) {
            $this->markTestSkipped('Block build files not present');
        }

        do_action('init');

        $registry = WP_Block_Type_Registry::get_instance();
        $block = $registry->get_registered('kaais/share-buttons');

        $this->assertEquals('widgets', $block->category);
    }
}

<?php
/**
 * Integration tests for auto-insert functionality
 *
 * Run with: TESTSUITE=integration vendor/bin/phpunit --testsuite integration
 */

class AutoInsertIntegrationTest extends WP_UnitTestCase {

    protected $post_id;

    public function setUp(): void {
        parent::setUp();

        $this->post_id = $this->factory->post->create([
            'post_title' => 'Test Post',
            'post_content' => '<p>Original content here.</p>',
            'post_status' => 'publish',
        ]);

        delete_option('kaais_settings');
    }

    public function tearDown(): void {
        wp_delete_post($this->post_id, true);
        delete_option('kaais_settings');
        parent::tearDown();
    }

    /**
     * Test auto-insert is disabled by default
     */
    public function test_auto_insert_disabled_by_default(): void {
        update_option('kaais_settings', kaais_get_defaults());

        $content = '<p>Test content</p>';
        $filtered = apply_filters('the_content', $content);

        // Should not contain share buttons when auto_insert is false
        $this->assertStringNotContainsString('class="kaais"', $filtered);
    }

    /**
     * Test auto-insert adds buttons after content
     */
    public function test_auto_insert_after_content(): void {
        $settings = kaais_get_defaults();
        $settings['auto_insert'] = true;
        $settings['position'] = 'after';
        update_option('kaais_settings', $settings);

        // Simulate singular view
        $this->go_to(get_permalink($this->post_id));

        global $post;
        $post = get_post($this->post_id);
        setup_postdata($post);

        $content = '<p>Test content</p>';
        $filtered = kaais_auto_insert($content);

        // Content should come first
        $this->assertStringStartsWith('<p>Test content</p>', $filtered);
        // Buttons should be at the end
        $this->assertStringContainsString('class="kaais"', $filtered);

        wp_reset_postdata();
    }

    /**
     * Test auto-insert adds buttons before content
     */
    public function test_auto_insert_before_content(): void {
        $settings = kaais_get_defaults();
        $settings['auto_insert'] = true;
        $settings['position'] = 'before';
        update_option('kaais_settings', $settings);

        $this->go_to(get_permalink($this->post_id));

        global $post;
        $post = get_post($this->post_id);
        setup_postdata($post);

        $content = '<p>Test content</p>';
        $filtered = kaais_auto_insert($content);

        // Buttons should be at the start
        $this->assertStringStartsWith('<div class="kaais">', $filtered);
        // Content should be at the end
        $this->assertStringEndsWith('</p>', $filtered);

        wp_reset_postdata();
    }

    /**
     * Test auto-insert adds buttons both positions
     */
    public function test_auto_insert_both_positions(): void {
        $settings = kaais_get_defaults();
        $settings['auto_insert'] = true;
        $settings['position'] = 'both';
        update_option('kaais_settings', $settings);

        $this->go_to(get_permalink($this->post_id));

        global $post;
        $post = get_post($this->post_id);
        setup_postdata($post);

        $content = '<p>Test content</p>';
        $filtered = kaais_auto_insert($content);

        // Should have buttons at start
        $this->assertStringStartsWith('<div class="kaais">', $filtered);

        // Count occurrences of kaais container
        $count = substr_count($filtered, 'class="kaais"');
        $this->assertEquals(2, $count, 'Should have buttons before and after');

        wp_reset_postdata();
    }

    /**
     * Test auto-insert respects post type setting
     */
    public function test_auto_insert_respects_post_types(): void {
        // Create a page
        $page_id = $this->factory->post->create([
            'post_type' => 'page',
            'post_title' => 'Test Page',
            'post_content' => 'Page content',
            'post_status' => 'publish',
        ]);

        $settings = kaais_get_defaults();
        $settings['auto_insert'] = true;
        $settings['post_types'] = ['post']; // Only posts, not pages
        update_option('kaais_settings', $settings);

        // Go to the page
        $this->go_to(get_permalink($page_id));

        global $post;
        $post = get_post($page_id);
        setup_postdata($post);

        $content = '<p>Page content</p>';
        $filtered = kaais_auto_insert($content);

        // Should NOT have buttons on page
        $this->assertStringNotContainsString('class="kaais"', $filtered);

        wp_reset_postdata();
        wp_delete_post($page_id, true);
    }

    /**
     * Test auto-insert only works on singular views
     */
    public function test_auto_insert_only_singular(): void {
        $settings = kaais_get_defaults();
        $settings['auto_insert'] = true;
        update_option('kaais_settings', $settings);

        // Go to archive/home (not singular)
        $this->go_to('/');

        $content = '<p>Archive content</p>';
        $filtered = kaais_auto_insert($content);

        // Should NOT have buttons on non-singular
        $this->assertEquals($content, $filtered);
    }

    /**
     * Test auto-insert skips password-protected posts
     */
    public function test_auto_insert_skips_password_protected(): void {
        // Create password-protected post
        $protected_post_id = $this->factory->post->create([
            'post_title' => 'Protected Post',
            'post_content' => 'Secret content',
            'post_status' => 'publish',
            'post_password' => 'secret123',
        ]);

        $settings = kaais_get_defaults();
        $settings['auto_insert'] = true;
        update_option('kaais_settings', $settings);

        $this->go_to(get_permalink($protected_post_id));

        global $post;
        $post = get_post($protected_post_id);
        setup_postdata($post);

        $content = '<p>Protected content</p>';
        $filtered = kaais_auto_insert($content);

        // Should NOT have buttons on password-protected posts
        $this->assertStringNotContainsString('class="kaais"', $filtered);

        wp_reset_postdata();
        wp_delete_post($protected_post_id, true);
    }

    /**
     * Test auto-insert skips feed requests
     */
    public function test_auto_insert_skips_feed(): void {
        $settings = kaais_get_defaults();
        $settings['auto_insert'] = true;
        update_option('kaais_settings', $settings);

        // Go to feed
        $this->go_to('/?feed=rss2');

        $content = '<p>Feed content</p>';
        $filtered = kaais_auto_insert($content);

        // Should NOT have buttons in feeds
        $this->assertEquals($content, $filtered);
    }

    /**
     * Test auto-insert works with custom post types when configured
     */
    public function test_auto_insert_works_with_custom_post_types(): void {
        // Register a custom post type for testing
        register_post_type('book', [
            'public' => true,
            'label' => 'Books',
        ]);

        $book_id = $this->factory->post->create([
            'post_type' => 'book',
            'post_title' => 'Test Book',
            'post_content' => 'Book content',
            'post_status' => 'publish',
        ]);

        $settings = kaais_get_defaults();
        $settings['auto_insert'] = true;
        $settings['post_types'] = ['post', 'book'];
        update_option('kaais_settings', $settings);

        $this->go_to(get_permalink($book_id));

        global $post;
        $post = get_post($book_id);
        setup_postdata($post);

        $content = '<p>Book content</p>';
        $filtered = kaais_auto_insert($content);

        // Should have buttons on custom post type
        $this->assertStringContainsString('class="kaais"', $filtered);

        wp_reset_postdata();
        wp_delete_post($book_id, true);
        unregister_post_type('book');
    }

    /**
     * Test layout classes are applied in auto-insert output
     */
    public function test_auto_insert_applies_layout_class(): void {
        $settings = kaais_get_defaults();
        $settings['auto_insert'] = true;
        $settings['layout'] = 'stacked';
        update_option('kaais_settings', $settings);

        $this->go_to(get_permalink($this->post_id));

        global $post;
        $post = get_post($this->post_id);
        setup_postdata($post);

        $content = '<p>Test content</p>';
        $filtered = kaais_auto_insert($content);

        $this->assertStringContainsString('kaais--stacked', $filtered);

        wp_reset_postdata();
    }
}

<?php
/**
 * Unit tests for AI platform and social network definitions
 */

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

class PlatformsTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test AI platforms have required structure
     */
    public function test_ai_platforms_structure(): void {
        $platforms = kaais_get_ai_platforms();

        $this->assertIsArray($platforms);
        $this->assertNotEmpty($platforms);

        foreach ($platforms as $id => $platform) {
            $this->assertIsString($id, "Platform ID should be string");
            $this->assertArrayHasKey('name', $platform, "Platform '$id' missing 'name'");
            $this->assertArrayHasKey('url', $platform, "Platform '$id' missing 'url'");
            $this->assertArrayHasKey('icon', $platform, "Platform '$id' missing 'icon'");
        }
    }

    /**
     * Test AI platform URLs contain {prompt} placeholder
     */
    public function test_ai_platform_urls_have_prompt_placeholder(): void {
        $platforms = kaais_get_ai_platforms();

        foreach ($platforms as $id => $platform) {
            $this->assertStringContainsString(
                '{prompt}',
                $platform['url'],
                "Platform '$id' URL should contain {prompt} placeholder"
            );
        }
    }

    /**
     * Test AI platform icons are valid SVG
     */
    public function test_ai_platform_icons_are_svg(): void {
        $platforms = kaais_get_ai_platforms();

        foreach ($platforms as $id => $platform) {
            $this->assertStringStartsWith(
                '<svg',
                $platform['icon'],
                "Platform '$id' icon should be SVG"
            );
            $this->assertStringContainsString(
                '</svg>',
                $platform['icon'],
                "Platform '$id' icon should have closing SVG tag"
            );
        }
    }

    /**
     * Test specific AI platforms exist
     */
    public function test_required_ai_platforms_exist(): void {
        $platforms = kaais_get_ai_platforms();

        $required = ['chatgpt', 'claude', 'gemini', 'grok', 'perplexity'];

        foreach ($required as $platform_id) {
            $this->assertArrayHasKey(
                $platform_id,
                $platforms,
                "Required platform '$platform_id' should exist"
            );
        }
    }

    /**
     * Test ChatGPT URL is correctly formatted
     */
    public function test_chatgpt_url(): void {
        $platforms = kaais_get_ai_platforms();

        $this->assertStringContainsString(
            'chat.openai.com',
            $platforms['chatgpt']['url']
        );
        $this->assertStringContainsString(
            '?q={prompt}',
            $platforms['chatgpt']['url']
        );
    }

    /**
     * Test Claude URL is correctly formatted
     */
    public function test_claude_url(): void {
        $platforms = kaais_get_ai_platforms();

        $this->assertStringContainsString(
            'claude.ai/new',
            $platforms['claude']['url']
        );
        $this->assertStringContainsString(
            '?q={prompt}',
            $platforms['claude']['url']
        );
    }

    /**
     * Test Grok URL uses correct parameter
     */
    public function test_grok_url(): void {
        $platforms = kaais_get_ai_platforms();

        $this->assertStringContainsString(
            'x.com/i/grok',
            $platforms['grok']['url']
        );
        // Grok uses ?text= not ?q=
        $this->assertStringContainsString(
            '?text={prompt}',
            $platforms['grok']['url']
        );
    }

    /**
     * Test social networks have required structure
     */
    public function test_social_networks_structure(): void {
        $networks = kaais_get_social_networks();

        $this->assertIsArray($networks);
        $this->assertNotEmpty($networks);

        foreach ($networks as $id => $network) {
            $this->assertIsString($id, "Network ID should be string");
            $this->assertArrayHasKey('name', $network, "Network '$id' missing 'name'");
            $this->assertArrayHasKey('url', $network, "Network '$id' missing 'url'");
            $this->assertArrayHasKey('icon', $network, "Network '$id' missing 'icon'");
        }
    }

    /**
     * Test social network URLs contain required placeholders
     */
    public function test_social_network_urls_have_placeholders(): void {
        $networks = kaais_get_social_networks();

        foreach ($networks as $id => $network) {
            // Should have {url} and/or {title}
            $has_url = strpos($network['url'], '{url}') !== false;
            $has_title = strpos($network['url'], '{title}') !== false;

            $this->assertTrue(
                $has_url || $has_title,
                "Network '$id' URL should contain {url} or {title} placeholder"
            );
        }
    }

    /**
     * Test required social networks exist
     */
    public function test_required_social_networks_exist(): void {
        $networks = kaais_get_social_networks();

        $required = ['twitter', 'linkedin', 'reddit', 'facebook', 'whatsapp', 'email'];

        foreach ($required as $network_id) {
            $this->assertArrayHasKey(
                $network_id,
                $networks,
                "Required network '$network_id' should exist"
            );
        }
    }

    /**
     * Test Twitter/X URL format
     */
    public function test_twitter_url(): void {
        $networks = kaais_get_social_networks();

        $this->assertStringContainsString(
            'x.com/intent/tweet',
            $networks['twitter']['url']
        );
    }

    /**
     * Test email URL is mailto
     */
    public function test_email_url(): void {
        $networks = kaais_get_social_networks();

        $this->assertStringStartsWith(
            'mailto:',
            $networks['email']['url']
        );
    }
}

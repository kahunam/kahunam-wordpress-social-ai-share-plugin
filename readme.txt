=== AI Share Buttons ===
Contributors: kahunam
Tags: share buttons, ai, chatgpt, claude, social sharing, gemini, grok, perplexity
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Share buttons for AI platforms and social networks. Let visitors explore your content with ChatGPT, Claude, Gemini, Grok, and more.

== Description ==

AI Share Buttons adds a new way for visitors to engage with your content. Beyond traditional social sharing, visitors can send your articles directly to AI platforms like ChatGPT, Claude, Gemini, and Grok with pre-configured prompts.

**AI Platforms (5 built-in):**

* ChatGPT (OpenAI)
* Claude (Anthropic)
* Gemini (Google)
* Grok (X/Twitter)
* Perplexity

**Social Networks (6 built-in):**

* X (Twitter)
* LinkedIn
* Reddit
* Facebook
* WhatsApp
* Email

**Key Features:**

* **CSS-only dropdowns** - No JavaScript required, fast and lightweight
* **4 customizable prompts** - Key takeaways, Explain principles, Create action plan, Future perspectives
* **Custom networks** - Add any AI platform with a URL template
* **Developer friendly** - Hooks, filters, and a "disable CSS" option for theme developers
* **Auto-insert** - Automatically add buttons after post content
* **Shortcode support** - Place buttons anywhere with `[kaais_share_buttons]`

== Installation ==

1. Upload the `ai-share-buttons` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure settings under Settings → AI Share Buttons

== Frequently Asked Questions ==

= How do I add the buttons to my posts? =

Three options:
1. Enable "Auto-insert" in settings to automatically add after content
2. Use the shortcode: `[kaais_share_buttons]`
3. Use PHP in your theme: `<?php kaais_share_buttons(); ?>`

= Can I add other AI platforms? =

Yes! Go to Settings → AI Share Buttons → Custom Networks. Add a name, icon URL, and URL template with `{prompt}` placeholder.

Example for Microsoft Copilot:
`https://copilot.microsoft.com/?q={prompt}`

= How do I customize the prompts? =

Edit the prompt text in Settings → AI Share Buttons → Prompts. Use `{url}` where you want the post URL to appear.

= Can I disable the plugin's CSS? =

Yes, check "Disable CSS" in Display Settings. Then add your own styles targeting the `.kaais` classes.

= Is JavaScript required? =

No! The dropdowns are CSS-only using `:hover` and `:focus-within`. No JavaScript is loaded on the frontend.

== Screenshots ==

1. AI share buttons with dropdown menu
2. Admin settings page
3. Custom network configuration

== Changelog ==

= 2.0.0 =
* Complete rebuild from scratch
* Reduced codebase from 3500+ to ~1000 lines
* CSS-only dropdowns (no JavaScript)
* Added custom network support
* Added 4 customizable prompts
* Simplified admin interface
* Added developer hooks and filters
* BEM CSS naming convention

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 2.0.0 =
Major update with breaking changes. All function names changed from `ai_share_*` to `kaais_*`. Settings will need to be reconfigured.

== Developer Documentation ==

**Action Hooks:**

* `kaais_before_buttons` - Before the container
* `kaais_after_buttons` - After the container
* `kaais_before_ai_section` - Before AI buttons
* `kaais_after_ai_section` - After AI buttons
* `kaais_before_social_section` - Before social links
* `kaais_after_social_section` - After social links

**Filter Hooks:**

* `kaais_ai_platforms` - Modify AI platform list
* `kaais_social_networks` - Modify social network list
* `kaais_prompts` - Modify prompt list
* `kaais_output` - Filter final HTML output

See the full documentation on GitHub.

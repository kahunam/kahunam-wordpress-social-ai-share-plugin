# AI Share Buttons Plugin - Rebuild Design

## Overview

Rebuild the AI Share Buttons WordPress plugin from scratch. The current implementation is 3500+ lines of over-engineered code. The new version will be ~650 lines, focused, and maintainable.

## Core Purpose

Allow visitors to share content to AI platforms (with prompt dropdowns) and social networks. CSS-only dropdowns, no JavaScript required.

## File Structure

```
ai-share-buttons/
├── ai-share-buttons.php          # Main plugin file (~300 lines)
├── includes/
│   └── class-aisb-settings.php   # Admin settings page (~200 lines)
├── assets/
│   └── css/
│       └── aisb-frontend.css     # BEM styles (~150 lines)
├── uninstall.php                 # Cleanup on delete
└── README.md                     # Developer documentation
```

## Platforms

### AI Platforms (5 built-in, verified working)

| Platform | Default | URL Pattern |
|----------|---------|-------------|
| ChatGPT | Enabled | `https://chat.openai.com/?q={prompt}` |
| Claude | Enabled | `https://claude.ai/new?q={prompt}` |
| Gemini | Enabled | `https://gemini.google.com/app?q={prompt}` |
| Grok | Enabled | `https://x.com/i/grok?text={prompt}` |
| Perplexity | Disabled | `https://www.perplexity.ai/search?q={prompt}` |

### Custom Networks

Users can add their own AI platforms with:
- Name
- Icon (URL or upload)
- URL template with `{prompt}` placeholder

### Social Networks (6 built-in, all disabled by default)

| Network | URL Pattern |
|---------|-------------|
| X/Twitter | `https://x.com/intent/tweet?text={title}&url={url}` |
| LinkedIn | `https://www.linkedin.com/shareArticle?mini=true&url={url}` |
| Reddit | `https://reddit.com/submit?url={url}&title={title}` |
| Facebook | `https://www.facebook.com/sharer/sharer.php?u={url}` |
| WhatsApp | `https://wa.me/?text={title}%20{url}` |
| Email | `mailto:?subject={title}&body={url}` |

## Default Prompts (4, shared across all AI platforms)

1. **Key takeaways** - "Synthesize the core message of this article {url} and provide the 5 most impactful insights in a clear, bulleted format."
2. **Explain principles** - "Identify the fundamental concepts in this article {url} and explain how they work together to solve a problem or create value."
3. **Create action plan** - "Based on the insights from {url}, outline a practical, step-by-step plan for someone to implement these ideas today."
4. **Future perspectives** - "Analyze the broader implications of this article {url}. How do these ideas connect to emerging trends, and what is the potential long-term impact?"

## Admin Settings

Single page under **Settings → AI Share Buttons** using native WordPress UI.

### Sections

1. **AI Platforms** - Checkbox for each built-in platform
2. **Custom Networks** - Add/edit/delete custom AI platforms
3. **Social Networks** - Checkbox for each social network
4. **Prompts** - 4 editable text fields
5. **Display Options:**
   - Auto-insert: checkbox
   - Post types: multi-select
   - Position: Before/After/Both content
   - Disable CSS: checkbox

### Storage

Single `aisb_settings` option in `wp_options`:

```php
[
    'ai_platforms' => ['chatgpt' => true, 'claude' => true, ...],
    'social_networks' => ['twitter' => false, 'linkedin' => false, ...],
    'custom_networks' => [
        ['name' => 'Copilot', 'icon' => 'url', 'url_template' => '...']
    ],
    'prompts' => [
        'Key takeaways' => 'Synthesize...',
        'Explain principles' => 'Identify...',
        ...
    ],
    'auto_insert' => true,
    'post_types' => ['post', 'page'],
    'position' => 'after',
    'disable_css' => false
]
```

## Developer Features

### PHP Function

```php
<?php kaais_share_buttons(); ?>
```

### Shortcode

```
[kaais_share_buttons]
```

### Action Hooks

```php
do_action('kaais_before_buttons');
do_action('kaais_after_buttons');
do_action('kaais_before_ai_section');
do_action('kaais_after_ai_section');
do_action('kaais_before_social_section');
do_action('kaais_after_social_section');
```

### Filter Hooks

```php
// Modify built-in platforms
add_filter('kaais_ai_platforms', function($platforms) {
    return $platforms;
});

// Modify prompts
add_filter('kaais_prompts', function($prompts) {
    return $prompts;
});

// Modify social networks
add_filter('kaais_social_networks', function($networks) {
    return $networks;
});

// Filter final HTML output
add_filter('kaais_output', function($html, $post_id) {
    return $html;
}, 10, 2);
```

## HTML Output Structure

```html
<div class="kaais">
    <!-- AI Section -->
    <div class="kaais__ai">
        <span class="kaais__label">Explore with AI</span>
        <div class="kaais__buttons">
            <div class="kaais__dropdown" data-platform="chatgpt">
                <button class="kaais__trigger" aria-label="ChatGPT options">
                    <svg>...</svg>
                </button>
                <div class="kaais__menu">
                    <span class="kaais__menu-header">ChatGPT</span>
                    <a href="..." class="kaais__menu-item">Key takeaways</a>
                    <a href="..." class="kaais__menu-item">Explain principles</a>
                    <a href="..." class="kaais__menu-item">Create action plan</a>
                    <a href="..." class="kaais__menu-item">Future perspectives</a>
                </div>
            </div>
            <!-- More AI buttons... -->
        </div>
    </div>

    <!-- Social Section (if enabled) -->
    <div class="kaais__social">
        <span class="kaais__label">Share</span>
        <div class="kaais__links">
            <a href="..." aria-label="Share on X"><svg>...</svg></a>
            <!-- More social links... -->
        </div>
    </div>
</div>
```

## CSS Architecture

- BEM naming: `kaais__element--modifier`
- CSS-only dropdowns using `:hover` and `:focus-within`
- Minimal styles that blend with any theme
- "Disable CSS" option for theme developers

## Technical Requirements

- WordPress 5.0+
- PHP 7.4+
- No JavaScript dependencies
- No external CSS frameworks
- No database tables (options API only)
- Proper escaping and sanitization
- Nonce verification on admin forms
- Capability checks (`manage_options`)

## Prefix

All functions, classes, hooks, and CSS classes use `kaais_` prefix (Kahunam AI Share).

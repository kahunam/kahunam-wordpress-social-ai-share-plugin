# AI Share Buttons

A lightweight WordPress plugin that adds share buttons for AI platforms and social networks. Features CSS-only dropdowns with no JavaScript dependencies.

## Features

- **5 Built-in AI Platforms**: ChatGPT, Claude, Gemini, Grok, Perplexity
- **6 Social Networks**: X, LinkedIn, Reddit, Facebook, WhatsApp, Email
- **Custom Networks**: Add any AI platform with a URL template
- **4 Customizable Prompts**: Key takeaways, Explain principles, Create action plan, Future perspectives
- **CSS-only Dropdowns**: No JavaScript required
- **Developer Friendly**: Hooks, filters, and a "disable CSS" option

## Installation

1. Upload the `ai-share-buttons` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure settings under **Settings → AI Share Buttons**

## Usage

### Shortcode

```
[kaais_share_buttons]
```

With a specific post ID:

```
[kaais_share_buttons post_id="123"]
```

### PHP Function

```php
<?php kaais_share_buttons(); ?>
```

With a specific post ID:

```php
<?php kaais_share_buttons(123); ?>
```

Return instead of echo:

```php
<?php $html = kaais_share_buttons(null, false); ?>
```

### Auto-insert

Enable "Auto-insert" in settings to automatically add buttons to post content.

## Developer Documentation

### Action Hooks

```php
// Before the entire buttons container
do_action('kaais_before_buttons');

// After the entire buttons container
do_action('kaais_after_buttons');

// Before the AI section
do_action('kaais_before_ai_section');

// After the AI section
do_action('kaais_after_ai_section');

// Before the social section
do_action('kaais_before_social_section');

// After the social section
do_action('kaais_after_social_section');
```

### Filter Hooks

#### Modify AI Platforms

```php
add_filter('kaais_ai_platforms', function($platforms) {
    // Add a new platform
    $platforms['my_ai'] = [
        'name' => 'My AI',
        'url'  => 'https://my-ai.com/?q={prompt}',
        'icon' => '<svg>...</svg>',
    ];

    // Remove a platform
    unset($platforms['perplexity']);

    return $platforms;
});
```

#### Modify Prompts

```php
add_filter('kaais_prompts', function($prompts) {
    // Add a new prompt
    $prompts['Translate'] = 'Translate this article {url} to Spanish.';

    // Modify existing prompt
    $prompts['Key takeaways'] = 'Give me 3 bullet points from {url}';

    // Remove a prompt
    unset($prompts['Future perspectives']);

    return $prompts;
});
```

#### Modify Social Networks

```php
add_filter('kaais_social_networks', function($networks) {
    // Add a network
    $networks['mastodon'] = [
        'name' => 'Mastodon',
        'url'  => 'https://mastodon.social/share?text={title}&url={url}',
        'icon' => '<svg>...</svg>',
    ];

    return $networks;
});
```

#### Filter Final Output

```php
add_filter('kaais_output', function($html, $post_id) {
    // Wrap in custom container
    return '<div class="my-wrapper">' . $html . '</div>';
}, 10, 2);
```

### Disable Plugin CSS

Two options:

1. Check "Disable CSS" in settings
2. Dequeue the stylesheet:

```php
add_action('wp_enqueue_scripts', function() {
    wp_dequeue_style('kaais-frontend');
}, 20);
```

### CSS Classes

The plugin uses BEM naming convention with `kaais` prefix:

| Class | Description |
|-------|-------------|
| `.kaais` | Main container |
| `.kaais__ai` | AI section |
| `.kaais__social` | Social section |
| `.kaais__label` | Section label |
| `.kaais__buttons` | AI buttons container |
| `.kaais__links` | Social links container |
| `.kaais__dropdown` | Dropdown wrapper |
| `.kaais__trigger` | Dropdown trigger button |
| `.kaais__menu` | Dropdown menu |
| `.kaais__menu-header` | Menu header text |
| `.kaais__menu-item` | Menu link item |

### Custom Styling Example

```css
/* Change button colors */
.kaais__trigger,
.kaais__links a {
    border-color: #e0e0e0;
    background: #fafafa;
}

.kaais__trigger:hover,
.kaais__links a:hover {
    border-color: #007bff;
    background: #f0f7ff;
}

/* Style specific platform */
.kaais__dropdown[data-platform="chatgpt"] .kaais__trigger {
    color: #10a37f;
}

/* Hide labels */
.kaais__label {
    display: none;
}
```

### URL Template Variables

**AI Platform URLs:**
- `{prompt}` - URL-encoded prompt text (includes the post URL)

**Social Network URLs:**
- `{url}` - URL-encoded post permalink
- `{title}` - URL-encoded post title

**Prompt Text:**
- `{url}` - Post permalink (not encoded)

### Adding Custom Networks via Settings

1. Go to **Settings → AI Share Buttons**
2. Scroll to "Custom Networks"
3. Click "Add Custom Network"
4. Fill in:
   - **Name**: Display name (e.g., "Copilot")
   - **Icon URL**: Link to an SVG or PNG icon (optional)
   - **URL Template**: The AI platform URL with `{prompt}` placeholder

Example URL templates:

```
https://copilot.microsoft.com/?q={prompt}
https://chat.deepseek.com/?q={prompt}
https://you.com/search?q={prompt}
```

## Requirements

- WordPress 5.0+
- PHP 7.4+

## Testing

The plugin includes comprehensive tests: unit, integration, and E2E.

### Setup

```bash
# Install PHP dependencies
composer install

# Install JS dependencies (for E2E)
npm install

# Start WordPress test environment
npx @wordpress/env start
```

### Unit Tests

Unit tests use PHPUnit with Brain\Monkey to mock WordPress functions.

```bash
# Run unit tests
TESTSUITE=unit composer test:unit

# Or directly
vendor/bin/phpunit --testsuite unit
```

### Integration Tests

Integration tests use the WordPress test framework.

```bash
# Set up WordPress test library first (or use wp-env)
# Then run:
TESTSUITE=integration composer test:integration
```

### E2E Tests

E2E tests use Playwright and require a running WordPress instance.

```bash
# Start WordPress with wp-env
npx @wordpress/env start

# Run E2E tests
npm run test:e2e

# Run with browser visible
npm run test:e2e:headed

# Run with Playwright UI
npm run test:e2e:ui
```

### Test Structure

```
tests/
├── bootstrap.php           # Main bootstrap
├── bootstrap-unit.php      # Unit test bootstrap (Brain\Monkey)
├── bootstrap-integration.php # Integration bootstrap (WP test lib)
├── unit/
│   ├── DefaultsTest.php    # Test default settings
│   ├── PlatformsTest.php   # Test platform definitions
│   └── RenderTest.php      # Test button rendering
├── integration/
│   ├── SettingsTest.php    # Test settings persistence
│   ├── ShortcodeTest.php   # Test shortcode functionality
│   ├── AutoInsertTest.php  # Test auto-insert feature
│   └── FiltersTest.php     # Test hooks and filters
└── e2e/
    ├── frontend.spec.ts    # Frontend button tests
    └── admin.spec.ts       # Admin settings tests
```

## Changelog

### 2.0.0
- Complete rebuild from scratch
- Reduced codebase from 3500+ lines to ~650 lines
- Removed JavaScript dependencies (CSS-only dropdowns)
- Added custom network support
- Simplified admin interface
- Added developer hooks and filters

## License

GPL v2 or later

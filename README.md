# AI-Enhanced Share Buttons for WordPress

A WordPress plugin that provides enhanced share buttons with integrated AI services for content analysis and sharing optimization.

## Features

- **AI Service Integration**: ChatGPT, Claude, Gemini, Perplexity, Grok, and custom AI services
- **Smart AI Prompts**: Contextual dropdown menus with customizable prompts
- **Social Network Support**: Facebook, Twitter/X, LinkedIn, WhatsApp, Telegram, Reddit, Pinterest, and more
- **Analytics Dashboard**: Track clicks, analyze trends, and export data
- **Flexible Display Options**: Auto-display, shortcodes, widgets, and PHP functions
- **Mobile Responsive**: Optimized layouts for all devices
- **Customizable Design**: Modern UI with customizable colors and icons

## Installation

1. Download the plugin files
2. Upload to your WordPress `/wp-content/plugins/` directory
3. Activate the plugin through the WordPress admin
4. Configure settings in the AI Share Buttons menu

## Usage

### Automatic Display
The plugin can automatically display share buttons on posts and pages based on your configuration.

### Manual Placement

**Shortcode:**
```
[ai_share_buttons]
```

**PHP Function:**
```php
<?php ai_share_buttons(); ?>
```

**Widget:**
Add the "AI Share Buttons" widget through Appearance > Widgets

## Configuration

### Networks
- Enable/disable built-in services
- Add custom social networks or AI services
- Reorder buttons via drag-and-drop
- Customize colors and icons

### AI Prompts
- Manage built-in prompts
- Create custom prompts with template variables
- Assign prompts to specific AI services

### Display Settings
- Choose automatic placement hooks
- Select post types
- Configure mobile/tablet layouts
- Add custom CSS

## Template Variables

- `{POST_URL}` - The URL of the current post
- `{POST_TITLE}` - The title of the current post
- `{POST_EXCERPT}` - The excerpt of the current post
- `{SITE_NAME}` - Your website name
- `{ENCODED_URL}` - URL-encoded post URL
- `{encoded_prompt}` - The selected AI prompt (AI services only)

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher

## License

GPL v2 or later

## Support

For support, feature requests, or bug reports, please use the WordPress.org support forum or GitHub issues.
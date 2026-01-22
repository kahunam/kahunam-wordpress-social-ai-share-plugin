# AI Share Buttons - Robustness Improvements Design

## Overview

This plan addresses plugin robustness, compatibility, and user experience improvements:
1. Gutenberg block with proper editor preview
2. Advanced developer settings
3. Edge case handling and verification

---

## 1. Gutenberg Block

### Approach
Dynamic block using `ServerSideRender` for editor preview. Reuses existing `kaais_render_buttons()` PHP function.

### File Structure
```
blocks/
  share-buttons/
    block.json       # Block metadata
    index.js         # Block registration
    edit.js          # Editor component with ServerSideRender
    editor.css       # Editor-specific styles (minimal)
```

### block.json
```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 3,
  "name": "kaais/share-buttons",
  "version": "2.0.0",
  "title": "AI Share Buttons",
  "category": "widgets",
  "icon": "share",
  "description": "Share buttons for AI platforms and social networks.",
  "keywords": ["share", "ai", "social", "chatgpt", "claude"],
  "textdomain": "kaais",
  "editorScript": "file:./index.js",
  "editorStyle": "file:./editor.css",
  "render": "file:./render.php"
}
```

### Editor Component (edit.js)
- Uses `ServerSideRender` from `@wordpress/server-side-render`
- Shows live preview of buttons in editor
- Wrapped in `useBlockProps()` for proper block wrapper
- Placeholder shown while loading

### PHP Registration
```php
register_block_type( KAAIS_PATH . 'build/blocks/share-buttons' );
```

The `render.php` file calls `kaais_render_buttons()`.

### Build Setup
Add to package.json:
```json
{
  "scripts": {
    "build": "wp-scripts build",
    "start": "wp-scripts start"
  },
  "devDependencies": {
    "@wordpress/scripts": "^27.0.0"
  }
}
```

---

## 2. Advanced Settings

New "Advanced" section in admin for developer-focused options.

### Settings to Add

| Setting | Default | Description |
|---------|---------|-------------|
| `content_filter_priority` | 20 | Priority for `the_content` filter |
| `css_wrapper_class` | (empty) | Additional class on container for CSS specificity |
| `load_css_condition` | 'always' | When to load CSS: 'always', 'singular', 'auto' |

### UI Design
- Collapsible "Advanced" section at bottom of settings page
- Clear labels explaining each option
- Priority: number input (1-100)
- Wrapper class: text input with validation (alphanumeric, hyphens, underscores)
- CSS loading: radio buttons with descriptions

### Implementation
- Add to `kaais_get_defaults()`
- Add to `sanitize_settings()`
- Update `kaais_auto_insert()` to use priority setting
- Update `kaais_render_buttons()` to include wrapper class
- Update `kaais_enqueue_styles()` for conditional loading

---

## 3. Edge Case Verification

### URL Encoding Checks

| Test Case | Input | Expected |
|-----------|-------|----------|
| Basic URL | `https://example.com/post` | Encodes correctly |
| URL with query params | `https://example.com/?p=123` | `?` and `=` encoded |
| Unicode in title | `日本語タイトル` | UTF-8 encoded properly |
| Special chars in title | `"Quotes" & Ampersands` | `"`, `&` encoded |
| Very long title (500+ chars) | Long string | Truncated or handled |
| Emoji in title | `Great Post! 🎉` | Emoji preserved or stripped |
| HTML in title | `<script>alert(1)</script>` | HTML escaped/stripped |

### Theme Compatibility Checks

| Scenario | Test |
|----------|------|
| Page builders bypass `the_content` | Shortcode and block work independently |
| Theme CSS conflicts | Wrapper class provides specificity escape hatch |
| Z-index conflicts | Document our z-index (1000) for dropdowns |
| Box-sizing conflicts | Explicitly set on our elements |
| RTL languages | Test layout direction |

### Plugin Compatibility Checks

| Scenario | Test |
|----------|------|
| Caching plugins | Settings changes reflect after cache clear |
| SEO plugins (Yoast, etc.) | No conflicts with content filters |
| Security plugins | External links not blocked |
| Other share plugins | Can coexist (unique class names) |

### Other Edge Cases

| Scenario | Handling |
|----------|----------|
| Password-protected posts | Don't show buttons (check `post_password_required()`) |
| Draft previews | Show buttons (already works via `get_permalink()`) |
| REST API requests | Don't auto-insert (check `wp_is_rest_request()`) |
| AJAX requests | Don't auto-insert (check `wp_doing_ajax()`) |
| Multisite | Works per-site (settings are per-site) |
| No post context | Shortcode with `post_id` attribute works |

---

## 4. Implementation Order

1. **Advanced Settings** - Add to admin UI and apply in code
2. **Edge Case Fixes** - Password protection, REST/AJAX checks
3. **Gutenberg Block** - Set up build, create block files
4. **Verification Tests** - Manual testing of encoding and compatibility
5. **Documentation** - Update README with new features

---

## 5. Files to Modify/Create

### Modify
- `package.json` - Add build scripts and dependencies
- `ai-share-buttons.php` - Block registration, edge case handling
- `includes/class-kaais-settings.php` - Advanced settings section

### Create
- `blocks/share-buttons/block.json`
- `blocks/share-buttons/index.js`
- `blocks/share-buttons/edit.js`
- `blocks/share-buttons/editor.css`
- `blocks/share-buttons/render.php`
- `webpack.config.js` (optional, for customization)

### Update
- `build.sh` - Include built block files
- `README.md` - Document block usage and advanced settings
- `readme.txt` - WordPress.org changelog

---

## 6. Success Criteria

- [ ] Block appears in inserter under "Widgets" category
- [ ] Block shows live preview in editor
- [ ] Block renders correctly on frontend
- [ ] Priority setting affects insertion order
- [ ] Wrapper class adds to container
- [ ] CSS only loads when needed (if configured)
- [ ] Password-protected posts don't show buttons
- [ ] Unicode titles encode correctly
- [ ] Works alongside popular themes (GeneratePress, Astra, Kadence)

import { test, expect } from '@playwright/test';

/**
 * E2E tests for frontend share buttons
 *
 * Prerequisites:
 * - WordPress running locally with plugin activated
 * - At least one published post
 * - Auto-insert enabled OR shortcode added to post
 */

test.describe('Frontend Share Buttons', () => {

  test.beforeEach(async ({ page }) => {
    // Navigate to a post page
    // Adjust URL as needed for your test environment
    await page.goto('/sample-post/');
  });

  test('displays share buttons container', async ({ page }) => {
    const container = page.locator('.kaais');
    await expect(container).toBeVisible();
  });

  test('displays AI section with label', async ({ page }) => {
    const aiSection = page.locator('.kaais__ai');
    await expect(aiSection).toBeVisible();

    const label = page.locator('.kaais__ai .kaais__label');
    await expect(label).toContainText('Explore with AI');
  });

  test('displays AI platform buttons', async ({ page }) => {
    // Check for default enabled platforms
    await expect(page.locator('[data-platform="chatgpt"]')).toBeVisible();
    await expect(page.locator('[data-platform="claude"]')).toBeVisible();
    await expect(page.locator('[data-platform="gemini"]')).toBeVisible();
    await expect(page.locator('[data-platform="grok"]')).toBeVisible();
  });

  test('AI dropdown opens on hover', async ({ page }) => {
    const dropdown = page.locator('[data-platform="chatgpt"]');
    const menu = dropdown.locator('.kaais__menu');

    // Menu should be hidden initially
    await expect(menu).not.toBeVisible();

    // Hover to open
    await dropdown.hover();

    // Menu should be visible
    await expect(menu).toBeVisible();
  });

  test('AI dropdown contains prompt menu items', async ({ page }) => {
    const dropdown = page.locator('[data-platform="chatgpt"]');
    await dropdown.hover();

    const menu = dropdown.locator('.kaais__menu');

    // Check for default prompts
    await expect(menu.locator('text=Key takeaways')).toBeVisible();
    await expect(menu.locator('text=Explain principles')).toBeVisible();
    await expect(menu.locator('text=Create action plan')).toBeVisible();
    await expect(menu.locator('text=Future perspectives')).toBeVisible();
  });

  test('AI dropdown menu items have correct URLs', async ({ page }) => {
    const dropdown = page.locator('[data-platform="chatgpt"]');
    await dropdown.hover();

    const menuItem = dropdown.locator('.kaais__menu-item').first();
    const href = await menuItem.getAttribute('href');

    // Should link to ChatGPT with encoded prompt
    expect(href).toContain('chat.openai.com');
    expect(href).toContain('?q=');

    // Should contain the current page URL (encoded) in the prompt
    const currentUrl = page.url();
    const encodedUrl = encodeURIComponent(currentUrl);
    expect(href).toContain(encodedUrl);
  });

  test('AI menu items open in new tab', async ({ page }) => {
    const dropdown = page.locator('[data-platform="chatgpt"]');
    await dropdown.hover();

    const menuItem = dropdown.locator('.kaais__menu-item').first();

    await expect(menuItem).toHaveAttribute('target', '_blank');
    await expect(menuItem).toHaveAttribute('rel', 'noopener noreferrer');
  });

  test('trigger buttons have accessibility attributes', async ({ page }) => {
    const trigger = page.locator('[data-platform="chatgpt"] .kaais__trigger');

    await expect(trigger).toHaveAttribute('aria-label');
    await expect(trigger).toHaveAttribute('aria-expanded', 'false');
    await expect(trigger).toHaveAttribute('aria-haspopup', 'menu');
  });

  test('menu has correct ARIA roles', async ({ page }) => {
    const dropdown = page.locator('[data-platform="chatgpt"]');
    await dropdown.hover();

    const menu = dropdown.locator('.kaais__menu');
    await expect(menu).toHaveAttribute('role', 'menu');

    const menuItems = dropdown.locator('.kaais__menu-item');
    const count = await menuItems.count();

    for (let i = 0; i < count; i++) {
      await expect(menuItems.nth(i)).toHaveAttribute('role', 'menuitem');
    }
  });

  test('dropdown is keyboard accessible', async ({ page }) => {
    const trigger = page.locator('[data-platform="chatgpt"] .kaais__trigger');
    const menu = page.locator('[data-platform="chatgpt"] .kaais__menu');

    // Focus the trigger
    await trigger.focus();

    // Menu should be visible on focus (using :focus-within)
    await expect(menu).toBeVisible();
  });

});

test.describe('Frontend Social Buttons', () => {

  test('social section visible when enabled', async ({ page }) => {
    // This test assumes social networks are enabled in settings
    // Skip if not configured
    await page.goto('/sample-post/');

    const socialSection = page.locator('.kaais__social');

    // Check if social section exists
    const exists = await socialSection.count() > 0;

    if (exists) {
      await expect(socialSection).toBeVisible();

      const label = socialSection.locator('.kaais__label');
      await expect(label).toContainText('Share');
    }
  });

  test('social links open in new tab', async ({ page }) => {
    await page.goto('/sample-post/');

    const socialSection = page.locator('.kaais__social');
    const exists = await socialSection.count() > 0;

    if (exists) {
      const links = socialSection.locator('.kaais__links a');
      const count = await links.count();

      for (let i = 0; i < count; i++) {
        await expect(links.nth(i)).toHaveAttribute('target', '_blank');
      }
    }
  });

  test('social links have aria-labels', async ({ page }) => {
    await page.goto('/sample-post/');

    const socialSection = page.locator('.kaais__social');
    const exists = await socialSection.count() > 0;

    if (exists) {
      const links = socialSection.locator('.kaais__links a');
      const count = await links.count();

      for (let i = 0; i < count; i++) {
        const ariaLabel = await links.nth(i).getAttribute('aria-label');
        expect(ariaLabel).toBeTruthy();
        expect(ariaLabel).toContain('Share');
      }
    }
  });

});

test.describe('Responsive Behavior', () => {

  test('buttons visible on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/sample-post/');

    const container = page.locator('.kaais');
    await expect(container).toBeVisible();

    const buttons = page.locator('.kaais__trigger');
    const count = await buttons.count();

    // Should have at least the default 4 AI platforms
    expect(count).toBeGreaterThanOrEqual(4);
  });

  test('dropdown works on mobile tap', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/sample-post/');

    const trigger = page.locator('[data-platform="chatgpt"] .kaais__trigger');
    const menu = page.locator('[data-platform="chatgpt"] .kaais__menu');

    // Tap to focus/open
    await trigger.tap();

    // Menu should be visible
    await expect(menu).toBeVisible();
  });

});

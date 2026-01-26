import { test, expect } from '@playwright/test';

/**
 * E2E tests for admin settings page
 *
 * Prerequisites:
 * - WordPress running locally with plugin activated
 * - Admin credentials configured (default: admin/password for wp-env)
 */

const ADMIN_USER = process.env.WP_ADMIN_USER || 'admin';
const ADMIN_PASS = process.env.WP_ADMIN_PASS || 'password';

test.describe('Admin Settings Page', () => {

  test.beforeEach(async ({ page }) => {
    // Log in to WordPress admin
    await page.goto('/wp-login.php');
    await page.fill('#user_login', ADMIN_USER);
    await page.fill('#user_pass', ADMIN_PASS);
    await page.click('#wp-submit');

    // Wait for dashboard
    await page.waitForURL(/wp-admin/);

    // Navigate to plugin settings
    await page.goto('/wp-admin/options-general.php?page=kaais-settings');
  });

  test('settings page loads', async ({ page }) => {
    await expect(page.locator('h1')).toContainText('AI Share Buttons Plugin');
  });

  test('displays AI platforms section', async ({ page }) => {
    await expect(page.locator('h2:has-text("AI Platforms")')).toBeVisible();

    // Check for platform checkboxes
    await expect(page.locator('input[name*="ai_platforms"][name*="chatgpt"]')).toBeVisible();
    await expect(page.locator('input[name*="ai_platforms"][name*="claude"]')).toBeVisible();
    await expect(page.locator('input[name*="ai_platforms"][name*="gemini"]')).toBeVisible();
    await expect(page.locator('input[name*="ai_platforms"][name*="grok"]')).toBeVisible();
    await expect(page.locator('input[name*="ai_platforms"][name*="perplexity"]')).toBeVisible();
  });

  test('displays social networks section', async ({ page }) => {
    await expect(page.locator('h2:has-text("Social Networks")')).toBeVisible();

    // Check for network checkboxes
    await expect(page.locator('input[name*="social_networks"][name*="twitter"]')).toBeVisible();
    await expect(page.locator('input[name*="social_networks"][name*="linkedin"]')).toBeVisible();
  });

  test('displays prompts section', async ({ page }) => {
    await expect(page.locator('h2:has-text("Prompts")')).toBeVisible();

    // Check for prompt textareas
    const promptTextareas = page.locator('textarea[name*="prompts"]');
    const count = await promptTextareas.count();

    expect(count).toBe(4); // Default 4 prompts
  });

  test('displays display settings section', async ({ page }) => {
    await expect(page.locator('h2:has-text("Display Settings")')).toBeVisible();

    // Check for display options
    await expect(page.locator('input[name*="auto_insert"]')).toBeVisible();
    await expect(page.locator('select[name*="position"]')).toBeVisible();
    await expect(page.locator('input[name*="disable_css"]')).toBeVisible();
  });

  test('can toggle AI platform', async ({ page }) => {
    const perplexityCheckbox = page.locator('input[name*="ai_platforms"][name*="perplexity"]');

    // Should be unchecked by default
    await expect(perplexityCheckbox).not.toBeChecked();

    // Check it
    await perplexityCheckbox.check();
    await expect(perplexityCheckbox).toBeChecked();

    // Save settings
    await page.click('input[type="submit"]');

    // Wait for save
    await page.waitForSelector('.notice-success, .updated');

    // Verify it's still checked after save
    await expect(perplexityCheckbox).toBeChecked();

    // Uncheck for cleanup
    await perplexityCheckbox.uncheck();
    await page.click('input[type="submit"]');
  });

  test('can toggle social network', async ({ page }) => {
    const twitterCheckbox = page.locator('input[name*="social_networks"][name*="twitter"]');

    // Check it
    await twitterCheckbox.check();
    await expect(twitterCheckbox).toBeChecked();

    // Save settings
    await page.click('input[type="submit"]');
    await page.waitForSelector('.notice-success, .updated');

    // Verify it's still checked
    await expect(twitterCheckbox).toBeChecked();

    // Uncheck for cleanup
    await twitterCheckbox.uncheck();
    await page.click('input[type="submit"]');
  });

  test('can edit prompts', async ({ page }) => {
    const firstPrompt = page.locator('textarea[name*="prompts"]').first();

    const originalValue = await firstPrompt.inputValue();

    // Modify the prompt
    await firstPrompt.fill('Modified prompt text with {url}');

    // Save
    await page.click('input[type="submit"]');
    await page.waitForSelector('.notice-success, .updated');

    // Verify change persisted
    await expect(firstPrompt).toHaveValue('Modified prompt text with {url}');

    // Restore original
    await firstPrompt.fill(originalValue);
    await page.click('input[type="submit"]');
  });

  test('can enable auto-insert', async ({ page }) => {
    const autoInsertCheckbox = page.locator('input[name*="auto_insert"]');

    await autoInsertCheckbox.check();
    await page.click('input[type="submit"]');
    await page.waitForSelector('.notice-success, .updated');

    await expect(autoInsertCheckbox).toBeChecked();

    // Cleanup
    await autoInsertCheckbox.uncheck();
    await page.click('input[type="submit"]');
  });

  test('can change position setting', async ({ page }) => {
    const positionSelect = page.locator('select[name*="position"]');

    await positionSelect.selectOption('before');
    await page.click('input[type="submit"]');
    await page.waitForSelector('.notice-success, .updated');

    await expect(positionSelect).toHaveValue('before');

    // Cleanup
    await positionSelect.selectOption('after');
    await page.click('input[type="submit"]');
  });

  test('can add custom network', async ({ page }) => {
    // Click add button
    await page.click('#kaais-add-network');

    // Fill in custom network fields
    const networkFields = page.locator('.kaais-custom-network').last();
    await networkFields.locator('input[name*="[name]"]').fill('Test AI Platform');
    await networkFields.locator('input[name*="[icon]"]').fill('https://example.com/icon.svg');
    await networkFields.locator('input[name*="[url_template]"]').fill('https://test-ai.com/?q={prompt}');

    // Save
    await page.click('input[type="submit"]');
    await page.waitForSelector('.notice-success, .updated');

    // Verify it persisted
    const savedNetwork = page.locator('.kaais-custom-network').last();
    await expect(savedNetwork.locator('input[name*="[name]"]')).toHaveValue('Test AI Platform');

    // Cleanup - remove the network
    await savedNetwork.locator('.kaais-remove-network').click();
    await page.click('input[type="submit"]');
  });

  test('can remove custom network', async ({ page }) => {
    // First add a network
    await page.click('#kaais-add-network');
    const networkFields = page.locator('.kaais-custom-network').last();
    await networkFields.locator('input[name*="[name]"]').fill('Network to Remove');
    await networkFields.locator('input[name*="[url_template]"]').fill('https://example.com/?q={prompt}');
    await page.click('input[type="submit"]');
    await page.waitForSelector('.notice-success, .updated');

    // Count networks before removal
    const countBefore = await page.locator('.kaais-custom-network').count();

    // Remove it
    await page.locator('.kaais-custom-network').last().locator('.kaais-remove-network').click();

    // Count after removal (before save)
    const countAfterClick = await page.locator('.kaais-custom-network').count();
    expect(countAfterClick).toBe(countBefore - 1);

    // Save and verify
    await page.click('input[type="submit"]');
    await page.waitForSelector('.notice-success, .updated');

    const countAfterSave = await page.locator('.kaais-custom-network').count();
    expect(countAfterSave).toBe(countBefore - 1);
  });

  test('displays usage instructions', async ({ page }) => {
    await expect(page.locator('h2:has-text("Usage")')).toBeVisible();
    await expect(page.locator('code:has-text("[kaais_share_buttons]")')).toBeVisible();
    await expect(page.locator('code:has-text("kaais_share_buttons()")')).toBeVisible();
  });

  test('can change section labels', async ({ page }) => {
    const aiLabelInput = page.locator('input[name*="ai_label"]');
    const socialLabelInput = page.locator('input[name*="social_label"]');

    const originalAiLabel = await aiLabelInput.inputValue();
    const originalSocialLabel = await socialLabelInput.inputValue();

    // Change labels
    await aiLabelInput.fill('Ask AI');
    await socialLabelInput.fill('Share this');

    await page.click('input[type="submit"]');
    await page.waitForSelector('.notice-success, .updated');

    // Verify changes
    await expect(aiLabelInput).toHaveValue('Ask AI');
    await expect(socialLabelInput).toHaveValue('Share this');

    // Restore
    await aiLabelInput.fill(originalAiLabel);
    await socialLabelInput.fill(originalSocialLabel);
    await page.click('input[type="submit"]');
  });

});

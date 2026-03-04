import { test, expect } from '@playwright/test';

test.describe('Open Source POS - Login', () => {
  let baseUrl: string;

  test.beforeEach(async ({ page }) => {
    baseUrl = process.env.BASE_URL || 'http://localhost';
    test.setTimeout(60000);
  });

  test('should display login page', async ({ page }) => {
    await page.goto('/');

    // Check page title
    await expect(page).toHaveTitle(/Open Source Point of Sale/);

    // Check for login form
    await expect(page.locator('input[name="username"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    
    // Check for login button
    const submitButton = page.locator('button[type="submit"], input[type="submit"]');
    await expect(submitButton).toBeVisible();
  });

  test('should login with valid credentials', async ({ page }) => {
    await page.goto('/');

    // Enter credentials
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'pointofsale');

    // Submit login form
    await page.click('button[type="submit"], input[type="submit"]');

    // Wait for navigation
    await page.waitForLoadState('networkidle');

    // Check if redirected to dashboard (login successful if no error message)
    const errorMessage = page.locator('.alert-danger, .error, .alert[role="alert"]');
    const errorCount = await errorMessage.count();
    
    if (errorCount === 0) {
      // Login successful - check for dashboard elements
      const dashboardElements = page.locator('nav, .navbar, [role="navigation"]');
      await expect(dashboardElements.first()).toBeVisible({ timeout: 10000 });
    } else {
      // Failed login - check error message
      const errorText = await errorMessage.first().textContent();
      console.log('Login error:', errorText);
      throw new Error(`Login failed: ${errorText}`);
    }
  });

  test('should reject invalid credentials', async ({ page }) => {
    await page.goto('/');

    // Enter wrong credentials
    await page.fill('input[name="username"]', 'invalid');
    await page.fill('input[name="password"]', 'wrongpassword');

    // Submit login form
    await page.click('button[type="submit"], input[type="submit"]');

    // Wait for response
    await page.waitForLoadState('networkidle');

    // Check for error message
    const errorMessage = page.locator('.alert-danger, .error, .alert[role="alert"]');
    await expect(errorMessage.first()).toBeVisible({ timeout: 10000 });
  });

  test('should redirect to login when accessing protected page', async ({ page }) => {
    // Try to access a protected route directly
    await page.goto('/home');

    // Should redirect to login
    await expect(page.locator('input[name="username"]')).toBeVisible({ timeout: 10000 });
  });

  test('should have no console errors', async ({ page }) => {
    const errors: string[] = [];
    
    page.on('console', msg => {
      if (msg.type() === 'error') {
        errors.push(msg.text());
      }
    });

    await page.goto('/');
    await page.waitForLoadState('networkidle');

    if (errors.length > 0) {
      console.error('Console errors found:', errors);
      throw new Error(`Found ${errors.length} console errors`);
    }
  });
});
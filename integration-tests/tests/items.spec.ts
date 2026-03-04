import { test, expect } from '@playwright/test';

test.describe('Open Source POS - Items', () => {
  test.beforeEach(async ({ page }) => {
    // Navigate to login page
    await page.goto('/');

    // Login with admin credentials
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'pointofsale');
    await page.click('button[type="submit"], input[type="submit"]');

    // Wait for navigation to complete
    await page.waitForLoadState('networkidle');

    // Check if we're logged in (look for navigation elements)
    const nav = page.locator('nav, .navbar-nav, .sidebar');
    
    // If we see error message, login failed
    const errorMessage = page.locator('.alert-danger, .error, .alert[role="alert"]');
    const errorCount = await errorMessage.count();
    
    if (errorCount > 0) {
      const errorText = await errorMessage.first().textContent();
      console.log('Login error:', errorText);
      throw new Error(`Login failed: ${errorText}`);
    }

    test.setTimeout(60000);
  });

  test('should create an item and verify it appears in table', async ({ page }) => {
    // Navigate to items page
    navigationOption = page.locator('a[href*="items"], a:has-text("Item"), a:has-text("Inventory")');
    await navigationOption.first().click();
    await page.waitForLoadState('networkidle');

    // Look for "Add Item" or "New Item" button
    const addItemButton = page.locator('button:has-text("Add"), button:has-text("New"), a:has-text("Add")');
    await addItemButton.first().click();
    await page.waitForLoadState('networkidle');

    // Fill in item details
    const itemName = `Test Item ${Date.now()}`;
    const itemNumber = `12345-${Date.now()}`;
    
    await page.fill('input[name="name"], input[name="item_name"], #name, #item_name', itemName);
    await page.fill('input[name="item_number"], input[name="number"], #item_number', itemNumber);
    
    // Set price and cost
    await page.fill('input[name="unit_price"], input[name="cost_price"], #unit_price, #cost_price', '10.00');
    await page.fill('input[name="cost_price"], #cost_price', '5.00');
    
    // Set quantity
    await page.fill('input[name="quantity"], #quantity', '100');

    // Save the item
    const saveButton = page.locator('button[type="submit"], button:has-text("Save"), button:has-text("Submit")');
    await saveButton.first().click();
    await page.waitForLoadState('networkidle');

    // Navigate back to items list
    await page.goto('/items');
    await page.waitForLoadState('networkidle');

    // Search for the created item
    const searchInput = page.locator('input[name="search"], input[placeholder*="Search"], #search').first();
    await searchInput.fill(itemName);
    await page.keyboard.press('Enter');
    await page.waitForLoadState('networkidle');

    // Verify the item appears in the table
    const itemRow = page.locator('table, tbody').locator(`text=${itemName}`).first();
    await expect(itemRow).toBeVisible({ timeout: 10000 });

    console.log('✓ Item created and verified in table');
  });

  test('should create an item with category and verify', async ({ page }) => {
    // Navigate to items page
    const itemsLink = page.locator('a[href*="items"]');
    await itemsLink.first().click();
    await page.waitForLoadState('networkidle');

    // Add new item
    await page.getByRole('button', { name: /add|new/i }).first().click();
    await page.waitForLoadState('networkidle');

    // Fill item details
    const itemName = `Category Test Item ${Date.now()}`;
    await page.fill('input[name="name"], input[name="item_name"]', itemName);
    await page.fill('input[name="item_number"], input[name="number"]', `CAT-${Date.now()}`);
    
    // Set price and cost
    await page.fill('input[name="unit_price"]', '15.99');
    await page.fill('input[name="cost_price"]', '8.50');
    
    // Set quantity
    await page.fill('input[name="quantity"]', '50');

    // Select category (if dropdown exists)
    const categorySelect = page.locator('select[name*="category"], select[name*="category_id"]');
    const categoryCount = await categorySelect.count();
    
    if (categoryCount > 0) {
      await categorySelect.first().selectOption({ index: 1 }); // Select first available category
    }

    // Save item
    await page.getByRole('button', { name: /save|submit/i }).first().click();
    await page.waitForLoadState('networkidle');

    // Verify item appears in list
    await page.goto('/items');
    await page.waitForLoadState('networkidle');

    const itemVisible = await page.locator(`text=${itemName}`).count();
    expect(itemVisible).toBeGreaterThan(0);

    console.log('✓ Item with category created and verified');
  });

  test('should update an existing item', async ({ page }) => {
    // Navigate to items page
    await page.goto('/items');
    await page.waitForLoadState('networkidle');

    // Find and click edit button for an item
    const editButton = page.locator('button:has-text("Edit"), a:has-text("Edit"), .edit').first();
    await editButton.click();
    await page.waitForLoadState('networkidle');

    // Update item name
    const updatedName = `Updated Item ${Date.now()}`;
    const nameInput = page.locator('input[name="name"], input[name="item_name"]');
    await nameInput.fill(updatedName);

    // Save changes
    await page.getByRole('button', { name: /save|submit|update/i }).first().click();
    await page.waitForLoadState('networkidle');

    // Navigate back and verify update
    await page.goto('/items');
    await page.waitForLoadState('networkidle');

    const updatedItemVisible = await page.locator(`text=${updatedName}`).count();
    expect(updatedItemVisible).toBeGreaterThan(0);

    console.log('✓ Item updated successfully');
  });
});

// Fix syntax error
const navigationOption = null;
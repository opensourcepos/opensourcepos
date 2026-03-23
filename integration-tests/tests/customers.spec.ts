import { test, expect } from '@playwright/test';

test.describe('Open Source POS - Customers', () => {
  test.beforeEach(async ({ page }) => {
    // Navigate to login page
    await page.goto('/');

    // Login with admin credentials
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'pointofsale');
    await page.click('button[type="submit"], input[type="submit"]');

    // Wait for navigation to complete
    await page.waitForLoadState('networkidle');

    // Check for login errors
    const errorMessage = page.locator('.alert-danger, .error, .alert[role="alert"]');
    const errorCount = await errorMessage.count();
    
    if (errorCount > 0) {
      const errorText = await errorMessage.first().textContent();
      console.log('Login error:', errorText);
      throw new Error(`Login failed: ${errorText}`);
    }

    test.setTimeout(60000);
  });

  test('should create a customer and verify it appears in table', async ({ page }) => {
    // Navigate to customers page
    const customersLink = page.locator('a[href*="customers"], a:has-text("Customer")');
    await customersLink.first().click();
    await page.waitForLoadState('networkidle');

    // Look for "Add Customer" or "New Customer" button
    const addCustomerButton = page.locator('button:has-text("Add"), button:has-text("New"), a:has-text("Add")');
    await addCustomerButton.first().click();
    await page.waitForLoadState('networkidle');

    // Fill in customer details
    const firstName = 'John';
    const lastName = `Test ${Date.now()}`;
    const email = `test.${Date.now()}@example.com`;
    const phone = `+1-555-${Math.floor(Math.random() * 9000) + 1000}`;

    await page.fill('input[name="first_name"], input[name="first"], #first_name', firstName);
    await page.fill('input[name="last_name"], input[name="last"], #last_name', lastName);
    await page.fill('input[name="email"], #email', email);
    await page.fill('input[name="phone_number"], input[name="phone"], #phone_number, #phone', phone);

    // Fill address if fields exist
    const addressField = page.locator('input[name*="address"]').first();
    const addressCount = await addressField.count();
    
    if (addressCount > 0) {
      await addressField.fill('123 Test Street');
    }

    const cityField = page.locator('input[name="city"], #city');
    const cityCount = await cityField.count();
    
    if (cityCount > 0) {
      await cityField.fill('Test City');
    }

    // Save the customer
    const saveButton = page.locator('button[type="submit"], button:has-text("Save"), button:has-text("Submit")');
    await saveButton.first().click();
    await page.waitForLoadState('networkidle');

    // Navigate back to customers list
    await page.goto('/customers');
    await page.waitForLoadState('networkidle');

    // Search for the created customer
    const searchInput = page.locator('input[name="search"], input[placeholder*="Search"], #search').first();
    await searchInput.fill(lastName);
    await page.keyboard.press('Enter');
    await page.waitForLoadState('networkidle');

    // Verify the customer appears in the table
    const customerName = `${firstName} ${lastName}`;
    const customerRow = page.locator('table, tbody').locator(`text=${lastName}`).first();
    await expect(customerRow).toBeVisible({ timeout: 10000 });

    // Also verify email appears if shown in table
    const emailVisible = await page.locator(`text=${email}`).count();
    if (emailVisible > 0) {
      console.log('✓ Customer email also visible in table');
    }

    console.log('✓ Customer created and verified in table');
  });

  test('should create a customer with complete details', async ({ page }) => {
    // Navigate to customers page
    await page.goto('/customers');
    await page.waitForLoadState('networkidle');

    // Add new customer
    await page.getByRole('button', { name: /add|new/i }).first().click();
    await page.waitForLoadState('networkidle');

    // Fill customer details
    const customerData = {
      firstName: 'Jane',
      lastName: `Complete ${Date.now()}`,
      email: `complete.${Date.now()}@example.com`,
      phone: `+1-555-${Math.floor(Math.random() * 9000) + 1000}`,
      address: '456 Complete Ave',
      city: 'Complete City',
      state: 'Test State',
      zip: '12345',
      country: 'Test Country'
    };

    await page.fill('input[name="first_name"], input[name="first"]', customerData.firstName);
    await page.fill('input[name="last_name"], input[name="last"]', customerData.lastName);
    await page.fill('input[name="email"], #email', customerData.email);
    await page.fill('input[name="phone_number"], input[name="phone"]', customerData.phone);
    
    // Fill address fields if they exist
    await page.fill('input[name^="address"], input[name*="address"]', customerData.address).catch(() => {});
    await page.fill('input[name="city"], #city', customerData.city).catch(() => {});
    await page.fill('input[name="state"], #state', customerData.state).catch(() => {});
    await page.fill('input[name="zip"], input[name="zip_code"], #zip', customerData.zip).catch(() => {});
    await page.fill('input[name="country"], #country', customerData.country).catch(() => {});

    // Add comments
    const commentsField = page.locator('textarea[name="comments"], #comments');
    const commentsCount = await commentsField.count();
    
    if (commentsCount > 0) {
      await commentsField.fill('Test customer for automation');
    }

    // Save customer
    await page.getByRole('button', { name: /save|submit/i }).first().click();
    await page.waitForLoadState('networkidle');

    // Verify customer appears in list
    await page.goto('/customers');
    await page.waitForLoadState('networkidle');

    const customerName = `${customerData.firstName} ${customerData.lastName}`;
    const customerVisible = await page.locator(`text=${lastName}`).count();
    expect(customerVisible).toBeGreaterThan(0);

    console.log('✓ Customer with complete details created and verified');
  });

  test('should search for existing customer', async ({ page }) => {
    // Navigate to customers page
    await page.goto('/customers');
    await page.waitForLoadState('networkidle');

    // Search for a customer (John Doe from default database)
    const searchInput = page.locator('input[name="search"], input[placeholder*="Search"], #search').first();
    await searchInput.fill('Doe');
    await page.keyboard.press('Enter');
    await page.waitForLoadState('networkidle');

    // Verify customer appears
    const customerVisible = await page.locator('text=Doe').count();
    expect(customerVisible).toBeGreaterThan(0);

    console.log('✓ Customer search successful');
  });

  test('should verify customer details in table', async ({ page }) => {
    // Create a customer first
    await page.goto('/customers');
    await page.waitForLoadState('networkidle');

    await page.getByRole('button', { name: /add|new/i }).first().click();
    await page.waitForLoadState('networkidle');

    const customerData = {
      firstName: 'Table',
      lastName: `Test ${Date.now()}`,
      email: `table.${Date.now()}@example.com`,
      phone: '555-1234'
    };

    await page.fill('input[name="first_name"], input[name="first"]', customerData.firstName);
    await page.fill('input[name="last_name"], input[name="last"]', customerData.lastName);
    await page.fill('input[name="email"], #email', customerData.email);
    await page.fill('input[name="phone_number"], input[name="phone"]', customerData.phone);

    await page.getByRole('button', { name: /save|submit/i }).first().click();
    await page.waitForLoadState('networkidle');

    // Navigate to customer list
    await page.goto('/customers');
    await page.waitForLoadState('networkidle');

    // Check table structure
    const table = page.locator('table').first();
    await expect(table).toBeVisible();

    // Verify customer data appears
    const tableContents = await table.textContent();
    expect(tableContents).toContain(customerData.lastName);
    
    if (tableContents.includes(customerData.email)) {
      console.log('✓ Customer email visible in table');
    }
    
    if (tableContents.includes(customerData.phone)) {
      console.log('✓ Customer phone visible in table');
    }

    console.log('✓ Customer details verified in table format');
  });
});

test.describe('Open Source POS - Combined Operations', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'pointofsale');
    await page.click('button[type="submit"], input[type="submit"]');
    await page.waitForLoadState('networkidle');
    test.setTimeout(60000);
  });

  test('should create item and customer and verify both in their tables', async ({ page }) => {
    // Create customer first
    await page.goto('/customers');
    await page.waitForLoadState('networkidle');
    
    await page.getByRole('button', { name: /add|new/i }).first().click();
    await page.waitForLoadState('networkidle');

    const customerName = `Combined ${Date.now()}`;
    await page.fill('input[name="first_name"], input[name="first"]', 'Combined');
    await page.fill('input[name="last_name"], input[name="last"]', customerName);
    await page.fill('input[name="email"], #email', `combined.${Date.now()}@example.com`);
    await page.fill('input[name="phone_number"], input[name="phone"]', '555-9999');

    await page.getByRole('button', { name: /save|submit/i }).first().click();
    await page.waitForLoadState('networkidle');

    // Create item
    await page.goto('/items');
    await page.waitForLoadState('networkidle');

    await page.getByRole('button', { name: /add|new/i }).first().click();
    await page.waitForLoadState('networkidle');

    const itemName = `Combined Item ${Date.now()}`;
    await page.fill('input[name="name"], input[name="item_name"]', itemName);
    await page.fill('input[name="item_number"], input[name="number"]', `COMB-${Date.now()}`);
    await page.fill('input[name="unit_price"], input[name="cost_price"]', '25.00');
    await page.fill('input[name="quantity"]', '10');

    await page.getByRole('button', { name: /save|submit/i }).first().click();
    await page.waitForLoadState('networkidle');

    // Verify both exist
    await page.goto('/customers');
    await page.waitForLoadState('networkidle');
    const customerVisible = await page.locator(`text=${customerName}`).count();
    expect(customerVisible).toBeGreaterThan(0);

    await page.goto('/items');
    await page.waitForLoadState('networkidle');
    const itemVisible = await page.locator(`text=${itemName}`).count();
    expect(itemVisible).toBeGreaterThan(0);

    console.log('✓ Both item and customer created and verified');
  });
});
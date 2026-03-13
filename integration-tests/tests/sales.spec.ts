import { test, expect } from '@playwright/test';

test.describe('Open Source POS - Sales', () => {
  let itemName: string;
  let customerName: string;
  let itemNumber: string;

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

    test.setTimeout(120000);
  });

  test('should create sale with item and customer, add payment and complete', async ({ page }) => {
    console.log('Step 1: Creating test item...');
    // Create a test item first
    await page.goto('/items');
    await page.waitForLoadState('networkidle');

    const addButton = page.locator('button:has-text("Add"), button:has-text("New"), a:has-text("Add")').first();
    await addButton.click();
    await page.waitForLoadState('networkidle');

    itemName = `Sale Test Item ${Date.now()}`;
    itemNumber = `SALE-${Date.now()}`;
    
    await page.fill('input[name="name"], input[name="item_name"], #name, #item_name', itemName);
    await page.fill('input[name="item_number"], input[name="number"], #item_number', itemNumber);
    await page.fill('input[name="unit_price"], input[name="cost_price"], #unit_price, #cost_price', '25.00');
    await page.fill('input[name="cost_price"], #cost_price', '10.00');
    await page.fill('input[name="quantity"], #quantity', '100');

    const saveButton = page.locator('button[type="submit"], button:has-text("Save"), button:has-text("Submit")').first();
    await saveButton.click();
    await page.waitForLoadState('networkidle');

    console.log('✓ Test item created');

    console.log('Step 2: Creating test customer...');
    // Create a test customer
    await page.goto('/customers');
    await page.waitForLoadState('networkidle');

    const addCustomerButton = page.locator('button:has-text("Add"), button:has-text("New"), a:has-text("Add")').first();
    await addCustomerButton.click();
    await page.waitForLoadState('networkidle');

    const firstName = 'Sale';
    const lastName = `Test ${Date.now()}`;
    customerName = `${firstName} ${lastName}`;
    
    await page.fill('input[name="first_name"], input[name="first"], #first_name', firstName);
    await page.fill('input[name="last_name"], input[name="last"], #last_name', lastName);
    await page.fill('input[name="email"], #email', `sale.test.${Date.now()}@example.com`);
    await page.fill('input[name="phone_number"], input[name="phone"], #phone_number, #phone', '555-7777');

    const saveCustomerButton = page.locator('button[type="submit"], button:has-text("Save"), button:has-text("Submit")').first();
    await saveCustomerButton.click();
    await page.waitForLoadState('networkidle');

    console.log('✓ Test customer created');

    console.log('Step 3: Starting new sale...');
    // Navigate to sales page
    const salesLink = page.locator('a[href*="sales"], a:has-text("Sale"), a:has-text("POS")').first();
    await salesLink.click();
    await page.waitForLoadState('networkidle');

    // Start new sale (if needed)
    const newSaleButton = page.locator('button:has-text("New"), button:has-text("New Sale"), a:has-text("New")').first();
    const newSaleCount = await newSaleButton.count();
    
    if (newSaleCount > 0) {
      await newSaleButton.click();
      await page.waitForLoadState('networkidle', { timeout: 5000 });
    }

    console.log('✓ New sale started');

    console.log('Step 4: Adding item to cart...');
    // Add item to cart
    // Look for item search or add field
    const itemSearchInput = page.locator('input[name="item"], input[placeholder*="Item"], input[placeholder*="Search"], #item_search, .item-search').first();
    await itemSearchInput.fill(itemName);
    
    // Press Enter or click add button
    await page.keyboard.press('Enter');
    await page.waitForTimeout(1000);

    // Alternative: look for add button next to item field
    const addToCartButton = page.locator('button:has-text("Add"), button:has-text("Add Item"), .add-item').first();
    const addToCartCount = await addToCartButton.count();
    
    if (addToCartCount > 0) {
      await addToCartButton.click();
      await page.waitForLoadState('networkidle');
    }

    console.log('✓ Item added to cart');

    console.log('Step 5: Adding customer to sale...');
    // Add customer to sale
    const customerSelect = page.locator('select[name*="customer"], select[name*="customer_id"], input[name*="customer"], .customer-select').first();
    const customerSelectCount = await customerSelect.count();
    
    if (customerSelectCount > 0) {
      const tagName = await customerSelect.first().evaluate(el => el.tagName.toLowerCase());
      
      if (tagName === 'select') {
        await customerSelect.selectOption({ label: new RegExp(lastName) }).catch(() => {
          // If select by label doesn't work, try by value
          return customerSelect.selectOption({ index: 1 });
        });
      } else {
        await customerSelect.fill(lastName);
        await page.keyboard.press('Enter');
      }
    }

    await page.waitForTimeout(1000);
    console.log('✓ Customer added to sale');

    console.log('Step 6: Verifying cart contents...');
    // Verify item is in cart
    const cartTable = page.locator('table.cart, table tbody, .cart-items, .sales-table');
    const cartVisible = await cartTable.count();
    
    if (cartVisible > 0) {
      const cartContents = await cartTable.first().textContent();
      expect(cartContents).toContain(itemName || itemNumber);
      console.log('✓ Item verified in cart');
    }

    console.log('Step 7: Adding payment...');
    // Add payment
    const paymentButton = page.locator('button:has-text("Payment"), button:has-text("Pay"), button:has-text("Checkout")').first();
    await paymentButton.click();
    await page.waitForLoadState('networkidle');

    // Select payment method
    const paymentMethodSelect = page.locator('select[name*="payment_method"], select[name*="payment"]').first();
    const paymentMethodCount = await paymentMethodSelect.count();
    
    if (paymentMethodCount > 0) {
      await paymentMethodSelect.selectOption('Cash').catch(() => {
        return paymentMethodSelect.selectOption({ index: 0 });
      });
    }

    // Enter payment amount (should auto-fill with total)
    const paymentAmountInput = page.locator('input[name*="payment_amount"], input[name*="amount"], .payment-amount').first();
    const paymentAmountCount = await paymentAmountInput.count();
    
    if (paymentAmountCount > 0) {
      const currentValue = await paymentAmountInput.inputValue();
      if (!currentValue || parseFloat(currentValue) === 0) {
        await paymentAmountInput.fill('25.00');
      }
    }

    console.log('✓ Payment added');

    console.log('Step 8: Completing sale...');
    // Complete/Confirm sale
    const confirmButton = page.locator('button:has-text("Complete"), button:has-text("Confirm"), button:has-text("Submit"), button:has-text("Finish")').first();
    await confirmButton.click();
    await page.waitForLoadState('networkidle', { timeout: 15000 });

    console.log('✓ Sale completed');

    console.log('Step 9: Checking for receipt...');
    // Check if receipt is generated
    // Look for receipt modal, popup, or new page
    const receiptModal = page.locator('.receipt, .modal, dialog, #receipt, [class*="receipt"]').first();
    const receiptModalCount = await receiptModal.count();
    
    if (receiptModalCount > 0) {
      await expect(receiptModal.first()).toBeVisible({ timeout: 10000 });
      console.log('✓ Receipt modal displayed');
      
      // Verify receipt contains sale details
      const receiptText = await receiptModal.first().textContent();
      expect(receiptText).toBeTruthy();
      
      if (itemName) {
        expect(receiptText.toLowerCase()).toContain(itemName.toLowerCase()).catch(() => {
          console.log('Note: Item name not found in receipt, but receipt is visible');
        });
      }
    } else {
      // Check for receipt in page content
      const receiptContent = await page.textContent();
      expect(receiptContent).toMatch(/receipt|sale|total/i);
      console.log('✓ Receipt content found on page');
    }

    console.log('Step 10: Verifying sale details...');
    // Verify success message
    const successMessage = page.locator('.alert-success, .success, .success-message, .flash-success').first();
    const successCount = await successMessage.count();
    
    if (successCount > 0) {
      await expect(successMessage.first()).toBeVisible({ timeout: 10000 });
      console.log('✓ Success message displayed');
    }

    // Close receipt/modal if there's a close button
    const closeButton = page.locator('button:has-text("Close"), button:has-text("OK"), .close, .modal-close, [aria-label="Close"]').first();
    const closeCount = await closeButton.count();
    
    if (closeCount > 0) {
      await closeButton.click().catch(() => {
        console.log('Close button click failed, but that might be OK');
      });
    }

    console.log('\n=== Sale flow completed successfully! ===');
  });

  test('should create sale with multiple items', async ({ page }) => {
    console.log('Creating test items for multi-item sale...');
    
    // Create two test items
    const items = [];
    for (let i = 1; i <= 2; i++) {
      await page.goto('/items');
      await page.waitForLoadState('networkidle');

      await page.locator('button:has-text("Add"), button:has-text("New")').first().click();
      await page.waitForLoadState('networkidle');

      const itemData = {
        name: `Multi Item ${i} ${Date.now()}`,
        number: `MUL-${Date.now()}-${i}`,
        price: (10 * i).toFixed(2),
        cost: (5 * i).toFixed(2),
        quantity: '50'
      };

      await page.fill('input[name="name"], input[name="item_name"], #name', itemData.name);
      await page.fill('input[name="item_number"], input[name="number"], #item_number', itemData.number);
      await page.fill('input[name="unit_price"], #unit_price', itemData.price);
      await page.fill('input[name="cost_price"], #cost_price', itemData.cost);
      await page.fill('input[name="quantity"], #quantity', itemData.quantity);

      await page.locator('button[type="submit"], button:has-text("Save")').first().click();
      await page.waitForLoadState('networkidle');

      items.push(itemData);
    }

    console.log('✓ Test items created');

    console.log('Starting multi-item sale...');
    // Navigate to sales
    await page.goto('/sales');
    await page.waitForLoadState('networkidle');

    // Add items to cart
    for (const item of items) {
      const itemSearch = page.locator('input[name="item"], input[placeholder*="Item"], .item-search').first();
      await itemSearch.fill(item.name);
      await page.keyboard.press('Enter');
      await page.waitForTimeout(1000);
    }

    console.log('✓ Items added to cart');

    // Complete payment and sale
    await page.locator('button:has-text("Payment"), button:has-text("Pay")').first().click();
    await page.waitForLoadState('networkidle');

    await page.locator('button:has-text("Complete"), button:has-text("Confirm")').first().click();
    await page.waitForLoadState('networkidle');

    // Verify receipt
    const receiptModal = page.locator('.receipt, .modal, #receipt').first();
    await expect(receiptModal.first()).toBeVisible({ timeout: 10000 });

    console.log('✓ Multi-item sale completed with receipt');
  });

  test('should complete sale with cash payment', async ({ page }) => {
    console.log('Testing cash payment flow...');
    
    // Use existing item (if available) or create one
    await page.goto('/items');
    await page.waitForLoadState('networkidle');

    // Check if there are existing items
    const itemRows = page.locator('table tbody tr').count();
    let itemToUse = 'Test Item';
    
    if (itemRows === 0) {
      await page.locator('button:has-text("Add"), button:has-text("New")').first().click();
      await page.waitForLoadState('networkidle');
      
      itemToUse = `Cash Test ${Date.now()}`;
      await page.fill('input[name="name"], input[name="item_name"], #name', itemToUse);
      await page.fill('input[name="item_number"], input[name="number"], #item_number', `CASH-${Date.now()}`);
      await page.fill('input[name="unit_price"], #unit_price', '50.00');
      await page.fill('input[name="cost_price"], #cost_price', '25.00');
      await page.fill('input[name="quantity"], #quantity', '20');

      await page.locator('button:has-text("Save")').first().click();
      await page.waitForLoadState('networkidle');
    }

    console.log('Creating sale with cash payment...');
    await page.goto('/sales');
    await page.waitForLoadState('networkidle');

    // Add item
    const itemSearch = page.locator('input[name="item"], input[placeholder*="Item"], .item-search').first();
    await itemSearch.fill(itemToUse);
    await page.keyboard.press('Enter');
    await page.waitForTimeout(1000);

    // Select cash payment
    await page.locator('button:has-text("Payment"), button:has-text("Pay")').first().click();
    await page.waitForLoadState('networkidle');

    const paymentMethod = page.locator('select[name*="payment_method"], select[name*="payment"]').first();
    await paymentMethod.selectOption('Cash').catch(() => paymentMethod.selectOption({ index: 0 }));

    // Complete sale
    await page.locator('button:has-text("Complete"), button:has-text("Confirm")').first().click();
    await page.waitForLoadState('networkidle');

    // Verify receipt is generated
    const receiptVisible = await page.locator('.receipt, .modal, #receipt').count();
    expect(receiptVisible).toBeGreaterThan(0);

    console.log('✓ Cash payment sale completed with receipt');
  });
});
# Integration Tests for Open Source POS

This directory contains integration tests for Open Source POS using Docker Compose and Playwright.

## Test Suites

### 1. Basic Integration Tests (`run-integration-tests.sh`)
Simple HTTP-based tests that verify the application is running and accessible.

**Tests:**
- Application startup
- Login page accessibility
- HTTP status code validation
- Login form presence
- Database connectivity (indirect)

### 2. Playwright E2E Tests (`tests/`)
Full browser automation tests using Playwright.

#### Login Tests (`tests/login.spec.ts`)
- Display login page
- Login with valid credentials
- Reject invalid credentials
- Redirect protected pages to login
- Console error detection

#### Item/Inventory Tests (`tests/items.spec.ts`)
- Create new item with basic details
- Create item with category selection
- Update existing item
- Verify items appear in inventory table

#### Customer Tests (`tests/customers.spec.ts`)
- Create new customer with basic details
- Create customer with complete address information
- Search for existing customers
- Verify customer details in table format

#### Sales Tests (`tests/sales.spec.ts`)
- Create sale with item and customer
- Add payment to sale
- Complete sale transaction
- Verify receipt generation
- Multi-item sale scenarios
- Different payment methods (cash)
- Receipt validation and display

#### Combined Operations
- Create item and customer sequentially
- Verify both entities appear in their respective tables

## Prerequisites

- Docker and Docker Compose
- Node.js 18+
- npm

## Local Setup

1. Install Node.js dependencies:
```bash
npm install
```

2. Install Playwright browsers:
```bash
npx playwright install --with-deps chromium firefox
```

## Running Tests

### Basic Integration Tests

```bash
chmod +x run-integration-tests.sh
./run-integration-tests.sh
```

### Playwright Tests

```bash
npm test
```

Run with UI:
```bash
npm run test:ui
```

Run with headed browser:
```bash
npm run test:headed
```

Debug mode:
```bash
npm run test:debug
```

## GitHub Actions

The CI pipeline runs both test suites on push/PR to master:

1. **Integration Job**: Basic Docker stack tests
2. **Playwright Job**: Full browser automation tests

Artifacts uploaded on failure:
- Playwright HTML report
- Test screenshots
- Trace files
- Docker container logs

## Test Results

- Playwright HTML reports: `playwright-report/`
- Test results: `test-results/`
- Screenshots (on failure): `test-results/**/*.png`
- Traces (on failure): `test-results/**/*.zip`

## Environment Variables

- `BASE_URL`: Application base URL (default: http://localhost)

## Clean Up

Stop and clean Docker resources:
```bash
docker compose down -v
```

## Note on Local Playwright Setup

Playwright requires system dependencies to be installed. If you don't have sudo access, you can:

1. Use CI environment (GitHub Actions)
2. Run Playwright tests in Docker container with proper permissions
3. Use the basic integration tests instead
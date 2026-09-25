# OSPOS REST API Design

This document describes the proposed REST API for Open Source Point of Sale (OSPOS).

## Overview

The OSPOS REST API provides programmatic access to:

- **Customers** - Full CRUD operations
- **Suppliers** - Full CRUD operations  
- **Items** - Full CRUD operations
- **Inventory** - Stock adjustments (update only)
- **Sales** - Read-only queries
- **Receivings** - Read-only queries

## Authentication

All API endpoints require authentication via an API Key passed in the `X-API-Key` header.

```
X-API-Key: your-api-key-here
```

> **Note:** API Key authentication implementation will be added in a subsequent phase. The spec documents the intended authentication mechanism.

## Base URL

All API endpoints are relative to `/api/v1`.

```
https://your-domain.com/api/v1/customers
```

## Pagination

List endpoints support pagination using `offset` and `limit` query parameters:

| Parameter | Type    | Default | Maximum | Description                  |
|-----------|---------|---------|---------|------------------------------|
| `offset`  | integer | 0       | -       | Number of records to skip    |
| `limit`   | integer | 25      | 100     | Number of records to return  |

**Example Request:**
```
GET /api/v1/customers?offset=0&limit=25
```

**Example Response:**
```json
{
  "total": 150,
  "offset": 0,
  "limit": 25,
  "rows": [
    { "person_id": 1, "first_name": "John", ... },
    { "person_id": 2, "first_name": "Jane", ... }
  ]
}
```

## Response Format

### Success Response

```json
{
  "success": true,
  "message": "Customer created successfully",
  "id": 42
}
```

### Error Response

```json
{
  "success": false,
  "message": "Error description here"
}
```

### HTTP Status Codes

| Status Code | Description                                      |
|-------------|--------------------------------------------------|
| 200         | Success                                          |
| 201         | Resource created successfully                    |
| 400         | Bad request / Invalid input                      |
| 401         | Unauthorized / Invalid API key                   |
| 404         | Resource not found                               |
| 409         | Conflict (e.g., duplicate unique field)          |
| 500         | Internal server error                            |

## Endpoints Summary

### Customers

| Method | Endpoint                      | Description                    | Access  |
|--------|-------------------------------|--------------------------------|---------|
| GET    | `/customers`                  | List customers                 | Read    |
| POST   | `/customers`                  | Create customer                | Write   |
| GET    | `/customers/{id}`              | Get customer by ID             | Read    |
| PUT    | `/customers/{id}`              | Update customer                | Write   |
| DELETE | `/customers/{id}`              | Delete customer (soft delete)  | Write   |
| POST   | `/customers/batch-delete`      | Delete multiple customers      | Write   |
| GET    | `/customers/suggest`           | Autocomplete suggestions       | Read    |

### Suppliers

| Method | Endpoint                      | Description                    | Access  |
|--------|-------------------------------|--------------------------------|---------|
| GET    | `/suppliers`                  | List suppliers                 | Read    |
| POST   | `/suppliers`                  | Create supplier                | Write   |
| GET    | `/suppliers/{id}`              | Get supplier by ID             | Read    |
| PUT    | `/suppliers/{id}`              | Update supplier                | Write   |
| DELETE | `/suppliers/{id}`              | Delete supplier (soft delete)  | Write   |
| POST   | `/suppliers/batch-delete`      | Delete multiple suppliers      | Write   |
| GET    | `/suppliers/suggest`           | Autocomplete suggestions       | Read    |

### Items

| Method | Endpoint                      | Description                    | Access  |
|--------|-------------------------------|--------------------------------|---------|
| GET    | `/items`                      | List items                     | Read    |
| POST   | `/items`                      | Create item                    | Write   |
| GET    | `/items/{id}`                  | Get item by ID                 | Read    |
| PUT    | `/items/{id}`                  | Update item                    | Write   |
| DELETE | `/items/{id}`                  | Delete item (soft delete)      | Write   |
| POST   | `/items/batch-delete`          | Delete multiple items          | Write   |
| POST   | `/items/batch-update`          | Update multiple items          | Write   |
| GET    | `/items/suggest`               | Autocomplete suggestions       | Read    |
| GET    | `/items/{id}/quantities`       | Get stock quantities           | Read    |

### Inventory

| Method | Endpoint                      | Description                    | Access  |
|--------|-------------------------------|--------------------------------|---------|
| GET    | `/inventory`                  | List inventory transactions     | Read    |
| POST   | `/inventory`                  | Create inventory adjustment    | Write   |
| POST   | `/inventory/bulk`              | Bulk inventory adjustments     | Write   |

### Sales (Read-Only)

| Method | Endpoint                      | Description                    | Access  |
|--------|-------------------------------|--------------------------------|---------|
| GET    | `/sales`                      | List sales                     | Read    |
| GET    | `/sales/{id}`                  | Get sale details               | Read    |
| GET    | `/sales/{id}/items`            | Get sale items                 | Read    |
| GET    | `/sales/{id}/payments`         | Get sale payments             | Read    |

### Receivings (Read-Only)

| Method | Endpoint                      | Description                    | Access  |
|--------|-------------------------------|--------------------------------|---------|
| GET    | `/receivings`                 | List receivings                | Read    |
| GET    | `/receivings/{id}`             | Get receiving details          | Read    |
| GET    | `/receivings/{id}/items`       | Get receiving items            | Read    |

## Schema Reference

### Common Fields

#### Person Fields (base for Customer, Supplier)

| Field         | Type      | Description                  |
|---------------|-----------|------------------------------|
| `first_name`  | string    | First name (required)        |
| `last_name`   | string    | Last name (required)         |
| `gender`      | integer   | Gender (0=male, 1=female)    |
| `phone_number`| string    | Phone number                 |
| `email`       | string    | Email address                |
| `address_1`   | string    | Address line 1               |
| `address_2`   | string    | Address line 2               |
| `city`        | string    | City                         |
| `state`       | string    | State/Province               |
| `zip`         | string    | Postal/ZIP code              |
| `country`     | string    | Country                      |
| `comments`    | string    | Additional notes             |

### Customer Fields

Extends Person fields with:

| Field              | Type      | Description                        |
|--------------------|-----------|------------------------------------|
| `person_id`        | integer   | Unique identifier (read-only)      |
| `account_number`   | string    | Customer account number            |
| `taxable`          | integer   | Taxable status (0/1)               |
| `tax_id`           | string    | Tax identification number          |
| `sales_tax_code_id`| integer   | Sales tax code ID                  |
| `discount`         | decimal   | Discount percentage/amount         |
| `discount_type`    | integer   | Discount type (0=percent, 1=fixed) |
| `company_name`     | string    | Company name                       |
| `package_id`       | integer   | Rewards package ID                 |
| `points`           | integer   | Rewards points balance             |
| `consent`          | integer   | Consent status (0/1)               |

### Supplier Fields

Extends Person fields with:

| Field           | Type      | Description                        |
|-----------------|-----------|-------------------------------------|
| `person_id`     | integer   | Unique identifier (read-only)      |
| `company_name`  | string    | Company name                        |
| `account_number`| string    | Supplier account number             |
| `tax_id`        | string    | Tax identification number          |
| `agency_name`   | string    | Agency name                         |
| `category`      | integer   | Category (0=goods, 1=cost)          |

### Item Fields

| Field                | Type      | Required | Description                          |
|----------------------|-----------|----------|--------------------------------------|
| `item_id`            | integer   | auto    | Unique identifier (read-only)        |
| `name`               | string    | yes     | Item name                            |
| `category`           | string    | yes     | Item category                        |
| `supplier_id`        | integer   | no      | Supplier ID                          |
| `item_number`        | string    | no      | Barcode/SKU                          |
| `description`        | string    | no      | Item description                     |
| `cost_price`         | decimal   | no      | Cost price                           |
| `unit_price`         | decimal   | yes     | Selling price                        |
| `reorder_level`      | decimal   | no      | Reorder threshold                    |
| `receiving_quantity` | decimal   | no      | Receiving quantity (default 1)       |
| `allow_alt_description`| integer | no      | Allow alt description (0/1)          |
| `is_serialized`      | integer   | no      | Has serial number (0/1)             |
| `stock_type`         | integer   | no      | Stock type (0=stocked, 1=non-stocked)|
| `item_type`          | integer   | no      | Item type (0=standard, 1=kit, 2=temp)|
| `tax_category_id`    | integer   | no      | Tax category ID                      |
| `qty_per_pack`       | decimal   | no      | Quantity per pack                    |
| `pack_name`          | string    | no      | Pack name                            |
| `hsn_code`           | string    | no      | HSN code                             |

### Inventory Adjustment

| Field            | Type      | Required | Description                          |
|------------------|-----------|----------|--------------------------------------|
| `item_id`        | integer   | yes      | Item ID to adjust                     |
| `trans_inventory`| decimal   | yes      | Quantity change (+ add, - remove)     |
| `trans_location` | integer   | no       | Stock location ID                     |
| `trans_comment`  | string    | no       | Reason for adjustment                 |

## OpenAPI Specification

The complete OpenAPI 3.1.0 specification is available at:

- **YAML format:** `/public/api/openapi.yaml`

This specification can be used with:
- [Swagger UI](https://swagger.io/tools/swagger-ui/) for interactive documentation
- [Swagger Codegen](https://swagger.io/tools/swagger-codegen/) to generate client SDKs
- [OpenAPI Generator](https://openapi-generator.tech/) for code generation
- API testing tools like Postman or Insomnia

## Implementation Notes

### Phase 1: Core Endpoints (Proposed)

1. Customers API (full CRUD)
2. Suppliers API (full CRUD)
3. Items API (full CRUD)
4. Inventory adjustments API (create only)

### Phase 2: Read-Only Endpoints (Proposed)

1. Sales API (read-only)
2. Receivings API (read-only)

### Phase 3: Extended Features (Future)

1. Batch operations for all endpoints
2. Search/filter capabilities
3. Authorization/permissions integration
4. Rate limiting
5. API key management interface

## Discussion Topics

The following aspects of the API design are open for discussion:

1. **Field naming conventions**: Currently following existing database column names. Should we use camelCase for JSON?

2. **Batch operations**: Current design separates batch-delete and batch-update. Should we consolidate?

3. **Date formats**: Using ISO 8601 (date-time). Is timezone handling needed?

4. **Error response structure**: Current format uses `{success, message}`. Should we include error codes?

5. **Relationship representations**: Should nested resources (e.g., sale items) always be included?

6. **Inventory adjustments**: Should we support setting absolute quantities vs. relative changes?

7. **Authorization integration**: How should API access integrate with existing employee permissions?

8. **Stock locations**: Multiple locations per item - do we need location-specific endpoints?
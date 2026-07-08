# Products Module

## Scope

Products master data for industrial MRP with tenant isolation.

Includes:
- product types: FG, WIP, RAW, CONSUMABLE
- SKU management
- UOM
- safety stock
- lead time
- lot control
- serial control

## Files generated

- Migration: `create_products_table`
- Model: `Product`
- DTOs: `CreateProductDTO`, `UpdateProductDTO`
- Repository: `ProductRepository`, `EloquentProductRepository`
- Service: `ProductService`
- Controller: `ProductController`
- Requests: `StoreProductRequest`, `UpdateProductRequest`

## API Endpoints

Tenant-protected and permission-protected:
- GET `/api/v1/products`
- POST `/api/v1/products`
- GET `/api/v1/products/{id}`
- PUT `/api/v1/products/{id}`
- DELETE `/api/v1/products/{id}`

Required middleware:
- `auth:sanctum`
- `ResolveTenant`
- `CheckPermission:products.<action>`

## Business rules

- SKU unique per tenant (`company_id + sku`).
- product_type constrained to `FG|WIP|RAW|CONSUMABLE`.
- No business rules in controller.
- Tenant scope auto-applied by `TenantModel`.

# Product Versioning Module

## Capabilities

- Immutable approved versions
- Effective dating (`effective_from`, `effective_to`)
- Approval workflow
- Version history
- Compatibility rules (`NONE`, `BACKWARD`, `FORWARD`, `FULL`)
- Effective version selection by date

## Persistence

- Table: `product_versions`
- Tenant-scoped by `company_id`
- Unique version per product and tenant (`company_id + product_id + version_number`)

## Workflow

1. Create draft version
2. Update draft while status is `DRAFT`
3. Approve draft to `APPROVED`
4. Optionally mark approved version as `OBSOLETE`

Approved versions are immutable.

## Compatibility rule behavior

- `NONE`: requires non-overlapping effective window with latest approved version.
- `BACKWARD`, `FORWARD`, `FULL`: accepted for controlled coexistence strategy.

## Endpoints

- GET `/api/v1/products/{productId}/versions`
- POST `/api/v1/products/{productId}/versions`
- GET `/api/v1/products/{productId}/versions/{versionId}`
- PUT `/api/v1/products/{productId}/versions/{versionId}`
- POST `/api/v1/products/{productId}/versions/{versionId}/approve`
- POST `/api/v1/products/{productId}/versions/{versionId}/obsolete`
- GET `/api/v1/products/{productId}/versions/effective?date=YYYY-MM-DD`

All endpoints are tenant-protected and permission-protected.

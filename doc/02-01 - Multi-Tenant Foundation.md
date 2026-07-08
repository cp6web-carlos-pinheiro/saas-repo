# Multi-Tenant Foundation (Laravel MRP)

## Tenant isolation strategy

Strategy: single database with strict row-level tenant isolation using `company_id`.

Rules:
- Every tenant-owned record must include `company_id`.
- Tenant is resolved per request by middleware using header `X-Company-Id` or user default tenant.
- Membership is validated before tenant context is accepted.
- Global tenant scope is auto-applied by trait for tenant-owned models.

## Implemented entities

- Company
- Plant
- Warehouse
- User
- Role
- Permission (supporting RBAC)

## Middleware

- `ResolveTenant`: resolves and validates tenant context.
- `CheckPermission`: enforces RBAC permission checks in tenant scope.

## DB scoping

- Trait `BelongsToTenant` applies global query scope by `company_id`.
- Trait also auto-fills `company_id` on model creation when tenant context is present.

## Authentication

- Sanctum token auth.
- Endpoints:
  - POST `/api/v1/auth/login`
  - GET `/api/v1/auth/me`
  - POST `/api/v1/auth/logout`

## Permission model (RBAC)

- `roles` are tenant-scoped.
- `permissions` are global capability definitions.
- `role_user` links user-role-company.
- `permission_role` links role-permission.
- `User::hasPermission(permissionSlug, companyId)` checks effective access.

## Migrations added

- create_companies_table
- create_users_table (+ company_user)
- create_plants_and_warehouses_tables
- create_roles_and_permissions_tables (+ pivots + personal_access_tokens)

## Operational notes

- Apply migrations before using APIs.
- Ensure providers are registered in Laravel bootstrap.
- Use `ResolveTenant` in every tenant-protected route group.
- Keep controllers thin and delegate business behavior to services/actions.

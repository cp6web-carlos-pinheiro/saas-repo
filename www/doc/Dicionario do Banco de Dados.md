# Dicionario do Banco de Dados

Inventario gerado a partir do schema ativo da aplicacao via `information_schema`. 

## Resumo

- Gerado em: 2026-07-18 19:40:56

## Indice

- Schema `mrp`
  - `account_invitations`
  - `audit_logs`
  - `bom_headers`
  - `bom_items`
  - `cache`
  - `cache_locks`
  - `companies`
  - `company_user`
  - `email_verifications`
  - `engineering_change_order_lines`
  - `engineering_change_orders`
  - `failed_jobs`
  - `genealogy_nodes`
  - `genealogy_relations`
  - `inventory_balances`
  - `inventory_lots`
  - `inventory_serials`
  - `job_batches`
  - `jobs`
  - `migrations`
  - `onboarding_profiles`
  - `organizations`
  - `password_reset_tokens`
  - `password_resets`
  - `permission_role`
  - `permissions`
  - `personal_access_tokens`
  - `plants`
  - `product_versions`
  - `production_calendar_days`
  - `production_order_bom_item_snapshots`
  - `production_order_bom_snapshots`
  - `production_order_material_consumptions`
  - `production_order_outputs`
  - `production_order_routing_operation_snapshots`
  - `production_order_snapshots`
  - `production_orders`
  - `products`
  - `purchase_order_lines`
  - `purchase_orders`
  - `purchase_requisition_lines`
  - `purchase_requisitions`
  - `role_user`
  - `roles`
  - `routing_operation_snapshots`
  - `routing_operations`
  - `routing_version_snapshots`
  - `routing_versions`
  - `sessions`
  - `social_accounts`
  - `stock_ledger_allocations`
  - `stock_ledger_movements`
  - `subscriptions`
  - `supplier_products`
  - `suppliers`
  - `tenants`
  - `trials`
  - `users`
  - `warehouses`
  - `work_center_shifts`
  - `work_centers`

## Schema `hermes`

### `TB_HMS_failed_jobs`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `uuid` | `varchar(255)` | NO | - | `UNI` | - |
| `connection` | `text` | NO | - | - | - |
| `queue` | `text` | NO | - | - | - |
| `payload` | `longtext` | NO | - | - | - |
| `exception` | `longtext` | NO | - | - | - |
| `failed_at` | `timestamp` | NO | `CURRENT_TIMESTAMP` | - | `DEFAULT_GENERATED` |

### `TB_HMS_lane_requirements`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `origin_site` | `varchar(255)` | NO | - | - | - |
| `destination_site` | `varchar(255)` | NO | - | - | - |
| `material_type` | `varchar(255)` | NO | - | - | - |
| `lane_id` | `varchar(255)` | NO | - | - | - |
| `doc_name` | `varchar(255)` | NO | - | - | - |
| `doc_source` | `varchar(255)` | NO | - | - | - |
| `review_needed_yn` | `char(255)` | NO | - | - | - |
| `user_reviewer` | `varchar(255)` | NO | - | - | - |
| `automatic_yn` | `char(255)` | NO | - | - | - |
| `number_of_physical_copies` | `int` | NO | - | - | - |
| `courrier_yn` | `varchar(255)` | NO | - | - | - |
| `driver_yn` | `varchar(255)` | NO | - | - | - |
| `user_owner` | `varchar(255)` | NO | - | - | - |
| `user_escalation_1` | `varchar(255)` | NO | - | - | - |
| `user_escalation_2` | `varchar(255)` | NO | - | - | - |
| `deleted_yn` | `char(255)` | NO | `N` | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `TB_HMS_migrations`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `int unsigned` | NO | - | `PRI` | `auto_increment` |
| `migration` | `varchar(255)` | NO | - | - | - |
| `batch` | `int` | NO | - | - | - |

### `TB_HMS_password_reset_tokens`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `email` | `varchar(255)` | NO | - | `PRI` | - |
| `token` | `varchar(255)` | NO | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |

### `TB_HMS_personal_access_tokens`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `tokenable_type` | `varchar(255)` | NO | - | `MUL` | - |
| `tokenable_id` | `bigint unsigned` | NO | - | - | - |
| `name` | `varchar(255)` | NO | - | - | - |
| `token` | `varchar(64)` | NO | - | `UNI` | - |
| `abilities` | `text` | YES | - | - | - |
| `last_used_at` | `timestamp` | YES | - | - | - |
| `expires_at` | `timestamp` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `TB_HMS_reject_reasons`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `reason` | `varchar(255)` | NO | - | `UNI` | - |
| `description` | `varchar(255)` | YES | - | - | - |
| `deleted_yn` | `char(255)` | NO | `N` | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `TB_HMS_site_connections`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `connection_name` | `varchar(255)` | NO | - | - | - |
| `id_site` | `bigint` | NO | `0` | - | - |
| `endpoint` | `varchar(255)` | YES | - | - | - |
| `username` | `varchar(255)` | YES | - | - | - |
| `password` | `varchar(255)` | YES | - | - | - |
| `database` | `varchar(255)` | YES | - | - | - |
| `port` | `varchar(255)` | YES | - | - | - |
| `deleted_yn` | `char(255)` | NO | `N` | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `TB_HMS_sites`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `site_name` | `varchar(255)` | NO | - | `UNI` | - |
| `sap_site_id` | `varchar(255)` | YES | - | - | - |
| `agh_api_endpoint` | `varchar(255)` | YES | - | - | - |
| `agh_api_username` | `varchar(255)` | YES | - | - | - |
| `agh_api_password` | `varchar(255)` | YES | - | - | - |
| `deleted_yn` | `char(255)` | NO | `N` | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |
| `agh_db_endpoint` | `varchar(255)` | YES | - | - | - |
| `agh_db_username` | `varchar(255)` | YES | - | - | - |
| `agh_db_password` | `varchar(255)` | YES | - | - | - |
| `agh_db_database` | `varchar(255)` | YES | - | - | - |

### `TB_HMS_user_models`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `TB_HMS_users`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `name` | `varchar(255)` | NO | - | - | - |
| `email` | `varchar(255)` | NO | - | `UNI` | - |
| `sponsor` | `varchar(255)` | YES | - | - | - |
| `company` | `varchar(255)` | YES | - | - | - |
| `profile` | `varchar(255)` | NO | - | - | - |
| `local_user_yn` | `varchar(255)` | NO | `N` | - | - |
| `password` | `varchar(255)` | NO | - | - | - |
| `deleted_yn` | `char(255)` | NO | `N` | - | - |
| `remember_token` | `varchar(100)` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `failed_jobs`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `uuid` | `varchar(255)` | NO | - | `UNI` | - |
| `connection` | `text` | NO | - | - | - |
| `queue` | `text` | NO | - | - | - |
| `payload` | `longtext` | NO | - | - | - |
| `exception` | `longtext` | NO | - | - | - |
| `failed_at` | `timestamp` | NO | `CURRENT_TIMESTAMP` | - | `DEFAULT_GENERATED` |

## Schema `mrp`

### `account_invitations`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `organization_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `invited_by_user_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `accepted_by_user_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `email` | `varchar(190)` | NO | - | `MUL` | - |
| `name` | `varchar(150)` | YES | - | - | - |
| `role_slug` | `varchar(80)` | NO | `organization-member` | - | - |
| `token` | `varchar(128)` | NO | - | `UNI` | - |
| `expires_at` | `datetime` | NO | - | - | - |
| `sent_at` | `datetime` | YES | - | - | - |
| `accepted_at` | `datetime` | YES | - | - | - |
| `revoked_at` | `datetime` | YES | - | - | - |
| `meta` | `json` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `audit_logs`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `user_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `organization_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `event` | `varchar(120)` | NO | - | `MUL` | - |
| `severity` | `varchar(20)` | NO | `info` | - | - |
| `ip_address` | `varchar(45)` | YES | - | - | - |
| `user_agent` | `text` | YES | - | - | - |
| `context` | `json` | YES | - | - | - |
| `occurred_at` | `timestamp` | NO | `CURRENT_TIMESTAMP` | `MUL` | `DEFAULT_GENERATED` |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `bom_headers`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `product_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `version_number` | `int unsigned` | NO | - | - | - |
| `status` | `varchar(20)` | NO | `DRAFT` | - | - |
| `effective_from` | `date` | YES | - | - | - |
| `effective_to` | `date` | YES | - | - | - |
| `description` | `varchar(255)` | YES | - | - | - |
| `approved_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `approved_at` | `timestamp` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `bom_items`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `bom_header_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `component_product_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `line_no` | `int unsigned` | NO | `1` | - | - |
| `quantity_per` | `decimal(18,6)` | NO | - | - | - |
| `scrap_factor` | `decimal(8,4)` | NO | `0.0000` | - | - |
| `uom` | `varchar(20)` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `cache`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `key` | `varchar(255)` | NO | - | `PRI` | - |
| `value` | `mediumtext` | NO | - | - | - |
| `expiration` | `int` | NO | - | `MUL` | - |

### `cache_locks`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `key` | `varchar(255)` | NO | - | `PRI` | - |
| `owner` | `varchar(255)` | NO | - | - | - |
| `expiration` | `int` | NO | - | `MUL` | - |

### `companies`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `name` | `varchar(150)` | NO | - | - | - |
| `code` | `varchar(50)` | NO | - | `UNI` | - |
| `is_active` | `tinyint(1)` | NO | `1` | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `company_user`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `user_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `is_default` | `tinyint(1)` | NO | `0` | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `email_verifications`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `user_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `token` | `varchar(128)` | NO | - | `UNI` | - |
| `expires_at` | `datetime` | NO | - | - | - |
| `verified_at` | `datetime` | YES | - | - | - |
| `requested_ip` | `varchar(45)` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `engineering_change_order_lines`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `engineering_change_order_id` | `bigint unsigned` | NO | - | - | - |
| `target_domain` | `varchar(20)` | NO | - | - | - |
| `target_entity_id` | `bigint unsigned` | NO | - | - | - |
| `change_type` | `varchar(40)` | NO | `VERSION_CHANGE` | - | - |
| `from_version_number` | `int unsigned` | YES | - | - | - |
| `to_version_number` | `int unsigned` | YES | - | - | - |
| `effective_from` | `date` | YES | - | - | - |
| `effective_to` | `date` | YES | - | - | - |
| `impact_level` | `varchar(20)` | NO | `MEDIUM` | - | - |
| `change_summary` | `text` | YES | - | - | - |
| `metadata` | `json` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `engineering_change_orders`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `eco_number` | `varchar(40)` | NO | - | - | - |
| `title` | `varchar(180)` | NO | - | - | - |
| `description` | `text` | YES | - | - | - |
| `status` | `varchar(20)` | NO | `DRAFT` | - | - |
| `effective_from` | `date` | YES | - | - | - |
| `effective_to` | `date` | YES | - | - | - |
| `requested_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `submitted_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `submitted_at` | `timestamp` | YES | - | - | - |
| `approved_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `approved_at` | `timestamp` | YES | - | - | - |
| `rejected_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `rejected_at` | `timestamp` | YES | - | - | - |
| `rejection_reason` | `text` | YES | - | - | - |
| `implemented_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `implemented_at` | `timestamp` | YES | - | - | - |
| `impact_summary` | `json` | YES | - | - | - |
| `metadata` | `json` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `failed_jobs`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `uuid` | `varchar(255)` | NO | - | `UNI` | - |
| `connection` | `text` | NO | - | - | - |
| `queue` | `text` | NO | - | - | - |
| `payload` | `longtext` | NO | - | - | - |
| `exception` | `longtext` | NO | - | - | - |
| `failed_at` | `timestamp` | NO | `CURRENT_TIMESTAMP` | - | `DEFAULT_GENERATED` |

### `genealogy_nodes`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `node_type` | `varchar(40)` | NO | - | - | - |
| `source_id` | `bigint unsigned` | NO | - | - | - |
| `source_reference` | `varchar(120)` | YES | - | - | - |
| `product_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `warehouse_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `metadata` | `json` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `genealogy_relations`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `parent_node_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `child_node_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `relation_type` | `varchar(40)` | NO | - | - | - |
| `quantity` | `decimal(18,6)` | YES | - | - | - |
| `uom` | `varchar(20)` | YES | - | - | - |
| `production_order_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `stock_movement_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `metadata` | `json` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `inventory_balances`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `warehouse_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `product_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `qty_available` | `decimal(18,6)` | NO | `0.000000` | - | - |
| `qty_reserved` | `decimal(18,6)` | NO | `0.000000` | - | - |
| `qty_in_transit` | `decimal(18,6)` | NO | `0.000000` | - | - |
| `qty_inspection` | `decimal(18,6)` | NO | `0.000000` | - | - |
| `last_movement_at` | `timestamp` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `inventory_lots`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `warehouse_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `product_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `lot_number` | `varchar(80)` | NO | - | - | - |
| `manufactured_at` | `date` | YES | - | - | - |
| `expires_at` | `date` | YES | - | - | - |
| `status` | `varchar(20)` | NO | `ACTIVE` | - | - |
| `source_movement_id` | `bigint unsigned` | YES | - | - | - |
| `metadata` | `json` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `inventory_serials`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `warehouse_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `product_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `inventory_lot_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `serial_number` | `varchar(120)` | NO | - | - | - |
| `status` | `varchar(20)` | NO | `ACTIVE` | - | - |
| `source_movement_id` | `bigint unsigned` | YES | - | - | - |
| `metadata` | `json` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `job_batches`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `varchar(255)` | NO | - | `PRI` | - |
| `name` | `varchar(255)` | NO | - | - | - |
| `total_jobs` | `int` | NO | - | - | - |
| `pending_jobs` | `int` | NO | - | - | - |
| `failed_jobs` | `int` | NO | - | - | - |
| `failed_job_ids` | `longtext` | NO | - | - | - |
| `options` | `mediumtext` | YES | - | - | - |
| `cancelled_at` | `int` | YES | - | - | - |
| `created_at` | `int` | NO | - | - | - |
| `finished_at` | `int` | YES | - | - | - |

### `jobs`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `queue` | `varchar(255)` | NO | - | `MUL` | - |
| `payload` | `longtext` | NO | - | - | - |
| `attempts` | `tinyint unsigned` | NO | - | - | - |
| `reserved_at` | `int unsigned` | YES | - | - | - |
| `available_at` | `int unsigned` | NO | - | - | - |
| `created_at` | `int unsigned` | NO | - | - | - |

### `migrations`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `int unsigned` | NO | - | `PRI` | `auto_increment` |
| `migration` | `varchar(255)` | NO | - | - | - |
| `batch` | `int` | NO | - | - | - |

### `onboarding_profiles`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `organization_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `user_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `segment` | `varchar(120)` | YES | - | - | - |
| `operation_size` | `varchar(80)` | YES | - | - | - |
| `timezone` | `varchar(80)` | YES | - | - | - |
| `import_data` | `tinyint(1)` | NO | `0` | - | - |
| `connect_integrations` | `tinyint(1)` | NO | `0` | - | - |
| `invite_team` | `tinyint(1)` | NO | `0` | - | - |
| `progress` | `tinyint unsigned` | NO | `0` | - | - |
| `completed_at` | `timestamp` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `organizations`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `name` | `varchar(180)` | NO | - | - | - |
| `slug` | `varchar(180)` | NO | - | `UNI` | - |
| `domain` | `varchar(180)` | YES | - | `MUL` | - |
| `segment` | `varchar(120)` | YES | - | - | - |
| `operation_size` | `varchar(80)` | YES | - | - | - |
| `timezone` | `varchar(80)` | NO | `UTC` | - | - |
| `preferences` | `json` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `password_reset_tokens`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `email` | `varchar(255)` | NO | - | `PRI` | - |
| `token` | `varchar(255)` | NO | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |

### `password_resets`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `email` | `varchar(190)` | NO | - | `MUL` | - |
| `token` | `varchar(128)` | NO | - | - | - |
| `expires_at` | `datetime` | NO | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `permission_role`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `permission_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `role_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `permissions`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `name` | `varchar(120)` | NO | - | - | - |
| `slug` | `varchar(120)` | NO | - | `UNI` | - |
| `module` | `varchar(100)` | NO | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `personal_access_tokens`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `tokenable_type` | `varchar(255)` | NO | - | `MUL` | - |
| `tokenable_id` | `bigint unsigned` | NO | - | - | - |
| `name` | `varchar(255)` | NO | - | - | - |
| `token` | `varchar(64)` | NO | - | `UNI` | - |
| `abilities` | `text` | YES | - | - | - |
| `last_used_at` | `timestamp` | YES | - | - | - |
| `expires_at` | `timestamp` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `plants`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `name` | `varchar(150)` | NO | - | - | - |
| `code` | `varchar(50)` | NO | - | - | - |
| `timezone` | `varchar(50)` | NO | `UTC` | - | - |
| `is_active` | `tinyint(1)` | NO | `1` | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `product_versions`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `product_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `version_number` | `int unsigned` | NO | - | - | - |
| `status` | `varchar(20)` | NO | `DRAFT` | - | - |
| `effective_from` | `date` | YES | - | - | - |
| `effective_to` | `date` | YES | - | - | - |
| `compatibility_rule` | `varchar(20)` | NO | `NONE` | - | - |
| `change_summary` | `text` | YES | - | - | - |
| `payload` | `json` | NO | - | - | - |
| `created_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `approved_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `approved_at` | `timestamp` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `production_calendar_days`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `work_center_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `calendar_date` | `date` | NO | - | - | - |
| `is_working_day` | `tinyint(1)` | NO | `1` | - | - |
| `available_capacity` | `decimal(10,2)` | YES | - | - | - |
| `notes` | `varchar(255)` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `production_order_bom_item_snapshots`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `production_order_bom_snapshot_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `source_bom_header_id` | `bigint unsigned` | NO | - | - | - |
| `source_bom_version_number` | `int unsigned` | NO | - | - | - |
| `parent_product_id` | `bigint unsigned` | NO | - | - | - |
| `component_product_id` | `bigint unsigned` | NO | - | - | - |
| `line_no` | `int unsigned` | NO | - | - | - |
| `level` | `int unsigned` | NO | - | - | - |
| `quantity_per` | `decimal(18,6)` | NO | - | - | - |
| `scrap_factor` | `decimal(8,4)` | NO | `0.0000` | - | - |
| `quantity_required` | `decimal(18,6)` | NO | - | - | - |
| `quantity_accumulated` | `decimal(24,6)` | NO | - | - | - |
| `path` | `varchar(4000)` | NO | - | - | - |
| `is_cycle` | `tinyint(1)` | NO | `0` | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `production_order_bom_snapshots`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `production_order_id` | `bigint unsigned` | NO | - | - | - |
| `product_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `production_order_quantity` | `decimal(18,6)` | NO | `1.000000` | - | - |
| `reference_date` | `date` | NO | - | - | - |
| `source_bom_header_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `source_bom_version_number` | `int unsigned` | NO | - | - | - |
| `snapshot_hash` | `varchar(64)` | NO | - | - | - |
| `has_cycle` | `tinyint(1)` | NO | `0` | - | - |
| `frozen_at` | `timestamp` | NO | - | - | - |
| `created_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `production_order_material_consumptions`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `production_order_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `product_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `warehouse_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `lot_number` | `varchar(80)` | YES | - | - | - |
| `quantity_consumed` | `decimal(18,6)` | NO | - | - | - |
| `quantity_scrapped` | `decimal(18,6)` | NO | `0.000000` | - | - |
| `ledger_movement_id` | `bigint unsigned` | YES | - | - | - |
| `reference_bom_component_id` | `varchar(64)` | YES | - | - | - |
| `consumed_at` | `timestamp` | NO | - | - | - |
| `operator_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `notes` | `text` | YES | - | - | - |
| `metadata` | `json` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `production_order_outputs`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `production_order_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `quantity_completed` | `decimal(18,6)` | NO | - | - | - |
| `quantity_scrapped` | `decimal(18,6)` | NO | `0.000000` | - | - |
| `lot_number` | `varchar(80)` | YES | - | - | - |
| `produced_at` | `timestamp` | NO | - | - | - |
| `created_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `metadata` | `json` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `production_order_routing_operation_snapshots`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `production_order_snapshot_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `routing_version_id` | `bigint unsigned` | NO | - | - | - |
| `work_center_id` | `bigint unsigned` | NO | - | - | - |
| `operation_no` | `int unsigned` | NO | - | - | - |
| `operation_code` | `varchar(50)` | NO | - | - | - |
| `operation_name` | `varchar(150)` | NO | - | - | - |
| `sequence` | `int unsigned` | NO | - | - | - |
| `setup_time_minutes` | `decimal(10,2)` | NO | `0.00` | - | - |
| `runtime_minutes` | `decimal(10,2)` | NO | `0.00` | - | - |
| `queue_time_minutes` | `decimal(10,2)` | NO | `0.00` | - | - |
| `move_time_minutes` | `decimal(10,2)` | NO | `0.00` | - | - |
| `is_outsourced` | `tinyint(1)` | NO | `0` | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `production_order_snapshots`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `production_order_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `product_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `bom_snapshot_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `bom_header_id` | `bigint unsigned` | YES | - | - | - |
| `bom_version_number` | `int unsigned` | YES | - | - | - |
| `routing_version_snapshot_id` | `bigint unsigned` | YES | - | - | - |
| `routing_version_id` | `bigint unsigned` | YES | - | - | - |
| `routing_version_number` | `int unsigned` | YES | - | - | - |
| `quantity_planned` | `decimal(18,6)` | NO | - | - | - |
| `quantity_scrapped_target` | `decimal(18,6)` | NO | `0.000000` | - | - |
| `snapshot_hash` | `varchar(64)` | NO | - | - | - |
| `frozen_at` | `timestamp` | NO | - | - | - |
| `frozen_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `production_orders`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `product_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `warehouse_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `bom_header_id` | `bigint unsigned` | YES | - | - | - |
| `bom_version_number` | `int unsigned` | YES | - | - | - |
| `routing_version_id` | `bigint unsigned` | YES | - | - | - |
| `routing_version_number` | `int unsigned` | YES | - | - | - |
| `source_type` | `varchar(20)` | NO | - | - | - |
| `source_reference_id` | `bigint unsigned` | YES | - | - | - |
| `source_reference_type` | `varchar(120)` | YES | - | - | - |
| `order_number` | `varchar(50)` | NO | - | - | - |
| `status` | `varchar(20)` | NO | `DRAFT` | - | - |
| `quantity_planned` | `decimal(18,6)` | NO | - | - | - |
| `quantity_produced` | `decimal(18,6)` | NO | `0.000000` | - | - |
| `quantity_scrapped` | `decimal(18,6)` | NO | `0.000000` | - | - |
| `scheduled_start_date` | `date` | YES | - | - | - |
| `scheduled_end_date` | `date` | YES | - | - | - |
| `released_at` | `timestamp` | YES | - | - | - |
| `started_at` | `timestamp` | YES | - | - | - |
| `completed_at` | `timestamp` | YES | - | - | - |
| `created_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `released_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `completed_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `metadata` | `json` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `products`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `sku` | `varchar(80)` | NO | - | - | - |
| `description` | `varchar(255)` | NO | - | - | - |
| `product_type` | `varchar(20)` | NO | - | - | - |
| `uom` | `varchar(20)` | NO | - | - | - |
| `safety_stock` | `int unsigned` | NO | `0` | - | - |
| `lead_time_days` | `int unsigned` | NO | `0` | - | - |
| `lot_control` | `tinyint(1)` | NO | `0` | - | - |
| `serial_control` | `tinyint(1)` | NO | `0` | - | - |
| `is_active` | `tinyint(1)` | NO | `1` | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `purchase_order_lines`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `purchase_order_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `purchase_requisition_line_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `product_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `warehouse_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `quantity_ordered` | `decimal(14,6)` | NO | `0.000000` | - | - |
| `quantity_received` | `decimal(14,6)` | NO | `0.000000` | - | - |
| `unit_price` | `decimal(14,6)` | YES | - | - | - |
| `need_by_date` | `date` | YES | - | - | - |
| `promised_date` | `date` | YES | - | - | - |
| `status` | `varchar(20)` | NO | `OPEN` | - | - |
| `metadata` | `json` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `purchase_orders`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `purchase_order_number` | `varchar(60)` | NO | - | - | - |
| `supplier_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `purchase_requisition_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `status` | `varchar(20)` | NO | `DRAFT` | - | - |
| `order_date` | `date` | NO | - | - | - |
| `expected_delivery_date` | `date` | YES | - | - | - |
| `created_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `approved_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `approved_at` | `timestamp` | YES | - | - | - |
| `notes` | `text` | YES | - | - | - |
| `metadata` | `json` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `purchase_requisition_lines`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `purchase_requisition_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `product_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `warehouse_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `supplier_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `suggested_quantity` | `decimal(14,6)` | NO | `0.000000` | - | - |
| `requested_quantity` | `decimal(14,6)` | NO | `0.000000` | - | - |
| `moq_applied` | `decimal(14,6)` | NO | `1.000000` | - | - |
| `lead_time_days` | `int unsigned` | NO | `0` | - | - |
| `need_by_date` | `date` | NO | - | - | - |
| `order_date` | `date` | NO | - | - | - |
| `status` | `varchar(20)` | NO | `OPEN` | - | - |
| `source_requirement_key` | `varchar(180)` | YES | - | - | - |
| `mrp_reference_date` | `date` | YES | - | - | - |
| `metadata` | `json` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `purchase_requisitions`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `requisition_number` | `varchar(60)` | NO | - | - | - |
| `status` | `varchar(20)` | NO | `DRAFT` | - | - |
| `required_date` | `date` | YES | - | - | - |
| `source_type` | `varchar(80)` | YES | - | - | - |
| `source_reference_id` | `bigint unsigned` | YES | - | - | - |
| `source_reference_type` | `varchar(120)` | YES | - | - | - |
| `requested_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `approved_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `approved_at` | `timestamp` | YES | - | - | - |
| `notes` | `text` | YES | - | - | - |
| `metadata` | `json` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `role_user`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `role_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `user_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `roles`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `name` | `varchar(120)` | NO | - | - | - |
| `slug` | `varchar(120)` | NO | - | - | - |
| `description` | `varchar(255)` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `routing_operation_snapshots`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `routing_version_snapshot_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `routing_version_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `work_center_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `operation_no` | `int unsigned` | NO | - | - | - |
| `operation_code` | `varchar(50)` | NO | - | - | - |
| `operation_name` | `varchar(150)` | NO | - | - | - |
| `sequence` | `int unsigned` | NO | - | - | - |
| `setup_time_minutes` | `decimal(10,2)` | NO | `0.00` | - | - |
| `runtime_minutes` | `decimal(10,2)` | NO | `0.00` | - | - |
| `queue_time_minutes` | `decimal(10,2)` | NO | `0.00` | - | - |
| `move_time_minutes` | `decimal(10,2)` | NO | `0.00` | - | - |
| `is_outsourced` | `tinyint(1)` | NO | `0` | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `routing_operations`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `routing_version_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `work_center_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `operation_no` | `int unsigned` | NO | - | - | - |
| `operation_code` | `varchar(50)` | NO | - | - | - |
| `operation_name` | `varchar(150)` | NO | - | - | - |
| `sequence` | `int unsigned` | NO | - | - | - |
| `setup_time_minutes` | `decimal(10,2)` | NO | `0.00` | - | - |
| `runtime_minutes` | `decimal(10,2)` | NO | `0.00` | - | - |
| `queue_time_minutes` | `decimal(10,2)` | NO | `0.00` | - | - |
| `move_time_minutes` | `decimal(10,2)` | NO | `0.00` | - | - |
| `is_outsourced` | `tinyint(1)` | NO | `0` | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `routing_version_snapshots`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `routing_version_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `product_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `version_number` | `int unsigned` | NO | - | - | - |
| `status` | `varchar(20)` | NO | - | - | - |
| `effective_from` | `date` | NO | - | - | - |
| `effective_to` | `date` | YES | - | - | - |
| `description` | `varchar(255)` | YES | - | - | - |
| `approved_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `approved_at` | `timestamp` | YES | - | - | - |
| `frozen_at` | `timestamp` | NO | - | - | - |
| `snapshot_hash` | `varchar(64)` | NO | - | - | - |
| `created_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `routing_versions`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `product_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `version_number` | `int unsigned` | NO | - | - | - |
| `status` | `varchar(20)` | NO | `DRAFT` | - | - |
| `effective_from` | `date` | YES | - | - | - |
| `effective_to` | `date` | YES | - | - | - |
| `description` | `varchar(255)` | YES | - | - | - |
| `approved_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `approved_at` | `timestamp` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `sessions`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `varchar(255)` | NO | - | `PRI` | - |
| `user_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `ip_address` | `varchar(45)` | YES | - | - | - |
| `user_agent` | `text` | YES | - | - | - |
| `payload` | `longtext` | NO | - | - | - |
| `last_activity` | `int` | NO | - | `MUL` | - |

### `social_accounts`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `user_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `provider` | `varchar(40)` | NO | - | `MUL` | - |
| `provider_user_id` | `varchar(180)` | NO | - | - | - |
| `email` | `varchar(190)` | YES | - | - | - |
| `meta` | `json` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `stock_ledger_allocations`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `issue_movement_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `receipt_movement_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `quantity` | `decimal(18,6)` | NO | - | - | - |
| `sequence_no` | `int unsigned` | NO | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `stock_ledger_movements`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `warehouse_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `product_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `movement_type` | `varchar(30)` | NO | - | - | - |
| `source_bucket` | `varchar(20)` | YES | - | - | - |
| `target_bucket` | `varchar(20)` | YES | - | - | - |
| `quantity` | `decimal(18,6)` | NO | - | - | - |
| `allocation_strategy` | `varchar(20)` | YES | - | - | - |
| `lot_number` | `varchar(80)` | YES | - | - | - |
| `expires_at` | `date` | YES | - | - | - |
| `reference_type` | `varchar(120)` | YES | - | - | - |
| `reference_id` | `bigint unsigned` | YES | - | - | - |
| `notes` | `text` | YES | - | - | - |
| `metadata` | `json` | YES | - | - | - |
| `movement_at` | `timestamp` | NO | - | - | - |
| `created_by` | `bigint unsigned` | YES | - | `MUL` | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `subscriptions`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `organization_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `trial_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `provider` | `varchar(40)` | NO | `stripe` | - | - |
| `provider_customer_id` | `varchar(120)` | YES | - | `MUL` | - |
| `provider_subscription_id` | `varchar(120)` | YES | - | `MUL` | - |
| `plan_code` | `varchar(80)` | NO | `trial` | - | - |
| `status` | `varchar(40)` | NO | `trialing` | `MUL` | - |
| `starts_at` | `datetime` | YES | - | - | - |
| `ends_at` | `datetime` | YES | - | - | - |
| `canceled_at` | `datetime` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `supplier_products`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `supplier_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `product_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `supplier_sku` | `varchar(80)` | YES | - | - | - |
| `moq` | `decimal(14,6)` | NO | `1.000000` | - | - |
| `lead_time_days` | `int unsigned` | NO | `0` | - | - |
| `unit_price` | `decimal(14,6)` | YES | - | - | - |
| `is_preferred` | `tinyint(1)` | NO | `0` | - | - |
| `is_active` | `tinyint(1)` | NO | `1` | - | - |
| `metadata` | `json` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `suppliers`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `code` | `varchar(50)` | NO | - | - | - |
| `name` | `varchar(180)` | NO | - | - | - |
| `email` | `varchar(180)` | YES | - | - | - |
| `phone` | `varchar(50)` | YES | - | - | - |
| `status` | `varchar(20)` | NO | `ACTIVE` | - | - |
| `default_lead_time_days` | `int unsigned` | NO | `0` | - | - |
| `payment_terms` | `varchar(80)` | YES | - | - | - |
| `metadata` | `json` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `tenants`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `organization_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `name` | `varchar(180)` | NO | - | - | - |
| `slug` | `varchar(180)` | NO | - | `UNI` | - |
| `is_active` | `tinyint(1)` | NO | `1` | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `trials`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `user_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `organization_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `trial_start_date` | `datetime` | NO | - | - | - |
| `trial_end_date` | `datetime` | NO | - | - | - |
| `grace_ends_at` | `datetime` | YES | - | - | - |
| `status` | `varchar(40)` | NO | `active` | `MUL` | - |
| `expired_at` | `timestamp` | YES | - | - | - |
| `is_expired` | `tinyint(1)` | NO | `0` | `MUL` | - |
| `email_domain` | `varchar(180)` | YES | - | `MUL` | - |
| `registration_ip` | `varchar(45)` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `users`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `name` | `varchar(150)` | NO | - | - | - |
| `email` | `varchar(190)` | NO | - | `UNI` | - |
| `preferred_locale` | `varchar(10)` | NO | `pt_BR` | `MUL` | - |
| `email_verified_at` | `timestamp` | YES | - | - | - |
| `password` | `varchar(255)` | NO | - | - | - |
| `current_company_id` | `bigint unsigned` | YES | - | `MUL` | - |
| `is_active` | `tinyint(1)` | NO | `1` | - | - |
| `is_platform_admin` | `tinyint(1)` | NO | `0` | `MUL` | - |
| `remember_token` | `varchar(100)` | YES | - | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `warehouses`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `plant_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `name` | `varchar(150)` | NO | - | - | - |
| `code` | `varchar(50)` | NO | - | - | - |
| `is_active` | `tinyint(1)` | NO | `1` | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `work_center_shifts`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `work_center_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `name` | `varchar(80)` | NO | - | - | - |
| `shift_start` | `time` | NO | - | - | - |
| `shift_end` | `time` | NO | - | - | - |
| `capacity_hours` | `decimal(5,2)` | NO | - | - | - |
| `is_active` | `tinyint(1)` | NO | `1` | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

### `work_centers`

| Campo | Tipo | Nulo | Padrao | Chave | Extra |
| --- | --- | --- | --- | --- | --- |
| `id` | `bigint unsigned` | NO | - | `PRI` | `auto_increment` |
| `company_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `plant_id` | `bigint unsigned` | NO | - | `MUL` | - |
| `code` | `varchar(50)` | NO | - | - | - |
| `name` | `varchar(150)` | NO | - | - | - |
| `resource_type` | `varchar(20)` | NO | - | - | - |
| `capacity_per_day` | `decimal(10,2)` | NO | - | - | - |
| `efficiency_factor` | `decimal(5,2)` | NO | `100.00` | - | - |
| `is_active` | `tinyint(1)` | NO | `1` | - | - |
| `created_at` | `timestamp` | YES | - | - | - |
| `updated_at` | `timestamp` | YES | - | - | - |

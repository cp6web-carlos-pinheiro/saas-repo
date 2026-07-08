Abaixo está a reorganização completa dos prompts seguindo a estratégia ótima para Copilot no VS Code:

* context window pequeno
* vertical slices
* domínio isolado
* dependência incremental
* SQL-first
* engine por último
* sem “megaprompts”

⸻

🧭 ORDEM DE EXECUÇÃO (CRÍTICA)

Fase 0 — Foundation (OBRIGATÓRIA)

Fase 1 — Master Data

Fase 2 — BOM

Fase 3 — Routing

Fase 4 — Inventory

Fase 5 — Production (MES básico)

Fase 6 — MRP Engine

Fase 7 — Scheduling / Capacity

Fase 8 — Genealogia

Fase 9 — ECO (Engenharia)

Fase 10 — Compras

Fase 11 — APIs

Fase 12 — Frontend

⸻

🟦 FASE 0 — FOUNDATION (COPILOT BASE LAYER)

PROMPT 0.1 — Arquitetura Base Laravel

Create a clean Laravel 10+ enterprise modular architecture for an industrial MRP system.
Constraints:
- PHP 8.3
- MyMySQL Server
- Redis
- Queue workers
- Modular monolith (NOT microservices)
Generate:
- folder structure (Domain driven)
- BaseService
- BaseRepository
- BaseDTO
- BaseAction
- BaseJob
- BaseEvent
- API response standard
- Exception handler
- Logging strategy
- Transaction manager
- Cache layer

⸻

PROMPT 0.2 — Convenções Globais do Sistema

Define strict coding conventions for an industrial MRP system using Laravel.
Include:
- naming conventions (tables, services, DTOs)
- folder structure rules
- service rules (no logic in controllers)
- repository rules
- DTO usage rules
- event usage rules
- queue usage rules
- MySQL Server best practices
- recursion rules for BOM
- snapshot rules
- versioning rules
Output as a developer handbook.

⸻

PROMPT 0.3 — Multi-Tenant Base

Implement multi-tenant foundation for Laravel MRP system.
Entities:
- Company
- Plant
- Warehouse
- User
- Role (RBAC)
Include:
- tenant isolation strategy
- middleware
- DB scoping
- authentication (JWT or Sanctum)
- permission model

⸻

🟩 FASE 1 — MASTER DATA (SMALL MODULES)

PROMPT 1.1 — Products Module

Build Products module for industrial MRP system in Laravel.
Include:
- product types (FG, WIP, RAW, consumables)
- SKU management
- UOM
- safety stock
- lead time
- lot control
- serial control
Generate:
- migration
- model
- DTO
- repository
- service
- controller
- API routes
- validation requests

⸻

PROMPT 1.2 — Product Versions (CRÍTICO)

Implement Product Versioning system.
Requirements:
- versioned products (immutable)
- effective dating (from/to)
- approval workflow
- version history
- compatibility rules
Generate:
- product_versions table
- Laravel models
- service layer
- version selection logic (based on date)
- API endpoints

⸻

PROMPT 1.3 — Work Centers & Calendars

Create Work Centers and Production Calendar module.
Include:
- machines / lines
- capacity per day
- shifts
- efficiency factor
- working calendar
Generate:
- migrations
- models
- services
- API

⸻

🟨 FASE 2 — BOM (MATERIAL STRUCTURE)

PROMPT 2.1 — BOM Base Structure

Create BOM module with multi-level recursive structure.
Requirements:
- infinite hierarchy BOM
- parent-child structure
- versioned BOM
- effective dating
- scrap factor
Generate:
- MySQL Server schema
- Laravel models
- repository

⸻

PROMPT 2.2 — BOM Explosion Engine (CORE)

Implement BOM explosion service using MySQL Server recursive CTE + Laravel.
Must support:
- infinite levels
- cycle detection
- version selection
- date-based BOM resolution
Return:
- exploded material list
- aggregated quantities

⸻

PROMPT 2.3 — BOM Snapshot Logic

Implement BOM snapshot freezing for production orders.
Rules:
- BOM must be copied at OP creation
- snapshot is immutable
- changes in engineering do NOT affect past OPs
Generate snapshot tables + service

⸻

🟧 FASE 3 — ROUTING (PROCESSO INDUSTRIAL)

PROMPT 3.1 — Routing Model

Create Routing system for production operations.
Include:
- operations
- sequence
- setup time
- runtime
- queue time
- move time
- work center assignment

⸻

PROMPT 3.2 — Routing Versioning

Implement routing versioning system with effective dating and approval workflow.
Include:
- routing_versions
- routing_operations
- immutable snapshots

⸻

🟪 FASE 4 — INVENTORY

PROMPT 4.1 — Inventory Core

Create inventory module.
Include:
- stock available
- reserved stock
- in transit
- inspection stock
- multi warehouse

⸻

PROMPT 4.2 — Stock Ledger

Implement stock ledger system (append-only).
Include:
- movements
- FIFO/FEFO rules
- audit trail
- transaction locking

⸻

PROMPT 4.3 — Lot & Serial Tracking

Implement lot and serial tracking system.
Must support:
- traceability per lot
- expiration dates
- genealogy compatibility

⸻

🟥 FASE 5 — PRODUCTION (MES CORE)

PROMPT 5.1 — Production Orders

Create Production Order module.
Include:
- creation from MRP
- manual creation
- statuses
- partial production
- completion

⸻

PROMPT 5.2 — Production Snapshots (CRITICAL)

Implement production order snapshot system.
Must freeze:
- BOM version
- routing version
- quantities
- operations
Ensure immutability.

⸻

PROMPT 5.3 — Material Consumption

Track material consumption per production order.
Include:
- lot consumption
- quantity consumed
- timestamp
- operator tracking

⸻

🟫 FASE 6 — MRP ENGINE

PROMPT 6.1 — MRP Core Engine

Implement MRP engine in Laravel.
Steps:
1. demand aggregation
2. BOM explosion
3. stock deduction
4. net requirement calculation
5. lead time offset
6. purchase suggestions
7. production suggestions

⸻

PROMPT 6.2 — MRP Scheduling Logic

Add time-based planning to MRP engine.
Include:
- bucketization (daily/weekly)
- backward scheduling
- lead time offset
- priority rules

⸻

PROMPT 6.3 — Incremental MRP Processing

Implement incremental MRP recalculation system.
Include:
- queue jobs
- cache layer
- partial recomputation
- idempotency

⸻

🟦 FASE 7 — SCHEDULING / CAPACITY

PROMPT 7.1 — Capacity Engine

Implement capacity planning system.
Include:
- work center load
- shifts
- efficiency
- bottleneck detection

⸻

PROMPT 7.2 — Scheduling Engine

Create production scheduling engine.
Support:
- finite scheduling
- infinite scheduling
- forward/backward scheduling
- sequencing

⸻

🟩 FASE 8 — GENEALOGY (AS-BUILT)

PROMPT 8.1 — Genealogy Model

Implement industrial genealogy system.
Track:
- lot lineage
- production order lineage
- material relationships

⸻

PROMPT 8.2 — Forward & Backward Trace

Implement traceability engine.
Support:
- forward trace (product → materials)
- backward trace (material → products)
- recursive MySQL Server queries

⸻

🟨 FASE 9 — ECO (ENGINEERING CHANGE)

PROMPT 9.1 — ECO System

Implement Engineering Change Order system.
Include:
- version changes
- approval workflow
- impact analysis
- effective dating

⸻

🟧 FASE 10 — SUPPLY / COMPRAS

PROMPT 10.1 — Procurement Module

Create purchasing module integrated with MRP.
Include:
- supplier management
- purchase requisitions
- purchase orders
- MOQ rules
- lead time

⸻

🟦 FASE 11 — API LAYER

PROMPT 11.1 — API Standardization

Create REST API layer for MRP system.
Include:
- versioning
- pagination
- filtering
- JWT authentication
- bulk endpoints
- error standardization

⸻

🟩 FASE 12 — FRONTEND

PROMPT 12.1 — Industrial Dashboard

Create Laravel Blade-based industrial dashboard.
Include:
- MRP cockpit
- production view
- inventory view
- BOM explorer
- genealogy explorer
- scheduling Gantt

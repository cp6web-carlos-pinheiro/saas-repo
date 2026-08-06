# Estoque, rastreabilidade e genealogia

## Objetivo

Controlar saldos, movimentos, reservas, lotes, números de série e a rastreabilidade entre materiais consumidos e itens produzidos.

## Saldos e ledger

- Consulta e atualização controlada de saldos por empresa, armazém e produto.
- Ledger imutável de movimentos com quantidade, referência, usuário e metadados.
- Movimentos de recebimento, saída, reserva, liberação, transferência e inspeção.
- Ajustes de estoque e reversão por movimento compensatório.
- Transferência entre armazéns com movimentos de saída e entrada relacionados.
- Prevenção de saldo disponível negativo nas operações que retiram estoque.

## Reservas

- Reserva de quantidade para uma referência operacional.
- Liberação manual e liberação de reservas expiradas.
- Separação entre quantidade física, reservada e disponível.

## Lotes e séries

- Cadastro e consulta de lotes e números de série.
- Validação de identificação em produtos configurados para controle por lote ou serial.
- Rastreamento do histórico de movimentos de um lote ou serial.
- Alocações do ledger associam o movimento às identificações movimentadas.

## Genealogia

- Nós e relações de genealogia para produto, lote, serial, ordem, saída e consumo.
- Associação do lote produzido à ordem de produção.
- Associação do consumo de material à genealogia do produto acabado.
- Consulta de rastreabilidade para frente e para trás a partir de uma referência.

## Integrações operacionais

- Recebimentos de compra lançam entrada no estoque.
- Consumo de produção lança saída.
- Apontamento de produto acabado lança recebimento.
- Estornos criam movimentos compensatórios e preservam a trilha original.

## Entidades principais

- `inventory_balances`, `inventory_reservations`.
- `stock_ledger_movements`, `stock_ledger_allocations`.
- `inventory_lots`, `inventory_serials`.
- `genealogy_nodes`, `genealogy_relations`.

## Dicionário de dados

As tabelas abaixo documentam o schema corrente do domínio. “Nula” informa se a coluna aceita `NULL`; “—” indica ausência de valor padrão explícito.

### `genealogy_nodes`

**Finalidade:** Nós rastreáveis da genealogia.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `node_type` | `varchar(40)` | Não | — | Tipo ou classificação de node type. |
| `source_id` | `bigint unsigned` | Não | — | Identificador relacionado a source. |
| `source_reference` | `varchar(120)` | Sim | — | Atributo funcional de source reference. |
| `product_id` | `bigint unsigned` | Sim | — | Referência a `products.id`. |
| `warehouse_id` | `bigint unsigned` | Sim | — | Referência a `warehouses.id`. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `genealogy_relations`

**Finalidade:** Relações entre nós de genealogia.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `parent_node_id` | `bigint unsigned` | Não | — | Referência a `genealogy_nodes.id`. |
| `child_node_id` | `bigint unsigned` | Não | — | Referência a `genealogy_nodes.id`. |
| `relation_type` | `varchar(40)` | Não | — | Tipo ou classificação de relation type. |
| `quantity` | `decimal(18,6)` | Sim | — | Quantidade associada. |
| `uom` | `varchar(20)` | Sim | — | Atributo funcional de uom. |
| `unit_id` | `bigint unsigned` | Sim | — | Identificador relacionado a unit. |
| `production_order_id` | `bigint unsigned` | Sim | — | Referência a `production_orders.id`. |
| `stock_movement_id` | `bigint unsigned` | Sim | — | Referência a `stock_ledger_movements.id`. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `inventory_balances`

**Finalidade:** Saldos por produto e armazém.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `warehouse_id` | `bigint unsigned` | Não | — | Referência a `warehouses.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `qty_available` | `decimal(18,6)` | Não | `0.000000` | Quantidade de available. |
| `qty_reserved` | `decimal(18,6)` | Não | `0.000000` | Quantidade de reserved. |
| `qty_in_transit` | `decimal(18,6)` | Não | `0.000000` | Quantidade de in transit. |
| `qty_inspection` | `decimal(18,6)` | Não | `0.000000` | Quantidade de inspection. |
| `last_movement_at` | `timestamp` | Sim | — | Data e hora de last movement. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `inventory_lots`

**Finalidade:** Registros funcionais de inventory lots.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `warehouse_id` | `bigint unsigned` | Não | — | Referência a `warehouses.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `lot_number` | `varchar(80)` | Não | — | Número funcional de lot number. |
| `manufactured_at` | `date` | Sim | — | Data e hora de manufactured. |
| `expires_at` | `date` | Sim | — | Data e hora de expires. |
| `status` | `varchar(20)` | Não | `ACTIVE` | Estado atual no workflow. |
| `source_movement_id` | `bigint unsigned` | Sim | — | Identificador relacionado a source movement. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `inventory_reservations`

**Finalidade:** Reservas vinculadas a demandas.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `warehouse_id` | `bigint unsigned` | Não | — | Referência a `warehouses.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `reservation_origin` | `varchar(30)` | Não | — | Atributo funcional de reservation origin. |
| `priority` | `int unsigned` | Não | `100` | Atributo funcional de priority. |
| `quantity` | `decimal(18,6)` | Não | — | Quantidade associada. |
| `status` | `varchar(20)` | Não | `RESERVED` | Estado atual no workflow. |
| `reference_type` | `varchar(120)` | Sim | — | Tipo ou classificação de reference type. |
| `reference_id` | `bigint unsigned` | Sim | — | Identificador relacionado a reference. |
| `reserved_at` | `timestamp` | Não | — | Data e hora de reserved. |
| `expires_at` | `timestamp` | Sim | — | Data e hora de expires. |
| `released_at` | `timestamp` | Sim | — | Data e hora de released. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `released_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `release_reason` | `text` | Sim | — | Atributo funcional de release reason. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `inventory_serials`

**Finalidade:** Registros funcionais de inventory serials.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `warehouse_id` | `bigint unsigned` | Não | — | Referência a `warehouses.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `inventory_lot_id` | `bigint unsigned` | Sim | — | Referência a `inventory_lots.id`. |
| `serial_number` | `varchar(120)` | Não | — | Número funcional de serial number. |
| `status` | `varchar(20)` | Não | `ACTIVE` | Estado atual no workflow. |
| `source_movement_id` | `bigint unsigned` | Sim | — | Identificador relacionado a source movement. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `stock_ledger_allocations`

**Finalidade:** Registros funcionais de stock ledger allocations.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `issue_movement_id` | `bigint unsigned` | Não | — | Referência a `stock_ledger_movements.id`. |
| `receipt_movement_id` | `bigint unsigned` | Não | — | Referência a `stock_ledger_movements.id`. |
| `quantity` | `decimal(18,6)` | Não | — | Quantidade associada. |
| `sequence_no` | `int unsigned` | Não | — | Atributo funcional de sequence no. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `stock_ledger_movements`

**Finalidade:** Ledger de movimentos de estoque.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `warehouse_id` | `bigint unsigned` | Não | — | Referência a `warehouses.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `movement_type` | `varchar(30)` | Não | — | Tipo ou classificação de movement type. |
| `source_bucket` | `varchar(20)` | Sim | — | Atributo funcional de source bucket. |
| `target_bucket` | `varchar(20)` | Sim | — | Atributo funcional de target bucket. |
| `quantity` | `decimal(18,6)` | Não | — | Quantidade associada. |
| `allocation_strategy` | `varchar(20)` | Sim | — | Valor monetário ou taxa de allocation strategy. |
| `lot_number` | `varchar(80)` | Sim | — | Número funcional de lot number. |
| `expires_at` | `date` | Sim | — | Data e hora de expires. |
| `reference_type` | `varchar(120)` | Sim | — | Tipo ou classificação de reference type. |
| `reference_id` | `bigint unsigned` | Sim | — | Identificador relacionado a reference. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `movement_at` | `timestamp` | Não | — | Data e hora de movement. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

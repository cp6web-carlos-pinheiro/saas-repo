# Produção, MES e qualidade

## Objetivo

Administrar a ordem de produção desde sua criação até a execução das operações, consumo, qualidade, retrabalho e encerramento.

## Ordem de produção

- Criação manual ou originada do MRP.
- Estados `DRAFT`, `RELEASED`, `IN_PROGRESS`, `PARTIALLY_COMPLETED`, `COMPLETED` e `CANCELLED`.
- Produto, armazém, quantidade planejada, datas e origem.
- Congelamento de BOM, roteiro e versões usadas, preservando a base histórica.
- Liberação, apontamentos parciais e conclusão.
- Ordens concluídas ou canceladas não aceitam novos apontamentos nem consumos; a interface oculta esses formulários quando concluída.

## Operações executáveis

- Materialização das operações do roteiro na OP.
- Sequência, centro, recurso planejado, quantidade, tempos previstos e referência ao snapshot.
- Planejamento de início e fim por operação.
- Estados próprios para acompanhamento da execução.

## Execução MES

- Início, pausa, retomada, parada, conclusão e cancelamento da operação.
- Eventos idempotentes com data de ocorrência, operador, recurso, motivo e metadados.
- Consolidação de tempo produtivo e tempo de pausa no servidor.
- Vínculo do recurso real e do operador que executou a operação.
- Validação de conflito para recurso já ocupado por outra operação em andamento.

## Apontamentos e qualidade

- Quantidade processada, boa, refugada e retrabalhada por operação.
- Saídas com lote, destino, recurso, operador e status de inspeção.
- Checkpoints `PENDING`, `APPROVED` e `REJECTED` na interface web.
- Registros de qualidade e não conformidade com causa, quantidade, destino e observações.
- Criação de ordem de retrabalho ligada à operação de origem e encerramento rastreável.

## Consumo de materiais

- Consumo contra os componentes da BOM congelada e consumo adicional explicitamente autorizado.
- Produto, armazém, quantidade, lote, operador e operação da OP.
- Baixa integrada ao estoque por movimento `ISSUE`.
- Chave de idempotência para evitar duplicidade.
- Estorno controlado por movimento inverso e vínculo entre consumo e movimentos de ledger.

## Entidades principais

- `production_orders`, `production_order_snapshots` e snapshots de BOM/roteiro.
- `production_order_operations`, `production_operation_events` e `production_operation_outputs`.
- `production_order_material_consumptions` e reversões.
- `production_quality_records` e `production_rework_orders`.

## Dicionário de dados

As tabelas abaixo documentam o schema corrente do domínio. “Nula” informa se a coluna aceita `NULL`; “—” indica ausência de valor padrão explícito.

### `production_material_consumption_reversals`

**Finalidade:** Registros funcionais de production material consumption reversals.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_material_consumption_id` | `bigint unsigned` | Não | — | Referência a `production_order_material_consumptions.id`. |
| `original_ledger_movement_id` | `bigint unsigned` | Não | — | Referência a `stock_ledger_movements.id`. |
| `reversal_ledger_movement_id` | `bigint unsigned` | Não | — | Referência a `stock_ledger_movements.id`. |
| `quantity` | `decimal(18,6)` | Não | — | Quantidade associada. |
| `reason` | `varchar(255)` | Não | — | Motivo da ação. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_operation_events`

**Finalidade:** Eventos de execução MES.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_id` | `bigint unsigned` | Sim | — | Referência a `production_orders.id`. |
| `production_order_operation_id` | `bigint unsigned` | Sim | — | Referência a `production_order_operations.id`. |
| `work_center_id` | `bigint unsigned` | Sim | — | Referência a `work_centers.id`. |
| `setup_time_minutes` | `decimal(10,2)` | Não | `0.00` | Tempo de preparação. |
| `process_time_minutes` | `decimal(10,2)` | Não | `0.00` | Tempo de processamento. |
| `event_type` | `varchar(20)` | Não | — | Tipo ou classificação de event type. |
| `idempotency_key` | `varchar(120)` | Não | — | Chave funcional de idempotency key. |
| `occurred_at` | `timestamp` | Não | — | Data e hora de occurred. |
| `operator_id` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `production_resource_id` | `bigint unsigned` | Sim | — | Referência a `production_resources.id`. |
| `reason_code` | `varchar(80)` | Sim | — | Atributo funcional de reason code. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_operation_outputs`

**Finalidade:** Resultados por operação.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_operation_id` | `bigint unsigned` | Não | — | Referência a `production_order_operations.id`. |
| `quantity_good` | `decimal(18,6)` | Não | `0.000000` | Quantidade de good. |
| `quantity_scrapped` | `decimal(18,6)` | Não | `0.000000` | Quantidade de scrapped. |
| `quantity_rework` | `decimal(18,6)` | Não | `0.000000` | Quantidade de rework. |
| `lot_number` | `varchar(80)` | Sim | — | Número funcional de lot number. |
| `inspection_status` | `varchar(20)` | Não | `PENDING` | Atributo funcional de inspection status. |
| `inspected_at` | `timestamp` | Sim | — | Data da inspeção. |
| `inspection_notes` | `text` | Sim | — | Observações da inspeção. |
| `scrap_cause_code` | `varchar(80)` | Sim | — | Atributo funcional de scrap cause code. |
| `destination` | `varchar(30)` | Sim | — | Atributo funcional de destination. |
| `operator_id` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `production_resource_id` | `bigint unsigned` | Sim | — | Referência a `production_resources.id`. |
| `reported_at` | `timestamp` | Não | — | Data e hora de reported. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_order_bom_item_snapshots`

**Finalidade:** Cópia histórica e imutável de production order bom item.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_bom_snapshot_id` | `bigint unsigned` | Não | — | Referência a `production_order_bom_snapshots.id`. |
| `source_bom_header_id` | `bigint unsigned` | Não | — | Identificador relacionado a source bom header. |
| `source_bom_version_number` | `int unsigned` | Não | — | Versão de source bom version number. |
| `parent_product_id` | `bigint unsigned` | Não | — | Identificador relacionado a parent product. |
| `component_product_id` | `bigint unsigned` | Não | — | Identificador relacionado a component product. |
| `unit_id` | `bigint unsigned` | Sim | — | Identificador relacionado a unit. |
| `line_no` | `int unsigned` | Não | — | Atributo funcional de line no. |
| `level` | `int unsigned` | Não | — | Atributo funcional de level. |
| `quantity_per` | `decimal(18,6)` | Não | — | Quantidade de per. |
| `quantity_required` | `decimal(18,6)` | Não | — | Quantidade de required. |
| `quantity_accumulated` | `decimal(24,6)` | Não | — | Quantidade de accumulated. |
| `path` | `varchar(4000)` | Não | — | Atributo funcional de path. |
| `is_cycle` | `tinyint(1)` | Não | `0` | Indicador booleano de is cycle. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_order_bom_snapshots`

**Finalidade:** Cópia histórica e imutável de production order bom.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_id` | `bigint unsigned` | Não | — | Identificador relacionado a production order. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `production_order_quantity` | `decimal(18,6)` | Não | `1.000000` | Quantidade de production order quantity. |
| `reference_date` | `date` | Não | — | Data de reference. |
| `source_bom_header_id` | `bigint unsigned` | Sim | — | Referência a `bom_headers.id`. |
| `source_bom_version_number` | `int unsigned` | Não | — | Versão de source bom version number. |
| `snapshot_hash` | `varchar(64)` | Não | — | Atributo funcional de snapshot hash. |
| `has_cycle` | `tinyint(1)` | Não | `0` | Indicador booleano de has cycle. |
| `frozen_at` | `timestamp` | Não | — | Data e hora de frozen. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_order_material_consumptions`

**Finalidade:** Consumos reais de materiais.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_id` | `bigint unsigned` | Não | — | Referência a `production_orders.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `warehouse_id` | `bigint unsigned` | Não | — | Referência a `warehouses.id`. |
| `lot_number` | `varchar(80)` | Sim | — | Número funcional de lot number. |
| `quantity_consumed` | `decimal(18,6)` | Não | — | Quantidade de consumed. |
| `quantity_scrapped` | `decimal(18,6)` | Não | `0.000000` | Quantidade de scrapped. |
| `ledger_movement_id` | `bigint unsigned` | Sim | — | Identificador relacionado a ledger movement. |
| `reference_bom_component_id` | `varchar(64)` | Sim | — | Identificador relacionado a reference bom component. |
| `consumed_at` | `timestamp` | Não | — | Data e hora de consumed. |
| `operator_id` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |
| `production_order_operation_id` | `bigint unsigned` | Sim | — | Referência a `production_order_operations.id`. |
| `idempotency_key` | `varchar(120)` | Sim | — | Chave funcional de idempotency key. |
| `reversed_by_movement_id` | `bigint unsigned` | Sim | — | Identificador relacionado a reversed by movement. |

### `production_order_operations`

**Finalidade:** Operações executáveis das ordens.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_id` | `bigint unsigned` | Não | — | Referência a `production_orders.id`. |
| `production_order_routing_operation_snapshot_id` | `bigint unsigned` | Sim | — | Referência a `production_order_routing_operation_snapshots.id`. |
| `routing_operation_id` | `bigint unsigned` | Sim | — | Identificador relacionado a routing operation. |
| `standard_time_id` | `bigint unsigned` | Sim | — | Identificador relacionado a standard time. |
| `standard_time_version` | `int unsigned` | Sim | — | Versão de standard time version. |
| `operation_no` | `int unsigned` | Não | — | Atributo funcional de operation no. |
| `operation_code` | `varchar(50)` | Não | — | Atributo funcional de operation code. |
| `operation_name` | `varchar(150)` | Não | — | Atributo funcional de operation name. |
| `sequence` | `int unsigned` | Não | — | Atributo funcional de sequence. |
| `work_center_id` | `bigint unsigned` | Não | — | Referência a `work_centers.id`. |
| `production_resource_id` | `bigint unsigned` | Sim | — | Referência a `production_resources.id`. |
| `status` | `varchar(20)` | Não | `PLANNED` | Estado atual no workflow. |
| `quantity_planned` | `decimal(18,6)` | Não | — | Quantidade de planned. |
| `setup_scope` | `varchar(20)` | Não | `ROUTING` | Atributo funcional de setup scope. |
| `setup_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de setup time. |
| `runtime_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de runtime time. |
| `queue_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de queue time. |
| `move_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de move time. |
| `productive_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de productive time. |
| `lead_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de lead time. |
| `total_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de total time. |
| `planned_start_at` | `datetime` | Sim | — | Data e hora de planned start. |
| `planned_end_at` | `datetime` | Sim | — | Data e hora de planned end. |
| `calculation_metadata` | `json` | Sim | — | Dados estruturados de calculation metadata em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |
| `actual_production_resource_id` | `bigint unsigned` | Sim | — | Referência a `production_resources.id`. |
| `operator_id` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `quantity_processed` | `decimal(18,6)` | Não | `0.000000` | Quantidade de processed. |
| `quantity_good` | `decimal(18,6)` | Não | `0.000000` | Quantidade de good. |
| `quantity_scrapped` | `decimal(18,6)` | Não | `0.000000` | Quantidade de scrapped. |
| `quantity_rework` | `decimal(18,6)` | Não | `0.000000` | Quantidade de rework. |
| `actual_productive_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de actual productive. |
| `actual_pause_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de actual pause. |
| `actual_started_at` | `datetime` | Sim | — | Data e hora de actual started. |
| `actual_completed_at` | `datetime` | Sim | — | Data e hora de actual completed. |

### `production_order_routing_operation_snapshots`

**Finalidade:** Cópia histórica e imutável de production order routing operation.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_snapshot_id` | `bigint unsigned` | Não | — | Referência a `production_order_snapshots.id`. |
| `routing_version_id` | `bigint unsigned` | Não | — | Identificador relacionado a routing version. |
| `standard_time_id` | `bigint unsigned` | Sim | — | Identificador relacionado a standard time. |
| `standard_time_version` | `int unsigned` | Sim | — | Versão de standard time version. |
| `work_center_id` | `bigint unsigned` | Não | — | Identificador relacionado a work center. |
| `operation_no` | `int unsigned` | Não | — | Atributo funcional de operation no. |
| `operation_code` | `varchar(50)` | Não | — | Atributo funcional de operation code. |
| `operation_name` | `varchar(150)` | Não | — | Atributo funcional de operation name. |
| `sequence` | `int unsigned` | Não | — | Atributo funcional de sequence. |
| `setup_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de setup time. |
| `runtime_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de runtime. |
| `queue_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de queue time. |
| `move_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de move time. |
| `is_outsourced` | `tinyint(1)` | Não | `0` | Indicador booleano de is outsourced. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_order_snapshots`

**Finalidade:** Cópia histórica e imutável de production order.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_id` | `bigint unsigned` | Não | — | Referência a `production_orders.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `bom_snapshot_id` | `bigint unsigned` | Sim | — | Referência a `production_order_bom_snapshots.id`. |
| `bom_header_id` | `bigint unsigned` | Sim | — | Identificador relacionado a bom header. |
| `bom_version_number` | `int unsigned` | Sim | — | Versão de bom version number. |
| `routing_version_snapshot_id` | `bigint unsigned` | Sim | — | Identificador relacionado a routing version snapshot. |
| `routing_version_id` | `bigint unsigned` | Sim | — | Identificador relacionado a routing version. |
| `routing_version_number` | `int unsigned` | Sim | — | Versão de routing version number. |
| `quantity_planned` | `decimal(18,6)` | Não | — | Quantidade de planned. |
| `quantity_scrapped_target` | `decimal(18,6)` | Não | `0.000000` | Quantidade de scrapped target. |
| `snapshot_hash` | `varchar(64)` | Não | — | Atributo funcional de snapshot hash. |
| `frozen_at` | `timestamp` | Não | — | Data e hora de frozen. |
| `frozen_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_orders`

**Finalidade:** Ordens de produção.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `warehouse_id` | `bigint unsigned` | Sim | — | Referência a `warehouses.id`. |
| `bom_header_id` | `bigint unsigned` | Sim | — | Identificador relacionado a bom header. |
| `bom_version_number` | `int unsigned` | Sim | — | Versão de bom version number. |
| `routing_version_id` | `bigint unsigned` | Sim | — | Identificador relacionado a routing version. |
| `routing_version_number` | `int unsigned` | Sim | — | Versão de routing version number. |
| `source_type` | `varchar(20)` | Não | — | Tipo ou classificação de source type. |
| `source_reference_id` | `bigint unsigned` | Sim | — | Identificador relacionado a source reference. |
| `source_reference_type` | `varchar(120)` | Sim | — | Tipo ou classificação de source reference type. |
| `order_number` | `varchar(50)` | Não | — | Número funcional de order number. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `quantity_planned` | `decimal(18,6)` | Não | — | Quantidade de planned. |
| `quantity_produced` | `decimal(18,6)` | Não | `0.000000` | Quantidade de produced. |
| `quantity_scrapped` | `decimal(18,6)` | Não | `0.000000` | Quantidade de scrapped. |
| `scheduled_start_date` | `date` | Sim | — | Data de scheduled start. |
| `scheduled_end_date` | `date` | Sim | — | Data de scheduled end. |
| `released_at` | `timestamp` | Sim | — | Data e hora de released. |
| `started_at` | `timestamp` | Sim | — | Data e hora de started. |
| `completed_at` | `timestamp` | Sim | — | Data e hora de completed. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `released_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `completed_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_quality_records`

**Finalidade:** Registros de qualidade.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_operation_id` | `bigint unsigned` | Não | — | Referência a `production_order_operations.id`. |
| `record_type` | `varchar(30)` | Não | `NON_CONFORMITY` | Tipo ou classificação de record type. |
| `status` | `varchar(30)` | Não | `PENDING` | Estado atual no workflow. |
| `quantity` | `decimal(18,6)` | Não | `0.000000` | Quantidade associada. |
| `cause_code` | `varchar(80)` | Sim | — | Atributo funcional de cause code. |
| `destination` | `varchar(30)` | Sim | — | Atributo funcional de destination. |
| `operator_id` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `production_resource_id` | `bigint unsigned` | Sim | — | Referência a `production_resources.id`. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_rework_orders`

**Finalidade:** Ordens de retrabalho.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `source_production_order_operation_id` | `bigint unsigned` | Não | — | Referência a `production_order_operations.id`. |
| `rework_production_order_operation_id` | `bigint unsigned` | Sim | — | Referência a `production_order_operations.id`. |
| `quantity` | `decimal(18,6)` | Não | — | Quantidade associada. |
| `status` | `varchar(20)` | Não | `OPEN` | Estado atual no workflow. |
| `reason_code` | `varchar(80)` | Sim | — | Atributo funcional de reason code. |
| `notes` | `text` | Sim | — | Observações livres. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `completed_at` | `timestamp` | Sim | — | Data e hora de completed. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

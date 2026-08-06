# Planejamento, MRP e PCP

## Objetivo

Calcular necessidades de materiais, governar sugestões de suprimento e programar ordens conforme calendário e capacidade produtiva.

## Planejamento MRP

- Recebe demanda, estoque, recebimentos programados e parâmetros de planejamento.
- Explode a BOM efetiva recursivamente e calcula necessidade bruta e líquida.
- Considera estoque de segurança, lead time, lote mínimo e múltiplos de compra quando disponíveis.
- Gera sugestões `PURCHASE` ou `PRODUCTION` com data de necessidade e liberação.
- Suporta planejamento completo e recálculo incremental/idempotente.
- Persiste execuções em `mrp_plan_runs` e o resumo de resultados.

## Workflow de sugestões

- Sugestões persistidas com chave rastreável e payload original.
- Consulta por tipo e status.
- Aprovação com ajuste de quantidade e justificativa, ou rejeição fundamentada.
- Conversão de sugestão aprovada de produção em ordem de produção.
- Conversão de sugestão aprovada de compra em requisição de compra.
- Eventos registram as transições e a sugestão conserva o documento gerado.

## Programação da produção

- Seleção de ordens liberadas ou em execução.
- Programação forward ou backward.
- Modos finito e infinito e regras de sequenciamento por prioridade/data.
- Uso de centros, turnos, calendário, capacidade e recursos produtivos.
- Cálculo de janelas previstas por operação, separando tempo produtivo e lead time.
- Persistência de programas e linhas, com número, versão, parâmetros e origem.
- Publicação, cancelamento e comparação entre versões de programa.

## Calendário e capacidade

- Calendário por centro de trabalho e dia.
- Geração em lote, dias úteis, exceções, turnos e minutos disponíveis.
- Recursos indisponíveis ou incompatíveis são excluídos da programação finita.

## Entidades principais

- `mrp_plan_runs`, `mrp_suggestions`, `mrp_suggestion_events`.
- `production_schedules`, `production_schedule_lines`.
- `production_calendar_days`, `work_centers`, `work_center_shifts` e `production_resources`.

## Limitações atuais

- Forecast estatístico e simulação avançada de cenários não formam um módulo funcional próprio.
- A qualidade do plano depende de BOM, lead times, estoques, calendários e tempos padrão mantidos corretamente.

## Dicionário de dados

As tabelas abaixo documentam o schema corrente do domínio. “Nula” informa se a coluna aceita `NULL`; “—” indica ausência de valor padrão explícito.

### `mrp_plan_runs`

**Finalidade:** Execuções persistidas do MRP.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `run_key` | `varchar(64)` | Não | — | Chave funcional de run key. |
| `status` | `varchar(20)` | Não | `COMPLETED` | Estado atual no workflow. |
| `reference_date` | `date` | Não | — | Data de reference. |
| `planning_bucket` | `varchar(20)` | Não | `daily` | Atributo funcional de planning bucket. |
| `priority_rule` | `varchar(40)` | Não | `priority_due_date` | Atributo funcional de priority rule. |
| `request_payload` | `json` | Não | — | Dados estruturados de request payload em JSON. |
| `result_summary` | `json` | Sim | — | Dados estruturados de result summary em JSON. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `mrp_suggestion_events`

**Finalidade:** Histórico de eventos de mrp suggestion.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `mrp_suggestion_id` | `bigint unsigned` | Não | — | Referência a `mrp_suggestions.id`. |
| `event_type` | `varchar(40)` | Não | — | Tipo ou classificação de event type. |
| `from_status` | `varchar(30)` | Sim | — | Atributo funcional de from status. |
| `to_status` | `varchar(30)` | Sim | — | Atributo funcional de to status. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `reason` | `text` | Sim | — | Motivo da ação. |
| `payload` | `json` | Sim | — | Conteúdo adicional em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `mrp_suggestions`

**Finalidade:** Sugestões de compra ou produção.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `mrp_plan_run_id` | `bigint unsigned` | Não | — | Referência a `mrp_plan_runs.id`. |
| `suggestion_key` | `varchar(180)` | Não | — | Chave funcional de suggestion key. |
| `suggestion_type` | `varchar(20)` | Não | — | Tipo ou classificação de suggestion type. |
| `status` | `varchar(30)` | Não | `GENERATED` | Estado atual no workflow. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `warehouse_id` | `bigint unsigned` | Sim | — | Referência a `warehouses.id`. |
| `original_quantity` | `decimal(18,6)` | Não | — | Quantidade de original quantity. |
| `approved_quantity` | `decimal(18,6)` | Sim | — | Quantidade de approved quantity. |
| `need_by_date` | `date` | Não | — | Data de need by. |
| `release_date` | `date` | Sim | — | Data de release. |
| `priority` | `int unsigned` | Não | `1000` | Atributo funcional de priority. |
| `bom_version_number` | `int unsigned` | Sim | — | Versão de bom version number. |
| `routing_version_id` | `bigint unsigned` | Sim | — | Identificador relacionado a routing version. |
| `source_requirement_key` | `varchar(180)` | Sim | — | Chave funcional de source requirement key. |
| `source_reference_type` | `varchar(120)` | Sim | — | Tipo ou classificação de source reference type. |
| `source_reference_id` | `bigint unsigned` | Sim | — | Identificador relacionado a source reference. |
| `production_order_id` | `bigint unsigned` | Sim | — | Identificador relacionado a production order. |
| `purchase_requisition_id` | `bigint unsigned` | Sim | — | Identificador relacionado a purchase requisition. |
| `decision_reason` | `text` | Sim | — | Atributo funcional de decision reason. |
| `original_payload` | `json` | Sim | — | Dados estruturados de original payload em JSON. |
| `adjusted_payload` | `json` | Sim | — | Dados estruturados de adjusted payload em JSON. |
| `decided_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `decided_at` | `timestamp` | Sim | — | Data e hora de decided. |
| `converted_at` | `timestamp` | Sim | — | Data e hora de converted. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_calendar_days`

**Finalidade:** Registros funcionais de production calendar days.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `work_center_id` | `bigint unsigned` | Não | — | Referência a `work_centers.id`. |
| `calendar_date` | `date` | Não | — | Data de calendar. |
| `is_working_day` | `tinyint(1)` | Não | `1` | Indicador booleano de is working day. |
| `available_capacity` | `decimal(10,2)` | Sim | — | Atributo funcional de available capacity. |
| `notes` | `varchar(255)` | Sim | — | Observações livres. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_schedule_lines`

**Finalidade:** Itens detalhados de production schedule.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_schedule_id` | `bigint unsigned` | Não | — | Referência a `production_schedules.id`. |
| `production_order_id` | `bigint unsigned` | Não | — | Referência a `production_orders.id`. |
| `production_order_operation_id` | `bigint unsigned` | Não | — | Referência a `production_order_operations.id`. |
| `work_center_id` | `bigint unsigned` | Não | — | Referência a `work_centers.id`. |
| `production_resource_id` | `bigint unsigned` | Sim | — | Referência a `production_resources.id`. |
| `planned_start_at` | `datetime` | Não | — | Data e hora de planned start. |
| `planned_end_at` | `datetime` | Não | — | Data e hora de planned end. |
| `total_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de total time. |
| `capacity_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de capacity time. |
| `lead_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de lead time. |
| `segments` | `json` | Sim | — | Dados estruturados de segments em JSON. |
| `status` | `varchar(20)` | Não | `PLANNED` | Estado atual no workflow. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_schedules`

**Finalidade:** Programas de produção versionados.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `plant_id` | `bigint unsigned` | Sim | — | Referência a `plants.id`. |
| `schedule_number` | `varchar(50)` | Não | — | Número funcional de schedule number. |
| `version_number` | `int unsigned` | Não | `1` | Versão de version number. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `reference_date` | `date` | Não | — | Data de reference. |
| `mode` | `varchar(20)` | Não | `finite` | Atributo funcional de mode. |
| `direction` | `varchar(20)` | Não | `forward` | Atributo funcional de direction. |
| `sequencing_rule` | `varchar(40)` | Não | `priority_due_date` | Atributo funcional de sequencing rule. |
| `parameters` | `json` | Sim | — | Dados estruturados de parameters em JSON. |
| `source_run_key` | `varchar(64)` | Sim | — | Chave funcional de source run key. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `published_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `published_at` | `timestamp` | Sim | — | Data e hora de published. |
| `change_reason` | `text` | Sim | — | Atributo funcional de change reason. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

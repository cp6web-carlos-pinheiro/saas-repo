# Engenharia de produto e processo

## Objetivo

Definir o produto fabricado, sua estrutura de materiais, roteiro, centros, recursos e tempos padrão de forma versionada e rastreável.

## Produtos

- CRUD web e API de produtos por empresa, com SKU, descrição, tipo, unidade, categoria, marca, estoque de segurança e lead time.
- Controle de lote e serial, atributos técnicos e comerciais, unidades alternativas, imagens e anexos.
- Importação e exportação por planilha.
- Versões de produto com histórico, vigência e estados `DRAFT`, `APPROVED` e `OBSOLETE`.

## Lista de materiais (BOM)

- Cabeçalhos e componentes versionados, com vigência, aprovação e obsolescência.
- Quantidade por componente, unidade, nível e sequência.
- Visualização da estrutura e manutenção das revisões pela interface web.
- Explosão recursiva pela API, com seleção da versão efetiva e detecção de ciclos.
- Congelamento da BOM e de seus itens na ordem de produção, preservando a estrutura usada historicamente.

## Roteiros, centros e recursos

- Versões de roteiro e operações sequenciadas por produto.
- Operações com código, nome, centro de trabalho, setup, processo, fila e movimentação.
- Aprovação e snapshot imutável do roteiro e das operações.
- Centros de trabalho, turnos, capacidade diária e calendário produtivo.
- Recursos produtivos vinculados à planta e ao centro, incluindo máquina, equipamento, ferramenta ou linha, com status e disponibilidade.
- Histórico de valor-hora por centro e consulta do valor efetivo por vigência.

## Tempos padrão

- Tempos padrão versionados por operação de roteiro.
- Estados `DRAFT`, `APPROVED` e `OBSOLETE`, vigência e aprovador.
- Cálculo de setup, runtime, fila, movimentação, tempo produtivo e lead time.
- Materialização do tempo efetivo nas operações da ordem de produção, incluindo a versão utilizada.

## Mudança de engenharia (ECO)

- Criação, edição, submissão, aprovação, rejeição e implementação de ordens de mudança.
- Linhas de mudança associadas aos alvos de engenharia.
- Consulta de impacto antes da implementação e validação de pertencimento ao tenant.

## Entidades principais

- `products`, `product_versions`, `bom_headers` e `bom_items`.
- `routing_versions`, `routing_operations` e respectivos snapshots.
- `work_centers`, `work_center_shifts`, `production_resources` e `work_center_hour_rates`.
- `routing_operation_standard_times`.
- `engineering_change_orders` e `engineering_change_order_lines`.

## Dicionário de dados

As tabelas abaixo documentam o schema corrente e, quando indicado, estruturas removidas preservadas como histórico. “Nula” informa se a coluna aceita `NULL`; “—” indica ausência de valor padrão explícito.

### `bom_headers`

**Finalidade:** Versões de listas de materiais.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `version_number` | `int unsigned` | Não | — | Versão de version number. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `effective_from` | `date` | Sim | — | Atributo funcional de effective from. |
| `effective_to` | `date` | Sim | — | Atributo funcional de effective to. |
| `description` | `varchar(255)` | Sim | — | Descrição funcional. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `bom_items`

**Finalidade:** Componentes de uma versão de BOM.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `bom_header_id` | `bigint unsigned` | Não | — | Referência a `bom_headers.id`. |
| `component_product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `unit_id` | `bigint unsigned` | Sim | — | Identificador relacionado a unit. |
| `line_no` | `int unsigned` | Não | `1` | Atributo funcional de line no. |
| `quantity_per` | `decimal(18,6)` | Não | — | Quantidade de per. |
| `uom` | `varchar(20)` | Sim | — | Atributo funcional de uom. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `engineering_change_order_lines`

**Finalidade:** Itens detalhados de engineering change order.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `engineering_change_order_id` | `bigint unsigned` | Não | — | Referência a `engineering_change_orders.id`. |
| `target_domain` | `varchar(20)` | Não | — | Atributo funcional de target domain. |
| `target_entity_id` | `bigint unsigned` | Não | — | Identificador relacionado a target entity. |
| `change_type` | `varchar(40)` | Não | `VERSION_CHANGE` | Tipo ou classificação de change type. |
| `from_version_number` | `int unsigned` | Sim | — | Versão de from version number. |
| `to_version_number` | `int unsigned` | Sim | — | Versão de to version number. |
| `effective_from` | `date` | Sim | — | Atributo funcional de effective from. |
| `effective_to` | `date` | Sim | — | Atributo funcional de effective to. |
| `impact_level` | `varchar(20)` | Não | `MEDIUM` | Atributo funcional de impact level. |
| `change_summary` | `text` | Sim | — | Atributo funcional de change summary. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `engineering_change_orders`

**Finalidade:** Registros funcionais de engineering change orders.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `eco_number` | `varchar(40)` | Não | — | Número funcional de eco number. |
| `title` | `varchar(180)` | Não | — | Atributo funcional de title. |
| `description` | `text` | Sim | — | Descrição funcional. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `effective_from` | `date` | Sim | — | Atributo funcional de effective from. |
| `effective_to` | `date` | Sim | — | Atributo funcional de effective to. |
| `requested_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `submitted_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `submitted_at` | `timestamp` | Sim | — | Data e hora de submitted. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `rejected_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `rejected_at` | `timestamp` | Sim | — | Data e hora de rejected. |
| `rejection_reason` | `text` | Sim | — | Atributo funcional de rejection reason. |
| `implemented_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `implemented_at` | `timestamp` | Sim | — | Data e hora de implemented. |
| `impact_summary` | `json` | Sim | — | Dados estruturados de impact summary em JSON. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `product_versions`

**Finalidade:** Registros funcionais de product versions.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `version_number` | `int unsigned` | Não | — | Versão de version number. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `effective_from` | `date` | Sim | — | Atributo funcional de effective from. |
| `effective_to` | `date` | Sim | — | Atributo funcional de effective to. |
| `compatibility_rule` | `varchar(20)` | Não | `NONE` | Atributo funcional de compatibility rule. |
| `change_summary` | `text` | Sim | — | Atributo funcional de change summary. |
| `payload` | `json` | Não | — | Conteúdo adicional em JSON. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_resources`

**Finalidade:** Máquinas, equipamentos, ferramentas e linhas.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `plant_id` | `bigint unsigned` | Não | — | Referência a `plants.id`. |
| `work_center_id` | `bigint unsigned` | Não | — | Referência a `work_centers.id`. |
| `code` | `varchar(80)` | Não | — | Código funcional. |
| `name` | `varchar(150)` | Não | — | Nome. |
| `resource_type` | `varchar(30)` | Não | — | Tipo ou classificação de resource type. |
| `status` | `varchar(20)` | Não | `ACTIVE` | Estado atual no workflow. |
| `capacity_per_day` | `decimal(10,2)` | Sim | — | Atributo funcional de capacity per day. |
| `efficiency_factor` | `decimal(7,3)` | Sim | — | Atributo funcional de efficiency factor. |
| `effective_from` | `date` | Sim | — | Atributo funcional de effective from. |
| `effective_to` | `date` | Sim | — | Atributo funcional de effective to. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `products`

**Finalidade:** Cadastro mestre de produtos.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `sku` | `varchar(80)` | Não | — | Atributo funcional de sku. |
| `description` | `varchar(255)` | Não | — | Descrição funcional. |
| `product_type` | `varchar(20)` | Não | — | Tipo ou classificação de product type. |
| `safety_stock` | `int unsigned` | Não | `0` | Atributo funcional de safety stock. |
| `lead_time_days` | `int unsigned` | Não | `0` | Atributo funcional de lead time days. |
| `lot_control` | `tinyint(1)` | Não | `0` | Atributo funcional de lot control. |
| `serial_control` | `tinyint(1)` | Não | `0` | Atributo funcional de serial control. |
| `is_active` | `tinyint(1)` | Não | `1` | Indica se está ativo. |
| `lifecycle_status` | `varchar(20)` | Não | `ACTIVE` | Atributo funcional de lifecycle status. |
| `technical_attributes` | `json` | Sim | — | Dados estruturados de technical attributes em JSON. |
| `commercial_attributes` | `json` | Sim | — | Dados estruturados de commercial attributes em JSON. |
| `fiscal_attributes` | `json` | Sim | — | Dados estruturados de fiscal attributes em JSON. |
| `alternate_uoms` | `json` | Sim | — | Dados estruturados de alternate uoms em JSON. |
| `image_urls` | `json` | Sim | — | Dados estruturados de image urls em JSON. |
| `attachment_urls` | `json` | Sim | — | Dados estruturados de attachment urls em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |
| `unit_id` | `bigint unsigned` | Sim | — | Identificador relacionado a unit. |
| `category_id` | `bigint unsigned` | Sim | — | Identificador relacionado a category. |
| `brand_id` | `bigint unsigned` | Sim | — | Identificador relacionado a brand. |

### `routing_operation_snapshots`

**Finalidade:** Cópia histórica e imutável de routing operation.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `routing_version_snapshot_id` | `bigint unsigned` | Não | — | Referência a `routing_version_snapshots.id`. |
| `routing_version_id` | `bigint unsigned` | Não | — | Referência a `routing_versions.id`. |
| `standard_time_id` | `bigint unsigned` | Sim | — | Identificador relacionado a standard time. |
| `standard_time_version` | `int unsigned` | Sim | — | Versão de standard time version. |
| `work_center_id` | `bigint unsigned` | Não | — | Referência a `work_centers.id`. |
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

### `routing_operation_standard_times`

**Finalidade:** Versões dos tempos padrão de uma operação.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `routing_operation_id` | `bigint unsigned` | Não | — | Referência a `routing_operations.id`. |
| `version_number` | `int unsigned` | Não | — | Versão de version number. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `time_basis` | `varchar(20)` | Não | `PER_PROCESS` | Atributo funcional de time basis. |
| `setup_scope` | `varchar(20)` | Não | `ROUTING` | Atributo funcional de setup scope. |
| `base_quantity` | `decimal(18,6)` | Não | `1.000000` | Quantidade de base quantity. |
| `setup_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de setup time. |
| `runtime_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de runtime. |
| `queue_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de queue time. |
| `move_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de move time. |
| `efficiency_factor` | `decimal(7,3)` | Não | `100.000` | Atributo funcional de efficiency factor. |
| `yield_factor` | `decimal(7,4)` | Não | `100.0000` | Atributo funcional de yield factor. |
| `effective_from` | `date` | Sim | — | Atributo funcional de effective from. |
| `effective_to` | `date` | Sim | — | Atributo funcional de effective to. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `change_reason` | `text` | Sim | — | Atributo funcional de change reason. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `routing_operations`

**Finalidade:** Operações de uma versão de roteiro.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `routing_version_id` | `bigint unsigned` | Não | — | Referência a `routing_versions.id`. |
| `work_center_id` | `bigint unsigned` | Não | — | Referência a `work_centers.id`. |
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

### `routing_version_snapshots`

**Finalidade:** Cópia histórica e imutável de routing version.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `routing_version_id` | `bigint unsigned` | Não | — | Referência a `routing_versions.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `version_number` | `int unsigned` | Não | — | Versão de version number. |
| `status` | `varchar(20)` | Não | — | Estado atual no workflow. |
| `effective_from` | `date` | Não | — | Atributo funcional de effective from. |
| `effective_to` | `date` | Sim | — | Atributo funcional de effective to. |
| `description` | `varchar(255)` | Sim | — | Descrição funcional. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `frozen_at` | `timestamp` | Não | — | Data e hora de frozen. |
| `snapshot_hash` | `varchar(64)` | Não | — | Atributo funcional de snapshot hash. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `routing_versions`

**Finalidade:** Versões de roteiro por produto.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `version_number` | `int unsigned` | Não | — | Versão de version number. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `effective_from` | `date` | Sim | — | Atributo funcional de effective from. |
| `effective_to` | `date` | Sim | — | Atributo funcional de effective to. |
| `description` | `varchar(255)` | Sim | — | Descrição funcional. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `work_center_hour_rates`

**Finalidade:** Registros funcionais de work center hour rates.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `work_center_id` | `bigint unsigned` | Não | — | Referência a `work_centers.id`. |
| `hourly_rate` | `decimal(18,6)` | Não | — | Valor monetário ou taxa de hourly rate. |
| `currency` | `varchar(3)` | Não | `BRL` | Atributo funcional de currency. |
| `effective_from` | `date` | Não | — | Atributo funcional de effective from. |
| `effective_to` | `date` | Sim | — | Atributo funcional de effective to. |
| `status` | `varchar(20)` | Não | `ACTIVE` | Estado atual no workflow. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `change_reason` | `text` | Sim | — | Atributo funcional de change reason. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `work_center_shifts`

**Finalidade:** Registros funcionais de work center shifts.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `work_center_id` | `bigint unsigned` | Não | — | Referência a `work_centers.id`. |
| `name` | `varchar(80)` | Não | — | Nome. |
| `shift_start` | `time` | Não | — | Atributo funcional de shift start. |
| `shift_end` | `time` | Não | — | Atributo funcional de shift end. |
| `capacity_hours` | `decimal(5,2)` | Não | — | Atributo funcional de capacity hours. |
| `is_active` | `tinyint(1)` | Não | `1` | Indica se está ativo. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `work_centers`

**Finalidade:** Centros de trabalho produtivos.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `plant_id` | `bigint unsigned` | Não | — | Referência a `plants.id`. |
| `code` | `varchar(50)` | Não | — | Código funcional. |
| `name` | `varchar(150)` | Não | — | Nome. |
| `resource_type` | `varchar(20)` | Não | — | Tipo ou classificação de resource type. |
| `capacity_per_day` | `decimal(10,2)` | Não | — | Atributo funcional de capacity per day. |
| `efficiency_factor` | `decimal(5,2)` | Não | `100.00` | Atributo funcional de efficiency factor. |
| `is_active` | `tinyint(1)` | Não | `1` | Indica se está ativo. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

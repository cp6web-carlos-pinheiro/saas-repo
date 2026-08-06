# Compras

## Objetivo

Administrar fornecedores e o fluxo desde a requisição até o recebimento, integrado ao MRP e ao estoque.

## Fornecedores

- CRUD web e API, com pessoa física/jurídica, identificação fiscal, contato e status.
- Regras por produto e fornecedor, incluindo prioridade, lead time, lote mínimo e múltiplo de compra.

## Requisições

- Requisições com linhas, produto, quantidade, data necessária e justificativa.
- Estados `DRAFT`, `APPROVED` e `CANCELLED`.
- Geração manual, geração pela API a partir do MRP e conversão em pedidos de compra.

## Cotações

- Registro de cotação e itens por fornecedor.
- Estados `DRAFT`, `RECEIVED`, `APPROVED` e `REJECTED`.
- Valores monetários persistidos em centavos e vínculo opcional à requisição.

## Pedidos de compra

- Pedidos e linhas vinculados a fornecedor, produto, requisição e regra de suprimento.
- Estados `DRAFT`, `APPROVED` e `CANCELLED`.
- Conversão de requisições aprovadas, agrupando itens conforme o fornecedor selecionado.

## Recebimentos

- Recebimentos e linhas vinculados ao pedido, fornecedor e armazém.
- Estados `DRAFT`, `POSTED` e `CANCELLED`.
- Postagem integrada ao estoque, incluindo lote/serial quando exigido pelo produto.
- Reversão controlada do recebimento por movimentos compensatórios.

## Entidades principais

- `suppliers`, `supplier_products`.
- `purchase_requisitions` e `purchase_requisition_lines`.
- `purchase_quotations` e respectivas linhas.
- `purchase_orders` e `purchase_order_lines`.
- `purchase_receipts` e respectivas linhas.

## Dicionário de dados

As tabelas abaixo documentam o schema corrente e, quando indicado, estruturas removidas preservadas como histórico. “Nula” informa se a coluna aceita `NULL`; “—” indica ausência de valor padrão explícito.

### `purchase_order_lines`

**Finalidade:** Itens detalhados de purchase order.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `purchase_order_id` | `bigint unsigned` | Não | — | Referência a `purchase_orders.id`. |
| `purchase_requisition_line_id` | `bigint unsigned` | Sim | — | Referência a `purchase_requisition_lines.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `warehouse_id` | `bigint unsigned` | Sim | — | Referência a `warehouses.id`. |
| `quantity_ordered` | `decimal(14,6)` | Não | `0.000000` | Quantidade de ordered. |
| `quantity_received` | `decimal(14,6)` | Não | `0.000000` | Quantidade de received. |
| `unit_price` | `decimal(14,6)` | Sim | — | Valor monetário ou taxa de unit price. |
| `need_by_date` | `date` | Sim | — | Data de need by. |
| `promised_date` | `date` | Sim | — | Data de promised. |
| `status` | `varchar(20)` | Não | `OPEN` | Estado atual no workflow. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `purchase_orders`

**Finalidade:** Pedidos enviados aos fornecedores.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `purchase_order_number` | `varchar(60)` | Não | — | Número funcional de purchase order number. |
| `supplier_id` | `bigint unsigned` | Não | — | Referência a `suppliers.id`. |
| `purchase_requisition_id` | `bigint unsigned` | Sim | — | Referência a `purchase_requisitions.id`. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `order_date` | `date` | Não | — | Data de order. |
| `expected_delivery_date` | `date` | Sim | — | Data de expected delivery. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `cancelled_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `cancelled_at` | `timestamp` | Sim | — | Data e hora de cancelled. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `purchase_quotation_lines`

**Finalidade:** Itens detalhados de purchase quotation.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `purchase_quotation_id` | `bigint unsigned` | Não | — | Referência a `purchase_quotations.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `purchase_requisition_line_id` | `bigint unsigned` | Sim | — | Referência a `purchase_requisition_lines.id`. |
| `quantity` | `decimal(14,6)` | Não | `0.000000` | Quantidade associada. |
| `unit_price` | `decimal(14,6)` | Sim | — | Valor monetário ou taxa de unit price. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `purchase_quotations`

**Finalidade:** Cotações de compra.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `quotation_number` | `varchar(60)` | Não | — | Número funcional de quotation number. |
| `purchase_requisition_id` | `bigint unsigned` | Sim | — | Referência a `purchase_requisitions.id`. |
| `supplier_id` | `bigint unsigned` | Sim | — | Referência a `suppliers.id`. |
| `quotation_date` | `date` | Não | — | Data de quotation. |
| `valid_until` | `date` | Sim | — | Atributo funcional de valid until. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `received_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `received_at` | `timestamp` | Sim | — | Data e hora de received. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `rejected_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `rejected_at` | `timestamp` | Sim | — | Data e hora de rejected. |
| `amount_cents` | `int` | Não | `0` | Valor monetário ou taxa de amount cents. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `purchase_receipt_lines`

**Finalidade:** Itens detalhados de purchase receipt.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `purchase_receipt_id` | `bigint unsigned` | Não | — | Referência a `purchase_receipts.id`. |
| `purchase_order_line_id` | `bigint unsigned` | Sim | — | Referência a `purchase_order_lines.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `warehouse_id` | `bigint unsigned` | Não | — | Referência a `warehouses.id`. |
| `quantity_received` | `decimal(14,6)` | Não | `0.000000` | Quantidade de received. |
| `lot_number` | `varchar(80)` | Sim | — | Número funcional de lot number. |
| `stock_ledger_movement_id` | `bigint unsigned` | Sim | — | Identificador relacionado a stock ledger movement. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `purchase_receipts`

**Finalidade:** Recebimentos e postagem no estoque.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `receipt_number` | `varchar(60)` | Não | — | Número funcional de receipt number. |
| `purchase_order_id` | `bigint unsigned` | Sim | — | Referência a `purchase_orders.id`. |
| `supplier_id` | `bigint unsigned` | Sim | — | Referência a `suppliers.id`. |
| `receipt_date` | `date` | Não | — | Data de receipt. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `posted_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `posted_at` | `timestamp` | Sim | — | Data e hora de posted. |
| `cancelled_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `cancelled_at` | `timestamp` | Sim | — | Data e hora de cancelled. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `purchase_requisition_lines`

**Finalidade:** Itens detalhados de purchase requisition.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `purchase_requisition_id` | `bigint unsigned` | Não | — | Referência a `purchase_requisitions.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `warehouse_id` | `bigint unsigned` | Sim | — | Referência a `warehouses.id`. |
| `supplier_id` | `bigint unsigned` | Sim | — | Referência a `suppliers.id`. |
| `suggested_quantity` | `decimal(14,6)` | Não | `0.000000` | Quantidade de suggested quantity. |
| `requested_quantity` | `decimal(14,6)` | Não | `0.000000` | Quantidade de requested quantity. |
| `moq_applied` | `decimal(14,6)` | Não | `1.000000` | Atributo funcional de moq applied. |
| `lead_time_days` | `int unsigned` | Não | `0` | Atributo funcional de lead time days. |
| `need_by_date` | `date` | Não | — | Data de need by. |
| `order_date` | `date` | Não | — | Data de order. |
| `status` | `varchar(20)` | Não | `OPEN` | Estado atual no workflow. |
| `source_requirement_key` | `varchar(180)` | Sim | — | Chave funcional de source requirement key. |
| `mrp_reference_date` | `date` | Sim | — | Data de mrp reference. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `purchase_requisitions`

**Finalidade:** Requisições internas de compra.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `requisition_number` | `varchar(60)` | Não | — | Número funcional de requisition number. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `required_date` | `date` | Sim | — | Data de required. |
| `source_type` | `varchar(80)` | Sim | — | Tipo ou classificação de source type. |
| `source_reference_id` | `bigint unsigned` | Sim | — | Identificador relacionado a source reference. |
| `source_reference_type` | `varchar(120)` | Sim | — | Tipo ou classificação de source reference type. |
| `requested_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `cancelled_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `cancelled_at` | `timestamp` | Sim | — | Data e hora de cancelled. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `supplier_products`

**Finalidade:** Regras por fornecedor e produto.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `supplier_id` | `bigint unsigned` | Não | — | Referência a `suppliers.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `supplier_sku` | `varchar(80)` | Sim | — | Atributo funcional de supplier sku. |
| `moq` | `decimal(14,6)` | Não | `1.000000` | Atributo funcional de moq. |
| `lead_time_days` | `int unsigned` | Não | `0` | Atributo funcional de lead time days. |
| `unit_price` | `decimal(14,6)` | Sim | — | Valor monetário ou taxa de unit price. |
| `is_preferred` | `tinyint(1)` | Não | `0` | Indicador booleano de is preferred. |
| `is_active` | `tinyint(1)` | Não | `1` | Indica se está ativo. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `suppliers`

**Finalidade:** Cadastro de fornecedores.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `code` | `varchar(50)` | Não | — | Código funcional. |
| `name` | `varchar(180)` | Não | — | Nome. |
| `person_type` | `varchar(2)` | Não | `PJ` | Tipo ou classificação de person type. |
| `email` | `varchar(180)` | Sim | — | Endereço de e-mail. |
| `phone` | `varchar(50)` | Sim | — | Atributo funcional de phone. |
| `status` | `varchar(20)` | Não | `ACTIVE` | Estado atual no workflow. |
| `default_lead_time_days` | `int unsigned` | Não | `0` | Atributo funcional de default lead time days. |
| `payment_terms` | `varchar(80)` | Sim | — | Atributo funcional de payment terms. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

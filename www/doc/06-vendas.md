# Vendas

## Objetivo

Administrar clientes, pedidos de venda e seu avanço comercial e operacional.

## Clientes

- CRUD web por empresa.
- Pessoa física ou jurídica, documentos, contatos, endereço e status `ACTIVE` ou `INACTIVE`.
- Pesquisa, filtros e ordenação para uso no pedido.

## Pedidos de venda

- Cabeçalho com cliente, data, status, valor total e observações.
- Linhas com produto, quantidade, preço unitário e total.
- Valores monetários armazenados em centavos.
- Estados comerciais `DRAFT`, `CONFIRMED` e `CANCELLED`.
- Bloqueio de alterações incompatíveis depois que o fluxo operacional avançou.

## Fluxo operacional

- Evolução controlada pelos estados de separação, faturamento, expedição e entrega.
- Registro do usuário e data de cada transição.
- Validação de pré-requisitos entre as etapas; não é permitido saltar uma etapa obrigatória.
- Auditoria das transições de status.

## Integrações atuais

- Produtos são selecionados do cadastro da empresa.
- Ao confirmar o pedido, o sistema tenta reservar o saldo disponível dos produtos acabados.
- Para a quantidade não atendida pelo estoque, produtos acabados ou em processo com BOM ativa podem gerar ordens de produção, incluindo as necessidades produtivas da estrutura explodida.
- As OPs originadas da venda conservam o vínculo por `source_reference_type = sale` e `source_reference_id = sales.id`.
- A referência comercial é exibida como `#<número da venda>` nas listas, detalhes e programação das OPs, facilitando o acompanhamento por pedido.
- O cancelamento da venda libera as reservas ainda abertas vinculadas ao pedido.
- O fluxo não realiza automaticamente baixa de estoque, emissão fiscal ou faturamento financeiro.

## Entidades principais

- `customers`, `sales` e `sale_lines`.

## Dicionário de dados

As tabelas abaixo documentam o schema corrente do domínio. “Nula” informa se a coluna aceita `NULL`; “—” indica ausência de valor padrão explícito.

### `customers`

**Finalidade:** Cadastro de clientes.

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
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `sale_lines`

**Finalidade:** Itens detalhados de sale.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `sale_id` | `bigint unsigned` | Não | — | Referência a `sales.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `quantity` | `decimal(14,6)` | Não | `0.000000` | Quantidade associada. |
| `unit_price` | `decimal(14,6)` | Não | `0.000000` | Valor monetário ou taxa de unit price. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `sales`

**Finalidade:** Pedidos de venda e seus workflows.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `customer_id` | `bigint unsigned` | Sim | — | Referência a `customers.id`. |
| `sale_date` | `date` | Não | — | Data de sale. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `operational_status` | `varchar(20)` | Não | `PENDING` | Atributo funcional de operational status. |
| `subtotal_cents` | `bigint unsigned` | Não | `0` | Atributo funcional de subtotal cents. |
| `discount_cents` | `bigint unsigned` | Não | `0` | Atributo funcional de discount cents. |
| `amount_cents` | `bigint unsigned` | Não | `0` | Valor monetário ou taxa de amount cents. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |
| `picking_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `picking_at` | `timestamp` | Sim | — | Data e hora de picking. |
| `invoiced_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `invoiced_at` | `timestamp` | Sim | — | Data e hora de invoiced. |
| `shipped_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `shipped_at` | `timestamp` | Sim | — | Data e hora de shipped. |
| `delivered_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `delivered_at` | `timestamp` | Sim | — | Data e hora de delivered. |
| `confirmed_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `confirmed_at` | `timestamp` | Sim | — | Data e hora de confirmed. |
| `canceled_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `canceled_at` | `timestamp` | Sim | — | Data e hora de canceled. |
| `cancel_reason` | `text` | Sim | — | Atributo funcional de cancel reason. |

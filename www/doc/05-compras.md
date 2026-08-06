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

Consulte as [tabelas de Compras](11-dicionario-de-dados.md#compras).

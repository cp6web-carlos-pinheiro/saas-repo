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
- O pedido comercial não executa automaticamente reserva, baixa de estoque, emissão fiscal ou faturamento financeiro.

## Entidades principais

- `customers`, `sales` e `sale_lines`.

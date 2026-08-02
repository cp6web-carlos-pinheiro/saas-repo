# Modulo Compras

Este modulo controla solicitacoes de compra, pedidos e relacionamento com fornecedores para suprimento de materiais.

## Ultima atualizacao
- 2026-08-02

## Status objetivo
- Status atual: Parcial.
- Cobertura atual: cadastro de fornecedores no fluxo tenant (CRUD web) e base de tabelas de compras definida.
- Pendencia principal: implementar solicitacao de compra, pedido, recebimento e integracao completa com estoque/financeiro.

## Tabelas relacionadas

### Mestres

- `suppliers`
- `supplier_products`

### Transacionais

- `purchase_requisitions`
- `purchase_requisition_lines`
- `purchase_orders`
- `purchase_order_lines`

### Relacionamento

- Nenhuma tabela de relacionamento especifica neste modulo.

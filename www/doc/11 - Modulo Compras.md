# Modulo Compras

Este modulo controla solicitacoes de compra, pedidos e relacionamento com fornecedores para suprimento de materiais.

## Ultima atualizacao
- 2026-08-04

## Status objetivo
- Status atual: Avancado.
- Cobertura atual: CRUD web tenant de fornecedores, solicitacoes, cotacoes, pedidos e recebimentos; itens/linhas em solicitacao, cotacao e pedido; transicoes operacionais com auditoria; bloqueios de edicao/exclusao apos POSTED; estorno com categoria e motivo obrigatorios.
- Integracoes ativas: recebimento com movimentacao de estoque e reversao com rastreabilidade operacional.
- Pendencia principal: evoluir dashboards operacionais e politicas avancadas de aprovacao/SLA para processos de compras.

## Entregas implementadas
- Fornecedores: CRUD completo no tenant com validacoes operacionais.
- Solicitacoes de compra: CRUD completo com linhas de itens.
- Cotacoes: CRUD completo com linhas de itens.
- Pedidos: CRUD completo com linhas de itens.
- Recebimentos: CRUD completo com integração de estoque e estorno auditavel.
- UI: lookup dinamico via AJAX para selecoes de compras e padronizacao visual de formularios de itens.
- Navegacao: Fornecedores publicados como subitem do menu Compras.

## Pendencias relevantes
- Workflow de aprovacao em multiplos niveis para pedidos e cotacoes.
- SLAs operacionais por etapa (lead time interno e externo).
- Relatorios gerenciais de compras (aberto x recebido x pendente).
- Regras adicionais de governanca operacional e rastreabilidade por processo.

## Tabelas relacionadas

### Mestres

- `suppliers`
- `supplier_products`

### Transacionais

- `purchase_requisitions`
- `purchase_requisition_lines`
- `purchase_quotations`
- `purchase_quotation_lines`
- `purchase_orders`
- `purchase_order_lines`
- `purchase_receipts`
- `purchase_receipt_lines`

### Relacionamento

- Nenhuma tabela de relacionamento especifica neste modulo.

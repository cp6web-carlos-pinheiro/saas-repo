# Modulo Compras

Este modulo controla solicitacoes de compra, pedidos e relacionamento com fornecedores para suprimento de materiais.

## Ultima atualizacao
- 2026-08-03

## Status objetivo
- Status atual: Avancado.
- Cobertura atual: CRUD web tenant de fornecedores, solicitacoes, cotacoes, pedidos, recebimentos e entradas fiscais; itens/linhas em solicitacao, cotacao e pedido; transicoes operacionais com auditoria; bloqueios de edicao/exclusao apos POSTED; estorno com categoria e motivo obrigatorios.
- Integracoes ativas: recebimento com movimentacao de estoque e entrada fiscal com ponte de posting (`purchase_fiscal_entry_postings`), incluindo reversao com rastreabilidade operacional.
- Pendencia principal: evoluir conciliacao financeiro-fiscal completa, dashboards operacionais e politicas avancadas de aprovacao/SLA para processos de compras.

## Entregas implementadas
- Fornecedores: CRUD completo no tenant com validacao de pessoa/tax id.
- Solicitacoes de compra: CRUD completo com linhas de itens.
- Cotacoes: CRUD completo com linhas de itens.
- Pedidos: CRUD completo com linhas de itens.
- Recebimentos: CRUD completo com integração de estoque e estorno auditavel.
- Entradas fiscais: CRUD completo com posting e estorno auditavel.
- UI: lookup dinamico via AJAX para selecoes de compras e padronizacao visual de formularios de itens.
- Navegacao: Fornecedores publicados como subitem do menu Compras.

## Pendencias relevantes
- Workflow de aprovacao em multiplos niveis para pedidos e cotacoes.
- SLAs operacionais por etapa (lead time interno e externo).
- Relatorios gerenciais de compras (aberto x recebido x fiscalizado).
- Integracao financeira completa para contas a pagar/liquidacao.

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
- `purchase_fiscal_entries`
- `purchase_fiscal_entry_postings`

### Relacionamento

- Nenhuma tabela de relacionamento especifica neste modulo.

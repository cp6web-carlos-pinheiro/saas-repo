# Modulo Estoque

Este modulo administra saldos, movimentacoes, lotes, seriais e alocacoes de estoque para suportar operacao e planejamento.

## Ultima atualizacao
- 2026-08-04

## Status objetivo
- Status atual: Parcial avancado.
- Cobertura atual: estrutura de saldos/movimentos/lotes/seriais ativa, CRUD web tenant de Armazens e Plantas implementado, integracao de recebimento de compras com movimentacao de estoque, fluxo operacional composto de transferencia/reversao no ledger, e reservas por origem/prioridade com liberacao automatica por expiracao.
- Pendencia principal: concluir inventario ciclico, valorizacao e consultas analiticas de saldo/disponibilidade.

## Entregas implementadas
- Estrutura de ledger operacional (`stock_ledger_movements` e `stock_ledger_allocations`).
- Saldos, lotes e seriais com base persistente para rastreabilidade.
- CRUD de Armazens no menu Estoque.
- CRUD de Plantas no menu Estoque.
- Integracao com Compras para entradas de recebimento e estornos controlados.
- Transferencia operacional entre armazens com reversao de movimentos no ledger.
- Reservas por origem/prioridade com liberacao automatica de reservas expiradas.

## Pendencias relevantes
- Inventario fisico com contagem cega e reconciliacao de divergencias.
- Camada de valorizacao de estoque (custo medio/FIFO conforme politica).

## Tabelas relacionadas

### Mestres

- `plants`
- `warehouses`
- `inventory_balances`
- `inventory_lots`
- `inventory_serials`

### Transacionais

- `stock_ledger_movements`
- `stock_ledger_allocations`

### Relacionamento

- Nenhuma tabela de relacionamento especifica neste modulo.

# Modulo Estoque

Este modulo administra saldos, movimentacoes, lotes, seriais e alocacoes de estoque para suportar operacao e planejamento.

## Ultima atualizacao
- 2026-08-02

## Status objetivo
- Status atual: Parcial inicial.
- Cobertura atual: estrutura de dados de saldos e movimentos definida no dominio.
- Pendencia principal: implementar entradas/saidas, transferencias, inventario, reservas e consultas operacionais de saldo.

## Tabelas relacionadas

### Mestres

- `inventory_balances`
- `inventory_lots`
- `inventory_serials`

### Transacionais

- `stock_ledger_movements`
- `stock_ledger_allocations`

### Relacionamento

- Nenhuma tabela de relacionamento especifica neste modulo.

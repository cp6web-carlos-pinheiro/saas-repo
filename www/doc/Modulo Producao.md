# Modulo Producao

Este modulo gerencia ordens de producao, saidas e registros operacionais para controle da execucao produtiva.

## Ultima atualizacao
- 2026-08-02

## Status objetivo
- Status atual: Planejado.
- Cobertura atual: dominio e tabelas de ordens/consumos/saidas definidos.
- Pendencia principal: implementar ciclo funcional de ordem de producao (abertura, apontamento, consumo, fechamento).

## Tabelas relacionadas

### Mestres

- Nenhuma tabela mestra dedicada neste modulo.

### Transacionais

- `production_orders`
- `production_order_outputs`
- `production_order_material_consumptions`
- `production_order_snapshots`
- `production_order_routing_operation_snapshots`

### Relacionamento

- Nenhuma tabela de relacionamento especifica neste modulo.

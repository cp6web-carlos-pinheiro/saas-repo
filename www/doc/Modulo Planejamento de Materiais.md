# Modulo Planejamento de Materiais

Este modulo calcula necessidades de materiais e gera sinais de reposicao e producao com base em demanda, estoque e estrutura de produto.

## Ultima atualizacao
- 2026-08-04

## Status objetivo
- Status atual: Parcial avancado.
- Cobertura atual: engine de calculo, sugestoes de compra/producao, alertas de estoque minimo e priorizacao basica por demanda/estoque ativos.
- Pendencia principal: implementar forecast, reposicao completa e scheduler finito com simulacao de cenarios.

## Tabelas relacionadas

### Mestres

- `products`
- `product_versions`
- `bom_headers`
- `bom_items`
- `inventory_balances`

### Transacionais

- `purchase_requisitions`
- `purchase_requisition_lines`
- `production_orders`

### Relacionamento

- Nenhuma tabela de relacionamento especifica neste modulo.

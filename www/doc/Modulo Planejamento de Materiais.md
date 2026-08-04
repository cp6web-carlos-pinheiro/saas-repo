# Modulo Planejamento de Materiais

Este modulo calcula necessidades de materiais e gera sinais de reposicao e producao com base em demanda, estoque e estrutura de produto.

## Ultima atualizacao
- 2026-08-04

## Status objetivo
- Status atual: Parcial avancado.
- Cobertura atual: engine de calculo, sugestoes de compra/producao, alertas de estoque minimo, entrada de previsao como demanda, scheduler finito simples por capacidade e priorizacao basica por demanda/estoque ativos.
- Pendencia principal: implementar forecast estruturado, reposicao completa e evoluir o scheduler com simulacao de cenarios.

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

# Modulo Planejamento de Materiais

Este modulo calcula necessidades de materiais e gera sinais de reposicao e producao com base em demanda, estoque e estrutura de produto.

## Ultima atualizacao
- 2026-08-02

## Status objetivo
- Status atual: Planejado.
- Cobertura atual: dependencias de dados do MRP identificadas (produto, versao, BOM, estoque e ordens).
- Pendencia principal: implementar engine de calculo, sugestoes e priorizacao de necessidades de compra/producao.

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

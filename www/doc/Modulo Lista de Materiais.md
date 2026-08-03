# Modulo Revisões

Este modulo gerencia a estrutura de produtos, componentes e quantidades que formam cada item fabricado.

## Ultima atualizacao
- 2026-08-02

## Status objetivo
- Status atual: Parcial avancado.
- Cobertura atual: CRUD tenant de revisoes (bom_headers + bom_items) e visao de estruturas agrupadas por produto.
- Pendencia principal: completar regras de engenharia (versionamento avancado, efetividade e governanca de alteracoes).

## Tabelas relacionadas

### Mestres

- `bom_headers`
- `bom_items`

### Transacionais

- `production_order_bom_snapshots`
- `production_order_bom_item_snapshots`

### Relacionamento

- Nenhuma tabela de relacionamento especifica neste modulo.

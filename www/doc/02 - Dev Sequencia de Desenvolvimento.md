# Infra 01 - Sequencia de Desenvolvimento

Sequencia consolidada para o Beyond MRP no escopo ativo, priorizando dependencias tecnicas e entrega incremental de valor.

## Ultima atualizacao
- 2026-08-04

## Status consolidado
- Fases 1 a 4: avancado.
- Fases 5 a 8: avancado.
- Fases 9 a 12: parcial avancado.
- Fases 13 em diante: planejado ou parcial inicial.

## Sequencia vigente

| Fase | Dominio | Status | Dependencia principal |
| --- | --- | --- | --- |
| 1 | Fundacao de Arquitetura | Avancado | - |
| 2 | Multitenancy | Avancado | Fundacao |
| 3 | Identidade e Seguranca | Avancado | Multitenancy |
| 4 | RBAC e Acesso | Avancado | Identidade |
| 5 | Administracao Tenant | Avancado | RBAC |
| 6 | Cadastros Gerais | Avancado | Administracao |
| 7 | Produto e Engenharia (BOM/Revisoes) | Avancado | Cadastros |
| 8 | Compras | Avancado | Produto + Estoque |
| 9 | Vendas | Parcial avancado | Produto + Estoque |
| 10 | Estoque | Parcial avancado | Compras + Vendas + Produto |
| 11 | Producao | Parcial avancado | BOM + Roteiros + Estoque |
| 12 | MRP e Programacao | Parcial avancado | Producao + Estoque |
| 13 | Observabilidade | Parcial inicial | Todos os fluxos web |
| 14 | Dashboards e Relatorios Operacionais | Planejado | Camada de metricas |
| 15 | APIs e Integracoes | Planejado | Dominios estabilizados |

## Destaques recentes
- Compras com fluxo operacional completo no tenant, incluindo estorno auditavel.
- Estoque com transferencias e reversoes integradas ao ledger.
- Produto/Revisoes com regras de vigencia e integridade de engenharia.
- Vendas com fluxo principal de pedidos e transicoes operacionais.
- Baseline de CI, seguranca (MFA) e telemetria implementados.

## Proximos marcos
1. Consolidar inventario formal e valorizacao em Estoque.
2. Aprofundar governanca e indicadores operacionais em Compras/Vendas/Producao.
3. Evoluir MRP com maior granularidade de capacidade e cenarios.

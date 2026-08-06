# ANA-001 - Fundação analítica de manufatura

## Objetivo

Criar uma camada de consultas/fatos consistente para análise de OP, operação, tempo, quantidade, consumo, qualidade, recurso e operador.

## Status da implementação

Implementada a fundação analítica live sobre os fatos transacionais de PCP/MES.

- `ManufacturingAnalyticsService` define o contrato `ANA-001.v1` e normaliza fatos de operação, quantidade, tempo, recurso, centro e operador.
- Filtros suportados: período, produto, OP, centro, recurso e operador.
- O tenant é aplicado pelo `TenantModel` nas consultas.
- O fato mantém IDs da OP/operação e versões de tempo padrão, permitindo drill-down transacional.
- Endpoints: `/api/v1/analytics/manufacturing/overview` e relatórios equivalentes.

## Problema anterior

A tela de analytics consulta diretamente ordens e outputs e calcula indicadores simples em memória. Não existe contrato analítico comum, dimensão temporal consolidada, snapshot de parâmetros ou estratégia para grandes volumes.

## Escopo

- Definir fatos de operação, produção, consumo, qualidade e parada.
- Definir dimensões: empresa, planta, centro, recurso, operador, produto, OP, operação, lote, data e turno.
- Padronizar timezone, período, unidade, status e regras de exclusão/cancelamento.
- Registrar versões de routing, BOM e tempos padrão usadas no fato.
- Criar queries/services de indicadores com filtros por período, planta, produto, OP, centro, recurso e operador.
- Planejar índices, agregações e cache.
- Definir política de dados corrigidos e eventos anulados.

## Entregas realizadas

- Contrato de métricas documentado.
- Query/service analítico reutilizável; cache e tabelas de agregação ficam como evolução para volume maior.
- Serviço de filtros e contrato de resposta comum.
- Testes de reconciliação com dados transacionais.

## Critérios de aceite

- [x] Indicadores usam fatos e definições comuns de quantidade/tempo.
- [x] OP e operação são rastreáveis pelos IDs transacionais.
- [x] Filtros passam pelas consultas tenant-aware.
- [ ] Reconciliação automatizada completa com ledger e agregações de grande volume ainda precisa de testes de integração/carga.

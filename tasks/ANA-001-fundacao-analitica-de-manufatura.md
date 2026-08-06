# ANA-001 - Fundação analítica de manufatura

## Objetivo

Criar uma camada de consultas/fatos consistente para análise de OP, operação, tempo, quantidade, consumo, qualidade, recurso e operador.

## Problema atual

A tela de analytics consulta diretamente ordens e outputs e calcula indicadores simples em memória. Não existe contrato analítico comum, dimensão temporal consolidada, snapshot de parâmetros ou estratégia para grandes volumes.

## Escopo

- Definir fatos de operação, produção, consumo, qualidade e parada.
- Definir dimensões: empresa, planta, centro, recurso, operador, produto, OP, operação, lote, data e turno.
- Padronizar timezone, período, unidade, status e regras de exclusão/cancelamento.
- Registrar versões de routing, BOM e tempos padrão usadas no fato.
- Criar queries/services de indicadores com filtros por período, planta, produto, OP, centro, recurso e operador.
- Planejar índices, agregações e cache.
- Definir política de dados corrigidos e eventos anulados.

## Entregas

- Contrato de métricas documentado.
- Views/queries ou tabelas analíticas conforme volume.
- Serviço de filtros e paginação.
- Testes de reconciliação com dados transacionais.

## Critérios de aceite

- Todos os indicadores usam as mesmas definições de quantidade e tempo.
- Uma OP pode ser rastreada do fato até os registros transacionais.
- Filtros não permitem vazamento entre tenants.
- Totais analíticos reconciliam com OP, outputs e ledger.

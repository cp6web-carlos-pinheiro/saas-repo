# ANA-004 - Histórico analítico e revisão dos tempos padrão

## Objetivo

Usar tempos realizados e resultados de produção para apoiar a revisão dos tempos padrão pela Engenharia de Processos.

## Status da implementação

Implementada a evidência estatística e o workflow de recomendação separado do cadastro aprovado.

- Média, mediana, P90, mínimo, máximo, tamanho de amostra e outliers (>30% da média).
- Tamanho mínimo de amostra configurável, padrão 5.
- Recomendações são persistidas em `manufacturing_analytics_recommendations`.
- Decisões aceitas: `ACCEPTED`, `REJECTED`, `INVESTIGATE` e `ECO_REQUIRED`.
- Nenhuma recomendação altera automaticamente o tempo padrão; aplicação deve seguir ENG-002/ENG-003.
- Endpoints de evidência, recomendação e decisão estão em `/api/v1/analytics/manufacturing/standard-times`.

## Escopo

- Comparar tempo padrão, previsto ajustado e real por operação.
- Calcular média, mediana, percentis, desvio, amostra e outliers.
- Separar setup, processo, pausa e parada.
- Filtrar por produto, versão, recurso, centro, operador, lote e período.
- Exigir tamanho mínimo de amostra antes de sugerir alteração.
- Sugerir novo tempo sem alterar automaticamente o cadastro aprovado.
- Registrar decisão da Engenharia: aceitar, rejeitar, investigar ou criar ECO.
- Manter histórico da recomendação e da versão efetivamente aplicada.

## Regras

- Apontamentos corrigidos, retrabalho e paradas classificadas não devem ser misturados sem regra explícita.
- Outliers devem ser identificados e não removidos silenciosamente.
- A recomendação precisa mostrar a origem dos dados.
- Mudança aprovada deve passar por ENG-003 e ENG-002.

## Critérios de aceite

- [x] Engenharia recebe evidência estatística e origem por operação.
- [x] Recomendação é distinta da alteração efetiva.
- [x] Decisão e motivo ficam persistidos.
- [x] OP histórica mantém `standard_time_id/version` no fato.
- [ ] Aceite automático criando nova versão via ECO ainda não foi conectado.

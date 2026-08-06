# ANA-002 - Indicadores previsto x real e eficiências operacionais

## Objetivo

Implementar os indicadores de desempenho solicitados por operação, operador, máquina/recurso e centro de trabalho.

## Pré-requisitos

- ENG-001 e ENG-002.
- PCP-001.
- MES-001, MES-002 e MES-003.
- ANA-001.

## Indicadores

- Tempo previsto x tempo real por operação.
- Quantidade planejada x boa x refugada x retrabalhada.
- Eficiência por operação: tempo padrão ajustado / tempo produtivo real.
- Eficiência por operador, como evolução opcional, caso o usuário/apontador seja identificado no MES.
- Eficiência por máquina/recurso.
- Utilização e eficiência por centro de trabalho.
- Aderência ao plano por OP, produto, centro e período.

## Regras a definir

- Se pausas planejadas entram no denominador.
R: Sim, as pausas planejadas entram no denominador mas devem estar evidenciadas.
- Como dividir tempo e quantidade entre múltiplos operadores, caso essa dimensão seja habilitada no futuro.
R: Utilizar o tempo médio e quantidade media caso essa dimensão seja habilitada no futuro
- Como tratar operações sem tempo padrão.
R: Tratar o tempo realizado como sendo o tempo padrão
- Como tratar apontamentos corrigidos ou cancelados.
R: Utilizar a ultima informação cadastrada.
- Se eficiência pode superar 100% e como exibir outliers.
R: A eficiencia não pode superar 100%.

## Status da implementação

Implementados os indicadores `ANA-002.v1` no `ManufacturingAnalyticsService`.

- Previsto x real por operação, recurso, centro e operador.
- Quantidades planejada, processada, boa, refugada e retrabalhada.
- Eficiência limitada a 100%; sem tempo padrão, o realizado é usado como padrão.
- Pausas ficam expostas separadamente e entram no denominador conforme a definição fornecida.
- Dados atuais da operação representam a última informação consolidada; eventos permanecem disponíveis para drill-down.
- Endpoints: `/api/v1/analytics/manufacturing/efficiency` e relatório `planned-vs-real`.

## Entregas

- Services/queries de métricas.
- API analítica com filtros, tabelas e exportação CSV.
- Drill-down do indicador até a operação/eventos.
- Testes com cenários de apontamento parcial, refugo, pausa e troca de recurso.

## Critérios de aceite

- [x] Fórmula e políticas de pausa/tempo ausente são retornadas no contrato.
- [x] Agrupamento por operação, recurso, centro e operador está disponível.
- [x] Resultado mostra quantidades e tempos usados.
- [x] Operadores não identificados aparecem como `UNIDENTIFIED`.
- [ ] Tela web e exportação XLSX/PDF continuam pendentes.

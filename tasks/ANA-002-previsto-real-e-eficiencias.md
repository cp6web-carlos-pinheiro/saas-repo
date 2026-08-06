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

## Entregas

- Services/queries de métricas.
- Tela analítica com filtros, tabelas e exportação futura.
- Drill-down do indicador até a operação/eventos.
- Testes com cenários de apontamento parcial, refugo, pausa e troca de recurso.

## Critérios de aceite

- Cada indicador tem fórmula documentada.
- O usuário consegue filtrar e agrupar por operação, recurso e centro; o filtro por operador só aparece quando houver identificação suficiente nos apontamentos.
- O resultado mostra quantidade e tempo usados no cálculo.
- Dados sem padrão ou com inconsistência são sinalizados, não silenciosamente descartados.

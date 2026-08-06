# ANA-003 - OEE, disponibilidade, performance e qualidade

## Objetivo

Implementar OEE como evolução da análise, usando eventos de execução, calendário, paradas, produção e qualidade.

## Fórmulas a homologar

- Disponibilidade = tempo de operação / tempo planejado de produção.
- Performance = produção real ajustada pelo tempo padrão / tempo de operação.
- Qualidade = quantidade boa / quantidade total processada.
- OEE = disponibilidade × performance × qualidade.

Os nomes podem variar, mas a fórmula oficial e os denominadores devem ser aprovados por Operações/Engenharia.

## Escopo

- Classificar tempo planejado, setup, produção, pausa planejada, parada não planejada e manutenção.
- Relacionar causas de parada a operação, recurso, centro e turno.
- Calcular OEE por recurso, centro, operação, produto, turno e período.
- Exibir perdas por disponibilidade, performance e qualidade.
- Permitir drill-down para eventos e apontamentos.
- Sinalizar períodos incompletos ou sem tempo padrão.

## Status da implementação

Implementado OEE inicial `ANA-003.v1` por recurso, a partir dos tempos reais MES e apontamentos de qualidade.

- Disponibilidade usa produtivo/(produtivo+pausa).
- Performance usa previsto/produtivo, limitada a 100%.
- Qualidade usa boa/processada, com refugo e retrabalho no total processado.
- OEE é calculado somente quando há dados mínimos; períodos incompletos retornam warning.
- Endpoint: `/api/v1/analytics/manufacturing/oee` e relatório `oee`.

Paradas com causa e duração ficam disponíveis nos eventos MES; classificação planejada/não planejada e turno ainda não possuem dimensão analítica própria.

## Dependências

ENG-001, ENG-002, MES-001, MES-002, MES-003, MES-004 e ANA-001.

## Critérios de aceite

- [x] OEE é reproduzível a partir dos fatos consolidados e eventos MES.
- [x] Pausas/paradas têm duração calculada por eventos; causa é preservada.
- [x] Qualidade usa a regra comum de boa/processada.
- [x] Dados insuficientes geram `PERIOD_WITHOUT_MINIMUM_DATA`.
- [ ] Calendário/turno, parada planejada/não planejada e OEE oficial por centro/produto são próximos incrementos.

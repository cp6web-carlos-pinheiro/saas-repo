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

## Dependências

ENG-001, ENG-002, MES-001, MES-002, MES-003, MES-004 e ANA-001.

## Critérios de aceite

- OEE de um período é reproduzível a partir dos eventos armazenados.
- Paradas têm causa e duração.
- Qualidade usa a mesma regra de boa/refugo/retrabalho do MES.
- O sistema não apresenta OEE oficial quando os dados mínimos não existem sem sinalização.

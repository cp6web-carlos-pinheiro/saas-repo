# Fase 07 - Producao

## Objetivo
Implementar o nucleo de execucao da manufatura (coracao do MRP).

## Status de implementacao
Parcial avancado no nucleo de OP e apontamento simples; ainda nao e um MES completo. Ordem de producao, consumo de materiais, saida boa/refugo, inspecao basica, roteiros, centros de trabalho, calendario e scheduler estao ligados no codigo. Nao existe ainda execucao transacional por operacao com inicio/fim, cronometro, pausas, operador, maquina/recurso ou retrabalho.

## Escopo
- Ordem de Producao: implementado
- BOM: implementado no planejamento e congelada na OP; consumo real x previsto ainda nao e calculado na tela de execucao
- Roteiro: implementado
- Centros de Trabalho: implementado
- Operacoes: implementado
- Consumo: implementado com baixa no ledger, lote opcional, operador opcional e referencia ao componente da BOM snapshot
- Apontamentos: parcial; registra saidas por operacao/centro e tempos informados manualmente, mas nao possui apontamento de inicio/fim da operacao
- Producao: implementado
- Refugo: parcial; quantidade e inspeção existem, mas causa, destino, retrabalho e aprovação formal nao existem
- Finalizacao: implementado

## Criterios para 100% implementado
- Ordem de producao completa: abertura, planejamento fino, execucao, pausas, encerramento e reabertura controlada.
- Apontamento completo: tempo de setup/processo, consumo real, producao boa, refugo e retrabalho por operacao.
- Controles em processo: checkpoints por etapa com bloqueio de avancos fora de conformidade operacional.
- Roteiro e capacidade completos: sequenciamento por centro de trabalho com disponibilidade real de recursos.
- Integracao completa com estoque: baixa de materia-prima, entrada de produto acabado e rastreio por lote/serial.
- Indicadores de manufatura: OEE, aderencia ao plano, eficiencia por operacao e causas de parada/refugo.
- MES completo: operacao executada, cronometro, pausas, operador, recurso/maquina, motivo de parada, retrabalho e trilha de eventos.
- Governanca operacional: segregacao de funcoes, aprovacao para desvios e trilha completa de auditoria.
- Qualidade: testes de regras de execucao, concorrencia operacional e consistencia de consumo/apontamento.

## Pendencias por dependencia e area

### Por dependencia
- Dependencia de processos: adicionar controles formais de inspecao em processo e tratamento de refugo.
- Dependencia de capacidade: integrar disponibilidade real de equipe e centros de trabalho.
- Dependencia de analytics: consolidar indicadores de OEE, paradas e causas de perdas.
- Dependencia de modelo MES: criar operacoes da OP, eventos de tempo/pausa, operadores/recursos e motivos de perda.

### Por area
- Area de Producao: ampliar padrao de apontamento e governanca de desvios.
- Area de Engenharia Industrial: consolidar regras de roteiro/capacidade.
- Area de Engenharia de Software: implementar controles e telemetria de execucao.

## Mapeamento solicitado - MES

| Capacidade | Situacao atual | Proximo desenvolvimento |
| --- | --- | --- |
| Inicio e termino das operacoes | Nao implementado; ha `started_at`/`completed_at` na OP, nao na operacao | Criar `production_order_operations` e eventos de inicio/fim |
| Cronometro | Nao implementado | Calcular tempo por eventos persistidos, com retomada segura |
| Pausas | Nao implementado | Criar pausas com motivo, usuario, inicio/fim e aprovacao quando necessario |
| Apontamento de producao | Parcial; `production_order_outputs` registra quantidade boa/refugo | Vincular apontamento a operacao, operador, recurso e lote |
| Consumo real de materiais | Implementado; gera movimento `ISSUE` e registro por produto/lote | Validar automaticamente contra a BOM snapshot e permitir estorno controlado |
| Refugo e retrabalho | Refugo quantitativo e inspeção basica implementados | Causas, retrabalho como fluxo/ordem, destino e aprovação |

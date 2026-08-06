# Fase 08 - Planejamento MRP

## Objetivo
Implementar o diferencial do sistema: planejamento e sincronizacao de demanda e capacidade.

## Status de implementacao
Parcial avancado. O motor de MRP, recalculo, sugestoes de compra/producao, alertas de estoque minimo, entradas de previsao como demanda, lead time e um scheduler finito simples por capacidade de centro de trabalho existem. A geracao da OP via MRP e a explosao da BOM estao disponiveis, mas o fluxo de PCP ainda nao fecha capacidade, tempos previstos e programacao persistida em um plano operacional executavel.

## Escopo
- Planejamento: parcial avancado
- Necessidade de Compra: parcial
- Necessidade de Producao: implementado como sugestao e endpoint de criacao de OP MRP
- Previsao: parcial avancado
- Sugestoes: parcial
- Reposicao: parcial
- Estoque Minimo: implementado
- Lead Time: parcial
- Capacidade: parcial
- MRP Engine: implementado
- Scheduler: parcial avancado; calcula cenarios em memoria/cache, nao grava uma programacao versionada

## Criterios para 100% implementado
- Planejamento integrado completo: demanda, estoque, capacidade e restricoes operacionais em um ciclo unico.
- Previsao de demanda completa: modelos de forecast, ajuste manual controlado e historico de acuracia.
- Politica de reposicao completa: ponto de pedido, estoque de seguranca, cobertura e lotes economicos por item.
- Sugestoes acionaveis: propostas de compra/producao com aprovacao e conversao automatizada em documentos.
- Scheduler completo: planejamento finito por recurso, janela de producao e simulacao de cenarios, com resultado persistido/versionado e aplicavel a OPs.
- Tempos previstos completos: derivar setup, processo, fila e movimentacao por quantidade/lote e gravar o previsto por operacao da OP.
- Governanca de parametros: lead time, rendimento, perdas e capacidade com historico de alteracao e vigencia.
- Monitoramento de performance: aderencia do plano, excecoes de ruptura e alertas proativos.
- Qualidade: testes de calculo do motor, consistencia de recomendacoes e desempenho para grande volume.

## Pendencias por dependencia e area

### Por dependencia
- Dependencia de dados: garantir qualidade de parametros (lead time, estoque minimo, capacidade, rendimento).
- Dependencia de processos: concluir forecast estruturado e evoluir o scheduler finito com simulacao de cenarios.
- Dependencia de compras/producao: habilitar conversao automatica das sugestoes aprovadas em documentos operacionais.
- Dependencia de Engenharia: definir como `runtime_minutes` varia por quantidade, lote, recurso e eficiencia; hoje o scheduler soma os tempos do roteiro sem multiplicar pela quantidade da OP.

### Por area
- Area de PCP: definir politicas de planejamento, excecoes e criterios de prioridade.
- Area de Dados/BI: suportar acuracia de forecast e analise de aderencia.
- Area de Engenharia: otimizar performance do motor e regras de recomendacao.

## Mapeamento solicitado - PCP

| Capacidade | Situacao atual | Proximo desenvolvimento |
| --- | --- | --- |
| Geracao da OP | Implementado manual e via sugestao MRP; ao criar/consolidar a OP congela BOM e, na liberacao, o routing | Converter sugestoes aprovadas com governanca, idempotencia e rastreabilidade de origem |
| Explosao de materiais | Implementado com versao efetiva, recursividade e detecção de ciclo; tambem usada no MRP | Expor necessidade planejada por OP e reconciliar com consumo real |
| Calculo automatico dos tempos previstos | Parcial no scheduler: soma tempos do routing | Calcular por quantidade e gravar previsto por operacao, centro e recurso |
| Verificacao de capacidade | Parcial: calendario, turnos, capacidade diaria e fator de eficiencia | Considerar disponibilidade real, conflitos, setup, feriados/excecoes e capacidade por recurso |
| Programacao da producao | Parcial: forward/backward, regras de sequenciamento e modos finito/infinito | Persistir programa, permitir aplicar/reprogramar OPs e controlar versoes/cenarios |

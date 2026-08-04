# Fase 08 - Planejamento MRP

## Objetivo
Implementar o diferencial do sistema: planejamento e sincronizacao de demanda e capacidade.

## Status de implementacao
Parcial avancado. O motor de MRP, recalculo, sugestoes de compra/producao, alertas de estoque minimo, entradas de previsao como demanda e um scheduler finito simples por capacidade de centro de trabalho existem, mas um fluxo de planejamento operacional completo ainda nao esta fechado.

## Escopo
- Planejamento: parcial
- Necessidade de Compra: parcial
- Necessidade de Producao: parcial
- Previsao: parcial avancado
- Sugestoes: parcial
- Reposicao: parcial
- Estoque Minimo: implementado
- Lead Time: parcial
- Capacidade: parcial
- MRP Engine: implementado
- Scheduler: parcial avancado

## Criterios para 100% implementado
- Planejamento integrado completo: demanda, estoque, capacidade e restricoes operacionais em um ciclo unico.
- Previsao de demanda completa: modelos de forecast, ajuste manual controlado e historico de acuracia.
- Politica de reposicao completa: ponto de pedido, estoque de seguranca, cobertura e lotes economicos por item.
- Sugestoes acionaveis: propostas de compra/producao com aprovacao e conversao automatizada em documentos.
- Scheduler completo: planejamento finito por recurso, janela de producao e simulacao de cenarios.
- Governanca de parametros: lead time, rendimento, perdas e capacidade com historico de alteracao e vigencia.
- Monitoramento de performance: aderencia do plano, excecoes de ruptura e alertas proativos.
- Qualidade: testes de calculo do motor, consistencia de recomendacoes e desempenho para grande volume.

## Pendencias por dependencia e area

### Por dependencia
- Dependencia de dados: garantir qualidade de parametros (lead time, estoque minimo, capacidade, rendimento).
- Dependencia de processos: concluir forecast estruturado e evoluir o scheduler finito com simulacao de cenarios.
- Dependencia de compras/producao: habilitar conversao automatica das sugestoes aprovadas em documentos operacionais.

### Por area
- Area de PCP: definir politicas de planejamento, excecoes e criterios de prioridade.
- Area de Dados/BI: suportar acuracia de forecast e analise de aderencia.
- Area de Engenharia: otimizar performance do motor e regras de recomendacao.


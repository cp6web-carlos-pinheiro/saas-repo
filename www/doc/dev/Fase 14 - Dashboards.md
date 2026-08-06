# Fase 14 - Dashboards

## Objetivo
Fornecer visao executiva e operacional para tomada de decisao.

## Status de implementacao
Parcial. Existe uma tela de indicadores de producao com periodo, aderencia ao plano, qualidade, tempos de setup/processo, status de OP, inspeções, refugo por dia e produtividade agregada por operação. Ainda não há uma camada analítica consolidada nem os recortes por operador, máquina e centro de trabalho solicitados.

## Escopo
- KPIs: nao iniciado
- Graficos: parcial
- Indicadores: parcial, concentrados em producao
- Widgets: nao iniciado

## Criterios para 100% implementado
- KPIs completos por dominio: compras, vendas, estoque, producao, MRP e observabilidade.
- Análise de manufatura completa: previsto x real, eficiência por operação/operador/máquina/centro, consumo previsto x real e histórico de tempos padrão.
- Graficos operacionais completos: series temporais, comparativos, alertas e filtros por periodo/planta.
- Indicadores executivos completos: metas, desvios, tendencias e drill-down por processo.
- Widgets completos: painel configuravel por perfil de usuario com preferencia persistida.
- Performance completa: cache de consultas, tempo de carga controlado e atualizacao incremental.
- Qualidade: validacao dos calculos de indicadores e testes de consistencia dos paines.

## Pendencias por dependencia e area

### Por dependencia
- Dependencia de dados: consolidar camadas metricas de compras, vendas, estoque, producao e MRP.
- Dependencia de UX: definir padrao de widgets e configuracao por perfil.
- Dependencia de performance: cache e estrategia de atualizacao incremental para grandes volumes.

### Por area
- Area Executiva: priorizar KPIs estrategicos e metas de acompanhamento.
- Area de BI/Dados: definir regras de calculo e fonte unica de indicadores.
- Area de Engenharia Front/Back: implementar paines configuraveis com boa performance.

## Mapeamento solicitado - Análise

| Indicador | Situacao atual | Falta |
| --- | --- | --- |
| Previsto x Real | Parcial: quantidade planejada x produzida e setup/processo informado | Tempos previstos por operação e comparação temporal confiável |
| Eficiência por operação | Parcial: produtividade agregada por `operation_no` | Comparar com tempo padrão e separar setup/processo/paradas |
| Eficiência por operador | Não implementado; operador só existe no consumo de material | Registrar operador no apontamento de execução e agregar métricas |
| Eficiência por máquina | Não implementado; centro de trabalho pode ser informado, máquina não | Modelar recurso/máquina e disponibilidade/eventos |
| Eficiência por centro de trabalho | Parcial: centro aparece no output, sem indicador dedicado | Agregar tempo, quantidade, perdas e capacidade por centro |
| Consumo real x previsto | Parcial: consumo real é registrado e resumido por produto/lote | Confrontar com a BOM snapshot e sinalizar desvios |
| OEE | Não implementado | Disponibilidade, performance e qualidade com eventos de parada e tempo planejado |
| Histórico dos tempos padrão | Não implementado | Versionar tempos, vincular versão usada na OP e alimentar revisão estatística |

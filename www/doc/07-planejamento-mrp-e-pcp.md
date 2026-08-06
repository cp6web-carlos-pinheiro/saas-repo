# Planejamento, MRP e PCP

## Objetivo

Calcular necessidades de materiais, governar sugestões de suprimento e programar ordens conforme calendário e capacidade produtiva.

## Planejamento MRP

- Recebe demanda, estoque, recebimentos programados e parâmetros de planejamento.
- Explode a BOM efetiva recursivamente e calcula necessidade bruta e líquida.
- Considera estoque de segurança, lead time, lote mínimo e múltiplos de compra quando disponíveis.
- Gera sugestões `PURCHASE` ou `PRODUCTION` com data de necessidade e liberação.
- Suporta planejamento completo e recálculo incremental/idempotente.
- Persiste execuções em `mrp_plan_runs` e o resumo de resultados.

## Workflow de sugestões

- Sugestões persistidas com chave rastreável e payload original.
- Consulta por tipo e status.
- Aprovação com ajuste de quantidade e justificativa, ou rejeição fundamentada.
- Conversão de sugestão aprovada de produção em ordem de produção.
- Conversão de sugestão aprovada de compra em requisição de compra.
- Eventos registram as transições e a sugestão conserva o documento gerado.

## Programação da produção

- Seleção de ordens liberadas ou em execução.
- Programação forward ou backward.
- Modos finito e infinito e regras de sequenciamento por prioridade/data.
- Uso de centros, turnos, calendário, capacidade e recursos produtivos.
- Cálculo de janelas previstas por operação, separando tempo produtivo e lead time.
- Persistência de programas e linhas, com número, versão, parâmetros e origem.
- Publicação, cancelamento e comparação entre versões de programa.

## Calendário e capacidade

- Calendário por centro de trabalho e dia.
- Geração em lote, dias úteis, exceções, turnos e minutos disponíveis.
- Recursos indisponíveis ou incompatíveis são excluídos da programação finita.

## Entidades principais

- `mrp_plan_runs`, `mrp_suggestions`, `mrp_suggestion_events`.
- `production_schedules`, `production_schedule_lines`.
- `production_calendar_days`, `work_centers`, `work_center_shifts` e `production_resources`.

## Limitações atuais

- Forecast estatístico e simulação avançada de cenários não formam um módulo funcional próprio.
- A qualidade do plano depende de BOM, lead times, estoques, calendários e tempos padrão mantidos corretamente.

# PCP-001 - Tempos previstos por operação da Ordem de Produção

## Objetivo

Materializar na OP os tempos previstos de cada operação, em vez de manter apenas tempos no routing e uma soma temporária no scheduler.

## Status da implementação

Implementado no backend/API em 2026-08-06.

- Migration `2026_08_06_000001_create_pcp_operations_schedules_and_mrp_workflow_tables.php` cria `production_order_operations`.
- `ProductionOrderOperationPlanningService` materializa uma linha por operação do snapshot congelado.
- O release da OP congela o snapshot e materializa as operações automaticamente.
- Endpoints: `GET /api/v1/production-orders/{id}/operations` e `POST /api/v1/production-orders/{id}/operations/materialize`.
- A OP detalhada passou a carregar a relação `operations`.

## Escopo funcional

1. Criar operações planejadas da OP a partir do routing snapshot.
2. Registrar sequência, centro, recurso elegível, tempo padrão de origem e tempo previsto calculado.
3. Separar setup, processo, fila e movimentação.
4. Registrar a quantidade da OP, a base de tempo e os metadados do cálculo.
5. Registrar datas/horas previstas e duração total.
6. Manter o cálculo congelado para OP liberada.
7. Permitir materialização idempotente e recálculo forçado somente para OP não encerrada.

## Modelo sugerido

`production_order_operations`: OP, snapshot de routing/operação, sequência, status, tempos padrão, tempos previstos, quantidade prevista, centro, recurso selecionado/elegível, início/fim planejados e metadata de cálculo.

## Regras implementadas

- A soma das partes deve fechar com a duração prevista.
- O cálculo usa o tempo padrão referenciado pelo snapshot; se a referência não existir, usa os valores congelados no snapshot.
- `PER_PROCESS` não multiplica runtime; `PER_UNIT` multiplica pelo quantity planned. `PER_BATCH` está aceito no cadastro e permanece com runtime informado pelo processo até a definição de tamanho de lote.
- Setup com escopo `ROUTING` ocorre somente na primeira operação; setup `OPERATION` ocorre em cada operação.
- Eficiência não altera o tempo padrão previsto, conforme decisão da ENG-002.
- Fila e movimentação são lead time e não consomem capacidade.
- Minutos calculados são arredondados para intervalos de 5 minutos; quantidades fracionárias permanecem sem arredondamento adicional.
- Operações terceirizadas ficam com tempos zerados e status `OUTSOURCED`, não entrando na capacidade interna.
- O cálculo registra no metadata a base, a exclusão da eficiência e a separação entre capacidade e lead time.

## Critérios de aceite atendidos / pendentes

- [x] Criar/liberar uma OP gera uma linha por operação do routing snapshot.
- [x] Runtime `PER_UNIT`, setup único, lead time, terceirização e arredondamento foram implementados.
- [x] API da OP expõe as operações materializadas.
- [ ] Datas planejadas por operação serão preenchidas pela publicação do PCP-003.
- [ ] Testes de integração dependem de banco de teste disponível; a execução atual está bloqueada por credencial MySQL.
- [ ] Histórico de cada recálculo ainda precisa de uma tabela de versões de cálculo.

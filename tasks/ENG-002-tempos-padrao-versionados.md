# ENG-002 - Tempos padrão versionados por operação

## Status de implementação

Implementado no backend/API e integrado ao snapshot de routing e da OP. A gestão possui versões DRAFT/APPROVED/OBSOLETE, vigência, base de tempo, eficiência e rendimento. A primeira versão não inclui telas web dedicadas.

## Decisões de negócio homologadas

- `runtime_minutes` será informado pelo usuário para cada processo. O sistema não inferirá automaticamente se o valor é por unidade, lote ou total da OP; ele representa o tempo padrão definido para aquele processo.
- Haverá apenas um setup para o conjunto de operações/segmentos da execução. O setup não será repetido em cada segmento ou operação.
- A eficiência do centro e do recurso não altera o tempo previsto. O padrão representa o funcionamento esperado da empresa; eficiência será analisada separadamente.
- Minutos calculados serão arredondados em intervalos de 5 minutos. Quantidades fracionárias não serão arredondadas.
- Fila e movimentação serão tratadas somente como lead time e não consumirão capacidade produtiva.
- Operações terceirizadas não serão computadas nos tempos internos; serão tratadas como produtos acabados/itens recebidos do processo externo.

## Objetivo

Transformar os tempos atualmente existentes no routing em um cadastro de tempos padrão controlado, versionado e utilizável por PCP, MES e Análise.

## Contexto atual

`routing_operations` possui `setup_time_minutes`, `runtime_minutes`, `queue_time_minutes` e `move_time_minutes`. Esses valores são congelados no snapshot do routing, porém não há histórico específico de revisão, base de quantidade/lote, faixa de aplicação, rendimento ou comparação com tempos realizados.

## Escopo funcional

Implementar:

1. Tempo de setup único para a execução do roteiro/OP.
2. Tempo de processo informado pelo usuário para cada processo.
3. Tempo de fila e movimentação como lead time.
4. Regra de arredondamento em blocos de 5 minutos, sem arredondamento de quantidade.
5. Rendimento e perdas planejadas quando aplicável; eficiência não altera o padrão.
6. Vigência, versão, status rascunho/aprovado/obsoleto e usuário aprovador.
7. Histórico de alterações e motivo da alteração.
8. Seleção determinística do tempo efetivo na criação/congelamento da OP.

## Regras de cálculo homologadas

- O usuário informa o runtime padrão de cada processo.
- O setup é único para o roteiro/OP e deve ser somado somente uma vez, mesmo que a execução seja dividida em operações ou segmentos.
- Eficiência do centro e do recurso não é multiplicador do tempo padrão.
- O arredondamento de minutos calculados usa blocos de 5 minutos; a quantidade original permanece com sua precisão fracionária.
- Fila e movimentação compõem lead time e não capacidade produtiva.
- Operações terceirizadas são excluídas do cálculo interno.

## Modelo de dados

Foi criada a entidade `routing_operation_standard_times`, com operação, versão, status, base de tempo, tempos, eficiência de referência, rendimento, vigência, aprovação e motivo. A versão atual aceita os modos legados `PER_UNIT`/`PER_BATCH` para compatibilidade, mas o comportamento homologado para novos processos é runtime informado explicitamente pelo usuário.

## Integrações

- Routing: aprovação deve congelar a versão efetiva.
- PCP: fornecer os tempos previstos, separando tempo de capacidade e lead time.
- MES: copiar o tempo padrão usado para a operação da OP.
- Análise: permitir comparar padrão, previsto e realizado sem usar eficiência como multiplicador.
- ECO: alterações relevantes podem ser associadas a uma mudança de engenharia.

## Implementação realizada

- Migration `2026_08_05_000001_create_production_resources_hour_rates_and_standard_times_tables.php` com `routing_operation_standard_times`.
- Model `RoutingOperationStandardTime`.
- `RoutingStandardTimeService` para criar, editar DRAFT, aprovar, obsoletar, consultar versão efetiva e calcular tempo por quantidade.
- `RoutingStandardTimeController` e requests na API REST.
- Ao aprovar um routing, o snapshot passa a usar o tempo padrão aprovado vigente; quando não há tempo padrão cadastrado, mantém fallback compatível para os campos legados da operação.
- `routing_operation_snapshots` e `production_order_routing_operation_snapshots` guardam `standard_time_id` e `standard_time_version`.
- O congelamento da OP passou a copiar as operações do routing snapshot aprovado, evitando reler o routing corrente.
- O cálculo deve aplicar as decisões homologadas: eficiência não é multiplicador, setup não é repetido por segmento, fila/movimentação não consomem capacidade e terceirização não é computada.

Endpoints principais:

- `GET|POST /api/v1/routing-operations/{routingOperationId}/standard-times`
- `PUT /api/v1/routing-standard-times/{id}`
- `POST /api/v1/routing-standard-times/{id}/approve`
- `POST /api/v1/routing-standard-times/{id}/obsolete`
- `GET /api/v1/routing-operations/{routingOperationId}/standard-times/effective`
- `POST /api/v1/routing-operations/{routingOperationId}/standard-times/calculate`

## Critérios de aceite

- Uma operação pode ter mais de uma versão sem sobreposição de vigência.
- A seleção do tempo efetivo para uma OP é reproduzível e fica registrada.
- Alterar o cadastro atual não muda tempos de OPs já congeladas.
- O sistema calcula explicitamente o tempo previsto para uma quantidade informada.
- O setup aparece uma única vez para o conjunto de operações/segmentos da OP.
- Eficiência de centro/recurso não altera o tempo previsto.
- Fila/movimentação são retornadas como lead time sem consumo de capacidade.
- Operação terceirizada não gera tempo interno calculado.
- Minutos são arredondados em blocos de 5 e quantidades fracionárias não são arredondadas.
- Há testes para vigência, quantidade, arredondamento, terceirização e aprovação.

## Validação realizada

- PHP lint e rotas API: aprovados.
- Teste feature criado para versão por unidade, aprovação e cálculo.
- Execução bloqueada antes das asserções pela indisponibilidade/autorização do banco de testes `mrp_test`.

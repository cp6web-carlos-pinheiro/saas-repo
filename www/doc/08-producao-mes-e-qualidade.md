# Produção, MES e qualidade

## Objetivo

Administrar a ordem de produção desde sua criação até a execução das operações, consumo, qualidade, retrabalho e encerramento.

## Ordem de produção

- Criação manual ou originada do MRP.
- Estados `DRAFT`, `RELEASED`, `IN_PROGRESS`, `PARTIALLY_COMPLETED`, `COMPLETED` e `CANCELLED`.
- Produto, armazém, quantidade planejada, datas e origem.
- Congelamento de BOM, roteiro e versões usadas, preservando a base histórica.
- Liberação, apontamentos parciais e conclusão.
- Ordens concluídas ou canceladas não aceitam novos apontamentos nem consumos; a interface oculta esses formulários quando concluída.

## Operações executáveis

- Materialização das operações do roteiro na OP.
- Sequência, centro, recurso planejado, quantidade, tempos previstos e referência ao snapshot.
- Planejamento de início e fim por operação.
- Estados próprios para acompanhamento da execução.

## Execução MES

- Início, pausa, retomada, parada, conclusão e cancelamento da operação.
- Eventos idempotentes com data de ocorrência, operador, recurso, motivo e metadados.
- Consolidação de tempo produtivo e tempo de pausa no servidor.
- Vínculo do recurso real e do operador que executou a operação.
- Validação de conflito para recurso já ocupado por outra operação em andamento.

## Apontamentos e qualidade

- Quantidade processada, boa, refugada e retrabalhada por operação.
- Saídas com lote, destino, recurso, operador e status de inspeção.
- Checkpoints `PENDING`, `APPROVED` e `REJECTED` na interface web.
- Registros de qualidade e não conformidade com causa, quantidade, destino e observações.
- Criação de ordem de retrabalho ligada à operação de origem e encerramento rastreável.

## Consumo de materiais

- Consumo contra os componentes da BOM congelada e consumo adicional explicitamente autorizado.
- Produto, armazém, quantidade, lote, operador e operação da OP.
- Baixa integrada ao estoque por movimento `ISSUE`.
- Chave de idempotência para evitar duplicidade.
- Estorno controlado por movimento inverso e vínculo entre consumo e movimentos de ledger.

## Entidades principais

- `production_orders`, `production_order_snapshots` e snapshots de BOM/roteiro.
- `production_order_operations`, `production_operation_events` e `production_operation_outputs`.
- `production_order_outputs`, `production_order_material_consumptions` e reversões.
- `production_quality_records` e `production_rework_orders`.

## Dicionário de dados

Consulte as [tabelas de Produção, MES e qualidade](11-dicionario-de-dados.md#producao-mes-e-qualidade).

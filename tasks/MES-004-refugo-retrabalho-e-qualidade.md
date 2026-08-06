# MES-004 - Refugo, retrabalho e qualidade por operação

## Objetivo

Evoluir o refugo quantitativo e a inspeção simples para um fluxo rastreável de perdas, não conformidades e retrabalho.

## Status da implementação

Implementado o registro operacional de qualidade e retrabalho.

- `production_operation_outputs` separa quantidade boa, refugo e retrabalho por operação.
- `production_quality_records` registra status, causa, destino, lote, recurso, operador opcional e observações.
- `production_rework_orders` vincula operação de origem à operação de retrabalho, com estado aberto/concluído.
- Excesso de apontamento é bloqueado por padrão; exceção exige flag explícita.

## Escopo funcional

- Cadastro de causas de refugo, parada e não conformidade.
- Registro de quantidade, unidade, operação, lote, operador, recurso, causa, destino e observação.
- Estados de qualidade: pendente, aprovado, rejeitado, segregado e liberado para retrabalho.
- Criar vínculo de retrabalho com operação de origem, operação executora, quantidade e motivo.
- Permitir retorno à operação anterior ou rota específica de retrabalho.
- Evitar dupla contagem de refugo em output e retrabalho.
- Integrar entrada de produto bom, estoque segregado e descarte conforme política.

## Regras

- Refugo não pode exceder quantidade processada sem autorização de exceção.
- Retrabalho deve manter genealogia e não alterar silenciosamente o apontamento original.
- Alterar inspeção após encerramento exige permissão e auditoria.
- Lote/serial deve ser obrigatório quando exigido pelo produto.

## Critérios de aceite

- [x] É possível registrar refugo com causa e destino por operação.
- [x] É possível abrir e concluir um registro de retrabalho.
- [x] A operação mantém quantidades boas, refugadas e de retrabalho separadas.
- [ ] Integração automática com estoque segregado, descarte e genealogia ainda precisa ser finalizada.
- [ ] Cadastro mestre de causas e inspeção com aprovação formal são próximos incrementos.

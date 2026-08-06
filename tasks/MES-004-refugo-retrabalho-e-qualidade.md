# MES-004 - Refugo, retrabalho e qualidade por operação

## Objetivo

Evoluir o refugo quantitativo e a inspeção simples para um fluxo rastreável de perdas, não conformidades e retrabalho.

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

- É possível registrar refugo com causa e destino.
- É possível encaminhar quantidade para retrabalho e encerrá-la posteriormente.
- A OP mostra quantidade boa, refugo, retrabalho e saldo em processo sem dupla contagem.
- A genealogia identifica origem, operação e resultado final.

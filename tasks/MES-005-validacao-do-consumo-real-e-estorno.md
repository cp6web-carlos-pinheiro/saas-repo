# MES-005 - Validação do consumo real contra BOM e estorno controlado

## Objetivo

Garantir que o consumo real seja relacionado à BOM congelada, que desvios sejam identificados e que correções ocorram por estorno auditável.

## Contexto atual

`MaterialConsumptionService` registra consumo e publica movimento `ISSUE`, com lote opcional e referência textual ao componente. Ainda não há reconciliação automática completa com a quantidade prevista da BOM nem fluxo explícito de estorno.

## Escopo

- Identificar componente da BOM snapshot por chave estável.
- Calcular previsto por componente e operação.
- Validar produto, unidade, lote, armazém e quantidade.
- Permitir consumo antecipado ou excedente somente com regra/permissão.
- Registrar motivo e aprovação para desvios.
- Criar estorno que gere movimento inverso e mantenha o consumo original.
- Integrar consumo a operação executável e lote/serial.
- Exibir saldo previsto, consumido, sucateado e restante.

## Regras

- Nunca apagar consumo já postado no ledger.
- Estorno deve ser idempotente e referenciar o movimento original.
- Não permitir consumo de item fora da BOM sem exceção registrada.
- Conversão de unidade deve ser explícita e testada.

## Critérios de aceite

- O sistema apresenta consumo real x previsto por componente.
- Excesso gera bloqueio ou exceção conforme política.
- Estorno reconcilia estoque e não remove histórico.
- Lotes/seriais permanecem rastreáveis após consumo e estorno.

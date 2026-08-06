# MES-005 - Validação do consumo real contra BOM e estorno controlado

## Objetivo

Garantir que o consumo real seja relacionado à BOM congelada, que desvios sejam identificados e que correções ocorram por estorno auditável.

## Status da implementação

Implementada a validação básica e o estorno controlado.

- O consumo exige que o produto exista na BOM congelada da OP.
- O componente pode ser identificado pelo item da BOM e o consumo pode ser vinculado à operação executável.
- O acumulado consumido é comparado com `quantity_required`; excesso é bloqueado por padrão e exige `allow_excess`.
- `idempotency_key` evita duplicação de consumo.
- O estorno chama `InventoryService::reverseMovement`, cria registro próprio e mantém o consumo original.
- O resumo desconsidera consumos estornados no total líquido.

## Contexto anterior

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

- [x] O backend valida o componente contra a BOM snapshot e guarda o vínculo operacional.
- [x] Excesso gera bloqueio ou exceção explícita.
- [x] Estorno reconcilia o estoque por movimento inverso e preserva histórico.
- [x] Lote permanece no consumo e no movimento de estoque.
- [ ] Endpoint dedicado de consumo previsto x real por componente, conversão de unidade e validação obrigatória de serial ainda precisam ser desenvolvidos.

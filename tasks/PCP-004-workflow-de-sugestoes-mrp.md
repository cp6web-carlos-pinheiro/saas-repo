# PCP-004 - Workflow de aprovação e conversão das sugestões MRP

## Objetivo

Fechar o fluxo entre sugestão MRP, análise do PCP, aprovação e geração de OP ou documentos de compra.

## Status da implementação

Implementado o workflow persistido básico de MRP.

- `mrp_plan_runs` persiste a execução, chave idempotente, payload, resumo e status.
- `mrp_suggestions` persiste sugestões de compra/produção, origem, quantidade original/aprovada, datas, versões e documento convertido.
- `mrp_suggestion_events` mantém o histórico de criação, decisão e conversão.
- O endpoint `POST /api/v1/mrp/plan` agora persiste o resultado além de retorná-lo.
- Consultas: `GET /api/v1/mrp/runs`, `GET/GET /api/v1/mrp/suggestions` e `/{id}`.
- Decisão: `POST /{id}/approve` e `POST /{id}/reject`; conversão: `POST /{id}/convert`.

## Escopo

- Persistir execução MRP, sugestões e linhas de origem.
- Status: gerada, em análise, aprovada, rejeitada, convertida, parcialmente convertida, cancelada.
- Registrar usuário, data, motivo e parâmetros da execução.
- Aprovar individualmente com quantidade ajustada e motivo; aprovação em lote permanece pendente.
- Converter sugestão de produção em OP idempotentemente.
- Converter sugestão de compra em requisição/pedido conforme regra existente.
- Impedir conversão duplicada.
- Permitir revisar quantidade, data, prioridade e origem com trilha de alteração.

## Regras implementadas

- Alteração manual deve ser diferenciada do resultado original do MRP.
- A execução é idempotente por fingerprint do payload e resultado essencial.
- Uma sugestão aprovada só pode ser convertida uma vez; repetição retorna o documento vinculado.
- A conversão de produção usa o payload do MRP e a criação de OP existente, que congela BOM/routing no release.
- A conversão de compra usa `PurchasingService::createRequisitionFromMrp`.
- A alteração manual fica em `adjusted_payload`, mantendo `original_payload`.
- Revalidação explícita de capacidade antes da conversão, aprovação em lote, agrupamento e cancelamento ainda são pendentes.
- Cancelamento não deve apagar histórico.

## Critérios de aceite

- [x] Runs e sugestões são listáveis por status/tipo, com vínculo ao produto e execução.
- [x] Uma sugestão aprovada gera OP ou requisição vinculada.
- [x] Repetir a conversão retorna a sugestão já convertida.
- [x] O histórico guarda valor original, ajustes e decisão final.
- [ ] Filtros avançados por produto/data/prioridade, aprovação em lote, revalidação de capacidade e cancelamento são próximos incrementos.

# PCP-004 - Workflow de aprovação e conversão das sugestões MRP

## Objetivo

Fechar o fluxo entre sugestão MRP, análise do PCP, aprovação e geração de OP ou documentos de compra.

## Contexto atual

O MRP retorna sugestões de produção e compra, e há endpoints de criação relacionados a MRP. Ainda falta um workflow consistente para revisar, aprovar, rejeitar, agrupar, converter e rastrear cada sugestão.

## Escopo

- Persistir execução MRP, sugestões e linhas de origem.
- Status: gerada, em análise, aprovada, rejeitada, convertida, parcialmente convertida, cancelada.
- Registrar usuário, data, motivo e parâmetros da execução.
- Aprovar em lote com validações de estoque/capacidade.
- Converter sugestão de produção em OP idempotentemente.
- Converter sugestão de compra em requisição/pedido conforme regra existente.
- Impedir conversão duplicada.
- Permitir revisar quantidade, data, prioridade e origem com trilha de alteração.

## Regras

- Alteração manual deve ser diferenciada do resultado original do MRP.
- Uma sugestão aprovada deve revalidar BOM, versão, estoque e capacidade antes da conversão.
- Sugestões de produtos sem BOM/routing elegível devem ser bloqueadas com motivo claro.
- Cancelamento não deve apagar histórico.

## Critérios de aceite

- O PCP consegue listar sugestões por produto, data, prioridade e exceção.
- Uma sugestão aprovada gera exatamente uma OP/requisição vinculada.
- Repetir a conversão retorna o documento existente.
- O histórico mostra valor MRP original, ajustes e decisão final.

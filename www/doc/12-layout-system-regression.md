# Regressão da migração para o Layout System

## Escopo não migrável: cupons

Em 20/08/2026, a comparação com `main` confirmou que não há rota, controller, model, migration ou view ativa de cupons. Portanto, não existe CRUD de cupons a ser migrado nesta tarefa visual. A criação desse módulo exige uma tarefa funcional separada, com regras de desconto, persistência, autorização, validação e testes próprios. Até essa definição existir, o menu Global Admin não deve anunciar um fluxo inexistente.

## Matriz de validação visual

Validar cada contexto em tema claro e escuro, nas larguras 360 px, 768 px e 1440 px:

| Contexto | Fluxos e estados mínimos |
| --- | --- |
| Público | login, cadastro, recuperação, MFA, convite, onboarding, pagamento e assinatura; erro e disabled |
| Global Admin | dashboard, clientes, empresas, planos, tutoriais, documentação e administradores; vazio, sucesso e confirmação destrutiva |
| Cliente | dashboards, engenharia, dados mestres, compras, vendas, estoque, produção, análises e RBAC; filtros, paginação e tabela larga |

Em cada execução, registrar navegador, usuário/permissão, tema, viewport, resultado, erros de console/rede e caminho da captura. Evidências visuais pertencem ao PR; este arquivo mantém o contrato reproduzível sem versionar imagens transitórias.

## Verificações automatizadas

`LayoutSystemComplianceTest` bloqueia a reintrodução de variantes/classes legadas, navegação de linha por JavaScript inline, paletas Tailwind diretas e cores hexadecimais nas views funcionais. O teste também cobre o contrato acessível de label, obrigatório, hint e erro de `x-ui.field`.

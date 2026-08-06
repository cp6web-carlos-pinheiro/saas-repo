# APIs e operação técnica

## Objetivo

Documentar as capacidades transversais usadas para integrar, operar e diagnosticar o sistema.

## API v1

- Prefixo principal `/api/v1`.
- Autenticação por Sanctum e JWT nos fluxos configurados.
- Resolução de tenant e autorização granular por permissão.
- Respostas padronizadas e paginação nos endpoints de listagem.
- Domínios expostos: identidade/onboarding, produtos, BOM, engenharia, roteiros, centros, recursos, calendário, programação, MRP, estoque, genealogia, produção/MES, compras e análise.

## Segurança funcional

- Middleware de autenticação antes das rotas protegidas.
- Tenant obrigatório para os dados empresariais.
- Permissões específicas por operação, como leitura, criação, aprovação, conversão, execução e exportação.
- Validação de payload por Form Requests ou validação explícita nos controllers.

## Processamento assíncrono e cache

- Estruturas Laravel para jobs, lotes de jobs, jobs falhos, cache e locks.
- Recálculo MRP utiliza chaves de execução/idempotência e pode reutilizar resultados conforme a implementação do serviço.

## Saúde e auditoria

- Endpoint autenticado de saúde do MRP.
- Endpoint padrão de disponibilidade da aplicação.
- Logs da aplicação e registro funcional de auditoria em banco para ações relevantes.
- Eventos de domínio preservam histórico operacional em MRP, MES, estoque e vendas.

## Interface web

- Layout tenant responsivo e menu organizado por Engenharia, Planejamento, Chão de fábrica, Análise, Inventário, Compras, Vendas e Administração.
- Componentes Blade compartilhados para painéis, alertas, campos, botões, menus e navegação.
- Tutoriais contextuais vinculados ao nome da rota.

## Limitações atuais

- Webhooks externos padronizados, limites de uso por cliente e SLO por endpoint não estão implementados como plataforma completa.
- A documentação formal OpenAPI não é gerada automaticamente pelo projeto.

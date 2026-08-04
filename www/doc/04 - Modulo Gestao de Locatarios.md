# Modulo Gestao de Locatarios

Este modulo trata o contexto multi-tenant da plataforma, incluindo empresas, estrutura fisica, organizacoes e configuracoes de conta.

## Ultima atualizacao
- 2026-08-02

## Status objetivo
- Status atual: Parcial avancado.
- Cobertura atual: companies, tenants, organizations, trials (14 dias), subscriptions e resolucao tenant no fluxo web.
- Pendencia principal: consolidar padrao unico entre web e API para isolamento, bootstrap e governanca de tenant.

## Tabelas relacionadas

### Mestres

- `companies`
- `plants`
- `warehouses`
- `organizations`
- `tenants`

### Transacionais

- `trials` (controle do periodo gratuito inicial de 14 dias)
- `subscriptions`
- `onboarding_profiles`
- `social_accounts`
- `account_invitations`

### Relacionamento

- `company_user`

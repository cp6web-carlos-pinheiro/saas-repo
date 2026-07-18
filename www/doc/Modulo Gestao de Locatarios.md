# Modulo Gestao de Locatarios

Este modulo trata o contexto multi-tenant da plataforma, incluindo empresas, estrutura fisica, organizacoes e configuracoes de conta.

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

# Plataforma SaaS e tenancy

## Objetivo

Administrar o ciclo inicial de uma conta SaaS, sua empresa, assinatura e separação de dados entre clientes.

## Funcionalidades implementadas

- Cadastro, login, recuperação de senha, verificação de e-mail e login social.
- Onboarding da empresa, com perfil inicial e associação do primeiro usuário.
- Período de avaliação de 14 dias e middleware que controla o acesso quando o trial não está ativo.
- Convites de conta com token e fluxo de aceitação.
- Catálogo de planos, valor em centavos, assinatura, alteração e cancelamento.
- Integração de pagamento via Pagar.me no fluxo de onboarding/assinatura.
- Seleção da empresa atual do usuário e resolução do tenant nas requisições web e API.
- Área administrativa global separada para administradores da plataforma, empresas, clientes, planos e tutoriais de página.
- Preferência de idioma do usuário.

## Isolamento de dados

Os models transacionais tenant-aware herdam de `TenantModel`. O contexto da empresa aplica o filtro de `company_id` e preenche o tenant em novos registros. Controllers também validam que recursos recebidos pertencem à empresa ativa.

## Entidades principais

- `companies`, `subscriptions`, `plans` e `trials`.
- `onboarding_profiles`, `account_invitations` e `email_verifications`.
- `users`, `admins`, `company_user` e `social_accounts`.

## Limitações atuais

- O ciclo comercial não implementa todos os cenários de cobrança recorrente, inadimplência, upgrade e downgrade automatizados.
- Não há medição completa de consumo e limites por plano.

## Dicionário de dados

Consulte as [tabelas da Plataforma SaaS e tenancy](11-dicionario-de-dados.md#plataforma-saas-e-tenancy).

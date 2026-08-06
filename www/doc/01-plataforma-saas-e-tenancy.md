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

As tabelas abaixo documentam o schema corrente do domínio. “Nula” informa se a coluna aceita `NULL`; “—” indica ausência de valor padrão explícito.

### `account_invitations`

**Finalidade:** Registros funcionais de account invitations.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `invited_by_user_id` | `bigint unsigned` | Não | — | Referência a `users.id`. |
| `accepted_by_user_id` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `email` | `varchar(190)` | Não | — | Endereço de e-mail. |
| `name` | `varchar(150)` | Sim | — | Nome. |
| `role_slug` | `varchar(80)` | Não | `organization-member` | Atributo funcional de role slug. |
| `token` | `varchar(128)` | Não | — | Token de autenticação ou confirmação. |
| `expires_at` | `datetime` | Não | — | Data e hora de expires. |
| `sent_at` | `datetime` | Sim | — | Data e hora de sent. |
| `accepted_at` | `datetime` | Sim | — | Data e hora de accepted. |
| `revoked_at` | `datetime` | Sim | — | Data e hora de revoked. |
| `meta` | `json` | Sim | — | Dados estruturados de meta em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `companies`

**Finalidade:** Empresas que delimitam o tenant dos dados operacionais.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `name` | `varchar(150)` | Não | — | Nome. |
| `code` | `varchar(50)` | Não | — | Código funcional. |
| `slug` | `varchar(180)` | Sim | — | Identificador público único da empresa. |
| `domain` | `varchar(180)` | Sim | — | Domínio corporativo. |
| `segment` | `varchar(120)` | Sim | — | Segmento de atuação. |
| `operation_size` | `varchar(80)` | Sim | — | Porte da operação. |
| `timezone` | `varchar(80)` | Não | `UTC` | Fuso horário da empresa. |
| `preferences` | `json` | Sim | — | Preferências SaaS e de onboarding. |
| `is_active` | `tinyint(1)` | Não | `1` | Indica se está ativo. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `onboarding_profiles`

**Finalidade:** Registros funcionais de onboarding profiles.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `user_id` | `bigint unsigned` | Não | — | Referência a `users.id`. |
| `import_data` | `tinyint(1)` | Não | `0` | Atributo funcional de import data. |
| `connect_integrations` | `tinyint(1)` | Não | `0` | Atributo funcional de connect integrations. |
| `invite_team` | `tinyint(1)` | Não | `0` | Atributo funcional de invite team. |
| `progress` | `tinyint unsigned` | Não | `0` | Atributo funcional de progress. |
| `completed_at` | `timestamp` | Sim | — | Data e hora de completed. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `plans`

**Finalidade:** Planos comerciais.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `code` | `varchar(80)` | Não | — | Código funcional. |
| `label` | `varchar(180)` | Não | — | Atributo funcional de label. |
| `description` | `text` | Sim | — | Descrição funcional. |
| `payment_method` | `varchar(120)` | Sim | — | Atributo funcional de payment method. |
| `billing_cycle_label` | `varchar(180)` | Sim | — | Atributo funcional de billing cycle label. |
| `amount_cents` | `int unsigned` | Não | `0` | Valor monetário ou taxa de amount cents. |
| `trial_days` | `smallint unsigned` | Sim | — | Atributo funcional de trial days. |
| `interval_months` | `smallint unsigned` | Sim | — | Atributo funcional de interval months. |
| `renewable` | `tinyint(1)` | Não | `1` | Atributo funcional de renewable. |
| `allow_once` | `tinyint(1)` | Não | `0` | Atributo funcional de allow once. |
| `default_status` | `varchar(40)` | Não | `active` | Atributo funcional de default status. |
| `is_active` | `tinyint(1)` | Não | `1` | Indica se está ativo. |
| `sort_order` | `int unsigned` | Não | `0` | Atributo funcional de sort order. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `subscriptions`

**Finalidade:** Assinaturas SaaS.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `trial_id` | `bigint unsigned` | Sim | — | Referência a `trials.id`. |
| `provider` | `varchar(40)` | Não | `stripe` | Atributo funcional de provider. |
| `provider_customer_id` | `varchar(120)` | Sim | — | Identificador relacionado a provider customer. |
| `provider_subscription_id` | `varchar(120)` | Sim | — | Identificador relacionado a provider subscription. |
| `plan_code` | `varchar(80)` | Não | `trial` | Atributo funcional de plan code. |
| `status` | `varchar(40)` | Não | `trialing` | Estado atual no workflow. |
| `starts_at` | `datetime` | Sim | — | Data e hora de starts. |
| `ends_at` | `datetime` | Sim | — | Data e hora de ends. |
| `canceled_at` | `datetime` | Sim | — | Data e hora de canceled. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `trials`

**Finalidade:** Períodos de avaliação.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `user_id` | `bigint unsigned` | Não | — | Referência a `users.id`. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `trial_start_date` | `datetime` | Não | — | Data de trial start. |
| `trial_end_date` | `datetime` | Não | — | Data de trial end. |
| `grace_ends_at` | `datetime` | Sim | — | Data e hora de grace ends. |
| `status` | `varchar(40)` | Não | `active` | Estado atual no workflow. |
| `expired_at` | `timestamp` | Sim | — | Data e hora de expired. |
| `is_expired` | `tinyint(1)` | Não | `0` | Indicador booleano de is expired. |
| `email_domain` | `varchar(180)` | Sim | — | Atributo funcional de email domain. |
| `registration_ip` | `varchar(45)` | Sim | — | Atributo funcional de registration ip. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

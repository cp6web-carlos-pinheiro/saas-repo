# Dicionário de dados

Schema MySQL `beyond_mrp` efetivamente migrado. Cada tabela possui um domínio principal; relacionamentos entre domínios aparecem nas referências das colunas.

- Tabelas: **86**.
- Consolidação estrutural aplicada em `2026_08_09_000001`: `companies` é a única raiz de tenancy; `production_operation_outputs` é a única fonte de apontamentos; estruturas legadas removidas não fazem mais parte do schema corrente.
- Colunas: **1185**.
- “Nula” informa se aceita `NULL`; “—” indica ausência de default explícito.

## Índice por domínio

- [Plataforma SaaS e tenancy](#plataforma-saas-e-tenancy)
- [Identidade, acesso e administração](#identidade-acesso-e-administracao)
- [Engenharia de produto e processo](#engenharia-de-produto-e-processo)
- [Estoque, rastreabilidade e genealogia](#estoque-rastreabilidade-e-genealogia)
- [Compras](#compras)
- [Vendas](#vendas)
- [Planejamento, MRP e PCP](#planejamento-mrp-e-pcp)
- [Produção, MES e qualidade](#producao-mes-e-qualidade)
- [Análise e relatórios](#analise-e-relatorios)
- [APIs e operação técnica](#apis-e-operacao-tecnica)

## Plataforma SaaS e tenancy

Documento funcional: [01-plataforma-saas-e-tenancy.md](01-plataforma-saas-e-tenancy.md).

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

### `organizations` (removida)

**Finalidade:** Estrutura legada consolidada em `companies` pela migration `2026_08_09_000001`.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Sim | — | Referência a `companies.id`. |
| `name` | `varchar(180)` | Não | — | Nome. |
| `slug` | `varchar(180)` | Não | — | Atributo funcional de slug. |
| `domain` | `varchar(180)` | Sim | — | Atributo funcional de domain. |
| `segment` | `varchar(120)` | Sim | — | Atributo funcional de segment. |
| `operation_size` | `varchar(80)` | Sim | — | Atributo funcional de operation size. |
| `timezone` | `varchar(80)` | Não | `UTC` | Atributo funcional de timezone. |
| `preferences` | `json` | Sim | — | Dados estruturados de preferences em JSON. |
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

### `tenants` (removida)

**Finalidade:** Estrutura legada consolidada em `companies` pela migration `2026_08_09_000001`.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `name` | `varchar(180)` | Não | — | Nome. |
| `slug` | `varchar(180)` | Não | — | Atributo funcional de slug. |
| `is_active` | `tinyint(1)` | Não | `1` | Indica se está ativo. |
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


## Identidade, acesso e administração

Documento funcional: [02-identidade-acesso-e-administracao.md](02-identidade-acesso-e-administracao.md).

### `admins`

**Finalidade:** Registros funcionais de admins.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `name` | `varchar(150)` | Não | — | Nome. |
| `email` | `varchar(190)` | Não | — | Endereço de e-mail. |
| `password` | `varchar(255)` | Não | — | Hash da senha. |
| `is_active` | `tinyint(1)` | Não | `1` | Indica se está ativo. |
| `remember_token` | `varchar(100)` | Sim | — | Atributo funcional de remember token. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `audit_logs`

**Finalidade:** Trilha de auditoria funcional e administrativa.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `user_id` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `company_id` | `bigint unsigned` | Sim | — | Referência a `companies.id`. |
| `event` | `varchar(120)` | Não | — | Atributo funcional de event. |
| `severity` | `varchar(20)` | Não | `info` | Atributo funcional de severity. |
| `ip_address` | `varchar(45)` | Sim | — | Atributo funcional de ip address. |
| `user_agent` | `text` | Sim | — | Atributo funcional de user agent. |
| `context` | `json` | Sim | — | Dados estruturados de context em JSON. |
| `occurred_at` | `timestamp; DEFAULT_GENERATED` | Não | `CURRENT_TIMESTAMP` | Data e hora de occurred. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `company_user`

**Finalidade:** Registros funcionais de company user.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `user_id` | `bigint unsigned` | Não | — | Referência a `users.id`. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `email_verifications`

**Finalidade:** Registros funcionais de email verifications.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `user_id` | `bigint unsigned` | Não | — | Referência a `users.id`. |
| `token` | `varchar(128)` | Não | — | Token de autenticação ou confirmação. |
| `expires_at` | `datetime` | Não | — | Data e hora de expires. |
| `verified_at` | `datetime` | Sim | — | Data e hora de verified. |
| `requested_ip` | `varchar(45)` | Sim | — | Atributo funcional de requested ip. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `master_data_records` (removida)

**Finalidade:** Estrutura legada substituída por tabelas específicas de unidades, categorias e marcas.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Empresa proprietária do registro. |
| `domain` | `varchar(40)` | Não | — | Atributo funcional de domain. |
| `code` | `varchar(60)` | Não | — | Código funcional. |
| `name` | `varchar(180)` | Não | — | Nome. |
| `description` | `text` | Sim | — | Descrição funcional. |
| `is_active` | `tinyint(1)` | Não | `1` | Indica se está ativo. |
| `created_by` | `bigint unsigned` | Sim | — | Atributo funcional de created by. |
| `updated_by` | `bigint unsigned` | Sim | — | Atributo funcional de updated by. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `page_tutorials`

**Finalidade:** Registros funcionais de page tutorials.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `route_name` | `varchar(190)` | Não | — | Atributo funcional de route name. |
| `title` | `varchar(190)` | Sim | — | Atributo funcional de title. |
| `content_html` | `longtext` | Não | — | Atributo funcional de content html. |
| `created_by_user_id` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `updated_by_user_id` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `password_reset_tokens`

**Finalidade:** Registros funcionais de password reset tokens.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `email` | `varchar(255)` | Não | — | Endereço de e-mail. |
| `token` | `varchar(255)` | Não | — | Token de autenticação ou confirmação. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |

### `password_resets` (removida)

**Finalidade:** Estrutura legada substituída por `password_reset_tokens`.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `email` | `varchar(190)` | Não | — | Endereço de e-mail. |
| `token` | `varchar(128)` | Não | — | Token de autenticação ou confirmação. |
| `expires_at` | `datetime` | Não | — | Data e hora de expires. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `permission_role`

**Finalidade:** Registros funcionais de permission role.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `permission_id` | `bigint unsigned` | Não | — | Referência a `permissions.id`. |
| `role_id` | `bigint unsigned` | Não | — | Referência a `roles.id`. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `permission_user_overrides` (removida)

**Finalidade:** Estrutura legada removida; a autorização vigente usa papéis e permissões.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `user_id` | `bigint unsigned` | Não | — | Referência a `users.id`. |
| `permission_id` | `bigint unsigned` | Não | — | Referência a `permissions.id`. |
| `is_allowed` | `tinyint(1)` | Não | — | Indicador booleano de is allowed. |
| `reason` | `varchar(255)` | Sim | — | Motivo da ação. |
| `created_by_user_id` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `permissions`

**Finalidade:** Catálogo de permissões funcionais.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `name` | `varchar(120)` | Não | — | Nome. |
| `slug` | `varchar(120)` | Não | — | Atributo funcional de slug. |
| `module` | `varchar(100)` | Não | — | Atributo funcional de module. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `personal_access_tokens`

**Finalidade:** Registros funcionais de personal access tokens.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `tokenable_type` | `varchar(255)` | Não | — | Tipo ou classificação de tokenable type. |
| `tokenable_id` | `bigint unsigned` | Não | — | Identificador relacionado a tokenable. |
| `name` | `varchar(255)` | Não | — | Nome. |
| `token` | `varchar(64)` | Não | — | Token de autenticação ou confirmação. |
| `abilities` | `text` | Sim | — | Atributo funcional de abilities. |
| `last_used_at` | `timestamp` | Sim | — | Data e hora de last used. |
| `expires_at` | `timestamp` | Sim | — | Data e hora de expires. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `plants`

**Finalidade:** Registros funcionais de plants.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `name` | `varchar(150)` | Não | — | Nome. |
| `code` | `varchar(50)` | Não | — | Código funcional. |
| `timezone` | `varchar(50)` | Não | `UTC` | Atributo funcional de timezone. |
| `is_active` | `tinyint(1)` | Não | `1` | Indica se está ativo. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `product_brands`

**Finalidade:** Registros funcionais de product brands.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Empresa proprietária do registro. |
| `code` | `varchar(40)` | Não | — | Código funcional. |
| `name` | `varchar(180)` | Não | — | Nome. |
| `description` | `text` | Sim | — | Descrição funcional. |
| `is_active` | `tinyint(1)` | Não | `1` | Indica se está ativo. |
| `created_by` | `bigint unsigned` | Sim | — | Atributo funcional de created by. |
| `updated_by` | `bigint unsigned` | Sim | — | Atributo funcional de updated by. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `product_categories`

**Finalidade:** Registros funcionais de product categories.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Empresa proprietária do registro. |
| `code` | `varchar(40)` | Não | — | Código funcional. |
| `name` | `varchar(180)` | Não | — | Nome. |
| `description` | `text` | Sim | — | Descrição funcional. |
| `is_active` | `tinyint(1)` | Não | `1` | Indica se está ativo. |
| `created_by` | `bigint unsigned` | Sim | — | Atributo funcional de created by. |
| `updated_by` | `bigint unsigned` | Sim | — | Atributo funcional de updated by. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `role_user`

**Finalidade:** Registros funcionais de role user.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `role_id` | `bigint unsigned` | Não | — | Referência a `roles.id`. |
| `user_id` | `bigint unsigned` | Não | — | Referência a `users.id`. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `roles`

**Finalidade:** Papéis RBAC atribuídos por empresa.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `name` | `varchar(120)` | Não | — | Nome. |
| `slug` | `varchar(120)` | Não | — | Atributo funcional de slug. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `sessions`

**Finalidade:** Registros funcionais de sessions.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `varchar(255)` | Não | — | Identificador único. |
| `user_id` | `bigint unsigned` | Sim | — | Identificador relacionado a user. |
| `ip_address` | `varchar(45)` | Sim | — | Atributo funcional de ip address. |
| `user_agent` | `text` | Sim | — | Atributo funcional de user agent. |
| `payload` | `longtext` | Não | — | Conteúdo adicional em JSON. |
| `last_activity` | `int` | Não | — | Atributo funcional de last activity. |

### `social_accounts`

**Finalidade:** Registros funcionais de social accounts.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `user_id` | `bigint unsigned` | Não | — | Referência a `users.id`. |
| `provider` | `varchar(40)` | Não | — | Atributo funcional de provider. |
| `provider_user_id` | `varchar(180)` | Não | — | Identificador relacionado a provider user. |
| `email` | `varchar(190)` | Sim | — | Endereço de e-mail. |
| `meta` | `json` | Sim | — | Dados estruturados de meta em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `units`

**Finalidade:** Registros funcionais de units.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Sim | — | Empresa proprietária do registro. |
| `code` | `varchar(20)` | Não | — | Código funcional. |
| `name` | `varchar(180)` | Não | — | Nome. |
| `description` | `text` | Sim | — | Descrição funcional. |
| `is_active` | `tinyint(1)` | Não | `1` | Indica se está ativo. |
| `created_by` | `bigint unsigned` | Sim | — | Atributo funcional de created by. |
| `updated_by` | `bigint unsigned` | Sim | — | Atributo funcional de updated by. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `users`

**Finalidade:** Usuários autenticáveis da aplicação.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `name` | `varchar(150)` | Não | — | Nome. |
| `email` | `varchar(190)` | Não | — | Endereço de e-mail. |
| `preferred_locale` | `varchar(10)` | Não | `pt_BR` | Atributo funcional de preferred locale. |
| `email_verified_at` | `timestamp` | Sim | — | Data e hora de email verified. |
| `password` | `varchar(255)` | Não | — | Hash da senha. |
| `current_company_id` | `bigint unsigned` | Sim | — | Referência a `companies.id`. |
| `is_active` | `tinyint(1)` | Não | `1` | Indica se está ativo. |
| `remember_token` | `varchar(100)` | Sim | — | Atributo funcional de remember token. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `warehouses`

**Finalidade:** Registros funcionais de warehouses.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `plant_id` | `bigint unsigned` | Não | — | Referência a `plants.id`. |
| `name` | `varchar(150)` | Não | — | Nome. |
| `code` | `varchar(50)` | Não | — | Código funcional. |
| `is_active` | `tinyint(1)` | Não | `1` | Indica se está ativo. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |


## Engenharia de produto e processo

Documento funcional: [03-engenharia-de-produto-e-processo.md](03-engenharia-de-produto-e-processo.md).

### `bom_headers`

**Finalidade:** Versões de listas de materiais.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `version_number` | `int unsigned` | Não | — | Versão de version number. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `effective_from` | `date` | Sim | — | Atributo funcional de effective from. |
| `effective_to` | `date` | Sim | — | Atributo funcional de effective to. |
| `description` | `varchar(255)` | Sim | — | Descrição funcional. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `bom_items`

**Finalidade:** Componentes de uma versão de BOM.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `bom_header_id` | `bigint unsigned` | Não | — | Referência a `bom_headers.id`. |
| `component_product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `unit_id` | `bigint unsigned` | Sim | — | Identificador relacionado a unit. |
| `line_no` | `int unsigned` | Não | `1` | Atributo funcional de line no. |
| `quantity_per` | `decimal(18,6)` | Não | — | Quantidade de per. |
| `uom` | `varchar(20)` | Sim | — | Atributo funcional de uom. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `engineering_change_order_lines`

**Finalidade:** Itens detalhados de engineering change order.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `engineering_change_order_id` | `bigint unsigned` | Não | — | Referência a `engineering_change_orders.id`. |
| `target_domain` | `varchar(20)` | Não | — | Atributo funcional de target domain. |
| `target_entity_id` | `bigint unsigned` | Não | — | Identificador relacionado a target entity. |
| `change_type` | `varchar(40)` | Não | `VERSION_CHANGE` | Tipo ou classificação de change type. |
| `from_version_number` | `int unsigned` | Sim | — | Versão de from version number. |
| `to_version_number` | `int unsigned` | Sim | — | Versão de to version number. |
| `effective_from` | `date` | Sim | — | Atributo funcional de effective from. |
| `effective_to` | `date` | Sim | — | Atributo funcional de effective to. |
| `impact_level` | `varchar(20)` | Não | `MEDIUM` | Atributo funcional de impact level. |
| `change_summary` | `text` | Sim | — | Atributo funcional de change summary. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `engineering_change_orders`

**Finalidade:** Registros funcionais de engineering change orders.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `eco_number` | `varchar(40)` | Não | — | Número funcional de eco number. |
| `title` | `varchar(180)` | Não | — | Atributo funcional de title. |
| `description` | `text` | Sim | — | Descrição funcional. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `effective_from` | `date` | Sim | — | Atributo funcional de effective from. |
| `effective_to` | `date` | Sim | — | Atributo funcional de effective to. |
| `requested_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `submitted_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `submitted_at` | `timestamp` | Sim | — | Data e hora de submitted. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `rejected_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `rejected_at` | `timestamp` | Sim | — | Data e hora de rejected. |
| `rejection_reason` | `text` | Sim | — | Atributo funcional de rejection reason. |
| `implemented_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `implemented_at` | `timestamp` | Sim | — | Data e hora de implemented. |
| `impact_summary` | `json` | Sim | — | Dados estruturados de impact summary em JSON. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `product_versions`

**Finalidade:** Registros funcionais de product versions.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `version_number` | `int unsigned` | Não | — | Versão de version number. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `effective_from` | `date` | Sim | — | Atributo funcional de effective from. |
| `effective_to` | `date` | Sim | — | Atributo funcional de effective to. |
| `compatibility_rule` | `varchar(20)` | Não | `NONE` | Atributo funcional de compatibility rule. |
| `change_summary` | `text` | Sim | — | Atributo funcional de change summary. |
| `payload` | `json` | Não | — | Conteúdo adicional em JSON. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_resources`

**Finalidade:** Máquinas, equipamentos, ferramentas e linhas.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `plant_id` | `bigint unsigned` | Não | — | Referência a `plants.id`. |
| `work_center_id` | `bigint unsigned` | Não | — | Referência a `work_centers.id`. |
| `code` | `varchar(80)` | Não | — | Código funcional. |
| `name` | `varchar(150)` | Não | — | Nome. |
| `resource_type` | `varchar(30)` | Não | — | Tipo ou classificação de resource type. |
| `status` | `varchar(20)` | Não | `ACTIVE` | Estado atual no workflow. |
| `capacity_per_day` | `decimal(10,2)` | Sim | — | Atributo funcional de capacity per day. |
| `efficiency_factor` | `decimal(7,3)` | Sim | — | Atributo funcional de efficiency factor. |
| `effective_from` | `date` | Sim | — | Atributo funcional de effective from. |
| `effective_to` | `date` | Sim | — | Atributo funcional de effective to. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `products`

**Finalidade:** Cadastro mestre de produtos.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `sku` | `varchar(80)` | Não | — | Atributo funcional de sku. |
| `description` | `varchar(255)` | Não | — | Descrição funcional. |
| `product_type` | `varchar(20)` | Não | — | Tipo ou classificação de product type. |
| `safety_stock` | `int unsigned` | Não | `0` | Atributo funcional de safety stock. |
| `lead_time_days` | `int unsigned` | Não | `0` | Atributo funcional de lead time days. |
| `lot_control` | `tinyint(1)` | Não | `0` | Atributo funcional de lot control. |
| `serial_control` | `tinyint(1)` | Não | `0` | Atributo funcional de serial control. |
| `is_active` | `tinyint(1)` | Não | `1` | Indica se está ativo. |
| `lifecycle_status` | `varchar(20)` | Não | `ACTIVE` | Atributo funcional de lifecycle status. |
| `technical_attributes` | `json` | Sim | — | Dados estruturados de technical attributes em JSON. |
| `commercial_attributes` | `json` | Sim | — | Dados estruturados de commercial attributes em JSON. |
| `fiscal_attributes` | `json` | Sim | — | Dados estruturados de fiscal attributes em JSON. |
| `alternate_uoms` | `json` | Sim | — | Dados estruturados de alternate uoms em JSON. |
| `image_urls` | `json` | Sim | — | Dados estruturados de image urls em JSON. |
| `attachment_urls` | `json` | Sim | — | Dados estruturados de attachment urls em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |
| `unit_id` | `bigint unsigned` | Sim | — | Identificador relacionado a unit. |
| `category_id` | `bigint unsigned` | Sim | — | Identificador relacionado a category. |
| `brand_id` | `bigint unsigned` | Sim | — | Identificador relacionado a brand. |

### `routing_operation_snapshots`

**Finalidade:** Cópia histórica e imutável de routing operation.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `routing_version_snapshot_id` | `bigint unsigned` | Não | — | Referência a `routing_version_snapshots.id`. |
| `routing_version_id` | `bigint unsigned` | Não | — | Referência a `routing_versions.id`. |
| `standard_time_id` | `bigint unsigned` | Sim | — | Identificador relacionado a standard time. |
| `standard_time_version` | `int unsigned` | Sim | — | Versão de standard time version. |
| `work_center_id` | `bigint unsigned` | Não | — | Referência a `work_centers.id`. |
| `operation_no` | `int unsigned` | Não | — | Atributo funcional de operation no. |
| `operation_code` | `varchar(50)` | Não | — | Atributo funcional de operation code. |
| `operation_name` | `varchar(150)` | Não | — | Atributo funcional de operation name. |
| `sequence` | `int unsigned` | Não | — | Atributo funcional de sequence. |
| `setup_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de setup time. |
| `runtime_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de runtime. |
| `queue_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de queue time. |
| `move_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de move time. |
| `is_outsourced` | `tinyint(1)` | Não | `0` | Indicador booleano de is outsourced. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `routing_operation_standard_times`

**Finalidade:** Versões dos tempos padrão de uma operação.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `routing_operation_id` | `bigint unsigned` | Não | — | Referência a `routing_operations.id`. |
| `version_number` | `int unsigned` | Não | — | Versão de version number. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `time_basis` | `varchar(20)` | Não | `PER_PROCESS` | Atributo funcional de time basis. |
| `setup_scope` | `varchar(20)` | Não | `ROUTING` | Atributo funcional de setup scope. |
| `base_quantity` | `decimal(18,6)` | Não | `1.000000` | Quantidade de base quantity. |
| `setup_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de setup time. |
| `runtime_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de runtime. |
| `queue_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de queue time. |
| `move_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de move time. |
| `efficiency_factor` | `decimal(7,3)` | Não | `100.000` | Atributo funcional de efficiency factor. |
| `yield_factor` | `decimal(7,4)` | Não | `100.0000` | Atributo funcional de yield factor. |
| `effective_from` | `date` | Sim | — | Atributo funcional de effective from. |
| `effective_to` | `date` | Sim | — | Atributo funcional de effective to. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `change_reason` | `text` | Sim | — | Atributo funcional de change reason. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `routing_operations`

**Finalidade:** Operações de uma versão de roteiro.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `routing_version_id` | `bigint unsigned` | Não | — | Referência a `routing_versions.id`. |
| `work_center_id` | `bigint unsigned` | Não | — | Referência a `work_centers.id`. |
| `operation_no` | `int unsigned` | Não | — | Atributo funcional de operation no. |
| `operation_code` | `varchar(50)` | Não | — | Atributo funcional de operation code. |
| `operation_name` | `varchar(150)` | Não | — | Atributo funcional de operation name. |
| `sequence` | `int unsigned` | Não | — | Atributo funcional de sequence. |
| `setup_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de setup time. |
| `runtime_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de runtime. |
| `queue_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de queue time. |
| `move_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de move time. |
| `is_outsourced` | `tinyint(1)` | Não | `0` | Indicador booleano de is outsourced. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `routing_version_snapshots`

**Finalidade:** Cópia histórica e imutável de routing version.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `routing_version_id` | `bigint unsigned` | Não | — | Referência a `routing_versions.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `version_number` | `int unsigned` | Não | — | Versão de version number. |
| `status` | `varchar(20)` | Não | — | Estado atual no workflow. |
| `effective_from` | `date` | Não | — | Atributo funcional de effective from. |
| `effective_to` | `date` | Sim | — | Atributo funcional de effective to. |
| `description` | `varchar(255)` | Sim | — | Descrição funcional. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `frozen_at` | `timestamp` | Não | — | Data e hora de frozen. |
| `snapshot_hash` | `varchar(64)` | Não | — | Atributo funcional de snapshot hash. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `routing_versions`

**Finalidade:** Versões de roteiro por produto.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `version_number` | `int unsigned` | Não | — | Versão de version number. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `effective_from` | `date` | Sim | — | Atributo funcional de effective from. |
| `effective_to` | `date` | Sim | — | Atributo funcional de effective to. |
| `description` | `varchar(255)` | Sim | — | Descrição funcional. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `work_center_hour_rates`

**Finalidade:** Registros funcionais de work center hour rates.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `work_center_id` | `bigint unsigned` | Não | — | Referência a `work_centers.id`. |
| `hourly_rate` | `decimal(18,6)` | Não | — | Valor monetário ou taxa de hourly rate. |
| `currency` | `varchar(3)` | Não | `BRL` | Atributo funcional de currency. |
| `effective_from` | `date` | Não | — | Atributo funcional de effective from. |
| `effective_to` | `date` | Sim | — | Atributo funcional de effective to. |
| `status` | `varchar(20)` | Não | `ACTIVE` | Estado atual no workflow. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `change_reason` | `text` | Sim | — | Atributo funcional de change reason. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `work_center_shifts`

**Finalidade:** Registros funcionais de work center shifts.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `work_center_id` | `bigint unsigned` | Não | — | Referência a `work_centers.id`. |
| `name` | `varchar(80)` | Não | — | Nome. |
| `shift_start` | `time` | Não | — | Atributo funcional de shift start. |
| `shift_end` | `time` | Não | — | Atributo funcional de shift end. |
| `capacity_hours` | `decimal(5,2)` | Não | — | Atributo funcional de capacity hours. |
| `is_active` | `tinyint(1)` | Não | `1` | Indica se está ativo. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `work_centers`

**Finalidade:** Centros de trabalho produtivos.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `plant_id` | `bigint unsigned` | Não | — | Referência a `plants.id`. |
| `code` | `varchar(50)` | Não | — | Código funcional. |
| `name` | `varchar(150)` | Não | — | Nome. |
| `resource_type` | `varchar(20)` | Não | — | Tipo ou classificação de resource type. |
| `capacity_per_day` | `decimal(10,2)` | Não | — | Atributo funcional de capacity per day. |
| `efficiency_factor` | `decimal(5,2)` | Não | `100.00` | Atributo funcional de efficiency factor. |
| `is_active` | `tinyint(1)` | Não | `1` | Indica se está ativo. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |


## Estoque, rastreabilidade e genealogia

Documento funcional: [04-estoque-rastreabilidade-e-genealogia.md](04-estoque-rastreabilidade-e-genealogia.md).

### `genealogy_nodes`

**Finalidade:** Nós rastreáveis da genealogia.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `node_type` | `varchar(40)` | Não | — | Tipo ou classificação de node type. |
| `source_id` | `bigint unsigned` | Não | — | Identificador relacionado a source. |
| `source_reference` | `varchar(120)` | Sim | — | Atributo funcional de source reference. |
| `product_id` | `bigint unsigned` | Sim | — | Referência a `products.id`. |
| `warehouse_id` | `bigint unsigned` | Sim | — | Referência a `warehouses.id`. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `genealogy_relations`

**Finalidade:** Relações entre nós de genealogia.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `parent_node_id` | `bigint unsigned` | Não | — | Referência a `genealogy_nodes.id`. |
| `child_node_id` | `bigint unsigned` | Não | — | Referência a `genealogy_nodes.id`. |
| `relation_type` | `varchar(40)` | Não | — | Tipo ou classificação de relation type. |
| `quantity` | `decimal(18,6)` | Sim | — | Quantidade associada. |
| `uom` | `varchar(20)` | Sim | — | Atributo funcional de uom. |
| `unit_id` | `bigint unsigned` | Sim | — | Identificador relacionado a unit. |
| `production_order_id` | `bigint unsigned` | Sim | — | Referência a `production_orders.id`. |
| `stock_movement_id` | `bigint unsigned` | Sim | — | Referência a `stock_ledger_movements.id`. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `inventory_balances`

**Finalidade:** Saldos por produto e armazém.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `warehouse_id` | `bigint unsigned` | Não | — | Referência a `warehouses.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `qty_available` | `decimal(18,6)` | Não | `0.000000` | Quantidade de available. |
| `qty_reserved` | `decimal(18,6)` | Não | `0.000000` | Quantidade de reserved. |
| `qty_in_transit` | `decimal(18,6)` | Não | `0.000000` | Quantidade de in transit. |
| `qty_inspection` | `decimal(18,6)` | Não | `0.000000` | Quantidade de inspection. |
| `last_movement_at` | `timestamp` | Sim | — | Data e hora de last movement. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `inventory_lots`

**Finalidade:** Registros funcionais de inventory lots.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `warehouse_id` | `bigint unsigned` | Não | — | Referência a `warehouses.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `lot_number` | `varchar(80)` | Não | — | Número funcional de lot number. |
| `manufactured_at` | `date` | Sim | — | Data e hora de manufactured. |
| `expires_at` | `date` | Sim | — | Data e hora de expires. |
| `status` | `varchar(20)` | Não | `ACTIVE` | Estado atual no workflow. |
| `source_movement_id` | `bigint unsigned` | Sim | — | Identificador relacionado a source movement. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `inventory_reservations`

**Finalidade:** Reservas vinculadas a demandas.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `warehouse_id` | `bigint unsigned` | Não | — | Referência a `warehouses.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `reservation_origin` | `varchar(30)` | Não | — | Atributo funcional de reservation origin. |
| `priority` | `int unsigned` | Não | `100` | Atributo funcional de priority. |
| `quantity` | `decimal(18,6)` | Não | — | Quantidade associada. |
| `status` | `varchar(20)` | Não | `RESERVED` | Estado atual no workflow. |
| `reference_type` | `varchar(120)` | Sim | — | Tipo ou classificação de reference type. |
| `reference_id` | `bigint unsigned` | Sim | — | Identificador relacionado a reference. |
| `reserved_at` | `timestamp` | Não | — | Data e hora de reserved. |
| `expires_at` | `timestamp` | Sim | — | Data e hora de expires. |
| `released_at` | `timestamp` | Sim | — | Data e hora de released. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `released_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `release_reason` | `text` | Sim | — | Atributo funcional de release reason. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `inventory_serials`

**Finalidade:** Registros funcionais de inventory serials.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `warehouse_id` | `bigint unsigned` | Não | — | Referência a `warehouses.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `inventory_lot_id` | `bigint unsigned` | Sim | — | Referência a `inventory_lots.id`. |
| `serial_number` | `varchar(120)` | Não | — | Número funcional de serial number. |
| `status` | `varchar(20)` | Não | `ACTIVE` | Estado atual no workflow. |
| `source_movement_id` | `bigint unsigned` | Sim | — | Identificador relacionado a source movement. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `stock_ledger_allocations`

**Finalidade:** Registros funcionais de stock ledger allocations.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `issue_movement_id` | `bigint unsigned` | Não | — | Referência a `stock_ledger_movements.id`. |
| `receipt_movement_id` | `bigint unsigned` | Não | — | Referência a `stock_ledger_movements.id`. |
| `quantity` | `decimal(18,6)` | Não | — | Quantidade associada. |
| `sequence_no` | `int unsigned` | Não | — | Atributo funcional de sequence no. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `stock_ledger_movements`

**Finalidade:** Ledger de movimentos de estoque.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `warehouse_id` | `bigint unsigned` | Não | — | Referência a `warehouses.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `movement_type` | `varchar(30)` | Não | — | Tipo ou classificação de movement type. |
| `source_bucket` | `varchar(20)` | Sim | — | Atributo funcional de source bucket. |
| `target_bucket` | `varchar(20)` | Sim | — | Atributo funcional de target bucket. |
| `quantity` | `decimal(18,6)` | Não | — | Quantidade associada. |
| `allocation_strategy` | `varchar(20)` | Sim | — | Valor monetário ou taxa de allocation strategy. |
| `lot_number` | `varchar(80)` | Sim | — | Número funcional de lot number. |
| `expires_at` | `date` | Sim | — | Data e hora de expires. |
| `reference_type` | `varchar(120)` | Sim | — | Tipo ou classificação de reference type. |
| `reference_id` | `bigint unsigned` | Sim | — | Identificador relacionado a reference. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `movement_at` | `timestamp` | Não | — | Data e hora de movement. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |


## Compras

Documento funcional: [05-compras.md](05-compras.md).

### `purchase_order_lines`

**Finalidade:** Itens detalhados de purchase order.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `purchase_order_id` | `bigint unsigned` | Não | — | Referência a `purchase_orders.id`. |
| `purchase_requisition_line_id` | `bigint unsigned` | Sim | — | Referência a `purchase_requisition_lines.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `warehouse_id` | `bigint unsigned` | Sim | — | Referência a `warehouses.id`. |
| `quantity_ordered` | `decimal(14,6)` | Não | `0.000000` | Quantidade de ordered. |
| `quantity_received` | `decimal(14,6)` | Não | `0.000000` | Quantidade de received. |
| `unit_price` | `decimal(14,6)` | Sim | — | Valor monetário ou taxa de unit price. |
| `need_by_date` | `date` | Sim | — | Data de need by. |
| `promised_date` | `date` | Sim | — | Data de promised. |
| `status` | `varchar(20)` | Não | `OPEN` | Estado atual no workflow. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `purchase_orders`

**Finalidade:** Pedidos enviados aos fornecedores.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `purchase_order_number` | `varchar(60)` | Não | — | Número funcional de purchase order number. |
| `supplier_id` | `bigint unsigned` | Não | — | Referência a `suppliers.id`. |
| `purchase_requisition_id` | `bigint unsigned` | Sim | — | Referência a `purchase_requisitions.id`. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `order_date` | `date` | Não | — | Data de order. |
| `expected_delivery_date` | `date` | Sim | — | Data de expected delivery. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `cancelled_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `cancelled_at` | `timestamp` | Sim | — | Data e hora de cancelled. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `purchase_quotation_lines`

**Finalidade:** Itens detalhados de purchase quotation.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `purchase_quotation_id` | `bigint unsigned` | Não | — | Referência a `purchase_quotations.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `purchase_requisition_line_id` | `bigint unsigned` | Sim | — | Referência a `purchase_requisition_lines.id`. |
| `quantity` | `decimal(14,6)` | Não | `0.000000` | Quantidade associada. |
| `unit_price` | `decimal(14,6)` | Sim | — | Valor monetário ou taxa de unit price. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `purchase_quotations`

**Finalidade:** Cotações de compra.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `quotation_number` | `varchar(60)` | Não | — | Número funcional de quotation number. |
| `purchase_requisition_id` | `bigint unsigned` | Sim | — | Referência a `purchase_requisitions.id`. |
| `supplier_id` | `bigint unsigned` | Sim | — | Referência a `suppliers.id`. |
| `quotation_date` | `date` | Não | — | Data de quotation. |
| `valid_until` | `date` | Sim | — | Atributo funcional de valid until. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `received_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `received_at` | `timestamp` | Sim | — | Data e hora de received. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `rejected_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `rejected_at` | `timestamp` | Sim | — | Data e hora de rejected. |
| `amount_cents` | `int` | Não | `0` | Valor monetário ou taxa de amount cents. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `purchase_receipt_lines`

**Finalidade:** Itens detalhados de purchase receipt.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `purchase_receipt_id` | `bigint unsigned` | Não | — | Referência a `purchase_receipts.id`. |
| `purchase_order_line_id` | `bigint unsigned` | Sim | — | Referência a `purchase_order_lines.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `warehouse_id` | `bigint unsigned` | Não | — | Referência a `warehouses.id`. |
| `quantity_received` | `decimal(14,6)` | Não | `0.000000` | Quantidade de received. |
| `lot_number` | `varchar(80)` | Sim | — | Número funcional de lot number. |
| `stock_ledger_movement_id` | `bigint unsigned` | Sim | — | Identificador relacionado a stock ledger movement. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `purchase_receipts`

**Finalidade:** Recebimentos e postagem no estoque.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `receipt_number` | `varchar(60)` | Não | — | Número funcional de receipt number. |
| `purchase_order_id` | `bigint unsigned` | Sim | — | Referência a `purchase_orders.id`. |
| `supplier_id` | `bigint unsigned` | Sim | — | Referência a `suppliers.id`. |
| `receipt_date` | `date` | Não | — | Data de receipt. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `posted_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `posted_at` | `timestamp` | Sim | — | Data e hora de posted. |
| `cancelled_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `cancelled_at` | `timestamp` | Sim | — | Data e hora de cancelled. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `purchase_requisition_lines`

**Finalidade:** Itens detalhados de purchase requisition.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `purchase_requisition_id` | `bigint unsigned` | Não | — | Referência a `purchase_requisitions.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `warehouse_id` | `bigint unsigned` | Sim | — | Referência a `warehouses.id`. |
| `supplier_id` | `bigint unsigned` | Sim | — | Referência a `suppliers.id`. |
| `suggested_quantity` | `decimal(14,6)` | Não | `0.000000` | Quantidade de suggested quantity. |
| `requested_quantity` | `decimal(14,6)` | Não | `0.000000` | Quantidade de requested quantity. |
| `moq_applied` | `decimal(14,6)` | Não | `1.000000` | Atributo funcional de moq applied. |
| `lead_time_days` | `int unsigned` | Não | `0` | Atributo funcional de lead time days. |
| `need_by_date` | `date` | Não | — | Data de need by. |
| `order_date` | `date` | Não | — | Data de order. |
| `status` | `varchar(20)` | Não | `OPEN` | Estado atual no workflow. |
| `source_requirement_key` | `varchar(180)` | Sim | — | Chave funcional de source requirement key. |
| `mrp_reference_date` | `date` | Sim | — | Data de mrp reference. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `purchase_requisitions`

**Finalidade:** Requisições internas de compra.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `requisition_number` | `varchar(60)` | Não | — | Número funcional de requisition number. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `required_date` | `date` | Sim | — | Data de required. |
| `source_type` | `varchar(80)` | Sim | — | Tipo ou classificação de source type. |
| `source_reference_id` | `bigint unsigned` | Sim | — | Identificador relacionado a source reference. |
| `source_reference_type` | `varchar(120)` | Sim | — | Tipo ou classificação de source reference type. |
| `requested_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `cancelled_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `cancelled_at` | `timestamp` | Sim | — | Data e hora de cancelled. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `supplier_products`

**Finalidade:** Regras por fornecedor e produto.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `supplier_id` | `bigint unsigned` | Não | — | Referência a `suppliers.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `supplier_sku` | `varchar(80)` | Sim | — | Atributo funcional de supplier sku. |
| `moq` | `decimal(14,6)` | Não | `1.000000` | Atributo funcional de moq. |
| `lead_time_days` | `int unsigned` | Não | `0` | Atributo funcional de lead time days. |
| `unit_price` | `decimal(14,6)` | Sim | — | Valor monetário ou taxa de unit price. |
| `is_preferred` | `tinyint(1)` | Não | `0` | Indicador booleano de is preferred. |
| `is_active` | `tinyint(1)` | Não | `1` | Indica se está ativo. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `suppliers`

**Finalidade:** Cadastro de fornecedores.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `code` | `varchar(50)` | Não | — | Código funcional. |
| `name` | `varchar(180)` | Não | — | Nome. |
| `person_type` | `varchar(2)` | Não | `PJ` | Tipo ou classificação de person type. |
| `email` | `varchar(180)` | Sim | — | Endereço de e-mail. |
| `phone` | `varchar(50)` | Sim | — | Atributo funcional de phone. |
| `status` | `varchar(20)` | Não | `ACTIVE` | Estado atual no workflow. |
| `default_lead_time_days` | `int unsigned` | Não | `0` | Atributo funcional de default lead time days. |
| `payment_terms` | `varchar(80)` | Sim | — | Atributo funcional de payment terms. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |


## Vendas

Documento funcional: [06-vendas.md](06-vendas.md).

### `customers`

**Finalidade:** Cadastro de clientes.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `code` | `varchar(50)` | Não | — | Código funcional. |
| `name` | `varchar(180)` | Não | — | Nome. |
| `person_type` | `varchar(2)` | Não | `PJ` | Tipo ou classificação de person type. |
| `email` | `varchar(180)` | Sim | — | Endereço de e-mail. |
| `phone` | `varchar(50)` | Sim | — | Atributo funcional de phone. |
| `status` | `varchar(20)` | Não | `ACTIVE` | Estado atual no workflow. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `sale_lines`

**Finalidade:** Itens detalhados de sale.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `sale_id` | `bigint unsigned` | Não | — | Referência a `sales.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `quantity` | `decimal(14,6)` | Não | `0.000000` | Quantidade associada. |
| `unit_price` | `decimal(14,6)` | Não | `0.000000` | Valor monetário ou taxa de unit price. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `sales`

**Finalidade:** Pedidos de venda e seus workflows.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `customer_id` | `bigint unsigned` | Sim | — | Referência a `customers.id`. |
| `sale_date` | `date` | Não | — | Data de sale. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `operational_status` | `varchar(20)` | Não | `PENDING` | Atributo funcional de operational status. |
| `subtotal_cents` | `bigint unsigned` | Não | `0` | Atributo funcional de subtotal cents. |
| `discount_cents` | `bigint unsigned` | Não | `0` | Atributo funcional de discount cents. |
| `amount_cents` | `bigint unsigned` | Não | `0` | Valor monetário ou taxa de amount cents. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |
| `picking_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `picking_at` | `timestamp` | Sim | — | Data e hora de picking. |
| `invoiced_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `invoiced_at` | `timestamp` | Sim | — | Data e hora de invoiced. |
| `shipped_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `shipped_at` | `timestamp` | Sim | — | Data e hora de shipped. |
| `delivered_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `delivered_at` | `timestamp` | Sim | — | Data e hora de delivered. |
| `confirmed_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `confirmed_at` | `timestamp` | Sim | — | Data e hora de confirmed. |
| `canceled_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `canceled_at` | `timestamp` | Sim | — | Data e hora de canceled. |
| `cancel_reason` | `text` | Sim | — | Atributo funcional de cancel reason. |


## Planejamento, MRP e PCP

Documento funcional: [07-planejamento-mrp-e-pcp.md](07-planejamento-mrp-e-pcp.md).

### `mrp_plan_runs`

**Finalidade:** Execuções persistidas do MRP.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `run_key` | `varchar(64)` | Não | — | Chave funcional de run key. |
| `status` | `varchar(20)` | Não | `COMPLETED` | Estado atual no workflow. |
| `reference_date` | `date` | Não | — | Data de reference. |
| `planning_bucket` | `varchar(20)` | Não | `daily` | Atributo funcional de planning bucket. |
| `priority_rule` | `varchar(40)` | Não | `priority_due_date` | Atributo funcional de priority rule. |
| `request_payload` | `json` | Não | — | Dados estruturados de request payload em JSON. |
| `result_summary` | `json` | Sim | — | Dados estruturados de result summary em JSON. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `mrp_suggestion_events`

**Finalidade:** Histórico de eventos de mrp suggestion.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `mrp_suggestion_id` | `bigint unsigned` | Não | — | Referência a `mrp_suggestions.id`. |
| `event_type` | `varchar(40)` | Não | — | Tipo ou classificação de event type. |
| `from_status` | `varchar(30)` | Sim | — | Atributo funcional de from status. |
| `to_status` | `varchar(30)` | Sim | — | Atributo funcional de to status. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `reason` | `text` | Sim | — | Motivo da ação. |
| `payload` | `json` | Sim | — | Conteúdo adicional em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `mrp_suggestions`

**Finalidade:** Sugestões de compra ou produção.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `mrp_plan_run_id` | `bigint unsigned` | Não | — | Referência a `mrp_plan_runs.id`. |
| `suggestion_key` | `varchar(180)` | Não | — | Chave funcional de suggestion key. |
| `suggestion_type` | `varchar(20)` | Não | — | Tipo ou classificação de suggestion type. |
| `status` | `varchar(30)` | Não | `GENERATED` | Estado atual no workflow. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `warehouse_id` | `bigint unsigned` | Sim | — | Referência a `warehouses.id`. |
| `original_quantity` | `decimal(18,6)` | Não | — | Quantidade de original quantity. |
| `approved_quantity` | `decimal(18,6)` | Sim | — | Quantidade de approved quantity. |
| `need_by_date` | `date` | Não | — | Data de need by. |
| `release_date` | `date` | Sim | — | Data de release. |
| `priority` | `int unsigned` | Não | `1000` | Atributo funcional de priority. |
| `bom_version_number` | `int unsigned` | Sim | — | Versão de bom version number. |
| `routing_version_id` | `bigint unsigned` | Sim | — | Identificador relacionado a routing version. |
| `source_requirement_key` | `varchar(180)` | Sim | — | Chave funcional de source requirement key. |
| `source_reference_type` | `varchar(120)` | Sim | — | Tipo ou classificação de source reference type. |
| `source_reference_id` | `bigint unsigned` | Sim | — | Identificador relacionado a source reference. |
| `production_order_id` | `bigint unsigned` | Sim | — | Identificador relacionado a production order. |
| `purchase_requisition_id` | `bigint unsigned` | Sim | — | Identificador relacionado a purchase requisition. |
| `decision_reason` | `text` | Sim | — | Atributo funcional de decision reason. |
| `original_payload` | `json` | Sim | — | Dados estruturados de original payload em JSON. |
| `adjusted_payload` | `json` | Sim | — | Dados estruturados de adjusted payload em JSON. |
| `decided_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `decided_at` | `timestamp` | Sim | — | Data e hora de decided. |
| `converted_at` | `timestamp` | Sim | — | Data e hora de converted. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_calendar_days`

**Finalidade:** Registros funcionais de production calendar days.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `work_center_id` | `bigint unsigned` | Não | — | Referência a `work_centers.id`. |
| `calendar_date` | `date` | Não | — | Data de calendar. |
| `is_working_day` | `tinyint(1)` | Não | `1` | Indicador booleano de is working day. |
| `available_capacity` | `decimal(10,2)` | Sim | — | Atributo funcional de available capacity. |
| `notes` | `varchar(255)` | Sim | — | Observações livres. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_schedule_lines`

**Finalidade:** Itens detalhados de production schedule.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_schedule_id` | `bigint unsigned` | Não | — | Referência a `production_schedules.id`. |
| `production_order_id` | `bigint unsigned` | Não | — | Referência a `production_orders.id`. |
| `production_order_operation_id` | `bigint unsigned` | Não | — | Referência a `production_order_operations.id`. |
| `work_center_id` | `bigint unsigned` | Não | — | Referência a `work_centers.id`. |
| `production_resource_id` | `bigint unsigned` | Sim | — | Referência a `production_resources.id`. |
| `planned_start_at` | `datetime` | Não | — | Data e hora de planned start. |
| `planned_end_at` | `datetime` | Não | — | Data e hora de planned end. |
| `total_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de total time. |
| `capacity_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de capacity time. |
| `lead_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de lead time. |
| `segments` | `json` | Sim | — | Dados estruturados de segments em JSON. |
| `status` | `varchar(20)` | Não | `PLANNED` | Estado atual no workflow. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_schedules`

**Finalidade:** Programas de produção versionados.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `plant_id` | `bigint unsigned` | Sim | — | Referência a `plants.id`. |
| `schedule_number` | `varchar(50)` | Não | — | Número funcional de schedule number. |
| `version_number` | `int unsigned` | Não | `1` | Versão de version number. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `reference_date` | `date` | Não | — | Data de reference. |
| `mode` | `varchar(20)` | Não | `finite` | Atributo funcional de mode. |
| `direction` | `varchar(20)` | Não | `forward` | Atributo funcional de direction. |
| `sequencing_rule` | `varchar(40)` | Não | `priority_due_date` | Atributo funcional de sequencing rule. |
| `parameters` | `json` | Sim | — | Dados estruturados de parameters em JSON. |
| `source_run_key` | `varchar(64)` | Sim | — | Chave funcional de source run key. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `approved_at` | `timestamp` | Sim | — | Data e hora de approved. |
| `published_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `published_at` | `timestamp` | Sim | — | Data e hora de published. |
| `change_reason` | `text` | Sim | — | Atributo funcional de change reason. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |


## Produção, MES e qualidade

Documento funcional: [08-producao-mes-e-qualidade.md](08-producao-mes-e-qualidade.md).

### `production_material_consumption_reversals`

**Finalidade:** Registros funcionais de production material consumption reversals.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_material_consumption_id` | `bigint unsigned` | Não | — | Referência a `production_order_material_consumptions.id`. |
| `original_ledger_movement_id` | `bigint unsigned` | Não | — | Referência a `stock_ledger_movements.id`. |
| `reversal_ledger_movement_id` | `bigint unsigned` | Não | — | Referência a `stock_ledger_movements.id`. |
| `quantity` | `decimal(18,6)` | Não | — | Quantidade associada. |
| `reason` | `varchar(255)` | Não | — | Motivo da ação. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_operation_events`

**Finalidade:** Eventos de execução MES.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_operation_id` | `bigint unsigned` | Não | — | Referência a `production_order_operations.id`. |
| `event_type` | `varchar(20)` | Não | — | Tipo ou classificação de event type. |
| `idempotency_key` | `varchar(120)` | Não | — | Chave funcional de idempotency key. |
| `occurred_at` | `timestamp` | Não | — | Data e hora de occurred. |
| `operator_id` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `production_resource_id` | `bigint unsigned` | Sim | — | Referência a `production_resources.id`. |
| `reason_code` | `varchar(80)` | Sim | — | Atributo funcional de reason code. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_operation_outputs`

**Finalidade:** Resultados por operação.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_operation_id` | `bigint unsigned` | Não | — | Referência a `production_order_operations.id`. |
| `quantity_good` | `decimal(18,6)` | Não | `0.000000` | Quantidade de good. |
| `quantity_scrapped` | `decimal(18,6)` | Não | `0.000000` | Quantidade de scrapped. |
| `quantity_rework` | `decimal(18,6)` | Não | `0.000000` | Quantidade de rework. |
| `lot_number` | `varchar(80)` | Sim | — | Número funcional de lot number. |
| `inspection_status` | `varchar(20)` | Não | `PENDING` | Atributo funcional de inspection status. |
| `scrap_cause_code` | `varchar(80)` | Sim | — | Atributo funcional de scrap cause code. |
| `destination` | `varchar(30)` | Sim | — | Atributo funcional de destination. |
| `operator_id` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `production_resource_id` | `bigint unsigned` | Sim | — | Referência a `production_resources.id`. |
| `reported_at` | `timestamp` | Não | — | Data e hora de reported. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_order_bom_item_snapshots`

**Finalidade:** Cópia histórica e imutável de production order bom item.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_bom_snapshot_id` | `bigint unsigned` | Não | — | Referência a `production_order_bom_snapshots.id`. |
| `source_bom_header_id` | `bigint unsigned` | Não | — | Identificador relacionado a source bom header. |
| `source_bom_version_number` | `int unsigned` | Não | — | Versão de source bom version number. |
| `parent_product_id` | `bigint unsigned` | Não | — | Identificador relacionado a parent product. |
| `component_product_id` | `bigint unsigned` | Não | — | Identificador relacionado a component product. |
| `unit_id` | `bigint unsigned` | Sim | — | Identificador relacionado a unit. |
| `line_no` | `int unsigned` | Não | — | Atributo funcional de line no. |
| `level` | `int unsigned` | Não | — | Atributo funcional de level. |
| `quantity_per` | `decimal(18,6)` | Não | — | Quantidade de per. |
| `quantity_required` | `decimal(18,6)` | Não | — | Quantidade de required. |
| `quantity_accumulated` | `decimal(24,6)` | Não | — | Quantidade de accumulated. |
| `path` | `varchar(4000)` | Não | — | Atributo funcional de path. |
| `is_cycle` | `tinyint(1)` | Não | `0` | Indicador booleano de is cycle. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_order_bom_snapshots`

**Finalidade:** Cópia histórica e imutável de production order bom.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_id` | `bigint unsigned` | Não | — | Identificador relacionado a production order. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `production_order_quantity` | `decimal(18,6)` | Não | `1.000000` | Quantidade de production order quantity. |
| `reference_date` | `date` | Não | — | Data de reference. |
| `source_bom_header_id` | `bigint unsigned` | Sim | — | Referência a `bom_headers.id`. |
| `source_bom_version_number` | `int unsigned` | Não | — | Versão de source bom version number. |
| `snapshot_hash` | `varchar(64)` | Não | — | Atributo funcional de snapshot hash. |
| `has_cycle` | `tinyint(1)` | Não | `0` | Indicador booleano de has cycle. |
| `frozen_at` | `timestamp` | Não | — | Data e hora de frozen. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_order_material_consumptions`

**Finalidade:** Consumos reais de materiais.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_id` | `bigint unsigned` | Não | — | Referência a `production_orders.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `warehouse_id` | `bigint unsigned` | Não | — | Referência a `warehouses.id`. |
| `lot_number` | `varchar(80)` | Sim | — | Número funcional de lot number. |
| `quantity_consumed` | `decimal(18,6)` | Não | — | Quantidade de consumed. |
| `quantity_scrapped` | `decimal(18,6)` | Não | `0.000000` | Quantidade de scrapped. |
| `ledger_movement_id` | `bigint unsigned` | Sim | — | Identificador relacionado a ledger movement. |
| `reference_bom_component_id` | `varchar(64)` | Sim | — | Identificador relacionado a reference bom component. |
| `consumed_at` | `timestamp` | Não | — | Data e hora de consumed. |
| `operator_id` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |
| `production_order_operation_id` | `bigint unsigned` | Sim | — | Referência a `production_order_operations.id`. |
| `idempotency_key` | `varchar(120)` | Sim | — | Chave funcional de idempotency key. |
| `reversed_by_movement_id` | `bigint unsigned` | Sim | — | Identificador relacionado a reversed by movement. |

### `production_order_operations`

**Finalidade:** Operações executáveis das ordens.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_id` | `bigint unsigned` | Não | — | Referência a `production_orders.id`. |
| `production_order_routing_operation_snapshot_id` | `bigint unsigned` | Sim | — | Referência a `production_order_routing_operation_snapshots.id`. |
| `routing_operation_id` | `bigint unsigned` | Sim | — | Identificador relacionado a routing operation. |
| `standard_time_id` | `bigint unsigned` | Sim | — | Identificador relacionado a standard time. |
| `standard_time_version` | `int unsigned` | Sim | — | Versão de standard time version. |
| `operation_no` | `int unsigned` | Não | — | Atributo funcional de operation no. |
| `operation_code` | `varchar(50)` | Não | — | Atributo funcional de operation code. |
| `operation_name` | `varchar(150)` | Não | — | Atributo funcional de operation name. |
| `sequence` | `int unsigned` | Não | — | Atributo funcional de sequence. |
| `work_center_id` | `bigint unsigned` | Não | — | Referência a `work_centers.id`. |
| `production_resource_id` | `bigint unsigned` | Sim | — | Referência a `production_resources.id`. |
| `status` | `varchar(20)` | Não | `PLANNED` | Estado atual no workflow. |
| `quantity_planned` | `decimal(18,6)` | Não | — | Quantidade de planned. |
| `setup_scope` | `varchar(20)` | Não | `ROUTING` | Atributo funcional de setup scope. |
| `setup_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de setup time. |
| `runtime_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de runtime time. |
| `queue_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de queue time. |
| `move_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de move time. |
| `productive_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de productive time. |
| `lead_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de lead time. |
| `total_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de total time. |
| `planned_start_at` | `datetime` | Sim | — | Data e hora de planned start. |
| `planned_end_at` | `datetime` | Sim | — | Data e hora de planned end. |
| `calculation_metadata` | `json` | Sim | — | Dados estruturados de calculation metadata em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |
| `actual_production_resource_id` | `bigint unsigned` | Sim | — | Referência a `production_resources.id`. |
| `operator_id` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `quantity_processed` | `decimal(18,6)` | Não | `0.000000` | Quantidade de processed. |
| `quantity_good` | `decimal(18,6)` | Não | `0.000000` | Quantidade de good. |
| `quantity_scrapped` | `decimal(18,6)` | Não | `0.000000` | Quantidade de scrapped. |
| `quantity_rework` | `decimal(18,6)` | Não | `0.000000` | Quantidade de rework. |
| `actual_productive_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de actual productive. |
| `actual_pause_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de actual pause. |
| `actual_started_at` | `datetime` | Sim | — | Data e hora de actual started. |
| `actual_completed_at` | `datetime` | Sim | — | Data e hora de actual completed. |

### `production_order_outputs` (removida)

**Finalidade:** Estrutura legada consolidada em `production_operation_outputs`.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_id` | `bigint unsigned` | Não | — | Referência a `production_orders.id`. |
| `quantity_completed` | `decimal(18,6)` | Não | — | Quantidade de completed. |
| `quantity_scrapped` | `decimal(18,6)` | Não | `0.000000` | Quantidade de scrapped. |
| `operation_no` | `int unsigned` | Sim | — | Atributo funcional de operation no. |
| `lot_number` | `varchar(80)` | Sim | — | Número funcional de lot number. |
| `produced_at` | `timestamp` | Não | — | Data e hora de produced. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |
| `work_center_id` | `bigint unsigned` | Sim | — | Referência a `work_centers.id`. |
| `setup_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de setup time. |
| `process_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de process time. |
| `inspection_status` | `varchar(20)` | Não | `APPROVED` | Atributo funcional de inspection status. |
| `inspected_at` | `timestamp` | Sim | — | Data e hora de inspected. |
| `inspection_notes` | `text` | Sim | — | Atributo funcional de inspection notes. |

### `production_order_routing_operation_snapshots`

**Finalidade:** Cópia histórica e imutável de production order routing operation.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_snapshot_id` | `bigint unsigned` | Não | — | Referência a `production_order_snapshots.id`. |
| `routing_version_id` | `bigint unsigned` | Não | — | Identificador relacionado a routing version. |
| `standard_time_id` | `bigint unsigned` | Sim | — | Identificador relacionado a standard time. |
| `standard_time_version` | `int unsigned` | Sim | — | Versão de standard time version. |
| `work_center_id` | `bigint unsigned` | Não | — | Identificador relacionado a work center. |
| `operation_no` | `int unsigned` | Não | — | Atributo funcional de operation no. |
| `operation_code` | `varchar(50)` | Não | — | Atributo funcional de operation code. |
| `operation_name` | `varchar(150)` | Não | — | Atributo funcional de operation name. |
| `sequence` | `int unsigned` | Não | — | Atributo funcional de sequence. |
| `setup_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de setup time. |
| `runtime_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de runtime. |
| `queue_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de queue time. |
| `move_time_minutes` | `decimal(10,2)` | Não | `0.00` | Duração em minutos de move time. |
| `is_outsourced` | `tinyint(1)` | Não | `0` | Indicador booleano de is outsourced. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_order_snapshots`

**Finalidade:** Cópia histórica e imutável de production order.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_id` | `bigint unsigned` | Não | — | Referência a `production_orders.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `bom_snapshot_id` | `bigint unsigned` | Sim | — | Referência a `production_order_bom_snapshots.id`. |
| `bom_header_id` | `bigint unsigned` | Sim | — | Identificador relacionado a bom header. |
| `bom_version_number` | `int unsigned` | Sim | — | Versão de bom version number. |
| `routing_version_snapshot_id` | `bigint unsigned` | Sim | — | Identificador relacionado a routing version snapshot. |
| `routing_version_id` | `bigint unsigned` | Sim | — | Identificador relacionado a routing version. |
| `routing_version_number` | `int unsigned` | Sim | — | Versão de routing version number. |
| `quantity_planned` | `decimal(18,6)` | Não | — | Quantidade de planned. |
| `quantity_scrapped_target` | `decimal(18,6)` | Não | `0.000000` | Quantidade de scrapped target. |
| `snapshot_hash` | `varchar(64)` | Não | — | Atributo funcional de snapshot hash. |
| `frozen_at` | `timestamp` | Não | — | Data e hora de frozen. |
| `frozen_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_orders`

**Finalidade:** Ordens de produção.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `product_id` | `bigint unsigned` | Não | — | Referência a `products.id`. |
| `warehouse_id` | `bigint unsigned` | Sim | — | Referência a `warehouses.id`. |
| `bom_header_id` | `bigint unsigned` | Sim | — | Identificador relacionado a bom header. |
| `bom_version_number` | `int unsigned` | Sim | — | Versão de bom version number. |
| `routing_version_id` | `bigint unsigned` | Sim | — | Identificador relacionado a routing version. |
| `routing_version_number` | `int unsigned` | Sim | — | Versão de routing version number. |
| `source_type` | `varchar(20)` | Não | — | Tipo ou classificação de source type. |
| `source_reference_id` | `bigint unsigned` | Sim | — | Identificador relacionado a source reference. |
| `source_reference_type` | `varchar(120)` | Sim | — | Tipo ou classificação de source reference type. |
| `order_number` | `varchar(50)` | Não | — | Número funcional de order number. |
| `status` | `varchar(20)` | Não | `DRAFT` | Estado atual no workflow. |
| `quantity_planned` | `decimal(18,6)` | Não | — | Quantidade de planned. |
| `quantity_produced` | `decimal(18,6)` | Não | `0.000000` | Quantidade de produced. |
| `quantity_scrapped` | `decimal(18,6)` | Não | `0.000000` | Quantidade de scrapped. |
| `scheduled_start_date` | `date` | Sim | — | Data de scheduled start. |
| `scheduled_end_date` | `date` | Sim | — | Data de scheduled end. |
| `released_at` | `timestamp` | Sim | — | Data e hora de released. |
| `started_at` | `timestamp` | Sim | — | Data e hora de started. |
| `completed_at` | `timestamp` | Sim | — | Data e hora de completed. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `released_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `completed_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_quality_records`

**Finalidade:** Registros de qualidade.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_operation_id` | `bigint unsigned` | Não | — | Referência a `production_order_operations.id`. |
| `record_type` | `varchar(30)` | Não | `NON_CONFORMITY` | Tipo ou classificação de record type. |
| `status` | `varchar(30)` | Não | `PENDING` | Estado atual no workflow. |
| `quantity` | `decimal(18,6)` | Não | `0.000000` | Quantidade associada. |
| `cause_code` | `varchar(80)` | Sim | — | Atributo funcional de cause code. |
| `destination` | `varchar(30)` | Sim | — | Atributo funcional de destination. |
| `operator_id` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `production_resource_id` | `bigint unsigned` | Sim | — | Referência a `production_resources.id`. |
| `notes` | `text` | Sim | — | Observações livres. |
| `metadata` | `json` | Sim | — | Metadados adicionais em JSON. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |

### `production_rework_orders`

**Finalidade:** Ordens de retrabalho.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `source_production_order_operation_id` | `bigint unsigned` | Não | — | Referência a `production_order_operations.id`. |
| `rework_production_order_operation_id` | `bigint unsigned` | Sim | — | Referência a `production_order_operations.id`. |
| `quantity` | `decimal(18,6)` | Não | — | Quantidade associada. |
| `status` | `varchar(20)` | Não | `OPEN` | Estado atual no workflow. |
| `reason_code` | `varchar(80)` | Sim | — | Atributo funcional de reason code. |
| `notes` | `text` | Sim | — | Observações livres. |
| `created_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `completed_at` | `timestamp` | Sim | — | Data e hora de completed. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |


## Análise e relatórios

Documento funcional: [09-analise-e-relatorios.md](09-analise-e-relatorios.md).

### `manufacturing_analytics_recommendations`

**Finalidade:** Recomendações de revisão de tempos.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `company_id` | `bigint unsigned` | Não | — | Referência a `companies.id`. |
| `production_order_operation_id` | `bigint unsigned` | Sim | — | Referência a `production_order_operations.id`. |
| `routing_operation_id` | `bigint unsigned` | Sim | — | Identificador relacionado a routing operation. |
| `standard_time_id` | `bigint unsigned` | Sim | — | Identificador relacionado a standard time. |
| `standard_time_version` | `int unsigned` | Sim | — | Versão de standard time version. |
| `status` | `varchar(20)` | Não | `PENDING` | Estado atual no workflow. |
| `current_time_minutes` | `decimal(10,2)` | Sim | — | Duração em minutos de current time. |
| `suggested_time_minutes` | `decimal(10,2)` | Sim | — | Duração em minutos de suggested time. |
| `sample_size` | `int unsigned` | Não | `0` | Atributo funcional de sample size. |
| `statistics` | `json` | Sim | — | Dados estruturados de statistics em JSON. |
| `filters` | `json` | Sim | — | Dados estruturados de filters em JSON. |
| `decision_reason` | `text` | Sim | — | Atributo funcional de decision reason. |
| `decided_by` | `bigint unsigned` | Sim | — | Referência a `users.id`. |
| `decided_at` | `timestamp` | Sim | — | Data e hora de decided. |
| `created_at` | `timestamp` | Sim | — | Data e hora de criação. |
| `updated_at` | `timestamp` | Sim | — | Data e hora da atualização. |


## APIs e operação técnica

Documento funcional: [10-apis-e-operacao-tecnica.md](10-apis-e-operacao-tecnica.md).

### `cache`

**Finalidade:** Registros funcionais de cache.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `key` | `varchar(255)` | Não | — | Chave funcional de key. |
| `value` | `mediumtext` | Não | — | Atributo funcional de value. |
| `expiration` | `int` | Não | — | Atributo funcional de expiration. |

### `cache_locks`

**Finalidade:** Registros funcionais de cache locks.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `key` | `varchar(255)` | Não | — | Chave funcional de key. |
| `owner` | `varchar(255)` | Não | — | Atributo funcional de owner. |
| `expiration` | `int` | Não | — | Atributo funcional de expiration. |

### `failed_jobs`

**Finalidade:** Jobs encerrados com falha.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `uuid` | `varchar(255)` | Não | — | Atributo funcional de uuid. |
| `connection` | `text` | Não | — | Atributo funcional de connection. |
| `queue` | `text` | Não | — | Atributo funcional de queue. |
| `payload` | `longtext` | Não | — | Conteúdo adicional em JSON. |
| `exception` | `longtext` | Não | — | Atributo funcional de exception. |
| `failed_at` | `timestamp; DEFAULT_GENERATED` | Não | `CURRENT_TIMESTAMP` | Data e hora de failed. |

### `job_batches`

**Finalidade:** Registros funcionais de job batches.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `varchar(255)` | Não | — | Identificador único. |
| `name` | `varchar(255)` | Não | — | Nome. |
| `total_jobs` | `int` | Não | — | Atributo funcional de total jobs. |
| `pending_jobs` | `int` | Não | — | Atributo funcional de pending jobs. |
| `failed_jobs` | `int` | Não | — | Atributo funcional de failed jobs. |
| `failed_job_ids` | `longtext` | Não | — | Atributo funcional de failed job ids. |
| `options` | `mediumtext` | Sim | — | Atributo funcional de options. |
| `cancelled_at` | `int` | Sim | — | Data e hora de cancelled. |
| `created_at` | `int` | Não | — | Data e hora de criação. |
| `finished_at` | `int` | Sim | — | Data e hora de finished. |

### `jobs`

**Finalidade:** Jobs aguardando processamento.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `queue` | `varchar(255)` | Não | — | Atributo funcional de queue. |
| `payload` | `longtext` | Não | — | Conteúdo adicional em JSON. |
| `attempts` | `tinyint unsigned` | Não | — | Atributo funcional de attempts. |
| `reserved_at` | `int unsigned` | Sim | — | Data e hora de reserved. |
| `available_at` | `int unsigned` | Não | — | Data e hora de available. |
| `created_at` | `int unsigned` | Não | — | Data e hora de criação. |

### `migrations`

**Finalidade:** Migrations aplicadas.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `int unsigned; auto_increment` | Não | — | Identificador único. |
| `migration` | `varchar(255)` | Não | — | Atributo funcional de migration. |
| `batch` | `int` | Não | — | Atributo funcional de batch. |

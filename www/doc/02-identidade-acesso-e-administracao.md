# Identidade, acesso e administração

## Objetivo

Controlar autenticação, sessões, segurança e autorização dos usuários, além dos cadastros administrativos compartilhados pelos domínios operacionais.

## Identidade e segurança

- Autenticação web, Sanctum e JWT.
- Logout, consulta do usuário autenticado e renovação de token JWT.
- Recuperação de senha e verificação de e-mail.
- Desafio MFA e gerenciamento das sessões abertas pelo usuário.
- Preferência de idioma e proteção de rotas autenticadas.

## RBAC e governança

- Papéis e permissões associados ao usuário dentro de uma empresa.
- Permissões granulares por ação e módulo, verificadas em rotas web e API.
- Console web para usuários, acessos e perfis RBAC da empresa.
- Identificação do administrador da empresa e proteção contra remoção do último administrador ativo.
- Registro de auditoria para ações administrativas e transições relevantes.

## Cadastros administrativos

- Unidades de medida globais ou da empresa.
- Categorias e marcas.
- Plantas e armazéns com ativação/inativação e vínculo organizacional.
- Tutoriais contextuais por rota, editáveis por administradores autorizados.

## Entidades principais

- `users`, `roles`, `permissions`, `role_user` e `permission_role`.
- `audit_logs`, `sessions` e estruturas de autenticação.
- `units`, `categories`, `brands`, `plants` e `warehouses`.

## Regras importantes

- A autorização funcional não depende apenas de esconder opções na interface; os controllers e middlewares validam a permissão.
- Cadastros referenciados por transações podem ter exclusão bloqueada e devem ser inativados quando aplicável.

## Dicionário de dados

As tabelas abaixo documentam o schema corrente do domínio. “Nula” informa se a coluna aceita `NULL`; “—” indica ausência de valor padrão explícito.

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

### `permission_role`

**Finalidade:** Registros funcionais de permission role.

| Coluna | Tipo | Nula | Padrão | Descrição |
| --- | --- | --- | --- | --- |
| `id` | `bigint unsigned; auto_increment` | Não | — | Identificador único. |
| `permission_id` | `bigint unsigned` | Não | — | Referência a `permissions.id`. |
| `role_id` | `bigint unsigned` | Não | — | Referência a `roles.id`. |
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

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
- Sobrescritas individuais de permissão por usuário quando configuradas.

## Cadastros administrativos

- Unidades de medida globais ou da empresa.
- Categorias e marcas.
- Plantas e armazéns com ativação/inativação e vínculo organizacional.
- Tutoriais contextuais por rota, editáveis por administradores autorizados.

## Entidades principais

- `users`, `roles`, `permissions`, `role_user`, `permission_role` e `permission_user_overrides`.
- `audit_logs`, `sessions` e estruturas de autenticação.
- `units`, `categories`, `brands`, `plants` e `warehouses`.

## Regras importantes

- A autorização funcional não depende apenas de esconder opções na interface; os controllers e middlewares validam a permissão.
- Cadastros referenciados por transações podem ter exclusão bloqueada e devem ser inativados quando aplicável.

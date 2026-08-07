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
- Catálogos de interface em `pt_BR`, `en` e `es`, selecionados por `users.preferred_locale`.
- Mensagens necessárias ao JavaScript são serializadas pelo layout no idioma ativo, evitando textos fixos no bundle do frontend.
- A máscara de duração converte entradas `HH:MM` em minutos antes do envio, mantendo compatibilidade com validações, serviços e contratos existentes.

## Representação de ordens de produção

- A serialização de uma OP inclui o atributo calculado `sales_order_reference`.
- O atributo retorna `#<source_reference_id>` somente quando `source_reference_type` é `sale` ou `sales_order`; nas demais origens retorna `null`.

## Limitações atuais

- Webhooks externos padronizados, limites de uso por cliente e SLO por endpoint não estão implementados como plataforma completa.
- A documentação formal OpenAPI não é gerada automaticamente pelo projeto.

## Dicionário de dados

As tabelas abaixo documentam o schema corrente do domínio. “Nula” informa se a coluna aceita `NULL`; “—” indica ausência de valor padrão explícito.

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

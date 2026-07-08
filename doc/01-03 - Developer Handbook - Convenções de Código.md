# Developer Handbook

## 1. Objetivo

Este handbook define padrões obrigatórios de código para o sistema industrial MRP em Laravel (monólito modular), garantindo consistência, rastreabilidade e segurança para cenários de alta criticidade.

Escopo:
- Back-end Laravel
- Banco SQL Server
- Processamento assíncrono com Redis Queue
- Módulos de domínio industrial (MRP, BOM, Routing, Inventory, Production, MES, Genealogy, ECO, Purchasing)

---

## 2. Princípios Arquiteturais

Regras obrigatórias:
- Modular monolith com bounded contexts por módulo.
- Separação estrita entre Domain, Application, Infrastructure e Presentation.
- Controllers sem regra de negócio.
- Regras de negócio somente em Services/Actions.
- Persistência abstraída por Repositories.
- DTO como contrato de entrada/saída da camada Application.
- Eventos de domínio para desacoplamento e auditabilidade.
- Snapshots e versionamento imutáveis para rastreabilidade industrial.

---

## 3. Convenções de Nomenclatura

### 3.1 Tabelas (SQL Server)

Regras:
- Sempre `snake_case`.
- Sempre no plural.
- Prefixar por contexto quando necessário para evitar ambiguidade.
- Tabelas de snapshot devem terminar com `_snapshots`.
- Tabelas de versionamento devem terminar com `_versions`.
- Tabelas pivot devem usar ordem alfabética dos nomes.

Exemplos:
- `products`
- `product_versions`
- `bom_headers`
- `bom_items`
- `production_order_snapshots`
- `product_warehouse`

### 3.2 Colunas

Regras:
- `snake_case`.
- Chave primária padrão: `id` (bigint).
- Chaves estrangeiras: `{entidade_singular}_id`.
- Datas de vigência: `effective_from`, `effective_to`.
- Auditoria mínima: `created_at`, `updated_at`, `created_by`, `updated_by`.
- Soft delete apenas quando regra de negócio permitir; senão usar estado lógico.

### 3.3 Services

Regras:
- Sufixo obrigatório: `Service`.
- Nome orientado a capacidade de negócio.
- Um service por agregado/caso de uso principal.

Exemplos:
- `MRPPlanningService`
- `BOMExplosionService`
- `ProductionSnapshotService`

### 3.4 DTOs

Regras:
- Sufixo obrigatório: `DTO`.
- Imutáveis (`readonly` em PHP 8.3 quando aplicável).
- Sem comportamento de domínio, apenas transporte de dados.

Exemplos:
- `CreateProductDTO`
- `ExplodedRequirementDTO`
- `ProductionOrderSnapshotDTO`

### 3.5 Repositories

Regras:
- Interfaces em Domain ou Application; implementação em Infrastructure.
- Sufixo obrigatório: `Repository`.
- Métodos com intenção explícita.

Exemplos:
- `ProductRepository`
- `BOMRepository`
- `ProductionOrderRepository`

---

## 4. Regras de Estrutura de Pastas

Padrão por módulo:

```text
app/Modules/<ModuleName>/
  Domain/
    Entities/
    Repositories/
    Services/
    Events/
  Application/
    DTO/
    Actions/
    Services/
    Jobs/
  Infrastructure/
    Persistence/Repositories/
    Cache/
    Queue/
    Integrations/
  Presentation/
    Http/Controllers/
    Http/Requests/
    Http/Resources/
```

Regras obrigatórias:
- Não acessar classes de Infrastructure diretamente a partir de Presentation.
- Domain não depende de Framework.
- Application pode orquestrar Domain e contratos.
- Shared contém componentes transversais reutilizáveis.

---

## 5. Regras de Service (Controller sem lógica)

Controllers devem:
- Validar entrada via Form Request.
- Instanciar DTO de entrada.
- Chamar Service/Action.
- Retornar resposta padronizada.

Controllers não devem:
- Conter regras de cálculo de MRP/BOM/scheduling.
- Abrir transações.
- Executar SQL direto.
- Manipular cache diretamente.

Services devem:
- Implementar regras de negócio.
- Coordenar transação, repositório, cache e eventos.
- Ser idempotentes quando aplicável.

---

## 6. Regras de Repository

Repositories devem:
- Encapsular queries e mapeamento de persistência.
- Expor operações orientadas ao domínio.
- Retornar entidades ou DTOs claramente definidos.

Repositories não devem:
- Conter regras de negócio industrial.
- Conhecer detalhes de transporte HTTP.

Boas práticas:
- Métodos específicos em vez de repositório genérico excessivo.
- Evitar N+1 com `eager loading`.
- Preferir paginação em consultas de leitura.

---

## 7. Regras de DTO

Regras:
- DTO obrigatório na fronteira Application.
- DTO de entrada separado de DTO de saída.
- Não reutilizar DTO de escrita para leitura complexa.
- Não usar Model Eloquent como payload de API.

Padrão:
- `fromArray(array $data): static`
- `toArray(): array`

Validação:
- Regras de formato em Form Request.
- Regras de negócio em Service.

---

## 8. Regras de Eventos

Tipos:
- Evento de domínio: representa fato de negócio.
- Evento de integração: comunicação com outros módulos internos.

Regras:
- Nome no passado: `ProductionOrderReleased`.
- Payload mínimo e estável.
- Evento deve carregar `event_id`, `occurred_at` e metadados de rastreio.
- Listeners devem ser idempotentes.

Quando usar:
- Pós-commit para efeitos colaterais.
- Atualização de projeções, notificações, auditoria, cache invalidation.

Quando não usar:
- Fluxo síncrono crítico que exige retorno imediato.

---

## 9. Regras de Filas (Queue)

Regras obrigatórias:
- Jobs com sufixo `Job` e payload serializável.
- Definir `tries`, `timeout`, `backoff` quando aplicável.
- Garantir idempotência por chave de negócio.
- Usar `after_commit=true` para não processar dados não confirmados.

Nomenclatura de filas:
- `mrp-high`
- `mrp-default`
- `mrp-low`

Uso recomendado:
- Explosão massiva de BOM
- Reprocessamento incremental de MRP
- Cálculos de capacidade
- Geração de projeções/relatórios

---

## 10. SQL Server Best Practices

Modelagem e integridade:
- Definir PK/FK explícitas e índices para FKs.
- Usar `rowversion` para concorrência otimista quando necessário.
- Normalizar dados transacionais; desnormalizar apenas para leitura analítica.

Performance:
- Indexar colunas de busca por SKU, datas de vigência e status.
- Evitar `SELECT *`.
- Usar paginação por chave para grandes volumes quando viável.
- Monitorar planos de execução e regressões.

Transações e bloqueios:
- Transações curtas.
- Ordem consistente de lock para evitar deadlocks.
- Usar isolamento apropriado para leituras críticas.

CTE recursiva:
- Limitar profundidade máxima operacional.
- Detectar ciclos explicitamente.
- Materializar resultados intermediários para cenários pesados.

---

## 11. Regras de Recursão para BOM

Regras obrigatórias:
- Toda explosão deve validar circularidade.
- Definir `max_depth` configurável por ambiente.
- Calcular quantidade acumulada multiplicando fatores por nível.
- Considerar `scrap_factor` em cada aresta da árvore.
- Resolver versão por data de referência (`effective_from/to`).

Contrato de saída mínimo:
- item
- nível
- quantidade base
- quantidade acumulada
- caminho de rastreio
- versão aplicada

---

## 12. Regras de Snapshot

Objetivo:
- Congelar estado operacional usado na execução industrial.

Regras obrigatórias:
- Snapshot criado no momento de liberação da OP.
- Imutável após criação.
- Mudanças de engenharia futuras não alteram snapshot histórico.
- Snapshot inclui BOM, routing, tempos, recursos e parâmetros de produção.
- Snapshot deve conter hash de integridade e metadados de versão.

Auditoria:
- Registrar `snapshot_created_at`, `snapshot_created_by`, `source_version_ids`.

---

## 13. Regras de Versionamento

Escopo:
- Produto, BOM, Routing e demais artefatos de engenharia.

Regras obrigatórias:
- Versão aprovada é imutável.
- Qualquer alteração cria nova versão.
- Controle de vigência por data (`effective_from/to`).
- Apenas uma versão efetiva por intervalo e contexto.
- Status mínimos: `draft`, `approved`, `obsolete`.

Seleção de versão:
- Sempre por data de referência + contexto (empresa/planta).
- Em produção, usar versão congelada por snapshot.

Compatibilidade:
- Regras explícitas para substituição entre revisões.
- Bloquear mistura de revisões incompatíveis sem ECO aprovado.

---

## 14. API e Erro Padronizado

Formato de sucesso:
- `success`
- `message`
- `data`
- `meta`
- `errors`
- `timestamp`

Formato de erro:
- Código HTTP semântico.
- Mensagem funcional para cliente.
- `errors` detalhado para validação.
- `trace_id` para observabilidade cruzada.

---

## 15. Observabilidade e Auditoria

Regras obrigatórias:
- Logs estruturados em JSON.
- Correlação por `trace_id` em request, job e evento.
- Log de SQL em canal dedicado para troubleshooting.
- Auditoria de ações críticas de engenharia e produção.

Eventos auditáveis mínimos:
- aprovação de versão
- liberação de OP
- criação de snapshot
- ajustes de estoque
- alterações de parâmetros MRP

---

## 16. Checklist de Pull Request

Todo PR deve comprovar:
- Controller sem regra de negócio.
- Service testado para regras críticas.
- Repository sem vazamento de regra de negócio.
- DTOs usados na fronteira correta.
- Eventos e jobs idempotentes.
- Queries com índice e paginação apropriada.
- Recursão BOM com proteção de ciclo.
- Snapshot e versionamento respeitando imutabilidade.

---

## 17. Política de Exceções

Desvios deste handbook somente com:
- ADR (Architecture Decision Record) aprovado.
- justificativa técnica documentada.
- plano de rollback.

Sem ADR aprovado, a convenção é obrigatória.

# Mapa de Cobertura - Engenharia, PCP, MES e Analise

## Objetivo

Registrar o estado real do sistema para os quatro blocos funcionais solicitados e separar claramente cadastro, planejamento, execucao e indicadores.

## Resumo executivo

| Modulo | Estado atual | Leitura |
| --- | --- | --- |
| Engenharia | Parcial avancado | Produto, BOM, routing, centros, recursos e tempos padrao versionados existem no backend/API; pacote integrado de ECO e telas web ainda faltam |
| PCP | Parcial avancado | MRP, explosao, sugestoes e scheduler finito existem; tempos previstos e programa persistido/aplicavel ainda faltam |
| MES | Parcial | OP, saida, refugo, inspeção basica e consumo real existem; falta execução por operação, cronometro, pausas, operador, máquina e retrabalho |
| Analise | Parcial inicial | Há indicadores HTML de produção; faltam comparações previstas/reais completas, dimensões operacionais e histórico estatístico |

## 1. Engenharia de Processos

### Implementado e como funciona

- **Produto:** `Product`/`ProductVersion`, CRUD tenant, ciclo de vida, atributos estendidos, unidades alternativas, variações e kits.
- **BOM:** `bom_headers` e `bom_items`, com versão, vigência, status `DRAFT/APPROVED/OBSOLETE`, aprovação e validação de sobreposição. `BomExplosionService` executa explosão recursiva, detecta ciclos e é usado pelo MRP. Na criação/liberação da OP, a estrutura é congelada em snapshots.
- **Roteiro:** `routing_versions` e `routing_operations`, com aprovação, vigência, sequência, centro de trabalho e tempos de setup, processo, fila e movimentação. A aprovação cria snapshot imutável.
- **Centro de trabalho:** `work_centers`, turnos e calendário por dia, com capacidade diária e fator de eficiência.
- **Operações:** cadastradas dentro de uma versão de routing e vinculadas a um centro de trabalho.

### Faltante ou a atualizar

- **Recursos:** `production_resources` representa máquina/equipamento/ferramenta/linha por centro, com status, capacidade, eficiência e vigência. Operador individual continua opcional.
- **Valor hora:** `work_center_hour_rates` mantém valor hora do centro por vigência; o custo individual de operador não faz parte do escopo.
- **Tempos padrão:** `routing_operation_standard_times` possui versões, aprovação, vigência, runtime informado por processo, setup único por roteiro, lead time de fila/movimentação e exclusão de terceirização do cálculo interno.
- **Governança:** ECO já aceita `STANDARD_TIME`, valida alvos no tenant e calcula impacto em OPs abertas; ainda falta vincular e aplicar Produto, BOM, routing, recursos e tempos em uma mudança única.

## 2. PCP

### Implementado e como funciona

- **Geração da OP:** manual e via MRP (`source_type` `MANUAL`/`MRP`). A OP conserva produto, quantidade, datas, BOM/routing e snapshots.
- **Explosão:** aplicada no endpoint de BOM e no `MrpPlanningService`, com demanda, explosão, estoque, necessidade líquida e lead time.
- **MRP:** gera sugestões de compra e produção, alertas de estoque mínimo, buckets de tempo e recalculo incremental com cache/idempotência.
- **Capacidade e programação:** `ProductionSchedulingService` aceita modo finito/infinito, direção forward/backward e regras de sequenciamento; usa turnos, calendário, capacidade diária e fator de eficiência.

### Faltante ou a atualizar

- O scheduler não persiste um programa versionado nem aplica o resultado às OPs; a execução web guarda o resultado temporariamente em cache.
- A duração calculada soma os tempos do routing, mas não materializa tempos previstos por operação nem os multiplica pela quantidade/lote da OP.
- A capacidade não considera máquinas individuais, operadores, conflitos reais, paradas, setup dependente de troca, feriados/exceções abrangentes ou cenários comparáveis.
- Falta workflow de aprovação/conversão das sugestões MRP e reprogramação controlada.

## 3. MES / Chão de Fábrica

### Implementado e como funciona

- OP possui estados `DRAFT`, `RELEASED`, `IN_PROGRESS`, `PARTIALLY_COMPLETED`, `COMPLETED` e `CANCELLED`.
- `production_order_outputs` registra quantidade concluída, refugo, operação, centro de trabalho, tempos informados, inspeção, lote e data.
- `MaterialConsumptionService` registra consumo real, baixa o estoque com movimento `ISSUE`, guarda lote, operador opcional e referência ao componente da BOM snapshot.
- Há inspeção simples de saída e recebimento de produto acabado no estoque.

### Faltante ou a atualizar

- Criar operações executáveis da OP, com status, quantidade prevista, quantidade boa/refugo, tempo padrão e vínculo ao snapshot.
- Criar eventos de início, pausa, retomada e término; o `started_at`/`completed_at` atual é somente da OP.
- Persistir cronômetro no servidor, evitando depender do relógio do navegador.
- Criar cadastro/vínculo de operador, máquina/recurso e motivo de parada/refugo.
- Implementar retrabalho como fluxo rastreável, com origem, operação, quantidade, causa e encerramento.
- Validar consumo contra a BOM congelada, permitir estorno controlado e manter auditoria de exceções.

## 4. Análise

### Implementado e como funciona

A rota `production/analytics` agrega, em uma janela de 7 a 180 dias, ordens e saídas para mostrar aderência quantidade planejada/produzida, qualidade, setup, processo, status, inspeções, refugo por dia e produtividade agregada por `operation_no`.

### Faltante ou a atualizar

- Previsto x real de tempo por operação, centro, máquina e operador.
- Eficiência por operador, máquina e centro de trabalho; hoje o operador aparece apenas no consumo de material e a máquina não possui entidade.
- Consumo real x previsto derivado da BOM snapshot, com desvios e causas.
- Histórico de tempos padrão para revisão da Engenharia.
- OEE: disponibilidade, performance e qualidade, dependente dos eventos de execução e paradas.
- Camada analítica persistida, filtros dimensionais, exportações e testes de acurácia.

## Ordem recomendada de desenvolvimento

1. Modelar recursos/máquinas, operadores e tempos padrão versionados.
2. Criar operações da OP e eventos de execução (início, pausa, retomada, fim e parada).
3. Persistir tempos previstos por operação e tornar o scheduler aplicável/versionado.
4. Integrar apontamento, consumo, refugo e retrabalho à operação executada.
5. Criar fatos/consultas analíticas para previsto x real, eficiências e consumo.
6. Evoluir para OEE e histórico de revisão dos tempos padrão.

## Referências de código

- `app/Modules/Product`, `app/Modules/Bom` e `app/Modules/Routing`
- `app/Modules/Scheduling` e `app/Modules/MRP`
- `app/Modules/Production`
- `app/Http/Controllers/Web/Tenant/ProductionAnalyticsController.php`
- `database/migrations/2026_06_12_000005` a `2026_06_12_000021` e migrações de extensão de outputs

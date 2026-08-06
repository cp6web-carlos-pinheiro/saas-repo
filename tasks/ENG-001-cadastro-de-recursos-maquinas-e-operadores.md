# ENG-001 - Cadastro de recursos, máquinas e custo hora por centro

## Status de implementação

Implementado no backend/API. Foram criados o cadastro tenant de recursos produtivos, o histórico de valor hora por centro e as permissões associadas. A primeira versão não inclui telas web dedicadas; a operação está disponível pela API REST.

## Objetivo

Criar o domínio de recursos produtivos necessário para representar máquinas, equipamentos, ferramentas e linhas, além de permitir o cálculo de custo por centro de produção. O centro de trabalho atual informa apenas `resource_type`, capacidade diária e fator de eficiência; isso não permite programar ou analisar uma máquina/recurso específico nem congelar o valor hora aplicado ao custo histórico.

O operador individual não será obrigatório nesta primeira versão. O custeio será feito pelo valor hora do centro de produção, e não pelo valor hora de cada pessoa. A identificação do usuário que realizou um apontamento continuará sendo útil para auditoria, mas não será usada para calcular custo.

## Contexto atual

- `work_centers` possui centro, planta, tipo, capacidade diária, fator de eficiência, turnos e calendário.
- Não existem entidades próprias para máquina, recurso ou ferramenta.
- O operador aparece apenas como `operator_id` opcional no consumo de material.
- `production_order_outputs` pode receber `work_center_id`, mas não identifica máquina/recurso.
- Não existe valor hora por centro nem histórico do valor aplicado a uma OP/apontamento.

## Escopo funcional

Implementar:

1. Cadastro de recursos produtivos vinculados a um centro de trabalho e planta.
2. Tipos de recurso, por exemplo máquina, equipamento, ferramenta, linha e recurso terceirizado.
3. Status do recurso: ativo, inativo, manutenção, bloqueado e desativado.
4. Capacidade, calendário, turnos, eficiência nominal e disponibilidade do recurso.
5. Cadastro de valor hora do centro de produção, com vigência, unidade monetária e origem do valor.
6. Congelamento do valor hora utilizado na OP/operação/apontamento para preservar o custo histórico.
7. Identificação opcional do usuário/apontador para auditoria, sem custo individual obrigatório.
8. Permissões, isolamento por tenant e auditoria de alterações.

Ficam fora do escopo inicial, como evolução futura:

- custo hora individual por operador;
- análise de eficiência por operador;
- relacionamento obrigatório operador-recurso;
- validação de habilidade/certificação individual;
- integração com folha de pagamento ou RH.

## Modelo de dados sugerido

- `production_resources`: `company_id`, `plant_id`, `work_center_id`, código, nome, tipo, status, capacidade, eficiência, datas de vigência e metadata.
- `work_center_hour_rates` ou extensão equivalente: centro, valor hora, moeda, vigência, origem, aprovação e metadata.
- Snapshot de custo em `production_order_operations`/apontamento: valor hora aplicado, moeda e referência da tabela de origem.
- Não criar cadastro obrigatório de operador nesta task. Caso o MES precise identificar o usuário, usar o usuário autenticado do apontamento e manter o campo opcional.

## Regras de negócio

- Código de recurso deve ser único dentro da empresa.
- Recurso inativo ou em manutenção não pode ser selecionado para nova programação nem iniciar nova operação.
- Não permitir vínculo entre registros de empresas diferentes.
- Uma operação pode exigir um tipo de recurso, um recurso específico ou qualquer recurso elegível do centro.
- O custo de mão de obra será calculado por `horas elegíveis do centro × valor hora vigente do centro`.
- Alterar o valor hora não pode alterar o custo histórico de OPs/apontamentos já congelados.
- Toda mudança de capacidade, status, recurso ou valor hora deve ser auditável e ter vigência quando afetar planejamento histórico.

## Entregas técnicas

- Migration `2026_08_05_000001_create_production_resources_hour_rates_and_standard_times_tables.php` com `production_resources` e `work_center_hour_rates`.
- Models `ProductionResource` e `WorkCenterHourRate`, com relacionamentos em `WorkCenter`.
- `ProductionResourceService` com CRUD de recursos, desativação lógica, cadastro de valor hora, consulta do valor efetivo e bloqueio de vigência sobreposta.
- `ProductionResourceController` e requests para a API REST.
- Permissões `production-resources.*` e `work-center-hour-rates.*`, incluídas no seeder de permissões e na matriz de papéis tenant.
- Recursos validam correspondência entre planta e centro de trabalho e respeitam isolamento tenant.
- O valor hora é retornado por vigência e pode ser congelado por consumidores futuros de OP/apontamento.

Endpoints principais:

- `GET|POST /api/v1/production-resources`
- `GET|PUT|DELETE /api/v1/production-resources/{id}`
- `GET|POST /api/v1/work-centers/{workCenterId}/hour-rates`
- `GET /api/v1/work-centers/{workCenterId}/hour-rates/effective`

Ficou pendente a camada de telas web dedicadas para recursos e valores hora e a gravação do snapshot de custo dentro de uma operação de OP, que depende das tasks MES/PCP.

## Critérios de aceite

- É possível cadastrar uma máquina/recurso e vinculá-la a um centro de trabalho.
- É possível marcar a máquina/recurso como indisponível e impedir sua seleção em nova programação.
- É possível cadastrar um valor hora para o centro com data de início e fim de vigência.
- Uma OP/apontamento mantém o valor hora usado no momento da execução, mesmo após alteração do cadastro.
- O sistema calcula o custo por centro sem exigir cadastro ou valor hora individual de operador.
- O usuário autenticado do apontamento pode ser armazenado para auditoria, sem participar do cálculo de custo.
- O sistema rejeita vínculos cross-tenant e códigos duplicados.
- Os testes cobrem autorização, isolamento, status, vigência, capacidade e congelamento do custo.

## Validação realizada

- PHP lint dos arquivos alterados: aprovado.
- Rotas API: registradas e verificadas.
- Teste feature criado em `tests/Feature/TenantEngineeringFoundationTest.php`.
- Execução dos testes bloqueada pelo ambiente: o banco `mrp_test` recusou as credenciais do usuário `mrp`; nenhuma asserção chegou a ser executada.

## Impactos e evolução futura

### Benefícios da decisão

- Reduz a complexidade inicial do MES e do cadastro de Engenharia.
- Permite calcular custo previsto x realizado por centro de produção.
- Evita dependência de dados salariais ou integração com RH.
- Mantém a possibilidade de adicionar operador individual posteriormente.

### Limitações aceitas

- Não haverá custo real individual por operador.
- Não haverá indicador oficial de eficiência por operador.
- Não será possível validar automaticamente certificação ou habilidade individual.
- OEE e eficiência continuarão sendo calculados por máquina/recurso e centro, quando os eventos de execução estiverem disponíveis.

### Evolução futura

Se houver necessidade operacional, criar posteriormente uma task específica para operador individual, sem alterar o modelo de custo por centro. O operador poderá ser adicionado como dimensão analítica e de auditoria, enquanto o custo permanecerá baseado no valor hora do centro.

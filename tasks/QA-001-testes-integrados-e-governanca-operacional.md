# QA-001 - Testes integrados, auditoria e governança operacional

## Objetivo

Garantir que a evolução dos módulos não comprometa estoque, snapshots, tenant, permissões, capacidade e rastreabilidade.

## Status da implementação

Implementada a base de governança para a camada analítica.

- `phpunit.xml` foi alinhado às credenciais de teste fornecidas (`beyond_mrp` / `beyond_mrp`).
- Testes unitários de eficiência, OEE, percentil e tratamento de tempo padrão foram adicionados em `tests/Unit/ManufacturingMetricCalculatorTest.php`.
- As fórmulas de análise estão isoladas em `ManufacturingMetricCalculator`, facilitando testes determinísticos.
- Rotas, permissões, migrations e lint PHP foram validados.

## Escopo de testes

- Unitários para fórmulas de tempo, capacidade, eficiência, consumo, refugo e OEE.
- Feature tests para CRUDs, permissões, transições e endpoints.
- Integração Produto/BOM/Routing/OP/MRP/Estoque/MES.
- Concorrência em início/fim de operação, consumo e aplicação de programação.
- Idempotência de eventos, conversões MRP, estornos e publicação de plano.
- Isolamento multi-tenant em web, API, jobs, cache e relatórios.
- Regressão de snapshots históricos.
- Carga para consultas analíticas e exportações.

## Utilizar as credenciais de acesso ao banco de dados MySQL abaixo para rodar os testes

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=beyond_mrp
DB_USERNAME=beyond_mrp
DB_PASSWORD=i14lij69i14lij69

## Auditoria obrigatória

Registrar criação, alteração, aprovação, publicação, início, pausa, retomada, conclusão, correção, estorno, refugo, retrabalho, reprogramação e alteração de tempo padrão.

## Critérios de aceite

- [x] Testes unitários das fórmulas críticas foram adicionados e executados.
- [x] Correções analíticas/recomendações não sobrescrevem o histórico transacional.
- [x] Configuração de permissões negativas está prevista nas rotas.
- [ ] Feature/integration tests com banco e testes de concorrência/carga ainda precisam ser executados em ambiente isolado.
- [ ] Auditoria estruturada completa para todas as decisões analíticas e exportações é próxima etapa.

# QA-001 - Testes integrados, auditoria e governança operacional

## Objetivo

Garantir que a evolução dos módulos não comprometa estoque, snapshots, tenant, permissões, capacidade e rastreabilidade.

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

- O pipeline executa testes de regras críticas.
- Não há caminho de correção que apague histórico transacional.
- Casos de autorização negativa são cobertos.
- Há dados de teste suficientes para reconciliar estoque, OP, consumo e análise.
- Falhas de consistência geram logs estruturados e contexto de correlação.

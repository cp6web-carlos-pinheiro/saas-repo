Especificação Funcional – Sistema Industrial MRP/MRP II + MES + Genealogia

1. Objetivo do Sistema

Desenvolver um sistema industrial integrado de:

* MRP (Material Requirements Planning)
* MRP II (Manufacturing Resource Planning)
* MES (Manufacturing Execution System)
* Genealogia Industrial
* Engineering Change Management

capaz de realizar o planejamento completo de materiais, produção, capacidade produtiva e rastreabilidade industrial ponta a ponta.

O sistema deverá permitir:

* Explosão multinível de estruturas de produto
* Planejamento de compras
* Planejamento de produção
* Controle de estoque e lotes
* Scheduling industrial
* Controle de capacidade produtiva
* Versionamento de engenharia
* Snapshot histórico de produção
* Rastreabilidade completa dos materiais consumidos
* Genealogia industrial forward/backward trace
* Controle de revisões e mudanças de engenharia
* Execução industrial em tempo real

⸻

2. Escopo Geral

O sistema deverá contemplar os seguintes módulos:

1. Cadastro de Produtos
2. Versionamento de Produtos
3. BOM (Bill of Materials)
4. Routing Industrial
5. Controle de Estoque
6. Controle de Lotes e Serialização
7. Ordens de Produção
8. MRP Engine
9. Scheduling e Capacity Planning
10. Compras e Supply Planning
11. MES – Execução Industrial
12. Genealogia Industrial
13. Engineering Change Orders (ECO)
14. APIs REST Enterprise
15. Dashboard e Frontend Industrial
16. Auditoria e Observabilidade

⸻

3. Conceitos Fundamentais

3.1 Produto Acabado (Finished Goods)

Produto final entregue ao cliente.

Exemplo:

* Bicicleta

⸻

3.2 Produto Semiacabado (Semi-Finished)

Produto intermediário produzido internamente e utilizado em outros produtos.

Exemplo:

* Quadro da bicicleta

⸻

3.3 Matéria-Prima (Raw Material)

Material adquirido de fornecedores.

Exemplo:

* Tubo de aço
* Tinta

⸻

3.4 BOM – Bill of Materials

Estrutura hierárquica dos componentes necessários para fabricar um produto.

A BOM deverá suportar:

* Estruturas multinível
* Versionamento
* Vigência
* Substituições
* Scrap factor
* Effective dating

⸻

3.5 Routing

Definição operacional do processo produtivo.

O routing deverá conter:

* Operações
* Sequência
* Work centers
* Setup
* Runtime
* Queue time
* Move time

⸻

3.6 As-Designed vs As-Built

As-Designed

Estrutura teórica da engenharia.

As-Built

Estrutura real utilizada na fabricação.

O sistema deverá armazenar ambos.

⸻

4. Cadastro de Produtos

4.1 Funcionalidades

O sistema deverá permitir:

* Cadastro de produtos
* Classificação por tipo
* Controle de unidades
* Controle de revisões
* Controle de lotes
* Controle serial
* Controle dimensional
* Controle temporal

⸻

4.2 Tipos de Produto

* Produto acabado
* Semiacabado
* Matéria-prima
* Consumível

⸻

4.3 Campos Obrigatórios

Campo	Descrição
SKU	Código produto
Description	Descrição
Product Type	Tipo
UOM	Unidade
Lead Time	Lead time
Setup Time	Tempo setup
Runtime	Tempo produção
Queue Time	Tempo fila
Move Time	Tempo movimentação
Safety Stock	Estoque segurança
Lot Control	Controle lote
Serial Control	Controle serial

⸻

5. Versionamento de Produtos

5.1 Objetivo

Garantir rastreabilidade histórica e controle de engenharia.

⸻

5.2 Funcionalidades

O sistema deverá suportar:

* Revisões
* Effective dating
* Aprovação engenharia
* Compatibilidade versões
* Histórico imutável
* Snapshot histórico

⸻

5.3 Regras

Regra 1

Versões aprovadas não podem ser alteradas.

Regra 2

Toda alteração gera nova versão.

⸻

6. BOM – Bill of Materials

6.1 Funcionalidades

O sistema deverá permitir:

* Estrutura multinível ilimitada
* Versionamento
* Effective dating
* Aprovação
* Snapshot congelado
* Substituições
* Scrap factor

⸻

6.2 Regras

Regra 1

Não permitir circularidade.

Regra 2

A BOM utilizada na produção deve ser congelada na OP.

⸻

7. Routing Industrial

7.1 Funcionalidades

O sistema deverá permitir:

* Cadastro de operações
* Sequenciamento
* Work centers
* Tempos operacionais
* Recursos necessários
* Routing alternativo
* Versionamento

⸻

7.2 Tempos

O sistema deverá controlar:

* Setup time
* Runtime
* Queue time
* Move time

⸻

8. Controle de Estoque

8.1 Funcionalidades

O sistema deverá controlar:

* Estoque disponível
* Reservado
* Em inspeção
* Em trânsito
* Lotes
* Serialização
* Validade
* Localização física

⸻

8.2 Movimentações

* Entrada
* Saída
* Consumo produção
* Ajuste
* Transferência
* Refugo

⸻

8.3 Estratégias

O sistema deverá suportar:

* FIFO
* LIFO
* FEFO

⸻

9. Ordens de Produção

9.1 Funcionalidades

O sistema deverá permitir:

* Criação manual
* Criação automática via MRP
* Snapshot congelado
* Baixa materiais
* Apontamento produção
* Produção parcial
* Encerramento
* Refugo
* Reprocesso

⸻

9.2 Snapshot Histórico

Ao liberar uma OP, o sistema deverá congelar:

* BOM
* Routing
* Operações
* Tempos
* Recursos

⸻

10. MRP Engine

10.1 Objetivo

Calcular necessidades líquidas de materiais e produção.

⸻

10.2 Entradas

* Pedidos venda
* Forecast
* Estoque
* Reservas
* Compras abertas
* OPs abertas
* Lead times
* Capacity constraints

⸻

10.3 Fórmula Principal

Necessidade\ Líquida = Demanda - Estoque\ Disponível - Recebimentos\ Planejados

⸻

10.4 Funcionalidades

* Explosão multinível
* Planejamento temporal
* Retroplanejamento
* Sugestão compra
* Sugestão produção
* Bucketização temporal
* Horizonte planejamento

⸻

11. Scheduling e Capacity Planning

11.1 Funcionalidades

O sistema deverá suportar:

* Finite scheduling
* Infinite scheduling
* Forward scheduling
* Backward scheduling
* Capacity planning
* Gargalos
* Balanceamento

⸻

11.2 Work Centers

O sistema deverá controlar:

* Máquinas
* Linhas
* Operadores
* Turnos
* Eficiência
* Calendário fabril

⸻

12. Compras e Supply Planning

12.1 Funcionalidades

O sistema deverá:

* Gerar solicitações compra
* Consolidar demanda
* Aplicar MOQ
* Aplicar múltiplos
* Gerenciar fornecedores
* Gerenciar recebimento

⸻

13. MES – Manufacturing Execution System

13.1 Funcionalidades

O sistema deverá controlar:

* Execução produção
* Consumo real
* Produção real
* Operadores
* Máquinas
* Eventos tempo real
* Refugo
* Reprocesso

⸻

13.2 As-Built Manufacturing

O sistema deverá registrar:

* Materiais reais consumidos
* Tempos reais
* Operações reais
* Recursos reais

⸻

14. Genealogia Industrial

14.1 Objetivo

Garantir rastreabilidade ponta a ponta.

⸻

14.2 Funcionalidades

O sistema deverá permitir:

* Forward trace
* Backward trace
* Recall simulation
* Genealogia multinível
* Rastreamento de lotes

⸻

14.3 Rastreabilidade

O sistema deverá rastrear:

* Produto final
* Semiacabados
* Matérias-primas
* Lotes
* Fornecedores
* Ordens produção
* Operadores
* Máquinas

⸻

15. Engineering Change Orders (ECO)

15.1 Funcionalidades

O sistema deverá suportar:

* Revisões engenharia
* Aprovação
* Workflow
* Impact analysis
* Histórico mudanças

⸻

16. APIs REST Enterprise

16.1 Funcionalidades

As APIs deverão suportar:

* OAuth2/JWT
* Versionamento
* Paginação
* Bulk operations
* Idempotência
* Rate limiting

⸻

17. Frontend Industrial

17.1 Funcionalidades

O frontend deverá possuir:

* Dashboard industrial
* Cockpit MRP
* Gantt produção
* Gestão estoque
* Gestão BOM
* Scheduling
* Compras
* Produção
* Genealogia
* KPIs

⸻

18. Auditoria e Observabilidade

18.1 Auditoria

O sistema deverá registrar:

* Alterações
* Usuários
* Eventos
* Aprovações
* Movimentações

⸻

18.2 Observabilidade

O sistema deverá possuir:

* Logs estruturados
* Métricas
* Distributed tracing
* Monitoramento filas
* Monitoramento SQL
* Alertas

⸻

19. Requisitos Não Funcionais

19.1 Performance

O sistema deverá:

* Explodir BOMs multinível rapidamente
* Processar milhares de SKUs
* Suportar alto volume transacional
* Reprocessar incrementalmente

⸻

19.2 Escalabilidade

O sistema deverá suportar:

* Multiempresa
* Multiplanta
* Multi warehouse
* Escalabilidade horizontal

⸻

19.3 Segurança

O sistema deverá possuir:

* RBAC
* Auditoria
* MFA
* Criptografia
* Logs segurança

⸻

20. Arquitetura Técnica Recomendada

Backend

* Laravel
* PHP 8+
* Queue Workers
* Redis

Banco

* Microsoft SQL Server

Frontend

* React

Infraestrutura

* Containers
* Horizontal scaling
* Observabilidade
* CI/CD

⸻

21. Resultado Esperado

Ao final, o sistema deverá ser capaz de:

1. Receber demanda
2. Explodir estruturas multinível
3. Planejar materiais
4. Planejar compras
5. Planejar produção
6. Planejar capacidade
7. Sequenciar operações
8. Executar produção
9. Registrar consumo real
10. Congelar snapshots históricos
11. Rastrear materiais ponta a ponta
12. Controlar revisões engenharia
13. Simular recalls
14. Garantir rastreabilidade completa industrial
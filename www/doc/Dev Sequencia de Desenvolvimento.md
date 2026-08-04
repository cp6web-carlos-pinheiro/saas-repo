# Infra 01 - Sequencia de Desenvolvimento

Como voce esta desenvolvendo um MRP SaaS multitenant, a ordem de desenvolvimento deve seguir tres criterios:

1. Dependencias tecnicas (o que precisa existir antes).
2. Infraestrutura compartilhada (tudo que sera reutilizado pelos demais modulos).
3. Valor de negocio (entregar um sistema utilizavel o quanto antes).

Abaixo esta a sequencia que eu adotaria.

## Ultima atualizacao
- 2026-08-04

## Status objetivo
- Fases 1 a 4: Avancado (Fundacao implementada; Multitenancy, Autenticacao e RBAC operacionais no fluxo web tenant).
- Fases 5 a 7: Avancado (Cadastros gerais ativos; Produto com CRUD, versoes e importacao/exportacao XLSX; Revisoes ativas no tenant com estrutura por produto).
- Fase 8: Avancado (Compras com CRUDs operacionais de solicitacao, cotacao, pedido, recebimento e entrada fiscal; itens por linha, transicoes operacionais, bloqueio apos POSTED e estorno com categoria+motivo e auditoria).
- Fase 9: Parcial avancado (Vendas com CRUD principal e cliente no tenant; menu de Clientes integrado como subitem em Vendas).
- Fases 10 em diante: Planejado ou parcial inicial (MRP completo, financeiro, observabilidade e automacoes ainda em evolucao).

## Destaques da ultima atualizacao
- Compras: estorno de recebimento e entrada fiscal exige categoria e motivo, com persistencia em metadata e trilha de auditoria.
- Compras: formulários com linhas dinamicas padronizados e integracoes de lookup via AJAX.
- Estoque: CRUD web de Armazens implementado e publicado no menu de Estoque.
- Estoque: CRUD web de Plantas implementado e publicado no menu de Estoque.
- Navegacao: Fornecedores movido para subitem de Compras e Clientes movido para subitem de Vendas.
- Engenharia de Plataforma: workflow de CI criado com gates de validacao (`composer validate`), estilo (`composer pint --test`) e testes (`php artisan test`) em SQLite em memoria.
- Seguranca: MFA de baixo atrito habilitavel por configuracao com desafio por codigo enviado por e-mail no login web.
- Seguranca: politica de senha centralizada em helper unico e aplicada nos fluxos web/admin/api.
- Observabilidade: middleware de telemetria com `X-Request-Id`, contexto padrao e canal dedicado `telemetry`.
- UX/Acessibilidade: componentes base (`input`, `select`, `textarea`, `alert`) com defaults de acessibilidade e foco visivel reforcado no menu lateral.

| Fase | Modulo | Prioridade | Depende de |
| --- | --- | --- | --- |
| 1 | Arquitetura Base | Critica | - |
| 2 | Multitenancy | Critica | Arquitetura |
| 3 | Autenticacao e Seguranca | Critica | Multitenancy |
| 4 | Controle de Permissoes (RBAC) | Critica | Usuarios |
| 5 | Configuracoes da Empresa (Tenant) | Alta | RBAC |
| 6 | Cadastros Gerais | Alta | Empresa |
| 7 | Produtos e Estoque | Alta | Cadastros |
| 8 | Compras | Alta | Produtos |
| 9 | Vendas | Alta | Produtos |
| 10 | Producao (MRP) | Muito Alta | Compras + Estoque + Produtos |
| 11 | Financeiro | Media | Compras/Vendas |
| 12 | Relatorios | Media | Todos |
| 13 | API | Media | Todos |
| 14 | Auditoria | Media | Todos |
| 15 | Automacoes | Baixa | Todos |
| 16 | Marketplace/Integracoes | Baixa | API |


Modules

↓

Permissions

↓

Data
```

---

## 3. Autenticacao

Implementar:

- Login
- Logout
- Forgot Password
- Reset Password
# Fase 2 - Administracao

Agora o sistema comeca a existir.

## Empresa

Cadastro de:

- Dados fiscais
- Logo
- Endereco
- Moeda
- Idioma
- Timezone
- Configuracoes

---

## Usuarios

Cadastro

Convites

Status

Avatar

Perfis

Ultimo acesso

---

## Filiais

Branches

---

## Departamentos

---

## Centros de Custo

---

## Armazens

Warehouse

---

## Localizacoes

Warehouse Locations

---

## Unidades

KG

UN

CX

LT

etc

---

## Categorias

---

## Marcas

---

## NCM

---

## CFOP

---

## Tributos

---

# Fase 3 - Produtos

Agora comeca o ERP.

## Produtos

Cadastro completo.

SKU

EAN

Descricao

Peso

Volume

Fornecedor

Categoria

Marca

Imagem

---

## Estrutura do Produto (BOM)

Bill of Materials.

---

## Variacoes

Cor

Tamanho

Modelo

---

## Kits

---

## Lotes

---

## Series

---

# Fase 4 - Estoque

Esse modulo sera usado por praticamente tudo.

Criar:

Entrada

Saida

Transferencia

Inventario

Ajuste

Reserva

Movimentacao

Saldo

Valorizacao

Historico

---

# Fase 5 - Compras

Fornecedor

Cotacao

Pedido

Recebimento

NF

Entrada

Pagamento

---

# Fase 6 - Vendas

Clientes

Orcamentos

Pedidos

Separacao

Expedicao

NF

Entrega

---

# Fase 7 - Producao

Esse e o coracao do MRP.

Ordem de Producao

BOM

Roteiro

Centros de Trabalho

Operacoes

Consumo

Apontamentos

Producao

Refugo

Finalizacao

---

# Fase 8 - Planejamento MRP

Aqui entra o diferencial.

Planejamento

Necessidade de Compra

Necessidade de Producao

Previsao

Sugestoes

Reposicao

Estoque Minimo

Lead Time

Capacidade

MRP Engine

Scheduler

---

# Fase 9 - Financeiro

Contas a Pagar

Contas a Receber

Fluxo de Caixa

Plano de Contas

Conciliacao

Boletos

PIX

Cartoes

---

# Fase 14 - Dashboards

KPIs

Graficos

Indicadores

Widgets

---

# Fase 15 - Relatorios

PDF

Excel

CSV

Filtros

Agendamento

---

# Fase 16 - APIs

REST API

Webhooks

Tokens

OAuth

Integracoes

---

# Fase 17 - SaaS

Somente agora implementar tudo relacionado ao modelo comercial.

Planos

Periodo gratuito inicial (14 dias)

Assinaturas

Stripe

Mercado Pago

Asaas

Faturas

Renovacao

Cancelamento

Upgrade

Downgrade

Uso

Limites

---

# Fase 18 - Administracao Global (Super Admin)

Painel exclusivo para voce administrar toda a plataforma:

- Empresas (Tenants)
- Planos
- Assinaturas
- Cobrancas
- Logs globais
- Auditoria
- Monitoramento
- Filas
- Jobs
- Cache
- Saude do sistema
- Feature Flags
- Modulos disponiveis por plano
- Gestao de suporte e impersonacao de usuarios

---

## Ordem resumida

```text
1. Arquitetura Base
2. Multitenancy
3. Autenticacao
4. Permissoes (RBAC)
5. Configuracoes da Empresa
6. Usuarios
7. Cadastros Gerais
8. Produtos
9. BOM (Estrutura dos Produtos)
10. Estoque
11. Compras
12. Vendas
13. Producao
14. MRP (Planejamento)
15. Financeiro
16. CRM
17. Qualidade
18. Dashboards
19. Relatorios
20. API
21. Integracoes
22. Automacoes
23. SaaS (Billing e Assinaturas)
24. Super Admin
```

Para o seu stack (Laravel + Blade + Tailwind + MySQL), eu ainda faria um refinamento adicional: desenvolver todos os modulos ja com uma arquitetura padronizada baseada em Services, Repositories, Policies, Form Requests, DTOs e Actions, evitando logica de negocio nos Controllers. Essa padronizacao facilita a manutencao e permite adicionar novos modulos (como PCP, WMS ou MES) mantendo a mesma estrutura de codigo. Alem disso, eu implementaria desde o inicio componentes Blade reutilizaveis (DataTable, Form Builder, Modal, Wizard, Filters e Charts), pois eles serao usados em praticamente todas as telas do sistema e reduzem significativamente o esforco de desenvolvimento nas fases seguintes.

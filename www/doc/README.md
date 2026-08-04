# Documentação Funcional dos Módulos

Este diretório concentra documentos funcionais dos módulos do sistema Beyond MRP.

## Painel de Status Atual

Ultima atualizacao consolidada: 2026-08-03

Resumo executivo:
- Fundacao, multitenancy e RBAC: avancado.
- Produtos e revisoes: avancado.
- Compras: avancado (CRUDs operacionais com linhas, transicoes, bloqueios apos POSTED, estorno com categoria+motivo e auditoria).
- Vendas: parcial avancado (CRUD principal e clientes no tenant; menu de clientes integrado em Vendas).
- Estoque: parcial avancado (ledger, saldos/lotes/seriais, CRUD de armazens e plantas, integracao com recebimento de compras).
- MRP, financeiro, observabilidade e automacoes: em evolucao.

Atalhos de status:
- Visao geral da sequencia: [Dev Sequencia de Desenvolvimento.md](Dev%20Sequencia%20de%20Desenvolvimento.md)
- Status de Compras: [Modulo Compras.md](Modulo%20Compras.md)
- Status de Estoque: [Modulo Estoque.md](Modulo%20Estoque.md)
- Status de Produto: [Modulo Produto.md](Modulo%20Produto.md)
- Status de MRP: [Modulo Planejamento de Materiais.md](Modulo%20Planejamento%20de%20Materiais.md)

Proximos focos recomendados:
1. Fechar workflow de aprovacao e SLA em Compras.
2. Concluir transferencias/inventario/valorizacao no Estoque.
3. Evoluir integracao financeiro-fiscal e relatorios gerenciais.

## Índice

1. [Módulo Identidade e Acesso](Modulo%20Identidade%20e%20Acesso.md)
2. [Módulo Gestão de Locatários](Modulo%20Gestao%20de%20Locatarios.md)
3. [Módulo Produto](Modulo%20Produto.md)
4. [Módulo Revisões](Modulo%20Lista%20de%20Materiais.md)
5. [Módulo Roteiro de Produção](Modulo%20Roteiro%20de%20Producao.md)
6. [Módulo Engenharia de Mudanças](Modulo%20Engenharia%20de%20Mudancas.md)
7. [Módulo Ordem de Mudança de Engenharia](Modulo%20Ordem%20de%20Mudanca%20de%20Engenharia.md)
8. [Módulo Estoque](Modulo%20Estoque.md)
9. [Módulo Compras](Modulo%20Compras.md)
10. [Módulo Planejamento de Materiais](Modulo%20Planejamento%20de%20Materiais.md)
11. [Módulo Programação da Produção](Modulo%20Programacao%20da%20Producao.md)
12. [Módulo Produção](Modulo%20Producao.md)
13. [Módulo Execução da Manufatura](Modulo%20Execucao%20da%20Manufatura.md)
14. [Módulo Genealogia](Modulo%20Genealogia.md)
15. [Módulo Observabilidade](Modulo%20Observabilidade.md)
16. [Dicionário do Banco de Dados](Dicionario%20do%20Banco%20de%20Dados.md)
17. [Biblioteca de Componentes Blade (Dev)](dev/Biblioteca%20de%20Componentes%20Blade.md)

# Fase 06 - Vendas

## Objetivo
Construir fluxo comercial ponta a ponta.

## Status de implementacao
Parcial avancado. O modulo possui CRUD de vendas com itens, clientes no tenant, transicoes operacionais principais, cancelamento controlado e bloqueio apos faturamento operacional.

## Escopo
- Clientes: implementado
- Orcamentos: nao iniciado
- Pedidos: implementado
- Separacao: parcial
- Expedicao: parcial
- Entrega: parcial

## Criterios para 100% implementado
- Fluxo comercial completo: cliente, orcamento, pedido, separacao, expedicao e entrega.
- Politicas comerciais completas: tabela de preco, desconto por alçada, condicao de pagamento e bloqueios por credito.
- Regras operacionais completas: transicoes de status, cancelamento com motivo, bloqueio apos faturamento e trilha de auditoria.
- Integracao completa com Estoque: reserva, baixa por expedicao e reversao controlada.
- Experiencia de usuario completa: filtros, buscas, dashboards comerciais e atalhos de produtividade no tenant.
- Indicadores de vendas: taxa de conversao, margem, ciclo do pedido e cumprimento de prazo de entrega.
- Qualidade: testes de regras comerciais, transicoes, permissao e integracao entre modulos.

## Pendencias por dependencia e area

### Por dependencia
- Dependencia de estoque: consolidar reserva, separacao e expedicao com baixa consistente.
- Dependencia comercial: concluir orcamentos e politicas de precificacao/desconto.
- Dependencia de analytics: consolidar indicadores de desempenho comercial.

### Por area
- Area Comercial: definir funil operacional, regras de desconto e bloqueios por credito.
- Area Logistica: definir processo de separacao, expedicao e entrega.
- Area de Engenharia: completar fluxos e testes de integracao ponta a ponta.


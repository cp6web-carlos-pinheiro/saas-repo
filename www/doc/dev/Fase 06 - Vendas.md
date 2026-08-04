# Fase 06 - Vendas

## Objetivo
Construir fluxo comercial ponta a ponta.

## Status de implementacao
Nao iniciado. Nao encontrei modulo, rotas ou entidades de vendas/cadastro comercial na base atual.

## Escopo
- Clientes: nao iniciado
- Orcamentos: nao iniciado
- Pedidos: nao iniciado
- Separacao: nao iniciado
- Expedicao: nao iniciado
- NF: nao iniciado
- Entrega: nao iniciado

## Criterios para 100% implementado
- Fluxo comercial completo: cliente, orcamento, pedido, separacao, expedicao, faturamento e entrega.
- Politicas comerciais completas: tabela de preco, desconto por alçada, condicao de pagamento e bloqueios por credito.
- Regras operacionais completas: transicoes de status, cancelamento com motivo, bloqueio apos faturamento e trilha de auditoria.
- Integracao completa com Estoque: reserva, baixa por expedicao e reversao controlada.
- Integracao completa com Financeiro/Fiscal: contas a receber, emissao fiscal, impostos e conciliacao de recebiveis.
- Experiencia de usuario completa: filtros, buscas, dashboards comerciais e atalhos de produtividade no tenant.
- Indicadores de vendas: taxa de conversao, margem, ciclo do pedido e cumprimento de prazo de entrega.
- Qualidade: testes de regras comerciais, transicoes, permissao e integracao entre modulos.

## Pendencias por dependencia e area

### Por dependencia
- Dependencia de estoque: consolidar reserva, separacao e expedicao com baixa consistente.
- Dependencia fiscal/financeira: fechar emissao fiscal, contas a receber e conciliacao de recebimentos.
- Dependencia comercial: concluir orcamentos e politicas de precificacao/desconto.

### Por area
- Area Comercial: definir funil operacional, regras de desconto e bloqueios por credito.
- Area Logistica: definir processo de separacao, expedicao e entrega.
- Area Fiscal/Financeira: validar faturamento e recebiveis.
- Area de Engenharia: completar fluxos e testes de integracao ponta a ponta.


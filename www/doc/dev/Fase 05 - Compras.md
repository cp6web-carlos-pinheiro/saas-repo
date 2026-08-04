# Fase 05 - Compras

## Objetivo
Estruturar fluxo de suprimentos e abastecimento.

## Status de implementacao
Avancado. O projeto possui fornecedores, solicitacoes, cotacoes, pedidos e recebimentos com transicoes operacionais, bloqueios apos POSTED e estorno controlado com auditoria.

## Escopo
- Fornecedor: implementado
- Cotacao: implementado
- Pedido: implementado
- Recebimento: implementado
- Governanca operacional: parcial
- Indicadores: parcial

## Criterios para 100% implementado
- Fluxo completo ponta a ponta: solicitacao, cotacao, pedido, recebimento, validacao e encerramento.
- Workflow de aprovacao: regras por alçada/valor, trilha de aprovacao e bloqueios por status.
- Regras operacionais completas: bloqueio apos POSTED, estorno controlado com categoria e motivo, e auditoria por usuario/timestamp.
- Integracao completa com Estoque: recebimento gerando movimento, reversao reconciliando saldo e rastreabilidade por linha.
- Fornecedores completos: condicoes comerciais, lead time, compliance documental e regras por item.
- Indicadores de compras: prazo medio, OTIF fornecedor, economia em cotacao e backlog por etapa.
- Qualidade: testes de transicao de status, autorizacao, integracao e reversoes.

## Pendencias por dependencia e area

### Por dependencia
- Dependencia de governanca: implementar workflow de aprovacao por alcada e SLA por etapa.
- Dependencia de analytics: consolidar indicadores operacionais por etapa e fornecedor.

### Por area
- Area de Compras/Suprimentos: definir regras de aprovacao, excecao e desempenho de fornecedores.
- Area de Engenharia: concluir integrações e relatorios operacionais de compras.


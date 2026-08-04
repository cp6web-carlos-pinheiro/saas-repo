# Fase 05 - Compras

## Objetivo
Estruturar fluxo de suprimentos e abastecimento.

## Status de implementacao
Parcial. O projeto ja tem fornecedor, requisicao/pedido de compra e aprovacao de pedido. Cotacao, recebimento, nota fiscal, entrada financeira e pagamento ainda nao estao visiveis.

## Escopo
- Fornecedor: parcial
- Cotacao: nao iniciado
- Pedido: parcial
- Recebimento: nao iniciado
- NF: nao iniciado
- Entrada: nao iniciado
- Pagamento: nao iniciado

## Criterios para 100% implementado
- Fluxo completo ponta a ponta: solicitacao, cotacao, pedido, recebimento, entrada fiscal, validacao e encerramento.
- Workflow de aprovacao: regras por alçada/valor, trilha de aprovacao e bloqueios por status.
- Regras operacionais completas: bloqueio apos POSTED, estorno controlado com categoria e motivo, e auditoria por usuario/timestamp.
- Integracao completa com Estoque: recebimento gerando movimento, reversao reconciliando saldo e rastreabilidade por linha.
- Integracao completa com Financeiro/Fiscal: provisionamento, contas a pagar, impostos e conciliacao de documento fiscal.
- Fornecedores completos: condicoes comerciais, lead time, compliance documental e regras por item.
- Indicadores de compras: prazo medio, OTIF fornecedor, economia em cotacao e backlog por etapa.
- Qualidade: testes de transicao de status, autorizacao, integracao e reversoes.

## Pendencias por dependencia e area

### Por dependencia
- Dependencia financeira: completar contas a pagar, conciliacao e baixa financeira dos documentos.
- Dependencia fiscal: concluir validacoes fiscais e consistencia de entrada fiscal para fechamento contabil.
- Dependencia de governanca: implementar workflow de aprovacao por alcada e SLA por etapa.

### Por area
- Area de Compras/Suprimentos: definir regras de aprovacao, excecao e desempenho de fornecedores.
- Area Fiscal/Financeira: homologar regras de impostos, provisoes e pagamento.
- Area de Engenharia: concluir integrações e relatorios operacionais de compras.


# Fase 04 - Estoque

## Objetivo
Implementar o modulo de controle de materiais usado por toda a operacao.

## Status de implementacao
Parcial avancado. Existem saldo, movimentacao, ajuste, reserva por origem/prioridade, lotes e series, com historico operacional via ledger. Transferencia, reversao de movimentos e liberacao automatica de reservas expiradas agora sao tratadas como fluxos operacionais compostos; inventario formal e valorizacao ainda nao estao completos.

## Escopo
- Entrada: parcial
- Saida: parcial
- Transferencia: implementado
- Inventario: parcial
- Ajuste: implementado
- Reserva: implementado
- Movimentacao: implementado
- Saldo: implementado
- Valorizacao: nao iniciado
- Historico: implementado

## Criterios para 100% implementado
- Fluxos completos de estoque: entrada, saida, transferencia entre armazens/plantas, ajuste e devolucao com regras operacionais claras.
- Inventario completo: contagem cega, reconferencia, divergencia, aprovacao e contabilizacao de ajuste.
- Reserva completa: reserva por origem (venda e producao), prioridade e liberacao automatica.
- Valorizacao completa: metodo de custo definido (ex.: FIFO/custo medio), apuracao por periodo e trilha de recalculo.
- Rastreabilidade completa: lote e serial em toda movimentacao critica, incluindo reversoes.
- Integracao completa: recebimentos de compras, consumo de producao e expedicao de vendas refletindo saldos em tempo real.
- Qualidade e operacao: testes de concorrencia, bloqueio de saldo negativo por politica e relatorios de auditoria de movimentos.

## Pendencias por dependencia e area

### Por dependencia
- Dependencia de processos: formalizar inventario ciclico e reserva por origem.
- Dependencia de modelagem de custo: definir estrategia de valorizacao por periodo.
- Dependencia de integracao: completar sincronizacao com vendas e producao para baixas e reversoes.

### Por area
- Area de Logistica/Estoque: definir politicas operacionais de contagem, separacao e transferencia.
- Area de Planejamento/Operacoes: homologar criterios de custo operacional e valorizacao interna.
- Area de Engenharia: implementar fluxos faltantes e testes de concorrencia/consistencia.


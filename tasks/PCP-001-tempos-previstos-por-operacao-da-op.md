# PCP-001 - Tempos previstos por operação da Ordem de Produção

## Objetivo

Materializar na OP os tempos previstos de cada operação, em vez de manter apenas tempos no routing e uma soma temporária no scheduler.

## Contexto atual

O scheduler soma setup, runtime, fila e movimentação do snapshot. A implementação não cria registros de operação da OP, não deixa explícita a base de quantidade e não multiplica o tempo pela quantidade planejada.

## Escopo funcional

1. Criar operações planejadas da OP a partir do routing snapshot.
2. Registrar sequência, centro, recurso elegível, tempo padrão de origem e tempo previsto calculado.
3. Separar setup, processo, fila e movimentação.
4. Registrar quantidade-base, quantidade da OP, eficiência e rendimento aplicados.
5. Registrar datas/horas previstas e duração total.
6. Manter o cálculo congelado para OP liberada.
7. Permitir recalcular apenas antes da liberação ou mediante reprogramação autorizada.

## Modelo sugerido

`production_order_operations`: OP, snapshot de routing/operação, sequência, status, tempos padrão, tempos previstos, quantidade prevista, centro, recurso selecionado/elegível, início/fim planejados e metadata de cálculo.

## Regras

- A soma das partes deve fechar com a duração prevista.
- O cálculo deve usar a versão efetiva de tempo padrão escolhida para a OP.
- Se não houver tempo ou centro elegível, a OP deve ser sinalizada como inconsistente e não liberada/programada.
- Reprogramação deve manter histórico do cálculo anterior.

## Critérios de aceite

- Criar/liberar uma OP gera uma linha por operação do routing snapshot.
- A duração de uma OP de quantidade 10 difere corretamente de uma OP de quantidade 1 quando o tempo for unitário.
- A tela da OP exibe tempos e datas previstas por operação.
- O cálculo é testado para setup fixo, runtime unitário, lotes fracionários e eficiência.

# Estoque, rastreabilidade e genealogia

## Objetivo

Controlar saldos, movimentos, reservas, lotes, números de série e a rastreabilidade entre materiais consumidos e itens produzidos.

## Saldos e ledger

- Consulta e atualização controlada de saldos por empresa, armazém e produto.
- Ledger imutável de movimentos com quantidade, referência, usuário e metadados.
- Movimentos de recebimento, saída, reserva, liberação, transferência e inspeção.
- Ajustes de estoque e reversão por movimento compensatório.
- Transferência entre armazéns com movimentos de saída e entrada relacionados.
- Prevenção de saldo disponível negativo nas operações que retiram estoque.

## Reservas

- Reserva de quantidade para uma referência operacional.
- Liberação manual e liberação de reservas expiradas.
- Separação entre quantidade física, reservada e disponível.

## Lotes e séries

- Cadastro e consulta de lotes e números de série.
- Validação de identificação em produtos configurados para controle por lote ou serial.
- Rastreamento do histórico de movimentos de um lote ou serial.
- Alocações do ledger associam o movimento às identificações movimentadas.

## Genealogia

- Nós e relações de genealogia para produto, lote, serial, ordem, saída e consumo.
- Associação do lote produzido à ordem de produção.
- Associação do consumo de material à genealogia do produto acabado.
- Consulta de rastreabilidade para frente e para trás a partir de uma referência.

## Integrações operacionais

- Recebimentos de compra lançam entrada no estoque.
- Consumo de produção lança saída.
- Apontamento de produto acabado lança recebimento.
- Estornos criam movimentos compensatórios e preservam a trilha original.

## Entidades principais

- `inventory_balances`, `inventory_reservations`.
- `stock_ledger_movements`, `stock_ledger_allocations`.
- `inventory_lots`, `inventory_serials`.
- `genealogy_nodes`, `genealogy_relations`.

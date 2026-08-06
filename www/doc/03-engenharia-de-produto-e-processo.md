# Engenharia de produto e processo

## Objetivo

Definir o produto fabricado, sua estrutura de materiais, roteiro, centros, recursos e tempos padrão de forma versionada e rastreável.

## Produtos

- CRUD web e API de produtos por empresa, com SKU, descrição, tipo, unidade, categoria, marca, estoque de segurança e lead time.
- Controle de lote e serial, atributos técnicos e comerciais, unidades alternativas, imagens e anexos.
- Importação e exportação por planilha.
- Versões de produto com histórico, vigência e estados `DRAFT`, `APPROVED` e `OBSOLETE`.

## Lista de materiais (BOM)

- Cabeçalhos e componentes versionados, com vigência, aprovação e obsolescência.
- Quantidade por componente, unidade, nível e sequência.
- Visualização da estrutura e manutenção das revisões pela interface web.
- Explosão recursiva pela API, com seleção da versão efetiva e detecção de ciclos.
- Congelamento da BOM e de seus itens na ordem de produção, preservando a estrutura usada historicamente.

## Roteiros, centros e recursos

- Versões de roteiro e operações sequenciadas por produto.
- Operações com código, nome, centro de trabalho, setup, processo, fila e movimentação.
- Aprovação e snapshot imutável do roteiro e das operações.
- Centros de trabalho, turnos, capacidade diária e calendário produtivo.
- Recursos produtivos vinculados à planta e ao centro, incluindo máquina, equipamento, ferramenta ou linha, com status e disponibilidade.
- Histórico de valor-hora por centro e consulta do valor efetivo por vigência.

## Tempos padrão

- Tempos padrão versionados por operação de roteiro.
- Estados `DRAFT`, `APPROVED` e `OBSOLETE`, vigência e aprovador.
- Cálculo de setup, runtime, fila, movimentação, tempo produtivo e lead time.
- Materialização do tempo efetivo nas operações da ordem de produção, incluindo a versão utilizada.

## Mudança de engenharia (ECO)

- Criação, edição, submissão, aprovação, rejeição e implementação de ordens de mudança.
- Linhas de mudança associadas aos alvos de engenharia.
- Consulta de impacto antes da implementação e validação de pertencimento ao tenant.

## Entidades principais

- `products`, `product_versions`, `bom_headers` e `bom_items`.
- `routing_versions`, `routing_operations` e respectivos snapshots.
- `work_centers`, `work_center_shifts`, `production_resources` e `work_center_hour_rates`.
- `routing_operation_standard_times`.
- `engineering_change_orders` e `engineering_change_order_lines`.

## Dicionário de dados

Consulte as [tabelas de Engenharia de produto e processo](11-dicionario-de-dados.md#engenharia-de-produto-e-processo).

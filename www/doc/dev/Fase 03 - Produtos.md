# Fase 03 - Produtos

## Objetivo
Iniciar o ERP com cadastro e engenharia de produtos.

## Status de implementacao
Parcial. O cadastro de produtos e a revisao por versoes estao implementados, com integracao com estoque, producao e genealogia. BOM, variacoes e kits ainda nao aparecem como fluxo fechado na base atual.

## Escopo
- Produtos: parcial
- Estrutura do Produto (BOM): parcial
- Variacoes: cor, tamanho, modelo: nao iniciado
- Kits: nao iniciado
- Lotes: parcial
- Series: parcial

## Criterios para 100% implementado
- Cadastro de produto completo: atributos tecnicos, comerciais e fiscais, multiplas unidades, imagens, anexos e status de ciclo de vida.
- Engenharia de produto completa: BOM com versoes, vigencia, aprovacao e rastreabilidade de alteracao.
- Variacoes completas: matriz de variantes (cor/tamanho/modelo) com SKU derivado e controle de disponibilidade.
- Kits completos: composicao de kits, regras de explosao e impacto no estoque.
- Rastreabilidade completa: lotes e seriais com regras de obrigatoriedade por tipo de item e consulta ponta a ponta.
- Integracoes completas: produto consumido por Compras, Vendas, Estoque, Producao e MRP sem adaptacoes manuais.
- Qualidade: testes de regras de versao, vigencia, variacao e rastreabilidade.

## Pendencias por dependencia e area

### Por dependencia
- Dependencia de engenharia de produto: fechar variacoes e kits com impacto em SKU e estrutura tecnica.
- Dependencia de estoque: vincular obrigatoriedade de lote/serial por tipo de item.
- Dependencia de producao/MRP: consolidar BOM com regras completas de vigencia e aprovacao.

### Por area
- Area de Produto/Engenharia: definir matriz de variacoes, kits e regras de revisao.
- Area de Operacoes: validar comportamento de rastreabilidade no fluxo real.
- Area de Engenharia de Software: implementar regras e testes de integridade entre modulos.


# Fase 03 - Produtos

## Objetivo
Iniciar o ERP com cadastro e engenharia de produtos.

## Status de implementacao
Avancado para Produto e BOM; Engenharia de Processos implementada no backend/API, com governanca integrada ainda parcial. O cadastro de produtos foi estendido com atributos tecnicos/comerciais, unidades alternativas, imagens/anexos e ciclo de vida. A engenharia com revisoes e BOM foi consolidada com regras de vigencia/aprovacao, e o payload de versao passou a normalizar variacoes e kits com SKU derivado e validacoes. Routing, centros de trabalho, recursos e tempos padrao versionados existem em modulos proprios e sao detalhados na Fase 07 e nas tasks ENG-001/002/003.

## Escopo
- Produtos: concluido no escopo da fase
- Estrutura do Produto (BOM): implementado, incluindo versao, vigencia, aprovacao, explosao e snapshot na OP
- Variacoes: cor, tamanho, modelo: concluido no escopo da fase
- Kits: concluido no escopo da fase
- Lotes: concluido no escopo da fase
- Series: concluido no escopo da fase

## Entregas implementadas
- Cadastro de produto estendido no modelo e tela com: ciclo de vida, atributos tecnicos/comerciais, unidades alternativas, imagens e anexos (campos JSON para simplicidade).
- Versoes de produto com normalizacao de payload para:
	- Matriz de variacoes (color/size/model) com geracao de SKU derivado.
	- Kits com composicao, quantidade e modo de explosao.
- Regra de integridade de engenharia:
	- BOM aprovado sem sobreposicao de vigencia para o mesmo produto.
	- Validacao de kits impedindo auto-referencia e componentes fora do tenant.
- Rastreabilidade operacional:
	- Recebimento de compra exige identificador de lote quando o produto exige rastreabilidade por lote/serial.
- Integracao entre modulos mantida sem adaptacoes manuais no fluxo web de Produto, Compras, Estoque e BOM.
- A BOM congelada na OP preserva a estrutura usada no planejamento/execucao; a unidade dos componentes e mantida no cadastro/snapshot.
- Qualidade:
	- Suite feature da fase criada para validar cadastro estendido, variacoes/kits, vigencia de BOM e obrigatoriedade de rastreabilidade.

## Criterios para 100% implementado
- Cadastro de produto completo: atributos tecnicos e comerciais, multiplas unidades, imagens, anexos e status de ciclo de vida.
- Engenharia de produto completa: BOM com versoes, vigencia, aprovacao e rastreabilidade de alteracao.
- Variacoes completas: matriz de variantes (cor/tamanho/modelo) com SKU derivado e controle de disponibilidade.
- Kits completos: composicao de kits, regras de explosao e impacto no estoque.
- Rastreabilidade completa: lotes e seriais com regras de obrigatoriedade por tipo de item e consulta ponta a ponta.
- Integracoes completas: produto consumido por Compras, Vendas, Estoque, Producao e MRP sem adaptacoes manuais.
- Engenharia de Processos completa: recursos e tempos padrao versionados estao implementados no backend/API; faltam telas web dedicadas e aplicacao completa do pacote integrado de ECO.
- Qualidade: testes de regras de versao, vigencia, variacao e rastreabilidade.

## Pendencias por dependencia e area

### Por dependencia
- [x] Dependencia de engenharia de produto: variacoes e kits fechados em payload de versao com impacto em SKU e regras de composicao.
- [x] Dependencia de estoque: obrigatoriedade de identificador para itens rastreaveis no recebimento.
- [x] Dependencia de producao/MRP: BOM consolidada com bloqueio de sobreposicao de vigencia aprovada.
- [ ] Dependencia de Engenharia de Processos: concluir telas web e governanca integrada de Produto, BOM, Routing, recursos e tempos via ECO.

### Por area
- [x] Area de Produto/Engenharia: matriz de variacoes e kits implementada no payload de revisao com regras de integridade.
- [x] Area de Operacoes: rastreabilidade aplicada em recebimento para produtos com controle de lote/serial.
- [x] Area de Engenharia de Software: regras e testes de integridade implementados na suite feature da fase.

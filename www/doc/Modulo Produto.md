# Modulo Produto

Este modulo centraliza o cadastro funcional de produtos e versoes tecnicas utilizadas pelos processos de negocio.

## Ultima atualizacao
- 2026-08-03

## Status objetivo
- Status atual: Parcial avancado.
- Cobertura atual: CRUD tenant de produtos, CRUD de versoes e acoes de ciclo de vida (aprovar, obsoletar, excluir), busca de produtos para versoes, e importacao/exportacao XLSX no cadastro de produtos.
- Pendencia principal: expandir regras de governanca tecnica, validacoes avancadas de engenharia e integracao completa com planejamento/MRP.

## Tabelas relacionadas

### Mestres

- `products`
- `product_versions`

### Transacionais

- Nenhuma tabela transacional dedicada neste modulo.

### Relacionamento

- Nenhuma tabela de relacionamento especifica neste modulo.

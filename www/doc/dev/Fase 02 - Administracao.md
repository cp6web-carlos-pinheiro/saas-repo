# Fase 02 - Administracao

## Objetivo
Estruturar os cadastros administrativos que sustentam os modulos transacionais.

## Status de implementacao
Parcial. O projeto ja cobre empresa, usuarios, filiais/warehouses, perfis/permissoes, convites, locale e administracao global de usuarios/planos. Os cadastros administrativos mais especificos ainda nao foram iniciados.

## Escopo
- Empresa: parcial
- Usuarios: parcial
- Filiais (Branches): parcial
- Departamentos: nao iniciado
- Centros de Custo: nao iniciado
- Armazens (Warehouse): parcial
- Localizacoes (Warehouse Locations): nao iniciado
- Unidades (KG, UN, CX, LT, etc): nao iniciado
- Categorias: nao iniciado
- Marcas: nao iniciado
- NCM: nao iniciado
- CFOP: nao iniciado
- Tributos: nao iniciado

## Criterios para 100% implementado
- Cadastros mestres completos: departamentos, centros de custo, unidades, categorias, marcas, NCM, CFOP e tributos com CRUD web tenant.
- Estrutura organizacional completa: filiais/planta/armazem/localizacao integradas com regras de ativacao, inativacao e bloqueio de exclusao com dados vinculados.
- Governanca de dados mestre: historico de alteracoes, validacoes fiscais, politica de codificacao e unicidade por tenant.
- Seguranca e permissao: permissao granular por cadastro administrativo, com auditoria de criacao, alteracao e exclusao.
- Integracao com modulos transacionais: todos os cadastros administrativos referenciados por Compras, Vendas, Estoque e Producao sem dependencias manuais.
- Qualidade: testes de feature cobrindo CRUDs, validacoes, filtros e regras de autorizacao.

## Pendencias por dependencia e area

### Por dependencia
- Dependencia de dados mestre: concluir cadastros de departamentos, centros de custo, unidades, categorias, marcas, NCM, CFOP e tributos.
- Dependencia de integracao: garantir consumo desses cadastros em Compras, Vendas, Estoque e Producao.
- Dependencia de governanca: aplicar regras de historico e bloqueio de exclusao com dados vinculados.

### Por area
- Area Administrativa/Fiscal: definir validacoes e taxonomia de dados obrigatorios.
- Area de Engenharia: implementar CRUDs faltantes e constraints de integridade.
- Area de Seguranca e Compliance: revisar permissoes e trilha de auditoria dos cadastros.


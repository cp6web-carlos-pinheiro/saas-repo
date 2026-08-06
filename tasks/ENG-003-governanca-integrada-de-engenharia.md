# ENG-003 - Governança integrada de Produto, BOM, Routing e tempos

## Status de implementação

Parcialmente implementado no módulo ECO/API. O ECO agora reconhece tempos padrão como domínio de engenharia, valida que todos os alvos pertençam ao tenant e calcula impacto de tempos padrão em OPs abertas. O fluxo completo de pacote integrado, comparação de versões e aplicação automática das alterações ainda não foi implementado.

## Objetivo

Consolidar Produto, BOM, Routing e tempos padrão em um fluxo de engenharia coerente, com impacto, aprovação, vigência e rastreabilidade.

## Problema

O sistema possui versões e ECOs em módulos separados, mas ainda não há garantia documentada de que uma mudança de engenharia atualize e aprove conjuntamente BOM, routing, recursos e tempos, nem de que o impacto seja apresentado ao usuário antes da implementação.

## Escopo

- Definir o pacote de engenharia ou mecanismo equivalente para agrupar alterações.
- Relacionar ECO a produto, BOM, routing, tempos, recursos e operações afetados.
- Calcular impacto em OPs abertas, programações, MRP, estoque e compras.
- Controlar estados: rascunho, submetido, aprovado, rejeitado, implementado e cancelado.
- Impedir alteração direta de versões aprovadas usadas por OPs.
- Registrar usuário, data, motivo, evidências e hash/snapshot das versões aprovadas.
- Exibir diferenças entre versões.

## Regras de negócio

- Uma mudança aprovada deve ter data efetiva.
- Não permitir sobreposição de versões efetivas.
- OP liberada usa snapshot imutável mesmo após nova aprovação.
- Alterações que afetem OP em execução devem exigir decisão explícita: manter versão, reprogramar ou cancelar.
- A implementação deve ser idempotente.

## Entregas

- Ajustes de domínio e persistência.
- Fluxo web/API e permissões.
- Serviço de impacto e tela de comparação.
- Auditoria e notificações internas, se o padrão do projeto permitir.
- Testes de transição, vigência, snapshot e isolamento.

Implementação realizada:

- Novo domínio ECO `STANDARD_TIME`, além de `PRODUCT`, `BOM` e `ROUTING`.
- Requests de criação e atualização aceitam `STANDARD_TIME`.
- `EngineeringChangeOrderService` valida a existência do alvo no tenant ativo antes de criar linhas.
- A análise de impacto identifica a operação/routing relacionado ao tempo padrão e lista OPs abertas afetadas.
- Teste feature criado para criação de ECO com linha de tempo padrão.

Ainda pendente nesta task:

- Pacote único de engenharia que agrupe Produto, BOM, Routing, recursos e tempos.
- Tela/API de comparação entre versões.
- Validação de compatibilidade entre versões `from`/`to` e seus objetos reais.
- Bloqueio de implementação quando versões necessárias não estiverem aprovadas.
- Aplicação transacional da ECO nos objetos de engenharia; atualmente `implement` altera o status do ECO e registra o usuário/data, mas não cria ou altera versões automaticamente.
- Notificação e decisão formal para OPs abertas impactadas.

## Critérios de aceite

- Uma ECO mostra todos os objetos afetados e o estado de cada aprovação.
- Não é possível implementar uma alteração sem versões válidas e aprovadas.
- O impacto em OPs abertas é identificado antes da implementação.

## Validação realizada

- PHP lint dos serviços, requests e rotas: aprovado.
- Teste feature criado para alvo `STANDARD_TIME`.
- Execução dos testes bloqueada antes das asserções pela recusa de credenciais do banco de testes `mrp_test`.
- A consulta histórica reproduz exatamente as versões usadas por uma OP.

# PCP-003 - Programação da produção persistida, versionada e aplicável

## Objetivo

Transformar o resultado temporário do scheduler em um plano de produção persistido, versionado, auditável e aplicável às OPs.

## Problema atual

A execução web guarda o resultado por aproximadamente 30 minutos em cache. Não existe histórico de versões, aprovação, publicação, comparação entre cenários ou atualização persistida de datas/recursos.

## Escopo funcional

1. Criar entidades de plano, versão, linhas de operação e cenários.
2. Persistir parâmetros usados: data de referência, direção, regra, modo, calendário e versão dos dados.
3. Permitir salvar cenário sem publicar.
4. Comparar cenários e identificar alterações.
5. Aprovar/publicar uma versão.
6. Aplicar a programação aprovada às operações das OPs.
7. Reprogramar com motivo e preservar a versão anterior.
8. Cancelar ou substituir uma versão publicada com autorização.

## Regras

- Uma empresa/planta pode ter uma única programação publicada para uma janela, salvo regra explícita de cenários.
- OP concluída/cancelada não pode ser alterada pela aplicação automática.
- OP em execução só pode ter etapas futuras reprogramadas.
- Aplicação deve ser idempotente e transacional.
- Toda alteração deve registrar usuário, data e motivo.

## Critérios de aceite

- Um plano continua disponível após expiração do cache.
- É possível consultar o que mudou entre duas versões.
- Publicar aplica datas e recursos às operações elegíveis.
- Falha parcial não deixa OPs com programação inconsistente.
- Existem testes para concorrência e autorização.

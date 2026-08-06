# PCP-003 - Programação da produção persistida, versionada e aplicável

## Objetivo

Transformar o resultado temporário do scheduler em um plano de produção persistido, versionado, auditável e aplicável às OPs.

## Status da implementação

Implementado o primeiro fluxo persistido e aplicável.

- `production_schedules` guarda cabeçalho, versão, parâmetros, status, usuário e datas de aprovação/publicação.
- `production_schedule_lines` guarda uma linha por operação, centro/recurso, janela, segmentos, capacidade e lead time.
- `ProductionScheduleService` cria draft a partir do scheduler, publica de forma transacional, aplica datas/recurso às operações e compara duas versões.
- Endpoints: `GET/POST /api/v1/production-schedules`, `GET /{id}`, `POST /{id}/publish`, `POST /{id}/cancel` e `GET /{id}/compare/{otherId}`.

## Escopo funcional

1. Criar entidades de plano, versão e linhas de operação.
2. Persistir parâmetros usados: data de referência, direção, regra, modo, calendário e versão dos dados.
3. Permitir salvar cenário sem publicar.
4. Comparar cenários e identificar alterações.
5. Aprovar/publicar uma versão.
6. Aplicar a programação aprovada às operações das OPs.
7. Reprogramar criando novo draft; o histórico de drafts/publicações anteriores é preservado.
8. Cancelar ou substituir uma versão publicada com autorização.

## Regras implementadas

- Publicação repetida é idempotente.
- Uma programação cancelada não pode ser publicada; uma publicada não pode ser cancelada diretamente, devendo ser substituída.
- Operações concluídas/canceladas são protegidas contra aplicação.
- Aplicação das linhas é transacional e registra usuário, timestamps e motivo.
- A unicidade de linha por schedule/operação impede duplicação no mesmo plano.

## Critérios de aceite

- [x] Um plano continua disponível após expiração do cache.
- [x] É possível comparar linhas entre duas versões.
- [x] Publicar aplica datas e recurso às operações elegíveis.
- [x] A aplicação usa transação e é idempotente.
- [ ] Regra de uma única publicação por planta/janela, aprovação formal separada e concorrência pessimista ainda precisam ser endurecidas.
- [ ] Testes de integração aguardam banco de teste configurado.

# Modulo Observabilidade

Este modulo monitora eventos e saude operacional da plataforma para facilitar deteccao e analise de incidentes.

## Ultima atualizacao
- 2026-08-04

## Status objetivo
- Status atual: Parcial avancado.
- Cobertura atual: trilha de auditoria persistida, padrao de telemetria HTTP com `X-Request-Id` e logs dedicados para autenticacao e telemetria.
- Pendencia principal: consolidar dashboards de saude, alertas automatizados e correlacao de eventos de negocio em paineis operacionais.

## Tabelas relacionadas

### Mestres

- Nenhuma tabela mestra dedicada neste modulo.

### Transacionais

- `audit_logs`

### Artefatos de log operacionais

- `storage/logs/auth.log`
- `storage/logs/telemetry.log`
- `storage/logs/sql.log`

### Relacionamento

- Nenhuma tabela de relacionamento especifica neste modulo.

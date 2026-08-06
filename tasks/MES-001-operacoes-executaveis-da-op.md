# MES-001 - Operações executáveis da Ordem de Produção

## Objetivo

Criar a camada operacional que representa cada operação da OP e serve de base para apontamento, cronômetro, pausas, consumo, qualidade e análise.

## Status da implementação

Implementado o núcleo de operações executáveis no backend.

- `production_order_operations` é criado pelo PCP-001 a partir do routing snapshot.
- A operação possui estado operacional, quantidades processada/boa/refugada/retrabalho, recurso efetivo, usuário apontador e tempos reais.
- `ProductionOperationExecutionService` centraliza transições e apontamentos por operação.
- A operação detalhada expõe eventos, outputs e registros de qualidade.

## Contexto anterior

Existe `production_order_outputs` para registrar quantidade produzida/refugada e tempos informados, mas não há uma entidade de operação executada com status e ciclo de vida próprio.

## Escopo implementado

- Gerar operações da OP a partir do routing snapshot e de PCP-001.
- Status: `PLANNED`, `READY`, `IN_PROGRESS`, `PAUSED`, `STOPPED`, `COMPLETED`, `CANCELLED` e `OUTSOURCED`.
- Registrar sequência, dependências, centro, recurso, operador elegível, tempos padrão/previstos e quantidades.
- Permitir iniciar apenas quando pré-requisitos forem atendidos.
- Permitir apontar parcialmente e concluir operação sem concluir a OP automaticamente de forma indevida.
- Consolidar outputs, consumos e eventos na operação correta.

## Regras implementadas

- A operação usa o routing snapshot da OP, não o cadastro corrente.
- Sequência e dependências devem ser respeitadas, salvo permissão de exceção.
- Quantidades boas, refugo e retrabalho precisam fechar com a quantidade processada.
- Uma operação não pode ser concluída duas vezes; comandos repetidos usam `idempotency_key`.
- Operações anteriores devem estar concluídas antes do início de uma operação posterior.
- Apontamento excedente é bloqueado por padrão e exige `allow_excess` para exceção explícita.

## Critérios de aceite

- [x] Liberar uma OP cria operações executáveis ordenadas.
- [x] A API MES mostra progresso, eventos e apontamentos por operação.
- [x] Apontamento parcial não mistura operações.
- [x] Sequência e idempotência são validadas no backend.
- [ ] Atualização automática do status agregado da OP ao concluir a última operação ainda precisa ser conectada ao workflow de qualidade.
- [ ] Reabertura autorizada e testes de concorrência dependem da infraestrutura de testes de banco.

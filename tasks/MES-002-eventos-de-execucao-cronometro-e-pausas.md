# MES-002 - Eventos de execução, cronômetro e pausas

## Objetivo

Implementar o registro confiável de início, pausa, retomada, parada e término das operações, permitindo calcular tempo real e suportar um futuro OEE.

## Status da implementação

Implementado o fluxo de eventos no backend.

- `production_operation_events` é append-only e registra `START`, `PAUSE`, `RESUME`, `STOP`, `COMPLETE` e `CANCEL`.
- Cada comando exige `idempotency_key`; repetição não cria evento duplicado.
- Timestamps são do servidor por padrão.
- O serviço calcula tempo produtivo e tempo de pausa a partir dos eventos.
- Pausa, parada e cancelamento exigem `reason_code`.

## Escopo

- Criar eventos append-only de operação.
- Eventos mínimos: start, pause, resume, stop, complete, cancel e ajuste autorizado.
- Registrar timestamp do servidor, usuário, operador, recurso, motivo e observação.
- Calcular tempo bruto, tempo produtivo, tempo de pausa, parada planejada e parada não planejada.
- Permitir retomada após queda de conexão sem duplicar evento.
- Fechar automaticamente ou alertar operações abertas há tempo excessivo.
- Exigir motivo para pausa/parada conforme política.

## Regras de consistência

- Não permitir dois cronômetros ativos para a mesma operação/recurso quando a capacidade for exclusiva.
- Sequência de eventos válida: start antes de pause/complete; pause antes de resume; complete encerra.
- Eventos não devem ser apagados; correções criam evento de ajuste com justificativa.
- O tempo deve ser calculado no servidor, não pelo relógio do navegador.
- Fuso horário e horário de verão devem seguir a configuração do tenant.

## Interface

- API idempotente para comandos de chão de fábrica.
- Tela operacional com estado atual, contador, botões permitidos e motivo de pausa.
- Atualização por polling ou mecanismo em tempo real compatível com a arquitetura existente.

## Critérios de aceite

- [x] Iniciar e parar produz tempo real reproduzível no servidor.
- [x] Pausa não contabiliza o intervalo como produtivo.
- [x] Repetição com a mesma chave é idempotente.
- [x] Transições inválidas retornam erro sem alterar o estado.
- [x] Recurso exclusivo já ocupado é rejeitado.
- [ ] Job de alerta/fechamento automático e atualização em tempo real ainda não foram desenvolvidos.
- [ ] Fuso configurável por tenant e testes de concorrência aguardam banco de teste.

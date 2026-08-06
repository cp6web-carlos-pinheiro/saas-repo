# MES-002 - Eventos de execução, cronômetro e pausas

## Objetivo

Implementar o registro confiável de início, pausa, retomada, parada e término das operações, permitindo calcular tempo real e suportar um futuro OEE.

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

- Iniciar e parar uma operação produz tempo real reproduzível.
- Pausar não contabiliza tempo produtivo durante o intervalo.
- Repetir uma requisição com a mesma chave não duplica evento.
- Eventos inválidos retornam erro sem corromper o estado.
- Testes cobrem concorrência, queda de sessão, fuso e encerramento.

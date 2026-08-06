# PCP-002 - Verificação de capacidade finita por recurso

## Objetivo

Evoluir a capacidade atual, baseada em centro/calendário, para considerar recursos individuais, turnos, indisponibilidades e conflitos reais.

## Contexto atual

`ProductionSchedulingService` usa calendário, turnos, capacidade diária e fator de eficiência do centro. O resultado é calculado em memória/cache e não considera máquinas, operadores, manutenção ou conflitos entre operações.

## Escopo

- Integrar ENG-001 e PCP-001.
- Calcular capacidade disponível por recurso e intervalo.
- Considerar turnos, feriados, exceções, manutenção, bloqueios e eficiência.
- Detectar sobreposição de operações no mesmo recurso.
- Tratar recurso alternativo e operação terceirizada.
- Identificar gargalos, atraso, sobrecarga e capacidade insuficiente.
- Diferenciar capacidade teórica, disponível e comprometida.
- Exibir mensagens de exceção acionáveis.

## Regras a homologar

- Se operador também limita capacidade e como contabilizar múltiplos operadores.
- Se setup ocupa recurso integralmente.
- Se fila/movimentação consome capacidade.
- Prioridade entre OPs conflitantes.
- Política para dividir operação entre dias/recursos.

## Entregas

- Serviço de capacidade desacoplado do scheduler.
- Consultas de disponibilidade e ocupação.
- Ajuste do scheduler forward/backward e modos finito/infinito.
- Testes de conflito, virada de turno, indisponibilidade e recurso alternativo.

## Critérios de aceite

- Duas OPs não são alocadas simultaneamente no mesmo recurso quando a capacidade é exclusiva.
- Uma manutenção reduz a janela disponível e pode gerar atraso identificado.
- O sistema informa o motivo quando não consegue alocar uma operação.
- A programação apresenta utilização por centro e recurso.

# PCP-002 - Verificação de capacidade finita por recurso

## Objetivo

Evoluir a capacidade atual, baseada em centro/calendário, para considerar recursos individuais, turnos, indisponibilidades e conflitos reais.

## Status da implementação

Implementação inicial concluída no backend do scheduler.

- A operação planejada guarda `production_resource_id` e seleciona o primeiro recurso ativo do centro quando disponível.
- A programação usa calendário, turnos, exceções e capacidade do centro, com o fator de eficiência aplicado à capacidade disponível.
- O tempo previsto não é multiplicado pela eficiência; eficiência é uma característica de capacidade, não de tempo padrão.
- Setup/runtime consomem capacidade; fila/movimentação são adicionados apenas como lead time.
- Operações terceirizadas não consomem capacidade interna.
- O scheduler divide a carga em segmentos por dia e evita exceder a capacidade acumulada do centro no cenário corrente.

## Escopo implementado e pendente

- Integrar ENG-001 e PCP-001.
- [x] Calcular capacidade disponível por centro, calendário e turnos.
- [x] Considerar eficiência na capacidade disponível, sem alterar o tempo padrão.
- [x] Tratar operação terceirizada fora da capacidade interna.
- [x] Separar capacidade produtiva de lead time.
- [ ] Capacidade exclusiva por máquina/recurso ainda precisa de cursor e ocupação persistida por recurso.
- [ ] Manutenção/bloqueios e recursos alternativos ainda precisam de entidades e política de seleção.
- [ ] Operador não é dimensão de capacidade nesta versão; o custo continuará por hora do centro de produção.
- Identificar gargalos, atraso, sobrecarga e capacidade insuficiente.
- Diferenciar capacidade teórica, disponível e comprometida.
- Exibir mensagens de exceção acionáveis.

## Regras definidas

- Operador não limita a programação nesta fase.
- Setup ocupa capacidade.
- Fila e movimentação são apenas lead time.
- Prioridade entre OPs conflitantes.
- Política para dividir operação entre dias/recursos.

## Entregas

- Serviço de capacidade desacoplado do scheduler.
- Consultas de disponibilidade e ocupação.
- Ajuste do scheduler forward/backward e modos finito/infinito.
- Testes de conflito, virada de turno, indisponibilidade e recurso alternativo.

## Critérios de aceite

- [x] A capacidade diária do centro é respeitada no cenário gerado e operações podem atravessar dias úteis.
- [x] O resultado informa segmentos, tempo de capacidade e lead time.
- [ ] Exclusividade/conflito persistido por recurso, manutenção e mensagens de gargalo são evolução PCP-002.1.

# MES-003 - Vínculo de recursos e identificação opcional do operador

## Objetivo

Registrar em qual máquina/recurso cada operação foi executada, usando o cadastro criado em ENG-001. O usuário/operador poderá ser identificado para auditoria, mas não será obrigatório para iniciar ou concluir a operação nesta primeira versão e não será usado no cálculo de custo.

## Status da implementação

Implementado o vínculo operacional básico.

- A operação seleciona o recurso previsto pelo PCP ou recebe recurso específico no comando de início.
- O recurso é validado pelo tenant, centro de trabalho e status `ACTIVE`.
- Recurso em manutenção/inativo e recurso de outro centro são rejeitados.
- Trocas de recurso ficam registradas nos eventos e no recurso efetivo da operação.
- Operador é opcional e salvo apenas como auditoria; não participa do custo, que continua calculado pelo valor-hora do centro de produção.

## Escopo

- Selecionar recurso específico ou alocar automaticamente um recurso elegível.
- Validar centro, status, turno e disponibilidade do recurso.
- Registrar troca de recurso durante a operação com vigência por evento.
- Registrar opcionalmente o usuário que iniciou ou registrou o apontamento.
- Aplicar permissões para apontamento, correção e encerramento.

Ficam fora do escopo inicial a validação de habilidade/certificação, o vínculo obrigatório operador-recurso e o rateio de horas entre operadores.

## Regras

- Recurso em manutenção não pode receber execução.
- Alterações retroativas exigem motivo e permissão.
- Os vínculos do recurso devem ser mantidos em snapshots/eventos para análise histórica.
- Se o usuário/apontador for armazenado, deve ser tratado como dado de auditoria, separado do custo por centro.

## Critérios de aceite

- [x] Operação registra recurso efetivo quando configurado.
- [x] Conclusão não exige valor-hora individual nem operador individual.
- [x] Máquina de outro centro ou tenant é rejeitada.
- [x] Custo permanece independente do usuário apontador.
- [x] Troca de recurso preserva a linha histórica de eventos.
- [ ] Validação de turno, certificação e rateio entre múltiplos operadores continuam fora da primeira versão.

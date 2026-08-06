# MES-003 - Vínculo de recursos e identificação opcional do operador

## Objetivo

Registrar em qual máquina/recurso cada operação foi executada, usando o cadastro criado em ENG-001. O usuário/operador poderá ser identificado para auditoria, mas não será obrigatório para iniciar ou concluir a operação nesta primeira versão e não será usado no cálculo de custo.

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

- Cada operação concluída identifica o recurso, quando o processo exigir recurso específico.
- O sistema permite concluir uma operação sem cadastro de valor hora individual ou operador individual.
- O sistema rejeita máquina de outro centro ou tenant.
- A eficiência e o custo podem ser consultados sem depender do estado atual do cadastro.
- Trocas durante a execução não perdem o tempo já contabilizado.

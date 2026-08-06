# REL-001 - Relatórios e exportações de manufatura

## Objetivo

Criar relatórios operacionais e analíticos para os dados de Engenharia, PCP, MES e Análise, superando a atual tela HTML simples.

## Relatórios prioritários

- OPs por status, atraso, produto, planta e prioridade.
- Programa planejado por centro/recurso e aderência executada.
- Previsto x real por operação.
- Consumo previsto x real e desvios.
- Refugo, retrabalho e causas.
- Eficiência por operação, operador, máquina e centro.
- OEE e perdas, quando ANA-003 estiver disponível.
- Histórico de revisão de tempos padrão.

## Escopo técnico

- Filtros por período, empresa/planta, produto, OP, centro, recurso, operador, status, lote e versão.
- Exportação CSV, XLSX e PDF conforme prioridade.
- Paginação, processamento assíncrono para grandes volumes e download seguro.
- Controle de permissão e mascaramento de dados sensíveis.
- Agendamento futuro, distribuição e histórico de execuções.
- Registro de usuário, parâmetros, horário e resultado da exportação.

## Critérios de aceite

- Um relatório reproduz os mesmos números da tela analítica para os mesmos filtros.
- Exportações respeitam tenant e permissões.
- Grandes consultas não bloqueiam a requisição web.
- Falhas de geração ficam registradas e podem ser reprocessadas.

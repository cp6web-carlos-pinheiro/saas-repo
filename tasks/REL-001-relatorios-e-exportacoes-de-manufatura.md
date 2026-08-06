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

## Status da implementação

Implementada a primeira camada de relatórios e exportação CSV.

- Endpoint JSON: `GET /api/v1/manufacturing-reports/{type}`.
- Exportação: `GET /api/v1/manufacturing-reports/{type}/export`.
- Tipos suportados: overview, efficiency/planned-vs-real, oee, consumption e standard-times.
- Os relatórios reutilizam o mesmo `ManufacturingAnalyticsService`, evitando divergência de fórmula entre tela e exportação.
- Permissões de leitura e exportação foram adicionadas.

## Escopo técnico

- Filtros por período, empresa/planta, produto, OP, centro, recurso, operador, status, lote e versão.
- Exportação CSV implementada; XLSX/PDF ficam para próxima etapa.
- Paginação, processamento assíncrono para grandes volumes e download seguro.
- Controle de permissão e mascaramento de dados sensíveis.
- Agendamento futuro, distribuição e histórico de execuções.
- Registro de usuário, parâmetros, horário e resultado da exportação.

## Critérios de aceite

- [x] Relatório e analytics usam o mesmo serviço e filtros.
- [x] Exportação respeita tenant e permissões.
- [ ] Processamento assíncrono, paginação específica de exportação, XLSX/PDF e histórico de execuções ainda pendentes.

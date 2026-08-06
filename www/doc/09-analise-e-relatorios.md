# Análise e relatórios

## Objetivo

Transformar os fatos de produção em indicadores operacionais reproduzíveis e relatórios exportáveis.

## Painel web

- Filtro por período.
- Quantidade planejada e produzida, aderência ao plano e qualidade.
- Tempos de setup e processo.
- Distribuição das ordens por status.
- Inspeções, refugo por dia e produtividade por operação.

## API analítica de manufatura

- Visão geral com fatos de OP, operação, produto, centro, recurso e operador.
- Filtros por período, produto, OP, centro, recurso e operador.
- Previsto versus realizado de tempo e quantidade.
- Eficiência por operação, recurso, centro e operador, limitada a 100% na regra atual.
- Pausas expostas separadamente e incluídas no denominador definido pelo contrato analítico.
- OEE inicial por recurso: disponibilidade × performance × qualidade.
- Aviso para períodos sem dados mínimos.
- Comparação do consumo previsto com o real, separando consumo adicional.

## Revisão de tempos padrão

- Evidência com média, mediana, P90, mínimo, máximo, amostra e outliers.
- Tamanho mínimo de amostra configurável.
- Recomendação persistida sem alterar automaticamente o tempo padrão aprovado.
- Decisões `ACCEPTED`, `REJECTED`, `INVESTIGATE` e `ECO_REQUIRED`, com responsável e justificativa.

## Relatórios

- Relatórios de previsto versus realizado, eficiência, OEE, consumo e qualidade conforme os tipos aceitos pelo controller.
- Resposta estruturada pela API e exportação CSV.
- Controle de acesso específico para leitura e exportação.

## Entidades principais

- Fatos transacionais de ordens, operações, eventos, saídas, consumos e qualidade.
- `manufacturing_analytics_recommendations` para o workflow de revisão.

## Limitações atuais

- Não há exportação XLSX/PDF nem agendamento de relatórios.
- Cache e agregações persistidas para grande volume ainda não substituem as consultas sobre fatos transacionais.

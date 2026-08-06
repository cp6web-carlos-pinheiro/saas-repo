# ANA-004 - Histórico analítico e revisão dos tempos padrão

## Objetivo

Usar tempos realizados e resultados de produção para apoiar a revisão dos tempos padrão pela Engenharia de Processos.

## Escopo

- Comparar tempo padrão, previsto ajustado e real por operação.
- Calcular média, mediana, percentis, desvio, amostra e outliers.
- Separar setup, processo, pausa e parada.
- Filtrar por produto, versão, recurso, centro, operador, lote e período.
- Exigir tamanho mínimo de amostra antes de sugerir alteração.
- Sugerir novo tempo sem alterar automaticamente o cadastro aprovado.
- Registrar decisão da Engenharia: aceitar, rejeitar, investigar ou criar ECO.
- Manter histórico da recomendação e da versão efetivamente aplicada.

## Regras

- Apontamentos corrigidos, retrabalho e paradas classificadas não devem ser misturados sem regra explícita.
- Outliers devem ser identificados e não removidos silenciosamente.
- A recomendação precisa mostrar a origem dos dados.
- Mudança aprovada deve passar por ENG-003 e ENG-002.

## Critérios de aceite

- A Engenharia consegue ver evidência suficiente para revisar um tempo.
- O sistema distingue recomendação de alteração efetiva.
- Uma nova versão de tempo mantém o histórico anterior.
- A OP histórica continua apontando para o tempo usado na época.

# Backlog de Tasks - Engenharia, PCP, MES e Análise

Este diretório contém as tasks necessárias para fechar os pontos pendentes identificados na revisão do sistema. Cada arquivo deve ser revisado e aprovado antes do início do desenvolvimento.

## Ordem sugerida

| Ordem | Grupo | Tasks |
| --- | --- | --- |
| 1 | Fundação de Engenharia | ENG-001, ENG-002, ENG-003 |
| 2 | PCP | PCP-001, PCP-002, PCP-003, PCP-004 |
| 3 | Fundação MES | MES-001, MES-002, MES-003 |
| 4 | Execução MES | MES-004, MES-005 |
| 5 | Análise | ANA-001, ANA-002, ANA-003, ANA-004 |
| 6 | Relatórios e qualidade | REL-001, QA-001 |

## Dependências principais

```text
ENG-001 ─┐
ENG-002 ─┼─> PCP-001 ─> PCP-002 ─> PCP-003
ENG-003 ─┘                         └─> PCP-004

PCP-001 ─> MES-001 ─> MES-002 ─> MES-003 ─> MES-004
MES-001 ─> MES-005

ENG-002 + MES-002 + MES-003 + MES-004 + MES-005
    └─> ANA-001 ─> ANA-002 ─> ANA-003 ─> ANA-004
                         └─> REL-001

Todas as tasks ─> QA-001
```

## Critério de uso

As tasks descrevem o resultado esperado, não uma autorização automática para implementar. Durante a revisão podem ser alterados escopo, regras de negócio, prioridades, nomes de tabelas, endpoints e critérios de aceite.

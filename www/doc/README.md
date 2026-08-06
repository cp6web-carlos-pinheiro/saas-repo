# Documentação funcional do Beyond MRP

Esta pasta é a fonte central da documentação funcional do sistema. O conteúdo descreve as funcionalidades verificadas no código atual, agrupadas por domínio, sem incorporar backlogs ou planos como se fossem entregas concluídas.

## Domínios

1. [Plataforma SaaS e tenancy](01-plataforma-saas-e-tenancy.md)
2. [Identidade, acesso e administração](02-identidade-acesso-e-administracao.md)
3. [Engenharia de produto e processo](03-engenharia-de-produto-e-processo.md)
4. [Estoque, rastreabilidade e genealogia](04-estoque-rastreabilidade-e-genealogia.md)
5. [Compras](05-compras.md)
6. [Vendas](06-vendas.md)
7. [Planejamento, MRP e PCP](07-planejamento-mrp-e-pcp.md)
8. [Produção, MES e qualidade](08-producao-mes-e-qualidade.md)
9. [Análise e relatórios](09-analise-e-relatorios.md)
10. [APIs e operação técnica](10-apis-e-operacao-tecnica.md)

O dicionário de dados está distribuído nesses documentos: cada domínio descreve suas próprias tabelas, colunas e relacionamentos.

## Convenções

- Os dados transacionais são isolados por empresa (`company_id`) por meio do contexto de tenant.
- A interface web usa autenticação de sessão; a API v1 aceita Sanctum e, nos fluxos previstos, JWT.
- Ações protegidas exigem permissões granulares do domínio.
- Estados e códigos técnicos são apresentados em maiúsculas porque correspondem aos valores persistidos.
- “Limitações atuais” descreve apenas fronteiras observadas na implementação, não um backlog comprometido.
- O schema MySQL corrente possui 86 tabelas; outras seis estruturas removidas permanecem documentadas como histórico nos respectivos domínios.
- A consolidação `2026_08_09_000001` tornou `companies` a única raiz de tenancy e `production_operation_outputs` a única fonte de apontamentos de produção.

## Atualização

Ao entregar uma funcionalidade, atualize o arquivo do domínio correspondente. Crie um novo arquivo somente quando surgir um domínio funcional independente.

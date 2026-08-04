# Fase 17 - SaaS

## Objetivo
Implementar o modelo comercial da plataforma.

## Status de implementacao
Parcial. Acesso gratuito inicial de 14 dias, onboarding, organizacao, assinatura e base de planos existem; cobranca recorrente, provider hooks e gestao comercial completa ainda sao parciais.

## Escopo
- Planos: parcial
- Acesso gratuito inicial (14 dias): implementado
- Assinaturas: parcial
- Stripe: nao iniciado
- Mercado Pago: nao iniciado
- Asaas: nao iniciado
- Faturas: nao iniciado
- Renovacao: nao iniciado
- Cancelamento: parcial
- Upgrade: nao iniciado
- Downgrade: nao iniciado
- Uso: nao iniciado
- Limites: nao iniciado

## Criterios para 100% implementado
- Catalogo comercial completo: planos, recursos, limites, precificacao e regras de contratacao.
- Ciclo de assinatura completo: trial, conversao, renovacao, upgrade, downgrade, cancelamento e reativacao.
- Cobranca completa: gateways priorizados, faturas, retries, inadimplencia e bloqueio gradual de acesso.
- Governanca de uso completo: medicao de consumo, limites por plano e alertas preventivos.
- Billing ops completo: conciliacao financeira, webhook de provedores e suporte a estorno.
- Compliance completo: termos, consentimentos, trilha de faturamento e retencao de dados financeiros.
- Qualidade: testes de regras de plano, cobranca e transicoes de assinatura.

## Pendencias por dependencia e area

### Por dependencia
- Dependencia de billing: integrar gateways de cobranca e webhooks de ciclo financeiro.
- Dependencia de produto: concluir limites por plano e medicao de uso.
- Dependencia de operacao comercial: fechar fluxos de upgrade, downgrade, renovacao e recuperacao de inadimplencia.

### Por area
- Area Comercial/SaaS: definir estrategias de plano, precificacao e politicas de churn.
- Area Financeira: homologar faturamento, conciliacao e estornos.
- Area de Engenharia: implementar automacoes de assinatura e governanca de consumo.


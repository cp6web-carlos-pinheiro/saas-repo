# MES-001 - Operações executáveis da Ordem de Produção

## Objetivo

Criar a camada operacional que representa cada operação da OP e serve de base para apontamento, cronômetro, pausas, consumo, qualidade e análise.

## Contexto atual

Existe `production_order_outputs` para registrar quantidade produzida/refugada e tempos informados, mas não há uma entidade de operação executada com status e ciclo de vida próprio.

## Escopo funcional

- Gerar operações da OP a partir do routing snapshot e de PCP-001.
- Status: planejada, liberada, pronta, em execução, pausada, concluída, bloqueada, cancelada e retrabalho.
- Registrar sequência, dependências, centro, recurso, operador elegível, tempos padrão/previstos e quantidades.
- Permitir iniciar apenas quando pré-requisitos forem atendidos.
- Permitir apontar parcialmente e concluir operação sem concluir a OP automaticamente de forma indevida.
- Consolidar outputs, consumos e eventos na operação correta.

## Regras

- A operação usa o routing snapshot da OP, não o cadastro corrente.
- Sequência e dependências devem ser respeitadas, salvo permissão de exceção.
- Quantidades boas, refugo e retrabalho precisam fechar com a quantidade processada.
- Uma operação não pode ser concluída duas vezes.

## Critérios de aceite

- Liberar uma OP cria operações executáveis ordenadas.
- A tela da OP mostra progresso por operação.
- É possível apontar produção em uma operação sem misturar dados de outra.
- A conclusão da última operação atualiza a OP segundo regras de quantidade e qualidade.
- Testes cobrem paralelismo, apontamento parcial, reabertura autorizada e tenant.

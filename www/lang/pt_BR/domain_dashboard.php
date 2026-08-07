<?php

return [
    'pending' => 'Pendências', 'in_progress' => 'Em andamento', 'no_pending' => 'Nenhuma pendência mapeada para este domínio.', 'no_in_progress' => 'Nenhum item em andamento para este domínio.', 'shortcuts' => 'Atalhos de gerenciamento', 'shortcuts_hint' => 'Acesse rapidamente as telas operacionais deste domínio.',
    'severity' => ['critical' => 'Crítico', 'attention' => 'Atenção', 'normal' => 'Normal'],
    'descriptions' => [
        'engineering' => 'Visão das estruturas de produto e processo com foco em pendências técnicas e liberações.',
        'planning' => 'Acompanhe planejamento MRP e programação da produção com foco em filas e publicação de planos.',
        'shop_floor' => 'Monitore execução no chão de fábrica, gargalos de início e qualidade em aberto.',
        'analysis' => 'Acompanhe indicadores operacionais, recomendações e pontos que exigem ação imediata.',
        'inventory' => 'Controle rápido de reservas, criticidades de saldo e movimentações recentes.',
        'purchasing' => 'Consolide pendências de suprimentos e acompanhe o fluxo de compras em execução.',
        'sales' => 'Visualize rapidamente o funil operacional de vendas e acesse os cadastros principais.',
        'administration' => 'Gerencie acessos e governança com foco em convites, perfis e pendências administrativas.',
    ],
    'shortcuts_labels' => ['new_schedule' => 'Nova programação', 'generate_calendar' => 'Gerar calendário', 'new_order' => 'Nova ordem de produção', 'new_movement' => 'Novo movimento', 'new_sale' => 'Novo pedido de venda', 'new_customer' => 'Novo cliente'],
    'metrics' => [
        'draft_product_versions' => 'Versões de produto em rascunho com aging > :days d', 'draft_boms' => 'BOMs em rascunho com aging > :days d', 'draft_routings' => 'Roteiros em rascunho com aging > :days d', 'ecos_in_progress' => 'ECOs em andamento', 'versions_awaiting_approval' => 'Versões aguardando aprovação',
        'overdue_mrp_suggestions' => 'Sugestões MRP em atraso (necessidade vencida)', 'high_priority_mrp' => 'Sugestões MRP de alta prioridade (≤ :priority)', 'draft_schedules' => 'Programas em rascunho com aging > 2 d', 'mrp_runs' => 'Execuções MRP em andamento', 'draft_orders_priority' => 'Ordens em rascunho com prioridade de liberação (aging > 1 d)',
        'released_orders_sla' => 'Ordens liberadas com SLA estourado (> :days d)', 'late_orders' => 'Ordens em andamento atrasadas em relação ao fim planejado', 'open_reworks' => 'Retrabalhos abertos com aging > :days d', 'orders_in_progress' => 'Ordens em andamento', 'pending_quality' => 'Registros de qualidade pendentes',
        'pending_recommendations' => 'Recomendações pendentes com aging > :days d', 'quality_closing' => 'Qualidade pendente de fechamento', 'partial_postings' => 'Ordens com apontamentos parciais', 'today_postings' => 'Apontamentos de hoje',
        'high_priority_reservations' => 'Reservas de alta prioridade (≤ 20)', 'expired_reservations' => 'Reservas vencidas e ainda ativas', 'low_stock' => 'Itens abaixo do estoque de segurança', 'today_movements' => 'Movimentos de hoje', 'today_transfers' => 'Transferências de hoje', 'inspection_items' => 'Itens em inspeção',
        'overdue_requisition_lines' => 'Linhas de requisição vencidas (necessidade < hoje)', 'late_purchase_orders' => 'Pedidos de compra com entrega atrasada', 'draft_receipts' => 'Recebimentos em rascunho com aging > 2 d', 'open_purchase_orders' => 'Pedidos de compra em aberto', 'urgent_requisition_lines' => 'Linhas de requisição urgentes (necessidade ≤ D+3)',
        'draft_sales' => 'Pedidos em rascunho', 'pending_sales_sla' => 'Pedidos confirmados pendentes com SLA estourado (> :days d)', 'pending_sales_total' => 'Pedidos confirmados pendentes (total)', 'sales_in_fulfillment' => 'Pedidos em separação/faturamento/expedição', 'shipped_sales_aging' => 'Pedidos expedidos com aging > 2 d',
        'pending_invitations_sla' => 'Convites pendentes com SLA estourado (> :days d)', 'pending_access_approvals' => 'Aprovações de acesso pendentes com aging > 2 d', 'active_company_users' => 'Usuários ativos com acesso à empresa', 'today_invitations' => 'Convites enviados hoje',
    ],
];

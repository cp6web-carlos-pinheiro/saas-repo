<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PageTutorial;
use Illuminate\Database\Seeder;

final class PageTutorialSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->tutorialDefinitions() as $definition) {
            $html = $this->buildHtml(
                (string) $definition['summary'],
                (array) $definition['fields']
            );

            foreach ((array) $definition['routes'] as $routeName) {
                PageTutorial::query()->updateOrCreate(
                    ['route_name' => (string) $routeName],
                    [
                        'title' => (string) $definition['title'],
                        'content_html' => $html,
                    ]
                );
            }
        }
    }

    /**
     * @return array<int, array{title: string, summary: string, routes: array<int, string>, fields: array<int, string>}>
     */
    private function tutorialDefinitions(): array
    {
        return [
            $this->definition(
                'Cadastro Inicial de Conta',
                'Cria a conta inicial do administrador e define idioma e aceite de termos.',
                ['start-trial', 'company-signup'],
                ['name', 'email', 'password', 'password_confirmation', 'preferred_locale', 'terms']
            ),
            $this->definition(
                'Login',
                'Autentica o usuário no sistema e permite manter a sessão ativa.',
                ['login'],
                ['email', 'password', 'remember']
            ),
            $this->definition(
                'Redefinição de Senha',
                'Permite definir uma nova senha usando token de recuperação.',
                ['password.reset'],
                ['token', 'email', 'password', 'password_confirmation']
            ),
            $this->definition(
                'Verificação MFA',
                'Valida o código de segundo fator para concluir o acesso.',
                ['mfa.challenge'],
                ['code']
            ),
            $this->definition(
                'Aceite de Convite',
                'Conclui ativação de usuário convidado em uma conta existente.',
                ['account-invitations.show'],
                ['name', 'password', 'password_confirmation']
            ),
            $this->definition(
                'Onboarding da Empresa',
                'Configura dados iniciais da empresa, perfil operacional e plano.',
                ['onboarding.wizard'],
                ['company_name', 'company_domain', 'timezone', 'segment', 'operation_size', 'emails', 'plan_code']
            ),
            $this->definition(
                'Pagamento do Onboarding',
                'Coleta dados do cartão para ativação do plano durante onboarding.',
                ['onboarding.payment.create'],
                ['card_holder_name', 'card_number', 'card_exp_month', 'card_exp_year', 'card_cvv']
            ),
            $this->definition(
                'Assinatura e Planos',
                'Permite renovar ou alterar o plano da conta ativa.',
                ['billing.subscription.show'],
                ['plan_code']
            ),
            $this->definition(
                'Administradores Globais',
                'Gerencia contas administrativas da plataforma com acesso ao Global Admin.',
                ['global-admin.administrators.create', 'global-admin.administrators.edit'],
                ['name', 'email', 'password', 'password_confirmation', 'is_active']
            ),
            $this->definition(
                'Empresas Globais',
                'Cadastra e atualiza empresas da base global do sistema.',
                ['global-admin.companies.create', 'global-admin.companies.edit'],
                ['name', 'code', 'is_active']
            ),
            $this->definition(
                'Clientes Globais',
                'Cadastra usuários de clientes e define vínculo de empresa e perfil de acesso.',
                ['global-admin.customers.create', 'global-admin.customers.edit'],
                ['name', 'email', 'password', 'password_confirmation', 'company_id', 'access_profile', 'modules[]', 'is_active', 'return_to_company_id']
            ),
            $this->definition(
                'Planos de Assinatura',
                'Configura planos comerciais e regras de cobrança da plataforma.',
                ['global-admin.plans.create', 'global-admin.plans.edit'],
                ['code', 'label', 'description', 'payment_method', 'amount', 'billing_cycle_label', 'trial_days', 'interval_months', 'default_status', 'sort_order', 'renewable', 'allow_once', 'is_active']
            ),
            $this->definition(
                'Tutoriais Globais',
                'Gerencia os tutoriais contextuais por rota, compartilhados por todos os tenants.',
                ['global-admin.tutorials.create', 'global-admin.tutorials.edit'],
                ['route_name', 'title', 'content_html']
            ),
            $this->definition(
                'Usuários da Empresa (Acessos)',
                'Cadastra usuários internos da empresa e define papel/permissões no tenant ativo.',
                ['company-access.users.create', 'company-access.users.edit'],
                ['name', 'email', 'password', 'password_confirmation', 'role_id', 'is_active']
            ),
            $this->definition(
                'RBAC - Perfis',
                'Cadastra perfis de acesso e vincula permissões operacionais por empresa.',
                ['company-access.rbac.roles.create', 'company-access.rbac.roles.edit'],
                ['name', 'slug', 'description', 'permission_ids[]']
            ),
            $this->definition(
                'Marcas',
                'Mantém marcas de produtos para classificação e filtros.',
                ['admin-data.brands.create', 'admin-data.brands.edit'],
                ['name', 'description', 'is_active']
            ),
            $this->definition(
                'Categorias de Produto',
                'Mantém categorias utilizadas no cadastro de produtos.',
                ['admin-data.categories.create', 'admin-data.categories.edit'],
                ['name', 'description', 'is_active']
            ),
            $this->definition(
                'Unidades de Medida',
                'Mantém unidades de medida utilizadas em produtos, BOM e movimentações.',
                ['admin-data.units.create', 'admin-data.units.edit'],
                ['name', 'code', 'description', 'is_active']
            ),
            $this->definition(
                'Revisões de BOM',
                'Define estrutura de materiais de um produto e validade da revisão.',
                ['bom.material-lists.create', 'bom.material-lists.edit'],
                ['product_id', 'status', 'effective_from', 'effective_to', 'description', 'items[__INDEX__][component_product_id]', 'items[__INDEX__][quantity_per]', 'items[__INDEX__][unit_id]']
            ),
            $this->definition(
                'Clientes (Vendas)',
                'Cadastra clientes comerciais para uso em pedidos de venda.',
                ['customers.create', 'customers.edit'],
                ['name', 'person_type', 'email', 'phone', 'status']
            ),
            $this->definition(
                'Plantas',
                'Mantém plantas industriais e seus fusos de operação.',
                ['inventory.plants.create', 'inventory.plants.edit'],
                ['name', 'timezone', 'is_active']
            ),
            $this->definition(
                'Armazéns',
                'Mantém armazéns vinculados às plantas para estoque e operação logística.',
                ['inventory.warehouses.create', 'inventory.warehouses.edit'],
                ['name', 'plant_id', 'is_active']
            ),
            $this->definition(
                'Calendário de Produção',
                'Configura capacidade e disponibilidade por centro de trabalho e data.',
                ['production.calendar.create', 'production.calendar.edit'],
                ['work_center_id', 'calendar_date', 'is_working_day', 'available_capacity', 'notes']
            ),
            $this->definition(
                'Ordem de Produção',
                'Cria ordem de produção a partir de produto elegível e agenda operacional.',
                ['production.orders.create'],
                ['product_id', 'warehouse_id', 'quantity_planned', 'scheduled_start_date', 'scheduled_end_date']
            ),
            $this->definition(
                'Roteiros de Produção',
                'Cadastra versões de roteiro (routing) e validade para execução fabril.',
                ['production.routing.create', 'production.routing.edit'],
                ['product_id', 'version_number', 'description', 'effective_from', 'effective_to']
            ),
            $this->definition(
                'Programação da Produção',
                'Executa cálculo de programação de ordens por modo, direção e regra de sequenciamento.',
                ['production.scheduling.create', 'production.scheduling.edit'],
                ['reference_date', 'mode', 'direction', 'sequencing_rule', 'production_order_ids[]']
            ),
            $this->definition(
                'Centros de Trabalho',
                'Mantém recursos produtivos, capacidade diária e eficiência operacional.',
                ['production.work-centers.create', 'production.work-centers.edit'],
                ['name', 'code', 'plant_id', 'resource_type', 'capacity_per_day', 'efficiency_factor', 'is_active']
            ),
            $this->definition(
                'Produtos',
                'Cadastra itens mestres com atributos técnicos, comerciais e parâmetros logísticos.',
                ['products.create', 'products.edit'],
                ['sku', 'description', 'product_type', 'lifecycle_status', 'unit_id', 'category_id', 'brand_id', 'lead_time_days', 'safety_stock', 'lot_control', 'serial_control', 'technical_attributes_json', 'commercial_attributes_json', 'image_urls_json', 'attachment_urls_json', 'alternate_uoms_json', 'is_active']
            ),
            $this->definition(
                'Versão de Produto',
                'Cria e revisa versões técnicas de produto e payload versionado.',
                ['products.versions.create', 'products.versions.edit'],
                ['effective_from', 'effective_to', 'compatibility_rule', 'change_summary', 'payload_json']
            ),
            $this->definition(
                'Pedidos de Compra',
                'Emite pedidos de compra com fornecedor, datas e itens de suprimento.',
                ['purchasing.orders.create', 'purchasing.orders.edit'],
                ['supplier_id', 'purchase_requisition_id', 'order_date', 'expected_delivery_date', 'status', 'notes', 'items[__INDEX__][product_id]', 'items[__INDEX__][warehouse_id]', 'items[__INDEX__][quantity]', 'items[__INDEX__][unit_price]', 'items[__INDEX__][need_by_date]', 'items[__INDEX__][promised_date]']
            ),
            $this->definition(
                'Cotações de Compra',
                'Registra cotações por fornecedor com itens, preços e validade.',
                ['purchasing.quotations.create', 'purchasing.quotations.edit'],
                ['supplier_id', 'purchase_requisition_id', 'quotation_date', 'valid_until', 'status', 'notes', 'items[__INDEX__][product_id]', 'items[__INDEX__][quantity]', 'items[__INDEX__][unit_price]', 'items[__INDEX__][notes]']
            ),
            $this->definition(
                'Recebimentos de Compra',
                'Registra recebimento físico/fiscal de itens vinculados a pedidos.',
                ['purchasing.receipts.create', 'purchasing.receipts.edit'],
                ['supplier_id', 'purchase_order_id', 'receipt_date', 'status', 'notes', 'items[__INDEX__][purchase_order_line_id]', 'items[__INDEX__][product_id]', 'items[__INDEX__][warehouse_id]', 'items[__INDEX__][quantity_received]', 'items[__INDEX__][lot_number]', 'items[__INDEX__][notes]']
            ),
            $this->definition(
                'Requisições de Compra',
                'Solicita materiais para suprimentos com prazos e origem da demanda.',
                ['purchasing.requisitions.create', 'purchasing.requisitions.edit'],
                ['source_type', 'required_date', 'status', 'notes', 'items[__INDEX__][product_id]', 'items[__INDEX__][warehouse_id]', 'items[__INDEX__][supplier_id]', 'items[__INDEX__][quantity]', 'items[__INDEX__][need_by_date]', 'items[__INDEX__][order_date]']
            ),
            $this->definition(
                'Vendas',
                'Registra transações comerciais com cliente, itens e condições da venda.',
                ['sales.create', 'sales.edit'],
                ['customer_id', 'sale_date', 'status', 'discount_amount', 'cancel_reason', 'notes', 'items[__INDEX__][product_id]', 'items[__INDEX__][quantity]', 'items[__INDEX__][unit_price]']
            ),
            $this->definition(
                'Fornecedores',
                'Cadastra fornecedores e parâmetros padrão de negociação.',
                ['purchasing.suppliers.create', 'purchasing.suppliers.edit'],
                ['name', 'person_type', 'email', 'phone', 'payment_terms', 'default_lead_time_days', 'status']
            ),
        ];
    }

    /**
     * @param array<int, string> $routes
     * @param array<int, string> $fields
     * @return array{title: string, summary: string, routes: array<int, string>, fields: array<int, string>}
     */
    private function definition(string $title, string $summary, array $routes, array $fields): array
    {
        return [
            'title' => $title,
            'summary' => $summary,
            'routes' => $routes,
            'fields' => $fields,
        ];
    }

    /**
     * @param array<int, string> $fields
     */
    private function buildHtml(string $summary, array $fields): string
    {
        $normalizedFields = array_values(array_unique($fields));
        $items = [];

        foreach ($normalizedFields as $field) {
            $items[] = sprintf(
                '<li><strong><code>%s</code></strong>: %s</li>',
                e($field),
                e($this->fieldDescription($field))
            );
        }

        return sprintf(
            '<h3>Objetivo</h3><p>%s</p><h3>Campos do formulário</h3><ul>%s</ul><p><strong>Dica:</strong> revise os campos obrigatórios antes de salvar para evitar inconsistências.</p>',
            e($summary),
            implode('', $items)
        );
    }

    private function fieldDescription(string $field): string
    {
        $base = preg_replace('/^items\[[^\]]+\]\[([^\]]+)\]$/', '$1', $field) ?? $field;
        $base = str_replace('[]', '', $base);

        return match ($base) {
            'name' => 'Nome principal do registro exibido nas listas e buscas.',
            'email' => 'E-mail de contato/login, usado para comunicação e autenticação quando aplicável.',
            'preferred_locale' => 'Idioma preferencial da interface para o usuário.',
            'terms' => 'Confirmação obrigatória de aceite dos termos e política de privacidade.',
            'password' => 'Senha de acesso do usuário cadastrado.',
            'password_confirmation' => 'Confirmação da senha para evitar erros de digitação.',
            'remember' => 'Mantém o usuário autenticado por mais tempo neste dispositivo.',
            'token' => 'Token de segurança enviado para autorizar redefinição da senha.',
            'code' => 'Código de verificação de múltiplo fator (MFA).',
            'is_active' => 'Define se o registro ficará ativo para uso nas operações.',
            'code' => 'Código curto e único para identificação rápida.',
            'access_profile' => 'Perfil de acesso padrão do usuário na empresa.',
            'company_id' => 'Empresa à qual o usuário será vinculado.',
            'modules' => 'Conjunto de módulos liberados quando o perfil for customizado.',
            'return_to_company_id' => 'Empresa de retorno após salvar no fluxo administrativo.',
            'role_id' => 'Papel (role) que define permissões do usuário no tenant.',
            'slug' => 'Identificador técnico único usado internamente nas regras de acesso.',
            'permission_ids' => 'Permissões operacionais associadas ao perfil.',
            'description' => 'Descrição complementar para contextualizar o cadastro.',
            'route_name' => 'Nome técnico da rota/página onde o tutorial será exibido.',
            'title' => 'Título amigável para o tutorial desta página.',
            'content_html' => 'Conteúdo em HTML que será renderizado no painel de ajuda.',
            'product_id' => 'Produto principal associado ao processo.',
            'component_product_id' => 'Componente utilizado na composição da estrutura.',
            'quantity_per' => 'Quantidade do componente por unidade do produto principal.',
            'unit_id' => 'Unidade de medida usada para quantidades do item.',
            'effective_from' => 'Data inicial de vigência do registro/versão.',
            'effective_to' => 'Data final de vigência; deixe vazio quando aplicável para vigência aberta.',
            'status' => 'Situação atual do documento/processo no fluxo operacional.',
            'person_type' => 'Tipo de pessoa (física/jurídica) para regras cadastrais e fiscais.',
            'phone' => 'Telefone principal para contato.',
            'default_lead_time_days' => 'Prazo padrão de atendimento em dias para planejamento.',
            'payment_terms' => 'Condições de pagamento negociadas com fornecedor/cliente.',
            'company_name' => 'Nome fantasia/razão da empresa no onboarding.',
            'company_domain' => 'Domínio principal usado para identificação da organização.',
            'segment' => 'Segmento de atuação para parametrização inicial da conta.',
            'operation_size' => 'Porte operacional da empresa para configuração base.',
            'emails' => 'Lista de e-mails para convite dos usuários iniciais da operação.',
            'timezone' => 'Fuso horário utilizado para datas e horários da planta.',
            'plant_id' => 'Planta industrial responsável pelo registro.',
            'warehouse_id' => 'Armazém/estoque relacionado ao item ou movimento.',
            'work_center_id' => 'Centro de trabalho utilizado no calendário produtivo.',
            'calendar_date' => 'Data de referência do calendário de produção.',
            'is_working_day' => 'Indica se a data é útil para capacidade produtiva.',
            'available_capacity' => 'Capacidade disponível na data informada.',
            'notes' => 'Observações operacionais adicionais.',
            'quantity_planned' => 'Quantidade planejada para produção.',
            'scheduled_start_date' => 'Data prevista de início da execução.',
            'scheduled_end_date' => 'Data prevista de término da execução.',
            'version_number' => 'Número da revisão/versão do documento técnico.',
            'reference_date' => 'Data base para cálculo de programação/MRP.',
            'mode' => 'Modo de programação (capacidade finita ou infinita).',
            'direction' => 'Direção do cálculo: para frente ou para trás.',
            'sequencing_rule' => 'Regra usada para ordenar a execução das ordens.',
            'production_order_ids' => 'Lista de ordens de produção incluídas no cálculo.',
            'capacity_per_day' => 'Capacidade produtiva nominal por dia.',
            'efficiency_factor' => 'Fator de eficiência aplicado à capacidade do recurso.',
            'resource_type' => 'Tipo de recurso do centro de trabalho.',
            'sku' => 'Código SKU único do produto.',
            'product_type' => 'Classificação do item (acabado, matéria-prima etc.).',
            'lifecycle_status' => 'Estado do ciclo de vida do produto.',
            'category_id' => 'Categoria comercial/técnica do produto.',
            'brand_id' => 'Marca associada ao produto.',
            'lead_time_days' => 'Prazo de reposição/produção em dias.',
            'safety_stock' => 'Estoque de segurança para planejamento.',
            'lot_control' => 'Define se o item exige controle por lote.',
            'serial_control' => 'Define se o item exige controle por número de série.',
            'technical_attributes_json' => 'Atributos técnicos em formato estruturado JSON.',
            'commercial_attributes_json' => 'Atributos comerciais em formato estruturado JSON.',
            'image_urls_json' => 'Lista JSON de URLs de imagens do produto.',
            'attachment_urls_json' => 'Lista JSON de anexos/documentos relacionados.',
            'alternate_uoms_json' => 'Conversões de unidades alternativas em JSON.',
            'payload_json' => 'Dados técnicos versionados em JSON.',
            'compatibility_rule' => 'Regra de compatibilidade entre versões.',
            'change_summary' => 'Resumo das mudanças introduzidas na versão.',
            'supplier_id' => 'Fornecedor principal do documento de compras.',
            'required_date' => 'Data em que o material deve estar disponível.',
            'source_type' => 'Origem da demanda (manual, MRP, produção etc.).',
            'order_date' => 'Data de emissão do pedido/requisição.',
            'need_by_date' => 'Prazo necessário por item para atendimento da demanda.',
            'quantity' => 'Quantidade solicitada para o item.',
            'purchase_requisition_id' => 'Requisição que originou o documento atual.',
            'quotation_date' => 'Data de emissão da cotação.',
            'valid_until' => 'Data de validade da cotação recebida.',
            'unit_price' => 'Preço unitário negociado para o item.',
            'expected_delivery_date' => 'Previsão de entrega acordada no pedido.',
            'purchase_order_id' => 'Pedido de compra vinculado ao recebimento.',
            'receipt_date' => 'Data de entrada física/fiscal dos materiais.',
            'purchase_order_line_id' => 'Linha específica do pedido de compra recebida.',
            'quantity_received' => 'Quantidade efetivamente recebida.',
            'lot_number' => 'Lote informado no recebimento quando aplicável.',
            'sale_date' => 'Data da venda.',
            'customer_id' => 'Cliente associado à venda.',
            'discount_amount' => 'Desconto aplicado ao documento de venda.',
            'cancel_reason' => 'Motivo obrigatório em caso de cancelamento.',
            'default_status' => 'Status inicial padrão do plano/documento.',
            'plan_code' => 'Código do plano selecionado para assinatura.',
            'amount' => 'Valor monetário principal do plano.',
            'payment_method' => 'Método de pagamento padrão.',
            'billing_cycle_label' => 'Descrição amigável do ciclo de cobrança.',
            'trial_days' => 'Quantidade de dias do período de teste.',
            'interval_months' => 'Intervalo de renovação em meses.',
            'sort_order' => 'Ordem de exibição nas listagens.',
            'renewable' => 'Indica se o plano pode ser renovado.',
            'allow_once' => 'Indica se o plano pode ser contratado apenas uma vez.',
            'card_holder_name' => 'Nome impresso no cartão utilizado para pagamento.',
            'card_number' => 'Número do cartão para tokenização/autorização.',
            'card_exp_month' => 'Mês de expiração do cartão.',
            'card_exp_year' => 'Ano de expiração do cartão.',
            'card_cvv' => 'Código de segurança do cartão.',
            default => 'Campo operacional deste formulário; preencha conforme a regra de negócio da tela.',
        };
    }
}

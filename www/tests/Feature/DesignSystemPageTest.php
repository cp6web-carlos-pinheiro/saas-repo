<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DesignSystemPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_design_system_page(): void
    {
        $this->get(route('global-admin.design-system'))
            ->assertRedirect(route('global-admin.login'));
    }

    public function test_authenticated_global_admin_can_view_design_system_catalog(): void
    {
        $this->withoutVite();

        $admin = Admin::query()->create([
            'name' => 'Global Admin',
            'email' => 'global-admin@test.dev',
            'password' => 'Strong!Pass123',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('global-admin.design-system'))
            ->assertOk()
            ->assertViewIs('docs.design-system')
            ->assertSee('Beyond MRP Layout System')
            ->assertSee('Claro, escuro e sistema')
            ->assertSee('Cores e estados')
            ->assertSee('Campos e controles de seleção')
            ->assertSee('Tabelas e gráficos')
            ->assertSee('Ações e navegação contextual')
            ->assertSee('Modais e sheets')
            ->assertSee('Tabler Icons')
            ->assertSee('Fabricação de barcos')
            ->assertSee('Embarcação e navegação')
            ->assertSee('Fabricação e montagem')
            ->assertSee('Movimentação e logística')
            ->assertSee('Sistemas de bordo')
            ->assertSee('Qualidade e segurança')
            ->assertSee('Controle dimensional')
            ->assertSee('Combate a incêndio')
            ->assertSee('Furação')
            ->assertSee('Içamento')
            ->assertSee('Inspeção')
            ->assertSee('Como usar')
            ->assertSee('Como usar o layout')
            ->assertSee('Como usar os tokens semânticos')
            ->assertSee('Como usar o botão')
            ->assertSee('Monte seu botão')
            ->assertSee('data-ui-button-playground', false)
            ->assertSee('data-ui-button-control="variant"', false)
            ->assertSee('data-ui-readonly="true"', false)
            ->assertSee('min-h-12 px-6 py-3 text-base', false)
            ->assertSee('Como usar campo e input')
            ->assertSee('Como usar o select')
            ->assertSee('Modelos compostos')
            ->assertSee('Como usar prefixo e sufixo')
            ->assertSee('Como usar input com ícone')
            ->assertSee('Como usar select com busca')
            ->assertSee('Como usar select múltiplo')
            ->assertSee('name="ds_multiple_components[]"', false)
            ->assertSee('multiple', false)
            ->assertSee('data-search="on"', false)
            ->assertSee('data-ui-select2="true"', false)
            ->assertSee('ui-input-addon', false)
            ->assertSee('Como usar o textarea')
            ->assertSee('Como usar o checkbox')
            ->assertSee('Como usar o switch')
            ->assertSee('Como usar o radio')
            ->assertSee('Slider e progresso')
            ->assertSee('Como usar o slider')
            ->assertSee('Como usar o progresso')
            ->assertSee('data-ui-slider', false)
            ->assertSee('role="progressbar"', false)
            ->assertSee('Componentes compostos')
            ->assertSee('Accordion e Collapsible')
            ->assertSee('data-ui-accordion', false)
            ->assertSee('ui-collapsible', false)
            ->assertSee('Attachment e Item')
            ->assertSee('data-ui-attachment', false)
            ->assertSee('ui-badge-success', false)
            ->assertSee('role="group"', false)
            ->assertSee('ui-button-group-joined-primary', false)
            ->assertSee('ui-button-group-joined-outline', false)
            ->assertSee('ui-button-group-joined-surface', false)
            ->assertSee('Input Group')
            ->assertSee('Calendar e Date Picker')
            ->assertSee('data-ui-calendar', false)
            ->assertSee('data-ui-date-picker', false)
            ->assertSee('Gráficos')
            ->assertSee('ui-chart-line', false)
            ->assertSee('ui-chart-area', false)
            ->assertSee('ui-chart-horizontal', false)
            ->assertSee('ui-chart-donut', false)
            ->assertSee('6 exemplos')
            ->assertSee('Capacidade por setor')
            ->assertSee('Status das ordens')
            ->assertSee('Data Table')
            ->assertSee('data-ui-data-table', false)
            ->assertSee('data-ui-data-table-filter', false)
            ->assertSee('data-ui-data-table-previous', false)
            ->assertSee('data-ui-data-table-next', false)
            ->assertSee('data-ui-table-cell-copy', false)
            ->assertSee('data-ui-table-cell-edit', false)
            ->assertSee('data-ui-table-cell-save', false)
            ->assertSee('data-ui-table-cell-cancel', false)
            ->assertSee('data-ui-table-row-edit', false)
            ->assertSee('data-ui-copy-text=', false)
            ->assertSee('data-ui-tooltip', false)
            ->assertSee('Como usar Accordion e Collapsible')
            ->assertSee('Como usar Calendar e Date Picker')
            ->assertSee('Como usar Data Table')
            ->assertSee('Como usar a tabela')
            ->assertSee('Como usar o alert')
            ->assertSee('Como usar o dropdown')
            ->assertSee('Como usar as tabs')
            ->assertSee('Como usar o modal ou sheet')
            ->assertSee('data-ui-copy-code', false)
            ->assertSee('data-theme-option="dark"', false)
            ->assertSee('data-ds-sidebar-shell', false)
            ->assertSee('data-ds-sidebar-toggle', false)
            ->assertSee('data-ds-sidebar-submenu-toggle', false)
            ->assertSee('data-ds-sidebar-submenu', false)
            ->assertSee('Produtos e modelos')
            ->assertSee('Planejamento MRP')
            ->assertSee('Ordens em execução')
            ->assertSee('Saldos e disponibilidade')
            ->assertSee('Chão de fábrica')
            ->assertSee('data-ui-modal-open="ds-tutorial-panel"', false)
            ->assertSee('data-ui-modal-open="ds-preferences-panel"', false)
            ->assertSee('data-ui-demo-toast', false)
            ->assertSee('data-ui-demo-message=', false)
            ->assertSee('data-ui-alert-trigger="all"', false)
            ->assertSee('data-ui-demo-alert="success"', false)
            ->assertSee('&lt;x-ui.icon name=&quot;ship&quot; /&gt;', false)
            ->assertSee('aria-busy="true"', false)
            ->assertSee('data-ui-dropdown', false)
            ->assertSee('role="tablist"', false)
            ->assertSee('data-ui-modal-size="sheet"', false);

        $this->get(route('global-admin.docs.show', ['file' => '11-design-system.md']))
            ->assertOk()
            ->assertSee('href="/global-admin/design-system"', false);
    }
}

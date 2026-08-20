<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

final class LayoutSystemComplianceTest extends TestCase
{
    public function test_field_renders_label_required_hint_and_accessible_error_contract(): void
    {
        $hint = Blade::render('<x-ui.field label="Code" for="code" hint="Public identifier" required><x-ui.input id="code" aria-describedby="code-hint" /></x-ui.field>');
        $error = Blade::render('<x-ui.field label="Code" for="code" error="Invalid code"><x-ui.input id="code" aria-describedby="code-error" /></x-ui.field>');

        $this->assertStringContainsString('for="code"', $hint);
        $this->assertStringContainsString('id="code-hint"', $hint);
        $this->assertStringContainsString('aria-describedby="code-hint"', $hint);
        $this->assertStringContainsString(__('ui.required'), $hint);
        $this->assertStringContainsString('id="code-error"', $error);
        $this->assertStringContainsString('aria-describedby="code-error"', $error);
    }

    public function test_functional_views_do_not_reintroduce_legacy_visual_contracts(): void
    {
        $violations = [];

        foreach ($this->functionalViews() as $file) {
            $contents = file_get_contents($file->getPathname());
            $relative = str_replace(resource_path('views').'/', '', $file->getPathname());

            foreach ([
                'legacy button variant' => '/variant=["\'](?:brand-primary|material-(?:back|edit|remove|versions)|surface-muted)["\']/',
                'legacy client class' => '/\bind-[A-Za-z0-9_-]+/',
                'inline row navigation' => '/(?:onclick="window\.location|onkeydown="[^"]*window\.location)/',
                'direct Tailwind palette' => '/\b(?:bg|text|border)-(?:slate|gray|red|blue)-[0-9]{2,3}\b/',
                'fixed hexadecimal color' => '/#[0-9A-Fa-f]{6}\b/',
            ] as $contract => $pattern) {
                if (preg_match($pattern, $contents) === 1) {
                    $violations[] = $relative.': '.$contract;
                }
            }
        }

        $this->assertSame([], $violations, implode(PHP_EOL, $violations));
    }

    public function test_client_pages_and_crud_forms_use_shared_structural_components(): void
    {
        $headings = [];
        $fields = [];

        foreach ($this->functionalViews() as $file) {
            $path = str_replace('\\', '/', $file->getPathname());
            $contents = file_get_contents($path);
            $relative = str_replace(resource_path('views').'/', '', $path);

            if ((str_contains($path, '/views/client/') || (str_contains($path, '/views/admin/') && ! str_ends_with($path, '/login.blade.php')))
                && preg_match('/<h1\b/', $contents) === 1) {
                $headings[] = $relative;
            }

            if (str_contains($path, '/views/client/')
                && (str_ends_with($path, '/form.blade.php') || str_ends_with($path, '-form.blade.php'))
                && preg_match('/<label\b/', $contents) === 1) {
                $fields[] = $relative;
            }
        }

        $this->assertSame([], $headings, 'Manual page headings: '.implode(', ', $headings));
        $this->assertSame([], $fields, 'Manual CRUD field labels: '.implode(', ', $fields));
    }

    /** @return list<SplFileInfo> */
    private function functionalViews(): array
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views')));
        $files = [];

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo || ! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $path = str_replace('\\', '/', $file->getPathname());
            if (str_contains($path, '/views/docs/') || str_contains($path, '/views/emails/')) {
                continue;
            }

            $files[] = $file;
        }

        return $files;
    }
}

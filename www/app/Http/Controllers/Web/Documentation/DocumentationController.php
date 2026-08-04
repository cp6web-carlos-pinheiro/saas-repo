<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Documentation;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class DocumentationController extends Controller
{
    public function index(): RedirectResponse
    {
        $files = $this->docFiles();
        abort_if($files === [], 404, 'Nenhum arquivo de documentacao encontrado.');

        $default = in_array('01 - README.md', $files, true)
            ? '01 - README.md'
            : (in_array('README.md', $files, true) ? 'README.md' : $files[0]);

        return redirect()->route('docs.show', ['file' => $default]);
    }

    public function show(string $file): View
    {
        return $this->renderDocument($file, 'root');
    }

    public function showDev(string $file): View
    {
        return $this->renderDocument($file, 'dev');
    }

    private function renderDocument(string $file, string $scope): View
    {
        $files = $this->docFiles();
        $devFiles = $this->docFiles('dev');

        $scopeFiles = $scope === 'dev' ? $devFiles : $files;
        $resolved = $this->resolveFile($file, $scopeFiles);

        abort_if($resolved === null, 404);

        $basePath = $scope === 'dev' ? 'doc/dev/' : 'doc/';
        $absolutePath = base_path($basePath.$resolved);
        abort_unless(is_file($absolutePath), 404);

        $markdown = (string) file_get_contents($absolutePath);
        $markdown = $this->rewriteLocalMarkdownLinks($markdown, $files, $devFiles, $scope);

        $html = Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $documents = array_map(static function (string $docFile): array {
            return [
                'file' => $docFile,
                'label' => pathinfo($docFile, PATHINFO_FILENAME),
            ];
        }, $files);

        $devDocuments = array_map(static function (string $docFile): array {
            return [
                'file' => $docFile,
                'label' => pathinfo($docFile, PATHINFO_FILENAME),
            ];
        }, $devFiles);

        return view('docs.viewer', [
            'documents' => $documents,
            'devDocuments' => $devDocuments,
            'currentFile' => $resolved,
            'currentScope' => $scope,
            'currentTitle' => pathinfo($resolved, PATHINFO_FILENAME),
            'contentHtml' => $html,
        ]);
    }

    /**
     * @return list<string>
     */
    private function docFiles(string $subdirectory = ''): array
    {
        $directory = trim($subdirectory, '/');
        $pathPattern = $directory === '' ? 'doc/*.md' : 'doc/'.$directory.'/*.md';
        $paths = glob(base_path($pathPattern)) ?: [];

        $files = array_map(static fn (string $path): string => basename($path), $paths);

        usort($files, static function (string $a, string $b): int {
            if ($a === '01 - README.md') {
                return -1;
            }

            if ($b === '01 - README.md') {
                return 1;
            }

            if ($a === 'README.md') {
                return -1;
            }

            if ($b === 'README.md') {
                return 1;
            }

            return strnatcasecmp($a, $b);
        });

        return array_values($files);
    }

    /**
     * @param list<string> $availableFiles
     */
    private function resolveFile(string $requestedFile, array $availableFiles): ?string
    {
        $decoded = rawurldecode($requestedFile);

        if (str_contains($decoded, '/') || str_contains($decoded, '\\')) {
            return null;
        }

        return in_array($decoded, $availableFiles, true) ? $decoded : null;
    }

    /**
     * @param list<string> $availableFiles
     */
    private function rewriteLocalMarkdownLinks(string $markdown, array $availableFiles, array $availableDevFiles, string $currentScope): string
    {
        return (string) preg_replace_callback(
            '/\[([^\]]+)\]\(([^)]+)\)/',
            fn (array $matches): string => $this->rewriteSingleMarkdownLink($matches, $availableFiles, $availableDevFiles, $currentScope),
            $markdown
        ) ?? $markdown;
    }

    /**
     * @param list<string> $availableFiles
     * @param list<string> $availableDevFiles
     */
    private function rewriteSingleMarkdownLink(array $matches, array $availableFiles, array $availableDevFiles, string $currentScope): string
    {
        $label = (string) ($matches[1] ?? '');
        $target = trim((string) ($matches[2] ?? ''));
        $result = (string) ($matches[0] ?? '');

        if (! $this->isLocalMarkdownCandidate($target)) {
            return $result;
        }

        $resolvedTarget = $this->resolveMarkdownTarget($target, $currentScope);

        if ($resolvedTarget === null) {
            return $result;
        }

        $exists = $resolvedTarget['scope'] === 'dev'
            ? in_array($resolvedTarget['file'], $availableDevFiles, true)
            : in_array($resolvedTarget['file'], $availableFiles, true);

        if ($exists) {
            $routeName = $resolvedTarget['scope'] === 'dev' ? 'docs.dev.show' : 'docs.show';
            $url = route($routeName, ['file' => $resolvedTarget['file']]);
            $result = '['.$label.']('.$url.$resolvedTarget['fragment'].')';
        }

        return $result;
    }

    private function isLocalMarkdownCandidate(string $target): bool
    {
        return $target !== ''
            && ! str_starts_with($target, 'http://')
            && ! str_starts_with($target, 'https://')
            && ! str_starts_with($target, '#');
    }

    /**
     * @return array{scope: 'root'|'dev', file: string, fragment: string}|null
     */
    private function resolveMarkdownTarget(string $target, string $currentScope): ?array
    {
        $parts = parse_url($target);
        $path = (string) ($parts['path'] ?? '');
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        $normalizedPath = str_replace('\\', '/', rawurldecode($path));
        $normalizedPath = ltrim($normalizedPath, '/');

        while (str_starts_with($normalizedPath, './')) {
            $normalizedPath = substr($normalizedPath, 2);
        }

        $targetScope = $currentScope;

        if (str_starts_with($normalizedPath, 'dev/')) {
            $targetScope = 'dev';
            $normalizedPath = substr($normalizedPath, 4);
        } elseif ($currentScope === 'dev') {
            while (str_starts_with($normalizedPath, '../')) {
                $targetScope = 'root';
                $normalizedPath = substr($normalizedPath, 3);
            }
        }

        if ($normalizedPath === '' || str_contains($normalizedPath, '/')) {
            return null;
        }

        $candidate = basename($normalizedPath);

        if (! str_ends_with(strtolower($candidate), '.md')) {
            return null;
        }

        return [
            'scope' => $targetScope === 'dev' ? 'dev' : 'root',
            'file' => $candidate,
            'fragment' => $fragment,
        ];
    }
}

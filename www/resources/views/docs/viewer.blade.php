<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $currentTitle }} | {{ __('ui.documentation') }} | {{ __('ui.app_name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f8fafd;
            --surface: #ffffff;
            --surface-muted: #f1f3f4;
            --text: #202124;
            --text-muted: #5f6368;
            --line: #dadce0;
            --primary: #1a73e8;
            --primary-soft: #e8f0fe;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Roboto", sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        .layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: var(--surface);
            border-right: 1px solid var(--line);
            padding: 1rem;
            position: sticky;
            top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-height: 0;
        }

        .sidebar h1 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
        }

        .sidebar p {
            margin: 0.35rem 0 0.8rem;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            text-decoration: none;
            color: var(--primary);
            font-size: 0.86rem;
            margin-bottom: 0.8rem;
        }

        .doc-list {
            display: grid;
            gap: 0.25rem;
        }

        .sidebar-scroll {
            min-height: 0;
            overflow-y: auto;
            padding-right: 0.2rem;
            margin-top: 0.25rem;
        }

        .doc-folder {
            margin-top: 0.8rem;
            border-top: 1px solid var(--line);
            padding-top: 0.6rem;
        }

        .doc-folder summary {
            cursor: pointer;
            list-style: none;
            color: var(--text-muted);
            font-size: 0.86rem;
            font-weight: 500;
            padding: 0.35rem 0.2rem;
            user-select: none;
        }

        .doc-folder summary::-webkit-details-marker {
            display: none;
        }

        .doc-folder summary::before {
            content: '▸';
            margin-right: 0.35rem;
            color: var(--text-muted);
        }

        .doc-folder[open] summary::before {
            content: '▾';
        }

        .doc-list-nested {
            margin-top: 0.25rem;
            padding-left: 0.55rem;
            border-left: 2px solid var(--surface-muted);
        }

        .doc-list a {
            text-decoration: none;
            color: var(--text);
            padding: 0.5rem 0.7rem;
            border-radius: 10px;
            font-size: 0.9rem;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .doc-list a:hover {
            background: var(--surface-muted);
        }

        .doc-list a.is-active {
            background: var(--primary-soft);
            color: var(--primary);
            font-weight: 500;
        }

        .content-wrap {
            padding: 1.4rem;
            overflow: auto;
        }

        .article {
            max-width: 100%;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 1.4rem;
        }

        .article h1,
        .article h2,
        .article h3,
        .article h4 {
            color: var(--text);
            margin-top: 1.25em;
            margin-bottom: 0.55em;
        }

        .article h1 { margin-top: 0.2em; }

        .article p,
        .article li {
            line-height: 1.6;
            color: #2b2e31;
        }

        .article code {
            background: #eef2f7;
            border: 1px solid #d7dee8;
            border-radius: 6px;
            padding: 0.08rem 0.3rem;
            font-size: 0.88em;
        }

        .article pre {
            background: #0f172a;
            color: #e2e8f0;
            border-radius: 10px;
            padding: 0.9rem;
            overflow: auto;
        }

        .article pre code {
            background: transparent;
            border: none;
            color: inherit;
            padding: 0;
        }

        .article a {
            color: var(--primary);
        }

        .article table {
            width: 100%;
            border-collapse: collapse;
        }

        .article th,
        .article td {
            border: 1px solid var(--line);
            padding: 0.45rem 0.55rem;
            text-align: left;
        }

        @media (max-width: 980px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
                height: auto;
                border-right: none;
                border-bottom: 1px solid var(--line);
                overflow: visible;
            }

            .sidebar-scroll {
                overflow: visible;
                padding-right: 0;
            }
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <a class="back-link" href="{{ route('dashboard.industrial') }}">&larr; {{ __('ui.back_to_dashboard') }}</a>
            <h1>{{ __('ui.documentation') }}</h1>
            <p>{{ __('ui.documentation_subtitle') }}</p>

            <div class="sidebar-scroll">
                <nav class="doc-list" aria-label="{{ __('ui.documentation') }}">
                    @foreach ($documents as $document)
                        <a
                            class="{{ $currentScope === 'root' && $document['file'] === $currentFile ? 'is-active' : '' }}"
                            href="{{ route('docs.show', ['file' => $document['file']]) }}"
                        >
                            {{ $document['label'] }}
                        </a>
                    @endforeach
                </nav>

                @if (! empty($devDocuments))
                    <details class="doc-folder" {{ $currentScope === 'dev' ? 'open' : '' }}>
                        <summary>doc/dev</summary>

                        <nav class="doc-list doc-list-nested" aria-label="doc dev">
                            @foreach ($devDocuments as $document)
                                <a
                                    class="{{ $currentScope === 'dev' && $document['file'] === $currentFile ? 'is-active' : '' }}"
                                    href="{{ route('docs.dev.show', ['file' => $document['file']]) }}"
                                >
                                    {{ $document['label'] }}
                                </a>
                            @endforeach
                        </nav>
                    </details>
                @endif
            </div>
        </aside>

        <main class="content-wrap">
            <article class="article markdown-body">
                {!! $contentHtml !!}
            </article>
        </main>
    </div>
</body>
</html>

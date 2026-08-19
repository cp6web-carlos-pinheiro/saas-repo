{{--
    Aplica o tema (light/dark/system) antes do primeiro paint, evitando flash de tema
    incorreto. Compartilhado entre todas as áreas que usam o Layout System (catálogo,
    Global Admin, área cliente, páginas públicas) — ver doc/11-design-system.md.

    Este script resolve e aplica a preferência salva antes do carregamento do controle
    compartilhado, que permite alterná-la em tempo de execução.
--}}
<script>
    (() => {
        const storageKey = 'beyond-mrp.theme';
        let preference = 'system';

        try {
            const stored = window.localStorage.getItem(storageKey);
            if (stored === 'light' || stored === 'dark' || stored === 'system') preference = stored;
        } catch (_) {}

        const resolved = preference === 'system'
            ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
            : preference;

        document.documentElement.dataset.theme = resolved;
        document.documentElement.dataset.themePreference = preference;
        document.documentElement.style.colorScheme = resolved;
    })();
</script>

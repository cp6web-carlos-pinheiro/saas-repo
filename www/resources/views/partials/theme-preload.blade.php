{{--
    Aplica o tema (light/dark/system) antes do primeiro paint, evitando flash de tema
    incorreto. Compartilhado entre todas as áreas que usam o Layout System (catálogo,
    Global Admin, área cliente, páginas públicas) — ver doc/11-design-system.md.

    Nota: este script só resolve e aplica a preferência já salva. A alternância em tempo
    de execução (o botão de light/dark/system) ainda não existe em nenhuma área, incluindo
    o catálogo — ver observação na PR do item 2.
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
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('ui.app_name'))</title>
    @yield('head-preload')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Roboto:wght@400;500;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

    @php
        $uiTranslations = [
            'selectErrorLoading' => __('ui.select_error_loading'),
            'selectInputTooShortOne' => __('ui.select_input_too_short_one'),
            'selectInputTooShortMany' => __('ui.select_input_too_short_many'),
            'selectLoadingMore' => __('ui.select_loading_more'),
            'selectNoResults' => __('ui.select_no_results'),
            'selectRemoveItem' => __('ui.select_remove_item'),
            'selectSearching' => __('ui.select_searching'),
            'copy' => __('ui.copy'),
            'copied' => __('ui.copied'),
            'edit' => __('ui.edit'),
            'sortBy' => __('ui.sort_by', ['column' => ':column']),
            'sortedAscending' => __('ui.sorted_ascending'),
            'sortedDescending' => __('ui.sorted_descending'),
            'activateSort' => __('ui.activate_sort'),
        ];
    @endphp
    <script>
        window.uiTranslations = @json($uiTranslations);
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('head')
</head>
<body class="@yield('bodyClass')">
    @yield('content')
    @yield('scripts')
</body>
</html>

@props([
    'header' => null,
    'footer' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? site_name() }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-white text-slate-900 antialiased">
    <header>
        {{ $header }}
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer>
        {{ $footer }}
    </footer>

    @stack('scripts')
</body>
</html>

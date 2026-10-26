@props([
    'header' => null,
    'footer' => null,
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? site_name() }}</title>

    @if ($favicon = site()->faviconUrl())
        <link rel="icon" href="{{ $favicon }}">
    @endif

    @if ($googleFontsUrl = site()->googleFontsUrl())
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="{{ $googleFontsUrl }}" rel="stylesheet">
    @endif

    <style>
        :root {
            --color-primary: {{ site()->primaryRgb() }};
            --color-primary-hex: {{ site('primary_color', '#0f172a') }};
            --color-accent: {{ site()->accentRgb() }};
            --color-accent-hex: {{ site('accent_color', '#3b82f6') }};
            --font-heading: '{{ site('font_heading', 'Inter') }}', sans-serif;
            --font-body: '{{ site('font_body', 'Inter') }}', sans-serif;
        }

        body {
            font-family: var(--font-body);
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="flex min-h-screen flex-col bg-white text-slate-900 antialiased font-body">
    @if ($header !== null)
        {{ $header }}
    @else
        <x-site-header />
    @endif

    <main class="flex-1">
        {{ $slot }}
    </main>

    @if ($footer !== null)
        {{ $footer }}
    @else
        <x-site-footer />
    @endif

    @stack('scripts')
</body>
</html>

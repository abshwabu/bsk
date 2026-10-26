@php
    try {
        $name = function_exists('site_name') ? site_name() : config('app.name', 'Bina Site Kit');
        $primary = function_exists('site') ? site('primary_color', '#0f172a') : '#0f172a';
    } catch (\Throwable $e) {
        $name = config('app.name', 'Bina Site Kit');
        $primary = '#0f172a';
    }
    $isSuccess = empty($error);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - {{ $name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        :root {
            --color-primary: {{ $primary }};
        }
    </style>
</head>
<body class="flex min-h-screen flex-col items-center justify-center bg-slate-50 px-6 py-12 text-slate-800 antialiased">
    <div class="mx-auto w-full max-w-2xl rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-900/5">
        <div class="flex items-center space-x-3">
            @if ($isSuccess)
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-500/20">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
            @else
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-rose-600 ring-1 ring-rose-500/20">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
            @endif
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">{{ $title }}</h1>
                <p class="text-xs text-slate-500">Action: <code class="rounded bg-slate-100 px-1 py-0.5 font-mono text-slate-700">{{ $action }}</code> &bull; {{ $timestamp }}</p>
            </div>
        </div>

        @if ($output)
            <div class="mt-6">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Output</label>
                <pre class="mt-2 max-h-72 overflow-y-auto rounded-lg bg-slate-900 p-4 font-mono text-xs text-emerald-400 whitespace-pre-wrap">{{ $output }}</pre>
            </div>
        @endif

        @if ($error)
            <div class="mt-6">
                <label class="block text-xs font-semibold uppercase tracking-wider text-rose-600">Error</label>
                <pre class="mt-2 max-h-72 overflow-y-auto rounded-lg bg-rose-950 p-4 font-mono text-xs text-rose-200 whitespace-pre-wrap">{{ $error }}</pre>
            </div>
        @endif

        <div class="mt-8 flex items-center justify-between border-t border-slate-100 pt-6">
            <span class="text-xs text-slate-400">Bina Site Kit Web Maintenance</span>
            <div class="flex space-x-3">
                <a href="{{ url('/') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">
                    Visit Site
                </a>
                <a href="{{ url('/admin') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-slate-800">
                    Admin Panel
                </a>
            </div>
        </div>
    </div>
</body>
</html>

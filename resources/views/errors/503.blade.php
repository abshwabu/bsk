@php
    try {
        $name = function_exists('site_name') ? site_name() : config('app.name', 'Bina Site Kit');
        $primary = function_exists('site') ? site('primary_color', '#0f172a') : '#0f172a';
    } catch (\Throwable $e) {
        $name = config('app.name', 'Bina Site Kit');
        $primary = '#0f172a';
    }
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance - {{ $name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        :root {
            --color-primary: {{ $primary }};
        }
    </style>
</head>
<body class="flex min-h-screen flex-col items-center justify-center bg-slate-50 px-6 py-20 text-slate-800 antialiased">
    <div class="mx-auto max-w-lg text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-200/80 text-slate-700 shadow-sm">
            <svg class="h-8 w-8 text-slate-800" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091.491.077 1.01-.044 1.488" />
            </svg>
        </div>

        <h1 class="mt-6 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
            Under Scheduled Maintenance
        </h1>

        <p class="mt-4 text-base text-slate-600 sm:text-lg">
            We are performing brief scheduled updates on <strong>{{ $name }}</strong> to enhance performance and security. Please check back shortly.
        </p>

        <div class="mt-8 flex justify-center">
            <button
                type="button"
                onclick="window.location.reload()"
                class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2"
            >
                Reload Page
            </button>
        </div>
    </div>
</body>
</html>

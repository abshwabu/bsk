<x-layouts.site title="Page Not Found">
    <div class="flex min-h-[60vh] flex-col items-center justify-center px-6 py-20 text-center">
        <p class="font-heading text-7xl font-extrabold text-primary sm:text-8xl">
            404
        </p>
        <h1 class="mt-4 font-heading text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
            Page Not Found
        </h1>
        <p class="mt-4 max-w-md text-base text-slate-600 sm:text-lg">
            Sorry, we couldn’t find the page you’re looking for. The link may be broken or the page might have been removed.
        </p>
        <div class="mt-8 flex gap-4">
            <a
                href="{{ url('/') }}"
                class="inline-flex items-center justify-center rounded-lg bg-primary px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                Back to Home
            </a>
        </div>
    </div>
</x-layouts.site>

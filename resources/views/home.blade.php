<x-layouts.site>
    <x-slot:header>
        <div class="border-b border-slate-200 px-6 py-5">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <a class="flex items-center gap-3 font-heading font-semibold text-primary" href="{{ route('home') }}">
                    @if ($logo = site()->logoUrl())
                        <img src="{{ $logo }}" alt="{{ site_name() }}" class="h-8 w-auto">
                    @endif
                    <span>{{ site_name() }}</span>
                </a>
            </div>
        </div>
    </x-slot:header>

    <section class="mx-auto max-w-3xl px-6 py-24 text-center">
        <h1 class="font-heading text-4xl font-bold tracking-tight text-primary sm:text-5xl">Your website starts here</h1>
        <p class="mt-5 font-body text-lg text-slate-600">{{ site('tagline') ?: 'Add and manage your website content from the admin panel.' }}</p>
    </section>

    <x-slot:footer>
        <div class="border-t border-slate-200 px-6 py-5 text-center text-sm text-slate-500">
            {{ site('footer_text') ?: site_name() }}
        </div>
    </x-slot:footer>
</x-layouts.site>

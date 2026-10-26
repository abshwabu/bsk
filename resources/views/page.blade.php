<x-layouts.site :title="$page->seo_title ?: $page->title">
    @push('head')
        @if ($page->seo_description)
            <meta name="description" content="{{ $page->seo_description }}">
        @endif
        @if ($page->ogImageUrl())
            <meta property="og:image" content="{{ $page->ogImageUrl() }}">
        @endif
    @endpush

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

    <article>
        <x-blocks :page="$page" />
    </article>

    <x-slot:footer>
        <div class="border-t border-slate-200 px-6 py-5 text-center text-sm text-slate-500">
            {{ site('footer_text') ?: site_name() }}
        </div>
    </x-slot:footer>
</x-layouts.site>

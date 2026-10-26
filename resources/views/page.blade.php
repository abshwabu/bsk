<x-layouts.site :title="$page->seo_title ?: $page->title">
    @push('head')
        @if ($page->seo_description)
            <meta name="description" content="{{ $page->seo_description }}">
        @endif
        @if ($page->ogImageUrl())
            <meta property="og:image" content="{{ $page->ogImageUrl() }}">
        @endif
    @endpush

    <article>
        <x-blocks :page="$page" />
    </article>
</x-layouts.site>

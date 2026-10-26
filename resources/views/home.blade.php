<x-layouts.site :title="($page?->seo_title ?: $page?->title) ?? site_name()">
    @push('head')
        @if ($page?->seo_description)
            <meta name="description" content="{{ $page->seo_description }}">
        @endif
        @if ($page?->ogImageUrl())
            <meta property="og:image" content="{{ $page->ogImageUrl() }}">
        @endif
    @endpush

    @if ($page && ! empty($page->blocks))
        <article>
            <x-blocks :page="$page" />
        </article>
    @else
        <section class="mx-auto max-w-3xl px-6 py-24 text-center">
            <h1 class="font-heading text-4xl font-bold tracking-tight text-primary sm:text-5xl">Your website starts here</h1>
            <p class="mt-5 font-body text-lg text-slate-600">{{ site('tagline') ?: 'Add and manage your website content from the admin panel.' }}</p>
        </section>
    @endif
</x-layouts.site>

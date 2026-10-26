<x-layouts.site :title="($project->seo_title ?: $project->title) . ' - ' . site_name()">
    @push('head')
        @if ($project->seo_description)
            <meta name="description" content="{{ $project->seo_description }}">
        @endif
        @if ($og = $project->ogImageUrl())
            <meta property="og:image" content="{{ $og }}">
        @endif
    @endpush

    <article class="py-16 md:py-24" x-data="{ open: false, activeImage: '', activeAlt: '' }">
        <div class="mx-auto max-w-4xl px-6">
            {{-- Breadcrumb / Back Link --}}
            <nav class="mb-8 flex items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('projects.index') }}" class="transition hover:text-primary">&larr; Back to Projects</a>
                @if ($project->category)
                    <span>/</span>
                    <a href="{{ route('projects.index', ['category' => $project->category->slug]) }}" class="transition hover:text-primary">
                        {{ $project->category->name }}
                    </a>
                @endif
            </nav>

            {{-- Header --}}
            <header>
                @if ($project->category)
                    <span class="inline-flex items-center rounded-md bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                        {{ $project->category->name }}
                    </span>
                @endif

                <h1 class="mt-4 font-heading text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">
                    {{ $project->title }}
                </h1>

                @if ($project->summary)
                    <p class="mt-6 text-xl text-slate-600 leading-relaxed">
                        {{ $project->summary }}
                    </p>
                @endif

                {{-- Meta Information Grid --}}
                <div class="mt-8 grid grid-cols-2 gap-4 border-y border-slate-200 py-6 sm:grid-cols-3">
                    @if ($project->client_name)
                        <div>
                            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Client</span>
                            <span class="mt-1 block font-medium text-slate-900">{{ $project->client_name }}</span>
                        </div>
                    @endif

                    @if ($project->completed_on)
                        <div>
                            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Completed</span>
                            <span class="mt-1 block font-medium text-slate-900">{{ $project->completed_on->format('F Y') }}</span>
                        </div>
                    @endif

                    @if ($project->category)
                        <div>
                            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Discipline</span>
                            <span class="mt-1 block font-medium text-slate-900">{{ $project->category->name }}</span>
                        </div>
                    @endif
                </div>
            </header>

            {{-- Cover Image --}}
            @if ($cover = $project->coverImageUrl())
                <div class="mt-10 overflow-hidden rounded-2xl shadow-md">
                    <img
                        src="{{ $cover }}"
                        alt="{{ $project->title }}"
                        loading="lazy"
                        class="w-full object-cover max-h-[550px]"
                    />
                </div>
            @endif

            {{-- Body Content --}}
            @if ($project->body)
                <div class="prose prose-slate max-w-none mt-12">
                    {!! $project->body !!}
                </div>
            @endif

            {{-- Project Gallery --}}
            @if (! empty($project->galleryUrls()))
                <div class="mt-16 border-t border-slate-200 pt-12">
                    <h2 class="font-heading text-2xl font-bold text-slate-900 mb-6">
                        Project Showcase Gallery
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach ($project->galleryUrls() as $galleryUrl)
                            <button
                                type="button"
                                @click="activeImage = '{{ $galleryUrl }}'; activeAlt = '{{ $project->title }} gallery image'; open = true"
                                class="group relative aspect-[4/3] overflow-hidden rounded-xl bg-slate-100 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                            >
                                <img
                                    src="{{ $galleryUrl }}"
                                    alt="{{ $project->title }} gallery photo"
                                    loading="lazy"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                />
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Next / Previous Project Navigation --}}
            @php
                $prev = $project->previousProject();
                $next = $project->nextProject();
            @endphp

            @if ($prev || $next)
                <nav class="mt-16 flex flex-col sm:flex-row items-stretch justify-between gap-4 border-t border-slate-200 pt-8">
                    @if ($prev)
                        <a
                            href="{{ route('projects.show', $prev->slug) }}"
                            class="group flex flex-1 flex-col rounded-xl border border-slate-200 p-5 transition hover:border-primary hover:bg-slate-50"
                        >
                            <span class="text-xs font-semibold text-slate-400 group-hover:text-primary">&larr; Previous Project</span>
                            <span class="mt-1 font-heading text-base font-bold text-slate-900 group-hover:text-primary">{{ $prev->title }}</span>
                        </a>
                    @else
                        <div class="hidden sm:block flex-1"></div>
                    @endif

                    @if ($next)
                        <a
                            href="{{ route('projects.show', $next->slug) }}"
                            class="group flex flex-1 flex-col items-end text-right rounded-xl border border-slate-200 p-5 transition hover:border-primary hover:bg-slate-50"
                        >
                            <span class="text-xs font-semibold text-slate-400 group-hover:text-primary">Next Project &rarr;</span>
                            <span class="mt-1 font-heading text-base font-bold text-slate-900 group-hover:text-primary">{{ $next->title }}</span>
                        </a>
                    @endif
                </nav>
            @endif
        </div>

        {{-- Alpine Lightbox Modal --}}
        <template x-teleport="body">
            <div
                x-show="open"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @keydown.escape.window="open = false"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4 backdrop-blur-sm"
                @click.self="open = false"
            >
                <button
                    type="button"
                    @click="open = false"
                    class="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-white transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white"
                    aria-label="Close image preview"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="relative max-h-[90vh] max-w-[90vw]">
                    <img
                        :src="activeImage"
                        :alt="activeAlt"
                        class="max-h-[85vh] max-w-full rounded-lg object-contain shadow-2xl"
                    />
                </div>
            </div>
        </template>
    </article>
</x-layouts.site>

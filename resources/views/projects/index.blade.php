<x-layouts.site title="Our Projects - {{ site_name() }}">
    <div class="py-16 md:py-24">
        <div class="mx-auto max-w-7xl px-6">
            {{-- Header --}}
            <div class="mx-auto max-w-3xl text-center">
                <h1 class="font-heading text-4xl font-extrabold tracking-tight text-primary sm:text-5xl">
                    Our Projects
                </h1>
                <p class="mt-4 text-lg text-slate-600">
                    Explore our recent client deliverables, custom architectures, and digital transformations.
                </p>
            </div>

            {{-- Category Filter Bar (No JS required) --}}
            @if ($categories->isNotEmpty())
                <div class="mt-10 flex flex-wrap items-center justify-center gap-2">
                    <a
                        href="{{ route('projects.index') }}"
                        class="rounded-full px-4 py-2 text-sm font-medium transition {{ empty($currentCategory) ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                    >
                        All Projects
                    </a>
                    @foreach ($categories as $cat)
                        <a
                            href="{{ route('projects.index', ['category' => $cat->slug]) }}"
                            class="rounded-full px-4 py-2 text-sm font-medium transition {{ $currentCategory === $cat->slug ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                        >
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Projects Grid --}}
            @if ($projects->isNotEmpty())
                <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        <article class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition hover:shadow-md">
                            <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                                @if ($cover = $project->coverImageUrl())
                                    <img
                                        src="{{ $cover }}"
                                        alt="{{ $project->title }}"
                                        loading="lazy"
                                        width="600"
                                        height="375"
                                        class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                    />
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-slate-100 text-slate-400">
                                        <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-1 flex-col justify-between p-6">
                                <div>
                                    @if ($project->category)
                                        <span class="inline-flex items-center rounded-md bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary">
                                            {{ $project->category->name }}
                                        </span>
                                    @endif

                                    <h2 class="mt-3 font-heading text-xl font-bold text-slate-900 group-hover:text-primary transition">
                                        <a href="{{ route('projects.show', $project->slug) }}">
                                            {{ $project->title }}
                                        </a>
                                    </h2>

                                    @if ($project->summary)
                                        <p class="mt-2 text-sm text-slate-600 line-clamp-2">
                                            {{ $project->summary }}
                                        </p>
                                    @endif
                                </div>

                                <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4 text-xs text-slate-500">
                                    @if ($project->client_name)
                                        <span>Client: <strong class="text-slate-700">{{ $project->client_name }}</strong></span>
                                    @endif
                                    <a href="{{ route('projects.show', $project->slug) }}" class="font-semibold text-primary hover:underline ml-auto">
                                        View Case Study &rarr;
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $projects->links() }}
                </div>
            @else
                <div class="mt-16 rounded-2xl border-2 border-dashed border-slate-200 p-12 text-center">
                    <p class="text-slate-500">No projects found in this category.</p>
                    <a href="{{ route('projects.index') }}" class="mt-4 inline-block font-semibold text-primary hover:underline">
                        View All Projects
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-layouts.site>

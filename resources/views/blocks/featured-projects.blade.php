@props([
    'data' => [],
    'heading' => null,
    'subheading' => null,
    'count' => 3,
    'view_all_label' => 'View All Projects',
    'view_all_url' => '/projects',
])

@php
    $heading = $heading ?? ($data['heading'] ?? 'Featured Work');
    $subheading = $subheading ?? ($data['subheading'] ?? 'A selection of our proudest projects and digital transformations.');
    $limit = (int) ($count ?? ($data['count'] ?? 3));
    $viewAllLabel = $view_all_label ?? ($data['view_all_label'] ?? 'View All Projects');
    $viewAllUrl = $view_all_url ?? ($data['view_all_url'] ?? '/projects');

    $projects = collect();
    try {
        $projects = \App\Models\Project::query()
            ->published()
            ->where('is_featured', true)
            ->with('category')
            ->orderBy('sort')
            ->take($limit)
            ->get();

        if ($projects->isEmpty()) {
            $projects = \App\Models\Project::query()
                ->published()
                ->with('category')
                ->orderBy('sort')
                ->take($limit)
                ->get();
        }
    } catch (\Throwable $e) {
        // Fallback gracefully
    }
@endphp

<section class="py-16 md:py-24">
    <div class="mx-auto max-w-7xl px-6">
        <div class="mb-12 flex flex-col items-start justify-between gap-6 md:flex-row md:items-end md:mb-16">
            <div>
                @if ($heading)
                    <h2 class="font-heading text-3xl font-bold tracking-tight text-primary sm:text-4xl">
                        {{ $heading }}
                    </h2>
                @endif
                @if ($subheading)
                    <p class="mt-4 text-lg text-slate-600">
                        {{ $subheading }}
                    </p>
                @endif
            </div>

            @if ($viewAllLabel && $viewAllUrl)
                <a
                    href="{{ url($viewAllUrl) }}"
                    class="inline-flex items-center gap-2 font-semibold text-primary transition hover:opacity-80"
                >
                    <span>{{ $viewAllLabel }}</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            @endif
        </div>

        @if ($projects->isNotEmpty())
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
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

                                <h3 class="mt-3 font-heading text-xl font-bold text-slate-900 group-hover:text-primary transition">
                                    <a href="{{ url('/projects/' . $project->slug) }}">
                                        {{ $project->title }}
                                    </a>
                                </h3>

                                @if ($project->summary)
                                    <p class="mt-2 text-sm text-slate-600 line-clamp-2">
                                        {{ $project->summary }}
                                    </p>
                                @endif
                            </div>

                            <div class="mt-6 border-t border-slate-100 pt-4 flex items-center justify-between text-xs text-slate-500">
                                @if ($project->client_name)
                                    <span>Client: <strong class="text-slate-700">{{ $project->client_name }}</strong></span>
                                @endif
                                <a href="{{ url('/projects/' . $project->slug) }}" class="font-semibold text-primary hover:underline ml-auto">
                                    View Project &rarr;
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            {{-- Sensible Fallback if no projects exist in database --}}
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                @for ($i = 1; $i <= 3; $i++)
                    <div class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="aspect-[16/10] rounded-xl bg-slate-100 flex items-center justify-center text-slate-400">
                            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 font-heading text-lg font-bold text-slate-900">Showcase Project {{ $i }}</h3>
                        <p class="mt-1 text-sm text-slate-500">Comprehensive branding and custom development package.</p>
                    </div>
                @endfor
            </div>
        @endif
    </div>
</section>

@props([
    'data' => [],
    'heading' => null,
    'subheading' => null,
    'count' => 4,
    'view_all_label' => 'View All Products',
    'view_all_url' => '/shop',
])

@php
    $heading = $heading ?? ($data['heading'] ?? 'Featured Products');
    $subheading = $subheading ?? ($data['subheading'] ?? 'Explore our curated selection of products available for direct order.');
    $limit = (int) ($count ?? ($data['count'] ?? 4));
    $viewAllLabel = $view_all_label ?? ($data['view_all_label'] ?? 'View All Products');
    $viewAllUrl = $view_all_url ?? ($data['view_all_url'] ?? '/shop');

    $products = collect();

    if (site()->isModuleEnabled('catalog')) {
        try {
            $products = \App\Models\Product::query()
                ->published()
                ->where('is_featured', true)
                ->with('category')
                ->orderBy('sort')
                ->take($limit)
                ->get();

            if ($products->isEmpty()) {
                $products = \App\Models\Product::query()
                    ->published()
                    ->with('category')
                    ->orderBy('sort')
                    ->take($limit)
                    ->get();
            }
        } catch (\Throwable $e) {
            // Graceful fallback
        }
    }
@endphp

@if (site()->isModuleEnabled('catalog'))
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
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                @endif
            </div>

            @if ($products->isNotEmpty())
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($products as $product)
                        <article class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition hover:shadow-md">
                            <a href="{{ route('shop.show', $product->slug) }}" class="aspect-[4/3] overflow-hidden bg-slate-100 relative block">
                                @if ($cover = $product->primaryImageUrl())
                                    <img
                                        src="{{ $cover }}"
                                        alt="{{ $product->name }}"
                                        loading="lazy"
                                        width="400"
                                        height="300"
                                        class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                    />
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-slate-400">
                                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                        </svg>
                                    </div>
                                @endif

                                @if ($product->stock_status === \App\Models\Product::STOCK_OUT_OF_STOCK)
                                    <span class="absolute top-3 right-3 rounded-md bg-rose-600 px-2 py-0.5 text-[11px] font-semibold text-white shadow">
                                        Out of Stock
                                    </span>
                                @endif
                            </a>

                            <div class="flex flex-1 flex-col justify-between p-5">
                                <div>
                                    @if ($product->category)
                                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                            {{ $product->category->name }}
                                        </span>
                                    @endif
                                    <h3 class="mt-1 font-heading text-base font-bold text-slate-900 group-hover:text-primary transition line-clamp-1">
                                        <a href="{{ route('shop.show', $product->slug) }}">
                                            {{ $product->name }}
                                        </a>
                                    </h3>
                                </div>

                                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                                    <span class="text-sm font-bold text-slate-900">
                                        {{ $product->formattedPrice() }}
                                    </span>

                                    <a
                                        href="{{ route('shop.show', $product->slug) }}"
                                        class="text-xs font-semibold text-primary hover:underline"
                                    >
                                        Details &rarr;
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif

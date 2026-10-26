<x-layouts.site title="Shop & Catalog - {{ site_name() }}">
    <div class="py-16 md:py-24">
        <div class="mx-auto max-w-7xl px-6">
            {{-- Header with Cart Badge --}}
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 border-b border-slate-200/80 pb-8">
                <div>
                    <h1 class="font-heading text-4xl font-extrabold tracking-tight text-primary sm:text-5xl">
                        Product Catalog
                    </h1>
                    <p class="mt-3 text-lg text-slate-600 max-w-2xl">
                        Browse our products and submit an order request. No online payments required—we confirm and coordinate directly with you.
                    </p>
                </div>

                <div>
                    <a
                        href="{{ route('cart.index') }}"
                        class="inline-flex items-center gap-2.5 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:opacity-90"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <span>View Cart</span>
                        @if ($cartCount > 0)
                            <span class="inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-accent px-1.5 text-xs font-bold text-white">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if (session('cart_success'))
                <div class="mt-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-medium">{{ session('cart_success') }}</p>
                    </div>
                    <a href="{{ route('cart.index') }}" class="text-xs font-semibold text-emerald-800 underline hover:text-emerald-950">
                        Proceed to Cart &rarr;
                    </a>
                </div>
            @endif

            {{-- Filter and Search Toolbar --}}
            <div class="mt-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                {{-- Category Filter Pills --}}
                <div class="flex flex-wrap items-center gap-2">
                    <a
                        href="{{ route('shop.index', array_filter(['q' => $search])) }}"
                        class="rounded-full px-4 py-2 text-xs sm:text-sm font-medium transition {{ empty($selectedCategory) ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                    >
                        All Categories
                    </a>
                    @foreach ($categories as $cat)
                        <a
                            href="{{ route('shop.index', array_filter(['category' => $cat->slug, 'q' => $search])) }}"
                            class="rounded-full px-4 py-2 text-xs sm:text-sm font-medium transition {{ $selectedCategory === $cat->slug ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                        >
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>

                {{-- Search Input --}}
                <form action="{{ route('shop.index') }}" method="GET" class="relative w-full md:w-72">
                    @if ($selectedCategory)
                        <input type="hidden" name="category" value="{{ $selectedCategory }}">
                    @endif
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Search products..."
                        class="w-full rounded-full border border-slate-300 bg-white pl-10 pr-4 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                    >
                    <svg class="absolute left-3.5 top-2.5 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </form>
            </div>

            {{-- Products Grid --}}
            @if ($products->isNotEmpty())
                <div class="mt-10 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($products as $product)
                        <article class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition hover:shadow-md">
                            {{-- Image Cover --}}
                            <a href="{{ route('shop.show', $product->slug) }}" class="aspect-[4/3] overflow-hidden bg-slate-100 relative block">
                                @if ($cover = $product->primaryImageUrl())
                                    <img
                                        src="{{ $cover }}"
                                        alt="{{ $product->name }}"
                                        loading="lazy"
                                        class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                    />
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-slate-100 text-slate-400">
                                        <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                        </svg>
                                    </div>
                                @endif

                                @if ($product->stock_status === \App\Models\Product::STOCK_OUT_OF_STOCK)
                                    <span class="absolute top-3 right-3 rounded-md bg-rose-600 px-2 py-1 text-[11px] font-semibold text-white shadow">
                                        Out of Stock
                                    </span>
                                @elseif ($product->is_featured)
                                    <span class="absolute top-3 left-3 rounded-md bg-accent px-2 py-1 text-[11px] font-semibold text-white shadow">
                                        Featured
                                    </span>
                                @endif
                            </a>

                            {{-- Card Body --}}
                            <div class="flex flex-1 flex-col justify-between p-5">
                                <div>
                                    @if ($product->category)
                                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                            {{ $product->category->name }}
                                        </span>
                                    @endif
                                    <h3 class="mt-1 font-heading text-lg font-bold text-slate-900 group-hover:text-primary transition line-clamp-1">
                                        <a href="{{ route('shop.show', $product->slug) }}">
                                            {{ $product->name }}
                                        </a>
                                    </h3>
                                    @if ($product->description)
                                        <p class="mt-2 text-xs text-slate-500 line-clamp-2">
                                            {{ strip_tags($product->description) }}
                                        </p>
                                    @endif
                                </div>

                                <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                                    <div>
                                        <span class="text-xs text-slate-400 block">Price</span>
                                        <span class="text-base font-bold text-slate-900">
                                            {{ $product->formattedPrice() }}
                                        </span>
                                    </div>

                                    @if ($product->stock_status !== \App\Models\Product::STOCK_OUT_OF_STOCK)
                                        <form action="{{ route('cart.add') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input type="hidden" name="qty" value="1">
                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-primary/10 px-3 py-1.5 text-xs font-semibold text-primary transition hover:bg-primary hover:text-white"
                                            >
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                </svg>
                                                <span>Add</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-12">
                    {{ $products->links() }}
                </div>
            @else
                <div class="mt-16 rounded-2xl border border-slate-200/80 bg-white p-12 text-center shadow-sm">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 font-heading text-lg font-bold text-slate-900">No products found</h3>
                    <p class="mt-2 text-sm text-slate-500">
                        {{ $search ? "No products matching '{$search}'." : 'No products available in this category.' }}
                    </p>
                    <div class="mt-6">
                        <a
                            href="{{ route('shop.index') }}"
                            class="inline-flex items-center rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-90 transition"
                        >
                            Clear filters
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.site>

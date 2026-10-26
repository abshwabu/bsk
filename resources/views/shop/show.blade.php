<x-layouts.site
    title="{{ $product->seo_title ?: $product->name . ' - ' . site_name() }}"
    description="{{ $product->seo_description ?: Str::limit(strip_tags($product->description), 160) }}"
    image="{{ $product->og_image ? \Illuminate\Support\Facades\Storage::disk('public')->url($product->og_image) : $product->primaryImageUrl() }}"
>
    <div class="py-12 md:py-20">
        <div class="mx-auto max-w-7xl px-6">
            {{-- Breadcrumb Navigation --}}
            <nav class="flex items-center gap-2 text-sm text-slate-500 mb-8">
                <a href="{{ route('shop.index') }}" class="hover:text-primary transition">Shop</a>
                <span>/</span>
                @if ($product->category)
                    <a href="{{ route('shop.index', ['category' => $product->category->slug]) }}" class="hover:text-primary transition">
                        {{ $product->category->name }}
                    </a>
                    <span>/</span>
                @endif
                <span class="text-slate-900 font-medium truncate max-w-xs">{{ $product->name }}</span>
            </nav>

            {{-- Flash Messages --}}
            @if (session('cart_success'))
                <div class="mb-8 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-medium">{{ session('cart_success') }}</p>
                    </div>
                    <a href="{{ route('cart.index') }}" class="text-xs font-semibold text-emerald-800 underline hover:text-emerald-950">
                        View Cart & Checkout &rarr;
                    </a>
                </div>
            @endif

            {{-- Product Main Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-start">
                {{-- Left: Image Gallery (Alpine) --}}
                @php
                    $images = $product->imageUrls();
                @endphp
                <div x-data="{ currentImage: {{ count($images) > 0 ? "'" . $images[0] . "'" : 'null' }} }" class="space-y-4">
                    <div class="aspect-[4/3] overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-sm relative">
                        <template x-if="currentImage">
                            <img
                                :src="currentImage"
                                alt="{{ $product->name }}"
                                class="h-full w-full object-cover transition duration-300"
                            />
                        </template>
                        <template x-if="!currentImage">
                            <div class="flex h-full w-full items-center justify-center text-slate-400">
                                <svg class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                            </div>
                        </template>
                    </div>

                    {{-- Gallery Thumbnails --}}
                    @if (count($images) > 1)
                        <div class="flex flex-wrap gap-3">
                            @foreach ($images as $img)
                                <button
                                    type="button"
                                    @click="currentImage = '{{ $img }}'"
                                    :class="currentImage === '{{ $img }}' ? 'ring-2 ring-primary border-transparent' : 'border-slate-200 hover:border-slate-400'"
                                    class="h-20 w-20 overflow-hidden rounded-xl border bg-slate-100 transition"
                                >
                                    <img src="{{ $img }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Right: Product Details & Add to Cart --}}
                <div class="flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            @if ($product->category)
                                <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                                    {{ $product->category->name }}
                                </span>
                            @endif

                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $product->stock_status === \App\Models\Product::STOCK_IN_STOCK ? 'bg-emerald-100 text-emerald-800' : ($product->stock_status === \App\Models\Product::STOCK_OUT_OF_STOCK ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ $product->stockStatusLabel() }}
                            </span>
                        </div>

                        <h1 class="mt-4 font-heading text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                            {{ $product->name }}
                        </h1>

                        <div class="mt-4 text-3xl font-extrabold text-slate-900">
                            {{ $product->formattedPrice() }}
                        </div>

                        {{-- Order / Add to Cart Form --}}
                        <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <form action="{{ route('cart.add') }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">

                                <div>
                                    <label for="qty" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                        Quantity
                                    </label>
                                    <div class="mt-2 flex items-center gap-3">
                                        <input
                                            type="number"
                                            name="qty"
                                            id="qty"
                                            value="1"
                                            min="1"
                                            max="100"
                                            class="w-24 rounded-xl border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-900 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                                            {{ $product->stock_status === \App\Models\Product::STOCK_OUT_OF_STOCK ? 'disabled' : '' }}
                                        >
                                        <button
                                            type="submit"
                                            {{ $product->stock_status === \App\Models\Product::STOCK_OUT_OF_STOCK ? 'disabled' : '' }}
                                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                            </svg>
                                            <span>Add to Cart</span>
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <p class="mt-3 text-xs text-slate-500">
                                💡 No online payment needed. Submitting an order creates an order request sent directly to our team via Telegram for coordination.
                            </p>
                        </div>

                        {{-- Description & Details --}}
                        @if ($product->description)
                            <div class="mt-8 border-t border-slate-200 pt-8">
                                <h2 class="font-heading text-lg font-bold text-slate-900">About this product</h2>
                                <div class="prose prose-slate mt-4 max-w-none text-slate-600 text-sm leading-relaxed">
                                    {!! $product->description !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Related Products --}}
            @if ($related->isNotEmpty())
                <div class="mt-20 border-t border-slate-200/80 pt-16">
                    <h2 class="font-heading text-2xl font-bold text-slate-900">
                        You May Also Like
                    </h2>
                    <div class="mt-8 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($related as $rel)
                            <article class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition hover:shadow-md">
                                <a href="{{ route('shop.show', $rel->slug) }}" class="aspect-[4/3] overflow-hidden bg-slate-100">
                                    @if ($relCover = $rel->primaryImageUrl())
                                        <img src="{{ $relCover }}" alt="{{ $rel->name }}" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                    @endif
                                </a>
                                <div class="p-5 flex-1 flex flex-col justify-between">
                                    <div>
                                        <h3 class="font-heading text-base font-bold text-slate-900 group-hover:text-primary transition">
                                            <a href="{{ route('shop.show', $rel->slug) }}">{{ $rel->name }}</a>
                                        </h3>
                                        <p class="mt-1 font-semibold text-sm text-slate-900">{{ $rel->formattedPrice() }}</p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.site>

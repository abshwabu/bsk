<x-layouts.site title="Shopping Cart - {{ site_name() }}">
    <div class="py-12 md:py-20">
        <div class="mx-auto max-w-7xl px-6">
            <h1 class="font-heading text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                Your Shopping Cart
            </h1>

            {{-- Flash Messages --}}
            @if (session('cart_success'))
                <div class="mt-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 flex items-center gap-3">
                    <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-medium">{{ session('cart_success') }}</p>
                </div>
            @endif

            @if (! empty($items))
                <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-10 items-start">
                    {{-- Items List --}}
                    <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="divide-y divide-slate-100">
                            @foreach ($items as $item)
                                <div class="p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                            @if (! empty($item['image']))
                                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy" width="80" height="80" class="h-full w-full object-cover">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center text-slate-400">
                                                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>

                                        <div>
                                            <h3 class="font-heading text-base font-bold text-slate-900">
                                                <a href="{{ route('shop.show', $item['slug']) }}" class="hover:text-primary transition">
                                                    {{ $item['name'] }}
                                                </a>
                                            </h3>
                                            <p class="mt-1 text-xs text-slate-500">
                                                Unit price: <span class="font-semibold text-slate-700">{{ $currency }} {{ number_format((float) $item['price'], 2) }}</span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto">
                                        {{-- Quantity Modifier Form --}}
                                        <form action="{{ route('cart.update') }}" method="POST" class="flex items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                            <label for="qty_{{ $item['id'] }}" class="sr-only">Quantity</label>
                                            <input
                                                type="number"
                                                id="qty_{{ $item['id'] }}"
                                                name="qty"
                                                value="{{ $item['qty'] }}"
                                                min="1"
                                                max="99"
                                                class="w-16 rounded-lg border border-slate-300 py-1.5 text-center text-sm font-semibold text-slate-900 focus:border-primary focus:outline-none"
                                            >
                                            <button
                                                type="submit"
                                                title="Update quantity"
                                                class="rounded-lg bg-slate-100 p-2 text-slate-600 hover:bg-slate-200 transition"
                                            >
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                                </svg>
                                            </button>
                                        </form>

                                        {{-- Line Subtotal --}}
                                        <div class="text-right min-w-[80px]">
                                            <span class="block text-xs text-slate-400 sm:hidden">Subtotal:</span>
                                            <span class="font-bold text-slate-900 text-sm">
                                                {{ $currency }} {{ number_format((float) $item['subtotal'], 2) }}
                                            </span>
                                        </div>

                                        {{-- Remove Item --}}
                                        <form action="{{ route('cart.remove', $item['id']) }}" method="POST">
                                            @csrf
                                            <button
                                                type="submit"
                                                title="Remove item"
                                                class="text-slate-400 hover:text-rose-600 transition p-1"
                                            >
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="p-6 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                            <form action="{{ route('cart.clear') }}" method="POST">
                                @csrf
                                <button
                                    type="submit"
                                    class="text-xs font-semibold text-rose-600 hover:text-rose-700 transition"
                                >
                                    Clear Cart
                                </button>
                            </form>

                            <a href="{{ route('shop.index') }}" class="text-xs font-semibold text-primary hover:underline">
                                &larr; Continue Browsing
                            </a>
                        </div>
                    </div>

                    {{-- Order Summary Box --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-6">
                        <h2 class="font-heading text-xl font-bold text-slate-900 border-b border-slate-100 pb-4">
                            Order Summary
                        </h2>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between text-slate-600">
                                <span>Items Subtotal</span>
                                <span class="font-semibold text-slate-900">{{ $currency }} {{ number_format($total, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Online Payment</span>
                                <span class="font-semibold text-emerald-600">None ($0.00)</span>
                            </div>
                            <div class="pt-3 border-t border-slate-100 flex justify-between text-base font-extrabold text-slate-900">
                                <span>Estimated Total</span>
                                <span>{{ $currency }} {{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        <div class="pt-2">
                            <a
                                href="{{ route('shop.checkout') }}"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90"
                            >
                                <span>Proceed to Order Request</span>
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                        </div>

                        <p class="text-[11px] text-slate-500 text-center leading-relaxed">
                            💡 You will not be charged now. When you submit your order, our team receives it via Telegram and follows up directly to confirm details and fulfill your request.
                        </p>
                    </div>
                </div>
            @else
                <div class="mt-12 rounded-2xl border border-slate-200/80 bg-white p-12 text-center shadow-sm max-w-lg mx-auto">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 font-heading text-lg font-bold text-slate-900">Your cart is empty</h3>
                    <p class="mt-2 text-sm text-slate-500">
                        Explore our product catalog to discover items and submit an order request.
                    </p>
                    <div class="mt-6">
                        <a
                            href="{{ route('shop.index') }}"
                            class="inline-flex items-center rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90 transition"
                        >
                            Browse Products
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.site>

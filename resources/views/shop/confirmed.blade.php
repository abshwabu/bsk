<x-layouts.site title="Order Confirmed ({{ $order->reference }}) - {{ site_name() }}">
    <div class="py-16 md:py-24">
        <div class="mx-auto max-w-3xl px-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-8 sm:p-12 shadow-sm text-center">
                {{-- Success Icon --}}
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 mb-6">
                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <h1 class="font-heading text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                    Order Request Received!
                </h1>

                <p class="mt-3 text-base text-slate-600 max-w-xl mx-auto">
                    Thank you, <span class="font-semibold text-slate-900">{{ $order->customer_name }}</span>! Your order request has been submitted directly to our team. We will review it and reach out via phone (<span class="font-semibold text-slate-900">{{ $order->phone }}</span>) to confirm details and coordinate delivery.
                </p>

                {{-- Reference Badge --}}
                <div class="mt-8 inline-flex flex-col items-center justify-center rounded-2xl bg-slate-50 border border-slate-200 px-8 py-5">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Your Order Reference</span>
                    <span class="mt-1 font-mono text-2xl font-bold tracking-wider text-primary">{{ $order->reference }}</span>
                    <span class="mt-1 text-xs text-slate-400">Keep this reference for your records</span>
                </div>

                {{-- Order Snapshot Summary --}}
                <div class="mt-10 border-t border-slate-100 pt-8 text-left">
                    <h2 class="font-heading text-lg font-bold text-slate-900 mb-4">
                        Order Summary
                    </h2>

                    <div class="divide-y divide-slate-100 rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                        @foreach ($order->items as $item)
                            <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between text-sm">
                                <div>
                                    <span class="font-semibold text-slate-900">{{ $item['name'] }}</span>
                                    <span class="text-xs text-slate-500 block">&times; {{ $item['qty'] }} @ {{ $order->currency }} {{ number_format((float) $item['price'], 2) }}</span>
                                </div>
                                <span class="font-bold text-slate-900">
                                    {{ $order->currency }} {{ number_format((float) ($item['subtotal'] ?? ($item['price'] * $item['qty'])), 2) }}
                                </span>
                            </div>
                        @endforeach

                        <div class="pt-4 flex items-center justify-between font-extrabold text-base text-slate-900">
                            <span>Total Amount</span>
                            <span>{{ $order->formattedTotal() }}</span>
                        </div>
                    </div>

                    {{-- Customer Details Snapshot --}}
                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-slate-600 bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <div>
                            <span class="font-semibold text-slate-900 block">Contact:</span>
                            <span>{{ $order->phone }}</span>
                            @if ($order->email)
                                <span class="block">{{ $order->email }}</span>
                            @endif
                        </div>
                        @if ($order->address)
                            <div>
                                <span class="font-semibold text-slate-900 block">Delivery Address:</span>
                                <span>{{ $order->address }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                    <button
                        type="button"
                        onclick="window.print()"
                        class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 transition"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.656l10.5 0z" />
                        </svg>
                        <span>Print Confirmation</span>
                    </button>

                    <a
                        href="{{ route('shop.index') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90 transition"
                    >
                        <span>Continue Browsing Shop</span>
                        &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.site>

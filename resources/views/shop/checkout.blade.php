<x-layouts.site title="Checkout Order Request - {{ site_name() }}">
    <div class="py-12 md:py-20">
        <div class="mx-auto max-w-7xl px-6">
            <div class="border-b border-slate-200/80 pb-6 mb-8">
                <nav class="flex items-center gap-2 text-sm text-slate-500 mb-2">
                    <a href="{{ route('shop.index') }}" class="hover:text-primary transition">Shop</a>
                    <span>/</span>
                    <a href="{{ route('cart.index') }}" class="hover:text-primary transition">Cart</a>
                    <span>/</span>
                    <span class="text-slate-900 font-medium">Checkout</span>
                </nav>
                <h1 class="font-heading text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                    Submit Order Request
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    No online payment required. Provide your details and our team will follow up via phone or email to confirm.
                </p>
            </div>

            {{-- Error Summary --}}
            @if ($errors->any())
                <div class="mb-8 rounded-xl bg-rose-50 border border-rose-200 p-4 text-rose-800">
                    <p class="text-sm font-semibold">Please fix the following errors:</p>
                    <ul class="mt-2 list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 items-start">
                {{-- Left: Customer Details Form --}}
                <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                    <form action="{{ route('shop.checkout.submit') }}" method="POST" class="space-y-6">
                        @csrf

                        {{-- Honeypot Trap --}}
                        <div style="position: absolute; left: -9999px; top: -9999px; opacity: 0; pointer-events: none;" aria-hidden="true">
                            <label for="hp_website">Website</label>
                            <input type="text" name="hp_website" id="hp_website" tabindex="-1" autocomplete="off" value="">
                        </div>

                        {{-- Time-based signature token --}}
                        <input type="hidden" name="_token_time" value="{{ encrypt(time()) }}">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            {{-- Full Name --}}
                            <div>
                                <label for="customer_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                    Full Name <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="customer_name"
                                    id="customer_name"
                                    required
                                    value="{{ old('customer_name') }}"
                                    placeholder="Jane Doe"
                                    class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                                >
                                @error('customer_name')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Phone --}}
                            <div>
                                <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                    Phone Number <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="tel"
                                    name="phone"
                                    id="phone"
                                    required
                                    value="{{ old('phone') }}"
                                    placeholder="+1 (555) 000-0000"
                                    class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                                >
                                @error('phone')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Email Address --}}
                        <div>
                            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Email Address <span class="text-xs text-slate-400 lowercase font-normal">(optional)</span>
                            </label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email') }}"
                                placeholder="jane@example.com"
                                class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                            >
                            @error('email')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Delivery Address --}}
                        <div>
                            <label for="address" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Delivery Address / Location
                            </label>
                            <textarea
                                name="address"
                                id="address"
                                rows="3"
                                placeholder="Street address, city, region or preferred delivery meetup spot..."
                                class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 resize-y"
                            >{{ old('address') }}</textarea>
                            @error('address')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Order Notes --}}
                        <div>
                            <label for="notes" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Order Notes / Questions
                            </label>
                            <textarea
                                name="notes"
                                id="notes"
                                rows="2"
                                placeholder="Any specific requirements, sizing notes, or questions..."
                                class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 resize-y"
                            >{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                            <a href="{{ route('cart.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition">
                                &larr; Return to Cart
                            </a>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-8 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90"
                            >
                                <span>Submit Order Request</span>
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Right: Items Snapshot Summary --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-6">
                    <h2 class="font-heading text-lg font-bold text-slate-900 border-b border-slate-100 pb-3">
                        Review Items ({{ count($items) }})
                    </h2>

                    <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto pr-2 space-y-3">
                        @foreach ($items as $item)
                            <div class="pt-3 first:pt-0 flex items-center justify-between gap-3 text-sm">
                                <div class="truncate">
                                    <p class="font-semibold text-slate-900 truncate">{{ $item['name'] }}</p>
                                    <p class="text-xs text-slate-500">Qty: {{ $item['qty'] }} &times; {{ $currency }} {{ number_format((float) $item['price'], 2) }}</p>
                                </div>
                                <span class="font-bold text-slate-900 shrink-0">
                                    {{ $currency }} {{ number_format((float) $item['subtotal'], 2) }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-slate-200 pt-4 space-y-2 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal</span>
                            <span class="font-semibold text-slate-900">{{ $currency }} {{ number_format($total, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Payment Due Now</span>
                            <span class="font-semibold text-emerald-600">$0.00</span>
                        </div>
                        <div class="pt-2 border-t border-slate-100 flex justify-between text-lg font-extrabold text-slate-900">
                            <span>Estimated Total</span>
                            <span>{{ $currency }} {{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 text-xs text-slate-500 space-y-2">
                        <div class="flex items-center gap-2 font-semibold text-slate-800">
                            <svg class="h-4 w-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                            <span>How it works:</span>
                        </div>
                        <p>1. Your order is sent directly to our team via Telegram.</p>
                        <p>2. We confirm availability and contact you via phone or email.</p>
                        <p>3. Payment and delivery are coordinated directly with you.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.site>

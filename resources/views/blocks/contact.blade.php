@props([
    'data' => [],
    'heading' => null,
    'subheading' => null,
    'show_details' => true,
    'slot' => null,
])

@php
    $heading = $heading ?? ($data['heading'] ?? 'Get In Touch');
    $subheading = $subheading ?? ($data['subheading'] ?? 'Have questions or want to discuss a project? Reach out anytime.');
    $showDetails = $show_details ?? ($data['show_details'] ?? true);

    $phone = site('phone');
    $email = site('email');
    $address = site('address');
    $whatsapp = site('whatsapp');
    $socials = site('social_links') ?? [];
@endphp

<section id="contact" class="py-16 md:py-24 bg-slate-50 border-t border-slate-100">
    <div class="mx-auto max-w-7xl px-6">
        @if ($heading || $subheading)
            <div class="mx-auto mb-12 max-w-3xl text-center md:mb-16">
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
        @endif

        <div class="grid grid-cols-1 gap-12 lg:grid-cols-2 lg:gap-16">
            {{-- Contact Information Column --}}
            <div class="flex flex-col justify-between space-y-8 rounded-2xl bg-white p-8 border border-slate-200/80 shadow-sm">
                <div>
                    <h3 class="font-heading text-2xl font-bold text-slate-900">
                        Contact Information
                    </h3>
                    <p class="mt-2 text-slate-600">
                        We are available during regular business hours to assist you.
                    </p>

                    <dl class="mt-8 space-y-6 text-slate-600">
                        @if ($phone)
                            <div class="flex items-start gap-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                    </svg>
                                </span>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-900">Phone</dt>
                                    <dd class="mt-1">
                                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="transition hover:text-primary">
                                            {{ $phone }}
                                        </a>
                                    </dd>
                                </div>
                            </div>
                        @endif

                        @if ($email)
                            <div class="flex items-start gap-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                    </svg>
                                </span>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-900">Email</dt>
                                    <dd class="mt-1">
                                        <a href="mailto:{{ $email }}" class="transition hover:text-primary">
                                            {{ $email }}
                                        </a>
                                    </dd>
                                </div>
                            </div>
                        @endif

                        @if ($whatsapp)
                            <div class="flex items-start gap-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-.974-.974 5.968 5.968 0 011.057-2.035A8.04 8.04 0 013 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                                    </svg>
                                </span>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-900">WhatsApp</dt>
                                    <dd class="mt-1">
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" target="_blank" rel="noopener noreferrer" class="transition hover:text-emerald-600">
                                            {{ $whatsapp }}
                                        </a>
                                    </dd>
                                </div>
                            </div>
                        @endif

                        @if ($address)
                            <div class="flex items-start gap-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                </span>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-900">Address</dt>
                                    <dd class="mt-1 whitespace-pre-line leading-relaxed">
                                        {{ $address }}
                                    </dd>
                                </div>
                            </div>
                        @endif
                    </dl>
                </div>

                @if (! empty($socials))
                    <div class="border-t border-slate-100 pt-6">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Follow Us</span>
                        <div class="mt-3 flex flex-wrap gap-3">
                            @foreach ($socials as $soc)
                                @if (! empty($soc['url']))
                                    <a
                                        href="{{ $soc['url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-primary hover:text-white"
                                    >
                                        {{ ucfirst($soc['platform'] ?? 'Link') }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Interactive Inquiry Form Slot --}}
            <div class="flex flex-col justify-center">
                @if (isset($slot) && ! empty((string) $slot))
                    <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-white p-8 sm:p-10 shadow-sm">
                        {{ $slot }}
                    </div>
                @else
                    <x-inquiry-form />
                @endif
            </div>
        </div>
    </div>
</section>

@props([
    'data' => [],
    'heading' => null,
    'subheading' => null,
    'faqs' => null,
])

@php
    $heading = $heading ?? ($data['heading'] ?? 'Frequently Asked Questions');
    $subheading = $subheading ?? ($data['subheading'] ?? 'Everything you need to know about our platform and delivery process.');
    $items = $faqs ?? ($data['faqs'] ?? [
        [
            'question' => 'How quickly can we launch a project?',
            'answer' => 'Most client projects launch within 1 to 2 weeks thanks to the modular page blocks and streamlined administration.',
        ],
        [
            'question' => 'Can we customize colors, fonts, and branding?',
            'answer' => 'Yes! The site kit provides a full Site Settings panel where you can configure primary and accent colors, Google fonts, logos, and favicons with instant live reflection.',
        ],
        [
            'question' => 'Is this solution suitable for shared hosting?',
            'answer' => 'Yes. Bina Site Kit is purposely built without requiring Redis or background queue daemons, running happily on shared or cloud hosting.',
        ],
    ]);
@endphp

<section class="py-16 md:py-24" x-data="{ active: null }">
    <div class="mx-auto max-w-4xl px-6">
        @if ($heading || $subheading)
            <div class="mb-12 text-center md:mb-16">
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

        @if (! empty($items) && is_iterable($items))
            <div class="space-y-4">
                @foreach ($items as $index => $item)
                    @php
                        $question = $item['question'] ?? 'Question';
                        $answer = $item['answer'] ?? '';
                    @endphp
                    <div class="rounded-xl border border-slate-200 bg-white transition shadow-sm">
                        <button
                            type="button"
                            @click="active = (active === {{ $index }} ? null : {{ $index }})"
                            class="flex w-full items-center justify-between p-6 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
                            :aria-expanded="active === {{ $index }}"
                        >
                            <span class="font-heading text-lg font-semibold text-slate-900">
                                {{ $question }}
                            </span>
                            <span
                                class="ml-4 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition-transform duration-200"
                                :class="{ 'rotate-180 bg-primary/10 text-primary': active === {{ $index }} }"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </span>
                        </button>

                        <div
                            x-show="active === {{ $index }}"
                            x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-1"
                            class="border-t border-slate-100 px-6 pb-6 pt-4 text-slate-600 leading-relaxed"
                        >
                            {!! nl2br(e($answer)) !!}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

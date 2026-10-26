@props([
    'data' => [],
    'heading' => null,
    'subheading' => null,
    'stats' => null,
])

@php
    $heading = $heading ?? ($data['heading'] ?? 'Proven Track Record');
    $subheading = $subheading ?? ($data['subheading'] ?? 'Key milestones achieved across client launches.');
    $items = $stats ?? ($data['stats'] ?? [
        ['number' => '120+', 'label' => 'Websites Launched'],
        ['number' => '99.8%', 'label' => 'Uptime Guarantee'],
        ['number' => '2.5x', 'label' => 'Average Speed Boost'],
        ['number' => '24/7', 'label' => 'Support Availability'],
    ]);
@endphp

<section class="border-y border-slate-100 bg-slate-50 py-16 md:py-20">
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

        @if (! empty($items) && is_iterable($items))
            <div class="grid grid-cols-2 gap-8 md:grid-cols-4 md:gap-12 text-center">
                @foreach ($items as $item)
                    @php
                        $num = $item['number'] ?? '0';
                        $lbl = $item['label'] ?? '';
                    @endphp
                    <div class="flex flex-col items-center">
                        <span class="font-heading text-4xl font-extrabold tracking-tight text-primary sm:text-5xl">
                            {{ $num }}
                        </span>
                        @if ($lbl)
                            <span class="mt-2 text-sm font-medium text-slate-600 sm:text-base">
                                {{ $lbl }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

@props([
    'data' => [],
    'heading' => null,
    'subheading' => null,
    'columns' => '3',
    'features' => null,
])

@php
    $heading = $heading ?? ($data['heading'] ?? 'Our Services');
    $subheading = $subheading ?? ($data['subheading'] ?? 'High-impact solutions designed to accelerate your growth.');
    $cols = (string) ($columns ?? ($data['columns'] ?? '3'));
    $items = $features ?? ($data['features'] ?? [
        [
            'icon' => 'sparkles',
            'title' => 'Web Design & Development',
            'description' => 'Fast, accessible, and responsive websites crafted with modern tooling and design standards.',
        ],
        [
            'icon' => 'bolt',
            'title' => 'High Performance',
            'description' => 'Optimized for speed, SEO ranking, and frictionless visitor conversions on every device.',
        ],
        [
            'icon' => 'shield-check',
            'title' => 'Security & Reliability',
            'description' => 'Solid infrastructure with hardened security defaults and effortless maintenance.',
        ],
    ]);

    $gridClasses = match ($cols) {
        '2' => 'grid grid-cols-1 md:grid-cols-2 gap-8',
        '4' => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6',
        default => 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8',
    };

    $renderIcon = function (?string $iconName, string $classes = 'w-6 h-6 text-primary') {
        $icon = trim((string) $iconName);
        if ($icon === '') {
            $icon = 'sparkles';
        }
        $name = str_starts_with($icon, 'heroicon-') ? $icon : 'heroicon-o-' . ltrim($icon, 'o-');
        try {
            return svg($name, $classes)->toHtml();
        } catch (\Throwable $e) {
            return '<svg class="' . $classes . '" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" /></svg>';
        }
    };
@endphp

<section class="py-16 md:py-24">
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
            <div class="{{ $gridClasses }}">
                @foreach ($items as $item)
                    @php
                        $itemTitle = $item['title'] ?? 'Feature';
                        $itemDesc = $item['description'] ?? '';
                        $itemIcon = $item['icon'] ?? 'sparkles';
                    @endphp
                    <div class="relative flex flex-col rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition hover:shadow-md">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10">
                            {!! $renderIcon($itemIcon, 'h-6 w-6 text-primary') !!}
                        </div>
                        <h3 class="font-heading text-xl font-semibold text-slate-900">
                            {{ $itemTitle }}
                        </h3>
                        @if ($itemDesc)
                            <p class="mt-2 text-slate-600">
                                {{ $itemDesc }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

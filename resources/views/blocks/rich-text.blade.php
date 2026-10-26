@props([
    'data' => [],
    'heading' => null,
    'content' => null,
    'width' => 'narrow',
])

@php
    $heading = $heading ?? ($data['heading'] ?? null);
    $html = $content ?? ($data['content'] ?? '<p>We build elegant, reliable digital products that empower businesses to scale smoothly.</p>');
    $widthOption = $width ?? ($data['width'] ?? 'narrow');

    $maxWidthClass = match ($widthOption) {
        'wide' => 'max-w-5xl',
        'full' => 'max-w-7xl',
        default => 'max-w-3xl',
    };
@endphp

<section class="py-12 md:py-16">
    <div class="mx-auto {{ $maxWidthClass }} px-6">
        @if ($heading)
            <h2 class="mb-6 font-heading text-3xl font-bold tracking-tight text-primary">
                {{ $heading }}
            </h2>
        @endif

        @if ($html)
            <div class="prose prose-slate max-w-none">
                {!! $html !!}
            </div>
        @endif
    </div>
</section>

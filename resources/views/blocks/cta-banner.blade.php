@props([
    'data' => [],
    'heading' => null,
    'text' => null,
    'button_label' => null,
    'button_url' => null,
    'theme' => 'primary',
])

@php
    $heading = $heading ?? ($data['heading'] ?? 'Ready to Start Your Project?');
    $text = $text ?? ($data['text'] ?? 'Contact our team today for a free consultation and quick delivery timeline.');
    $label = $button_label ?? ($data['button_label'] ?? 'Contact Us Now');
    $url = $button_url ?? ($data['button_url'] ?? '#contact');
    $colorTheme = $theme ?? ($data['theme'] ?? 'primary');

    $containerClass = match ($colorTheme) {
        'accent' => 'bg-accent text-white',
        'surface' => 'bg-slate-100 text-slate-900 border border-slate-200',
        default => 'bg-primary text-white',
    };

    $buttonClass = match ($colorTheme) {
        'accent' => 'bg-white text-accent hover:bg-slate-100',
        'surface' => 'bg-primary text-white hover:bg-primary/90',
        default => 'bg-white text-primary hover:bg-slate-100',
    };

    $subtextClass = match ($colorTheme) {
        'surface' => 'text-slate-600',
        default => 'text-white/90',
    };
@endphp

<section class="py-16 md:py-20">
    <div class="mx-auto max-w-7xl px-6">
        <div class="relative overflow-hidden rounded-3xl p-8 sm:p-12 md:p-16 {{ $containerClass }} shadow-xl">
            <div class="mx-auto max-w-3xl text-center">
                @if ($heading)
                    <h2 class="font-heading text-3xl font-extrabold tracking-tight sm:text-4xl md:text-5xl">
                        {{ $heading }}
                    </h2>
                @endif

                @if ($text)
                    <p class="mx-auto mt-6 max-w-xl text-lg sm:text-xl {{ $subtextClass }}">
                        {{ $text }}
                    </p>
                @endif

                @if ($label)
                    <div class="mt-8 flex justify-center">
                        <a
                            href="{{ $url }}"
                            class="inline-flex items-center justify-center rounded-xl px-8 py-4 text-base font-bold shadow-md transition duration-150 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 {{ $buttonClass }}"
                        >
                            {{ $label }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

@props([
    'data' => [],
    'heading' => null,
    'subheading' => null,
    'background_image' => null,
    'background_image_alt' => null,
    'overlay_opacity' => '40',
    'alignment' => 'center',
    'primary_cta_label' => null,
    'primary_cta_url' => null,
    'secondary_cta_label' => null,
    'secondary_cta_url' => null,
])

@php
    $heading = $heading ?? ($data['heading'] ?? 'Build Your Next Great Project');
    $subheading = $subheading ?? ($data['subheading'] ?? 'A modern, modular starter kit designed for high-performing client websites.');
    $bgImage = $background_image ?? ($data['background_image'] ?? null);
    $bgAlt = $background_image_alt ?? ($data['background_image_alt'] ?? 'Hero visual background');
    $opacity = (string) ($overlay_opacity ?? ($data['overlay_opacity'] ?? '40'));
    $align = $alignment ?? ($data['alignment'] ?? 'center');
    $primaryLabel = $primary_cta_label ?? ($data['primary_cta_label'] ?? 'Get Started');
    $primaryUrl = $primary_cta_url ?? ($data['primary_cta_url'] ?? '#');
    $secondaryLabel = $secondary_cta_label ?? ($data['secondary_cta_label'] ?? null);
    $secondaryUrl = $secondary_cta_url ?? ($data['secondary_cta_url'] ?? '#');

    $bgUrl = null;
    if ($bgImage) {
        $bgUrl = (str_starts_with($bgImage, 'http://') || str_starts_with($bgImage, 'https://') || str_starts_with($bgImage, '//'))
            ? $bgImage
            : \Illuminate\Support\Facades\Storage::disk('public')->url($bgImage);
    }

    $alignClasses = match ($align) {
        'left' => 'text-left items-start',
        'right' => 'text-right items-end',
        default => 'text-center items-center',
    };

    $buttonAlignClasses = match ($align) {
        'left' => 'justify-start',
        'right' => 'justify-end',
        default => 'justify-center',
    };

    $opacityClass = match ($opacity) {
        '0' => 'bg-transparent',
        '20' => 'bg-slate-950/20',
        '60' => 'bg-slate-950/60',
        '80' => 'bg-slate-950/80',
        default => 'bg-slate-950/40',
    };
@endphp

<section class="relative overflow-hidden py-20 md:py-32 {{ $bgUrl ? 'text-white' : 'text-slate-900 bg-slate-50' }}">
    @if ($bgUrl)
        <div class="absolute inset-0 z-0">
            <img
                src="{{ $bgUrl }}"
                alt="{{ $bgAlt }}"
                loading="lazy"
                class="h-full w-full object-cover"
            />
            <div class="absolute inset-0 {{ $opacityClass }}"></div>
        </div>
    @endif

    <div class="relative z-10 mx-auto max-w-5xl px-6">
        <div class="flex flex-col {{ $alignClasses }}">
            @if ($heading)
                <h1 class="font-heading text-4xl font-extrabold tracking-tight sm:text-5xl md:text-6xl {{ $bgUrl ? 'text-white' : 'text-primary' }}">
                    {{ $heading }}
                </h1>
            @endif

            @if ($subheading)
                <p class="mt-6 max-w-2xl text-lg sm:text-xl {{ $bgUrl ? 'text-slate-100' : 'text-slate-600' }}">
                    {{ $subheading }}
                </p>
            @endif

            @if ($primaryLabel || $secondaryLabel)
                <div class="mt-8 flex flex-wrap gap-4 {{ $buttonAlignClasses }}">
                    @if ($primaryLabel)
                        <a
                            href="{{ $primaryUrl }}"
                            class="inline-flex items-center justify-center rounded-lg bg-primary px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
                        >
                            {{ $primaryLabel }}
                        </a>
                    @endif

                    @if ($secondaryLabel)
                        <a
                            href="{{ $secondaryUrl }}"
                            class="inline-flex items-center justify-center rounded-lg border px-6 py-3 text-base font-semibold transition {{ $bgUrl ? 'border-white/80 text-white hover:bg-white/20' : 'border-slate-300 text-slate-700 hover:bg-slate-100' }}"
                        >
                            {{ $secondaryLabel }}
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</section>

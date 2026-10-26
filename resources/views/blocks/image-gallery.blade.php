@props([
    'data' => [],
    'heading' => null,
    'subheading' => null,
    'images' => null,
    'layout' => 'grid',
    'columns' => '3',
])

@php
    $heading = $heading ?? ($data['heading'] ?? 'Photo Gallery');
    $subheading = $subheading ?? ($data['subheading'] ?? 'A visual showcase of our work and community.');
    $rawImages = $images ?? ($data['images'] ?? []);
    $layoutType = $layout ?? ($data['layout'] ?? 'grid');
    $cols = (string) ($columns ?? ($data['columns'] ?? '3'));

    $galleryList = [];
    if (! empty($rawImages) && is_array($rawImages)) {
        foreach ($rawImages as $img) {
            if (! $img) continue;
            $url = (str_starts_with($img, 'http://') || str_starts_with($img, 'https://') || str_starts_with($img, '//'))
                ? $img
                : \Illuminate\Support\Facades\Storage::disk('public')->url($img);
            $galleryList[] = [
                'url' => $url,
                'alt' => 'Gallery showcase photo',
            ];
        }
    }

    if (empty($galleryList)) {
        // Sensible fallback placeholders
        $galleryList = [
            ['url' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=800&q=80', 'alt' => 'Modern workspace setup'],
            ['url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80', 'alt' => 'Analytics and performance dashboard'],
            ['url' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80', 'alt' => 'Collaborative team meeting'],
        ];
    }

    $gridColClass = match ($cols) {
        '2' => 'grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6',
        '4' => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6',
        default => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6',
    };

    $masonryColClass = match ($cols) {
        '2' => 'columns-1 sm:columns-2 gap-4 md:gap-6 space-y-4 md:space-y-6',
        '4' => 'columns-1 sm:columns-2 lg:columns-4 gap-4 md:gap-6 space-y-4 md:space-y-6',
        default => 'columns-1 sm:columns-2 lg:columns-3 gap-4 md:gap-6 space-y-4 md:space-y-6',
    };
@endphp

<section class="py-16 md:py-24" x-data="{ open: false, activeImage: '', activeAlt: '' }">
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

        @if ($layoutType === 'masonry')
            <div class="{{ $masonryColClass }}">
                @foreach ($galleryList as $item)
                    <div class="group relative overflow-hidden rounded-xl bg-slate-100 shadow-sm transition hover:shadow-md">
                        <button
                            type="button"
                            class="block w-full text-left focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                            @click="activeImage = '{{ $item['url'] }}'; activeAlt = '{{ $item['alt'] }}'; open = true"
                        >
                            <img
                                src="{{ $item['url'] }}"
                                alt="{{ $item['alt'] }}"
                                loading="lazy"
                                class="w-full object-cover transition duration-300 group-hover:scale-105"
                            />
                        </button>
                    </div>
                @endforeach
            </div>
        @else
            <div class="{{ $gridColClass }}">
                @foreach ($galleryList as $item)
                    <div class="group relative aspect-[4/3] overflow-hidden rounded-xl bg-slate-100 shadow-sm transition hover:shadow-md">
                        <button
                            type="button"
                            class="h-full w-full focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                            @click="activeImage = '{{ $item['url'] }}'; activeAlt = '{{ $item['alt'] }}'; open = true"
                        >
                            <img
                                src="{{ $item['url'] }}"
                                alt="{{ $item['alt'] }}"
                                loading="lazy"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                            />
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Alpine Lightbox Modal --}}
    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @keydown.escape.window="open = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4 backdrop-blur-sm"
            @click.self="open = false"
        >
            <button
                type="button"
                @click="open = false"
                class="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-white transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white"
                aria-label="Close image preview"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="relative max-h-[90vh] max-w-[90vw]">
                <img
                    :src="activeImage"
                    :alt="activeAlt"
                    class="max-h-[85vh] max-w-full rounded-lg object-contain shadow-2xl"
                />
            </div>
        </div>
    </template>
</section>

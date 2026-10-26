@props([
    'data' => [],
    'heading' => null,
    'subheading' => null,
    'testimonials' => null,
])

@php
    $heading = $heading ?? ($data['heading'] ?? 'What People Say');
    $subheading = $subheading ?? ($data['subheading'] ?? 'Feedback from clients who trusted our platform.');
    $items = $testimonials ?? ($data['testimonials'] ?? [
        [
            'quote' => 'The site kit allowed us to launch a gorgeous, ultra-fast website in a matter of days. Highly recommended!',
            'name' => 'Sarah Jenkins',
            'role' => 'Founder & Product Lead',
            'photo' => null,
        ],
        [
            'quote' => 'Outstanding flexibility and clean design system. Managing blocks from the admin panel is effortless.',
            'name' => 'Michael Chen',
            'role' => 'Operations Director',
            'photo' => null,
        ],
    ]);
@endphp

<section class="bg-slate-50 py-16 md:py-24">
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
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($items as $item)
                    @php
                        $quote = $item['quote'] ?? '';
                        $name = $item['name'] ?? 'Anonymous';
                        $role = $item['role'] ?? '';
                        $photo = $item['photo'] ?? null;
                        $photoUrl = null;
                        if ($photo) {
                            $photoUrl = (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://') || str_starts_with($photo, '//'))
                                ? $photo
                                : \Illuminate\Support\Facades\Storage::disk('public')->url($photo);
                        }
                    @endphp
                    <div class="flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-8 shadow-sm">
                        <blockquote class="text-slate-700 leading-relaxed">
                            <span class="text-3xl font-serif text-accent leading-none select-none">“</span>
                            {{ $quote }}
                        </blockquote>

                        <div class="mt-6 flex items-center gap-4 border-t border-slate-100 pt-6">
                            @if ($photoUrl)
                                <img
                                    src="{{ $photoUrl }}"
                                    alt="{{ $name }}"
                                    loading="lazy"
                                    class="h-12 w-12 rounded-full object-cover ring-2 ring-primary/20"
                                />
                            @else
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-base font-bold text-primary">
                                    {{ strtoupper(substr($name, 0, 1)) }}
                                </div>
                            @endif

                            <div>
                                <div class="font-heading font-semibold text-slate-900">
                                    {{ $name }}
                                </div>
                                @if ($role)
                                    <div class="text-sm text-slate-500">
                                        {{ $role }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

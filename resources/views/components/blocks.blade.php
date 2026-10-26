@props([
    'page' => null,
    'blocks' => null,
])

@php
    $items = $blocks ?? ($page?->blocks ?? []);
@endphp

@if (is_iterable($items))
    @foreach ($items as $block)
        @php
            $type = is_array($block) ? ($block['type'] ?? null) : null;
            $data = is_array($block) ? ($block['data'] ?? []) : [];

            if (! $type) {
                continue;
            }

            $view = \App\Blocks\BlockRegistry::getView($type);

            if (! $view || ! \Illuminate\Support\Facades\View::exists($view)) {
                continue;
            }
        @endphp

        @include($view, ['data' => $data, ...$data])
    @endforeach
@endif

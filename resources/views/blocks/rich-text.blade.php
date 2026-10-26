@props([
    'data' => [],
    'content' => null,
])

@php
    $html = $content ?? ($data['content'] ?? '');
@endphp

@if ($html)
    <div class="prose prose-slate mx-auto max-w-4xl px-6 py-8">
        {!! $html !!}
    </div>
@endif

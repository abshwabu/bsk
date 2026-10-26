@php
    $navItems = \App\Services\Navigation::getItems();
    $logo = site()->logoUrl();
    $siteName = site_name();
@endphp

<header x-data="{ mobileOpen: false }" class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur-md">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
        {{-- Brand Logo / Site Name --}}
        <a href="{{ url('/') }}" class="flex items-center gap-3 font-heading text-xl font-bold tracking-tight text-primary transition hover:opacity-90">
            @if ($logo)
                <img src="{{ $logo }}" alt="{{ $siteName }}" width="120" height="32" class="h-8 w-auto" />
            @endif
            <span>{{ $siteName }}</span>
        </a>

        {{-- Desktop Navigation --}}
        <nav class="hidden md:flex items-center gap-1 lg:gap-2">
            @foreach ($navItems as $item)
                @php
                    $isActive = \App\Services\Navigation::isActive($item);
                @endphp
                <a
                    href="{{ $item['url'] }}"
                    @if ($item['open_in_new_tab']) target="_blank" rel="noopener noreferrer" @endif
                    class="rounded-lg px-3 py-2 text-sm transition {{ $isActive ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-primary font-medium' }}"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- Mobile Hamburger Button --}}
        <div class="flex items-center md:hidden">
            <button
                type="button"
                @click="mobileOpen = !mobileOpen"
                class="inline-flex items-center justify-center rounded-lg p-2 text-slate-700 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary"
                aria-label="Toggle navigation menu"
                :aria-expanded="mobileOpen"
            >
                <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg x-show="mobileOpen" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile Dropdown Menu --}}
    <div
        x-show="mobileOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="border-t border-slate-200 bg-white px-6 py-4 md:hidden shadow-lg"
    >
        <div class="flex flex-col space-y-1">
            @foreach ($navItems as $item)
                @php
                    $isActive = \App\Services\Navigation::isActive($item);
                @endphp
                <a
                    href="{{ $item['url'] }}"
                    @click="mobileOpen = false"
                    @if ($item['open_in_new_tab']) target="_blank" rel="noopener noreferrer" @endif
                    class="rounded-lg px-3 py-2 text-base transition {{ $isActive ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-primary font-medium' }}"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</header>

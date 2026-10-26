<x-layouts.site>
    <x-slot:header>
        <div class="border-b border-slate-200 px-6 py-5">
            <a class="font-semibold" href="{{ route('home') }}">{{ site_name() }}</a>
        </div>
    </x-slot:header>

    <section class="mx-auto max-w-3xl px-6 py-24 text-center">
        <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Your website starts here</h1>
        <p class="mt-5 text-lg text-slate-600">Add and manage your website content from the admin panel.</p>
    </section>

    <x-slot:footer>
        <div class="border-t border-slate-200 px-6 py-5 text-center text-sm text-slate-500">
            {{ site_name() }}
        </div>
    </x-slot:footer>
</x-layouts.site>

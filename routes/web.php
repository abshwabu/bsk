<?php

use App\Models\Page;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

$canPreviewUnpublished = function (): bool {
    if (! auth()->check()) {
        return false;
    }

    $user = auth()->user();

    if (method_exists($user, 'canAccessPanel')) {
        try {
            return (bool) $user->canAccessPanel(Filament::getPanel('admin'));
        } catch (Throwable $e) {
            return true;
        }
    }

    return true;
};

Route::get('/', function () use ($canPreviewUnpublished) {
    $canPreview = $canPreviewUnpublished();

    $page = Page::query()->where('is_home', true)->first();

    if (! $page) {
        return view('home', ['page' => null]);
    }

    if (! $page->is_published && ! $canPreview) {
        abort(404);
    }

    return view('home', ['page' => $page]);
})->name('home');

Route::get('/{slug}', function (string $slug) use ($canPreviewUnpublished) {
    $canPreview = $canPreviewUnpublished();

    $page = Page::query()->where('slug', $slug)->first();

    if (! $page) {
        abort(404);
    }

    if (! $page->is_published && ! $canPreview) {
        abort(404);
    }

    return view('page', ['page' => $page]);
})->where('slug', '^(?!admin|storage|sitemap\.xml|robots\.txt|inquiry|shop|projects$).*')->name('pages.show');

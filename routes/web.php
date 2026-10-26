<?php

use App\Models\Page;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $homePage = Page::query()
        ->where('is_home', true)
        ->where('is_published', true)
        ->first();

    return view('home', [
        'page' => $homePage,
    ]);
})->name('home');

Route::get('/{slug}', function (string $slug) {
    $page = Page::query()
        ->where('slug', $slug)
        ->where('is_published', true)
        ->firstOrFail();

    return view('page', [
        'page' => $page,
    ]);
})->where('slug', '^(?!admin).*$')->name('pages.show');

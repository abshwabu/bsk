<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\ShopController;
use App\Models\Page;
use App\Models\Project;
use App\Models\ProjectCategory;
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

// Home route
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

// Portfolio routes (guarded by enabled_modules.portfolio)
Route::prefix('projects')->name('projects.')->group(function () use ($canPreviewUnpublished) {
    Route::get('/', function () {
        if (! site()->isModuleEnabled('portfolio')) {
            abort(404);
        }

        $categories = ProjectCategory::query()->orderBy('sort')->get();
        $currentCategory = request('category');

        $query = Project::query()
            ->published()
            ->with('category')
            ->orderBy('sort')
            ->orderByDesc('created_at');

        if ($currentCategory) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $currentCategory));
        }

        $projects = $query->paginate(12)->withQueryString();

        return view('projects.index', [
            'projects' => $projects,
            'categories' => $categories,
            'currentCategory' => $currentCategory,
        ]);
    })->name('index');

    Route::get('/{slug}', function (string $slug) use ($canPreviewUnpublished) {
        if (! site()->isModuleEnabled('portfolio')) {
            abort(404);
        }

        $canPreview = $canPreviewUnpublished();

        $project = Project::query()
            ->where('slug', $slug)
            ->with('category')
            ->first();

        if (! $project) {
            abort(404);
        }

        if (! $project->is_published && ! $canPreview) {
            abort(404);
        }

        return view('projects.show', [
            'project' => $project,
        ]);
    })->name('show');
});

// Inquiry form submission (5 requests / hour / IP)
Route::post('/inquiry', [InquiryController::class, 'store'])
    ->middleware('throttle:5,60')
    ->name('inquiry.store');

// Catalog / Shop routes (guarded by enabled_modules.catalog inside controller)
Route::prefix('shop')->name('shop.')->group(function () {
    Route::get('/', [ShopController::class, 'index'])->name('index');
    Route::get('/{slug}', [ShopController::class, 'show'])->name('show');
});

// Cart routes
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::post('/update', [CartController::class, 'update'])->name('update');
    Route::post('/remove/{productId}', [CartController::class, 'remove'])->name('remove');
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');
});

// Checkout routes
Route::get('/checkout', [ShopController::class, 'checkout'])->name('shop.checkout');
Route::post('/checkout', [ShopController::class, 'submitCheckout'])
    ->middleware('throttle:5,60')
    ->name('shop.checkout.submit');
Route::get('/order-confirmed/{reference}', [ShopController::class, 'orderConfirmed'])->name('shop.order-confirmed');

// SEO routes
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// Dynamic CMS page route
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
})->where('slug', '^(?!admin|storage|sitemap\.xml|robots\.txt|inquiry|shop|projects|cart|checkout|order-confirmed$).*')->name('pages.show');

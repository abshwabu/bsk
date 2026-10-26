<?php

use App\Filament\Resources\PageResource\Pages\CreatePage;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\Navigation;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

it('renders home page on root URL when published', function () {
    Page::query()->create([
        'title' => 'Home Page',
        'slug' => 'home',
        'is_home' => true,
        'is_published' => true,
        'blocks' => [
            [
                'type' => 'rich_text',
                'data' => [
                    'content' => '<p>Homepage headline block content</p>',
                ],
            ],
        ],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Homepage headline block content', false);
});

it('renders published page by slug on public route', function () {
    Page::query()->create([
        'title' => 'Services Page',
        'slug' => 'services',
        'is_published' => true,
        'blocks' => [
            [
                'type' => 'rich_text',
                'data' => [
                    'content' => '<p>Specialized Services for Growth</p>',
                ],
            ],
        ],
    ]);

    $this->get('/services')
        ->assertOk()
        ->assertSee('Specialized Services for Growth', false);
});

it('returns 404 for missing pages with custom 404 view', function () {
    $this->get('/non-existent-page-url')
        ->assertNotFound()
        ->assertSee('404')
        ->assertSee('Page Not Found')
        ->assertSee('Back to Home');
});

it('returns 404 for unpublished pages to guests and allows admin preview', function () {
    $unpublishedPage = Page::query()->create([
        'title' => 'Draft Secret Page',
        'slug' => 'draft-secret',
        'is_published' => false,
        'blocks' => [
            [
                'type' => 'rich_text',
                'data' => [
                    'content' => '<p>Top Secret Upcoming Product Launch</p>',
                ],
            ],
        ],
    ]);

    // Guest cannot see unpublished page
    $this->get('/draft-secret')
        ->assertNotFound()
        ->assertSee('Page Not Found');

    // Admin user logged into Filament can preview the unpublished page
    $admin = User::factory()->create(['email' => config('bina.admin_email')]);

    $this->actingAs($admin)
        ->get('/draft-secret')
        ->assertOk()
        ->assertSee('Top Secret Upcoming Product Launch', false);
});

it('blocks reserved slugs during PageResource validation', function () {
    $admin = User::factory()->create(['email' => config('bina.admin_email')]);
    $reservedSlugs = ['admin', 'storage', 'sitemap.xml', 'robots.txt', 'inquiry', 'shop', 'projects'];

    foreach ($reservedSlugs as $slug) {
        Livewire::actingAs($admin)
            ->test(CreatePage::class)
            ->fillForm([
                'title' => 'Reserved Page '.$slug,
                'slug' => $slug,
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'not_in']);
    }
});

it('builds navigation from pages where show_in_nav is true ordered by nav_order', function () {
    Navigation::flushCache();

    Page::query()->create([
        'title' => 'Third Page',
        'slug' => 'third',
        'is_published' => true,
        'show_in_nav' => true,
        'nav_order' => 30,
    ]);

    Page::query()->create([
        'title' => 'First Page',
        'slug' => 'first',
        'is_published' => true,
        'show_in_nav' => true,
        'nav_order' => 10,
    ]);

    Page::query()->create([
        'title' => 'Second Page',
        'slug' => 'second',
        'is_published' => true,
        'show_in_nav' => true,
        'nav_order' => 20,
    ]);

    Page::query()->create([
        'title' => 'Hidden Page',
        'slug' => 'hidden',
        'is_published' => true,
        'show_in_nav' => false,
        'nav_order' => 5,
    ]);

    $navItems = Navigation::getItems();
    $labels = array_column($navItems, 'label');

    expect($labels)->toBe(['First Page', 'Second Page', 'Third Page'])
        ->and($labels)->not->toContain('Hidden Page');
});

it('flushes navigation cache and updates nav when pages are reordered or published', function () {
    Navigation::flushCache();

    $page1 = Page::query()->create([
        'title' => 'Alpha',
        'slug' => 'alpha',
        'is_published' => true,
        'show_in_nav' => true,
        'nav_order' => 1,
    ]);

    $page2 = Page::query()->create([
        'title' => 'Beta',
        'slug' => 'beta',
        'is_published' => true,
        'show_in_nav' => true,
        'nav_order' => 2,
    ]);

    // First request builds the nav and populates the cache
    $firstResponse = $this->get('/')->assertOk()->getContent();
    expect(Cache::has(Navigation::CACHE_KEY))->toBeTrue()
        ->and(strpos($firstResponse, 'Alpha'))->toBeLessThan(strpos($firstResponse, 'Beta'));

    // Reorder pages in admin: swap nav_order
    $page1->update(['nav_order' => 5]);

    // Cache should be flushed
    expect(Cache::has(Navigation::CACHE_KEY))->toBeFalse();

    $secondResponse = $this->get('/')->assertOk()->getContent();
    expect(strpos($secondResponse, 'Beta'))->toBeLessThan(strpos($secondResponse, 'Alpha'));
});

it('includes extra navigation links from site settings and respects open in new tab', function () {
    Navigation::flushCache();

    SiteSetting::singleton()->update([
        'extra_nav_links' => [
            [
                'label' => 'Client Portal',
                'url' => 'https://portal.example.com',
                'open_in_new_tab' => true,
            ],
        ],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Client Portal')
        ->assertSee('https://portal.example.com')
        ->assertSee('target="_blank"', false);
});

it('renders footer details and toggles powered by line', function () {
    $settings = SiteSetting::singleton();
    $settings->update([
        'footer_text' => 'Custom Copyright 2026',
        'show_powered_by' => true,
        'phone' => '+1 (555) 999-8888',
        'email' => 'contact@example.org',
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Custom Copyright 2026')
        ->assertSee('+1 (555) 999-8888')
        ->assertSee('contact@example.org')
        ->assertSee('Powered by');

    // Turn off powered by
    $settings->update(['show_powered_by' => false]);

    $this->get('/')
        ->assertOk()
        ->assertDontSee('Powered by');
});

it('renders maintenance 503 view without crashing', function () {
    $html = view('errors.503')->render();

    expect($html)->toBeString()
        ->and($html)->toContain('Under Scheduled Maintenance');
});

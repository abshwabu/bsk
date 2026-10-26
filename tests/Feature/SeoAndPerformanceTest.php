<?php

use App\Filament\Widgets\MissingSeoWidget;
use App\Models\Page;
use App\Models\Product;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('HasSeo trait provides sensible title, description, and image fallbacks', function () {
    $setting = SiteSetting::singleton();
    $setting->update([
        'site_name' => 'Acme Agency',
        'tagline' => 'Digital excellence for modern brands.',
        'logo' => 'branding/logo.webp',
    ]);

    // Page with explicit SEO fields
    $customPage = Page::create([
        'title' => 'About Us',
        'slug' => 'about',
        'seo_title' => 'Learn About Acme Agency - Who We Are',
        'seo_description' => 'We are a boutique studio crafting elite digital experiences.',
        'og_image' => 'seo/custom-about.webp',
        'is_published' => true,
    ]);

    expect($customPage->seoTitle())->toBe('Learn About Acme Agency - Who We Are')
        ->and($customPage->seoDescription())->toBe('We are a boutique studio crafting elite digital experiences.')
        ->and($customPage->seoImage())->toContain('seo/custom-about.webp')
        ->and($customPage->isMissingSeo())->toBeFalse()
        ->and($customPage->missingSeoFields())->toBeEmpty();

    // Page with missing SEO fields falling back to model title, block text, and logo
    $fallbackPage = Page::create([
        'title' => 'Services',
        'slug' => 'services',
        'blocks' => [
            [
                'type' => 'rich_text',
                'data' => [
                    'body' => '<p>We provide full-stack web development, API integration, and cloud architecture solutions for enterprises worldwide.</p>',
                ],
            ],
        ],
        'is_published' => true,
    ]);

    expect($fallbackPage->seoTitle())->toBe('Services - Acme Agency')
        ->and($fallbackPage->seoDescription())->toContain('We provide full-stack web development')
        ->and($fallbackPage->seoImage())->toContain('branding/logo.webp')
        ->and($fallbackPage->isMissingSeo())->toBeTrue()
        ->and($fallbackPage->missingSeoFields())->toContain('Description')
        ->and($fallbackPage->missingSeoFields())->toContain('Social Image');
});

test('it renders complete SEO meta tags, canonical URL, Open Graph, and Twitter cards', function () {
    $setting = SiteSetting::singleton();
    $setting->update([
        'site_name' => 'Acme Corp',
        'tagline' => 'Quality products and services.',
    ]);

    $page = Page::create([
        'title' => 'Contact Us',
        'slug' => 'contact',
        'seo_title' => 'Get in Touch with Acme',
        'seo_description' => 'Contact our friendly team today for consultations.',
        'og_image' => 'seo/contact-og.webp',
        'is_published' => true,
    ]);

    $response = $this->get('/contact');

    $response->assertOk()
        ->assertSee('<title>Get in Touch with Acme</title>', false)
        ->assertSee('<meta name="description" content="Contact our friendly team today for consultations.">', false)
        ->assertSee('<link rel="canonical" href="'.url('/contact').'">', false)
        ->assertSee('<meta name="robots" content="index, follow">', false)
        ->assertSee('<meta property="og:site_name" content="Acme Corp">', false)
        ->assertSee('<meta property="og:title" content="Get in Touch with Acme">', false)
        ->assertSee('<meta property="og:description" content="Contact our friendly team today for consultations.">', false)
        ->assertSee('<meta property="og:url" content="'.url('/contact').'">', false)
        ->assertSee('<meta property="og:image" content="'.url('/storage/seo/contact-og.webp').'">', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
        ->assertSee('<meta name="twitter:title" content="Get in Touch with Acme">', false)
        ->assertSee('<meta name="twitter:image" content="'.url('/storage/seo/contact-og.webp').'">', false);
});

test('it enforces noindex when indexing is discouraged or page is previewed as unpublished', function () {
    $setting = SiteSetting::singleton();

    // 1. Discourage indexing toggle in Site Settings
    $setting->update(['discourage_indexing' => true]);

    $publishedPage = Page::create([
        'title' => 'Staging Home',
        'slug' => 'home',
        'is_home' => true,
        'is_published' => true,
    ]);

    $this->get('/')->assertOk()->assertSee('<meta name="robots" content="noindex, nofollow">', false);

    // 2. Unpublished page previewed by admin
    $setting->update(['discourage_indexing' => false]);
    $admin = User::factory()->create(['email' => config('bina.admin_email')]);

    $draftPage = Page::create([
        'title' => 'Secret Draft',
        'slug' => 'secret-draft',
        'is_published' => false,
    ]);

    $this->actingAs($admin)
        ->get('/secret-draft')
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
});

test('it outputs JSON-LD for Organization, BreadcrumbList, and Product schema', function () {
    $setting = SiteSetting::singleton();
    $setting->update([
        'site_name' => 'Bina Global',
        'address' => '456 Business Blvd, Suite 200, City ST',
        'phone' => '+15551234567',
        'email' => 'contact@binaglobal.com',
        'social_links' => [
            ['platform' => 'twitter', 'url' => 'https://x.com/binaglobal'],
        ],
        'enabled_modules' => ['catalog' => true, 'portfolio' => true],
    ]);

    // Organization / LocalBusiness JSON-LD on root
    $this->get('/')
        ->assertOk()
        ->assertSee('"@type": "LocalBusiness"', false)
        ->assertSee('"name": "Bina Global"', false)
        ->assertSee('"telephone": "+15551234567"', false)
        ->assertSee('"email": "contact@binaglobal.com"', false)
        ->assertSee('"streetAddress": "456 Business Blvd, Suite 200, City ST"', false)
        ->assertSee('"sameAs": [', false);

    // Product and Breadcrumb JSON-LD on product detail page
    $product = Product::create([
        'name' => 'Custom Leather Bag',
        'slug' => 'custom-leather-bag',
        'description' => 'Handcrafted full-grain leather messenger bag.',
        'price' => 180.00,
        'currency' => 'USD',
        'stock_status' => Product::STOCK_IN_STOCK,
        'is_published' => true,
    ]);

    $this->get('/shop/custom-leather-bag')
        ->assertOk()
        ->assertSee('"@type": "BreadcrumbList"', false)
        ->assertSee('"name": "Custom Leather Bag"', false)
        ->assertSee('"@type": "Product"', false)
        ->assertSee('"price": "180.00"', false)
        ->assertSee('"priceCurrency": "USD"', false)
        ->assertSee('"availability": "https://schema.org/InStock"', false);
});

test('it generates dynamic sitemap.xml cached and flushes on changes', function () {
    $setting = SiteSetting::singleton();
    $setting->update([
        'enabled_modules' => ['portfolio' => true, 'catalog' => true],
    ]);

    $home = Page::create(['title' => 'Home', 'slug' => 'home', 'is_home' => true, 'is_published' => true]);
    $about = Page::create(['title' => 'About', 'slug' => 'about-us', 'is_published' => true]);
    $draft = Page::create(['title' => 'Draft', 'slug' => 'hidden-draft', 'is_published' => false]);

    $project = Project::create([
        'title' => 'FinTech Web App',
        'slug' => 'fintech-web-app',
        'is_published' => true,
    ]);

    $product = Product::create([
        'name' => 'Mechanical Keyboard',
        'slug' => 'mechanical-keyboard',
        'price' => 99.00,
        'is_published' => true,
    ]);

    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee('<loc>'.url('/').'</loc>', false)
        ->assertSee('<loc>'.url('/about-us').'</loc>', false)
        ->assertDontSee('<loc>'.url('/hidden-draft').'</loc>', false)
        ->assertSee('<loc>'.route('projects.index').'</loc>', false)
        ->assertSee('<loc>'.route('projects.show', 'fintech-web-app').'</loc>', false)
        ->assertSee('<loc>'.route('shop.index').'</loc>', false)
        ->assertSee('<loc>'.route('shop.show', 'mechanical-keyboard').'</loc>', false);

    // Verify it is cached
    expect(Cache::has('sitemap.xml'))->toBeTrue();

    // Updating a page flushes the cache
    $about->update(['title' => 'About Our Mission']);
    expect(Cache::has('sitemap.xml'))->toBeFalse();
});

test('it serves dynamic robots.txt referencing the sitemap and respects discourage indexing', function () {
    $setting = SiteSetting::singleton();

    // Standard production robots.txt
    $setting->update(['discourage_indexing' => false]);

    $response = $this->get('/robots.txt');

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('User-agent: *')
        ->assertSee('Disallow: /admin')
        ->assertSee('Disallow: /cart')
        ->assertSee('Disallow: /checkout')
        ->assertSee('Allow: /')
        ->assertSee('Sitemap: '.url('/sitemap.xml'));

    // Staging / discouraged indexing
    $setting->update(['discourage_indexing' => true]);

    $stagingResponse = $this->get('/robots.txt');

    $stagingResponse->assertOk()
        ->assertSee("User-agent: *\nDisallow: /", false)
        ->assertDontSee('Allow: /');
});

test('it runs bina:optimize artisan command to cache config, routes, and views', function () {
    $exitCode = Artisan::call('bina:optimize');
    expect($exitCode)->toBe(0);

    // Clean up caches so next tests have a clean state
    Artisan::call('optimize:clear');
});

test('it sets immutable cache headers for build assets', function () {
    $response = $this->get('/build/assets/test.js');

    expect($response->headers->get('Cache-Control'))->toContain('public')
        ->and($response->headers->get('Cache-Control'))->toContain('max-age=31536000')
        ->and($response->headers->get('Cache-Control'))->toContain('immutable');
});

test('MissingSeoWidget displays pages missing description or image', function () {
    $admin = User::factory()->create(['email' => config('bina.admin_email')]);

    $incompletePage = Page::create([
        'title' => 'Incomplete Portfolio Hub',
        'slug' => 'portfolio-hub',
        'seo_description' => null,
        'og_image' => null,
        'is_published' => true,
    ]);

    $completePage = Page::create([
        'title' => 'Complete Landing Page',
        'slug' => 'complete-landing',
        'seo_description' => 'Complete description for Google.',
        'og_image' => 'seo/complete.webp',
        'is_published' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(MissingSeoWidget::class)
        ->assertCanSeeTableRecords([$incompletePage])
        ->assertCanNotSeeTableRecords([$completePage])
        ->assertSee('Incomplete Portfolio Hub');
});

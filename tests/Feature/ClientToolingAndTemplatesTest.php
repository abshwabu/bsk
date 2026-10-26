<?php

use App\Models\Page;
use App\Models\Product;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    // Ensure clean state before each test
    SiteSetting::singleton()->update([
        'site_name' => 'Bina Test Starter',
        'enabled_modules' => ['portfolio' => false, 'catalog' => false],
    ]);
});

it('creates a new client via bina:new-client with custom branding and admin user', function () {
    $this->artisan('bina:new-client', [
        '--template' => 'business',
        '--name' => 'Acme Corporation',
        '--tagline' => 'Global Logistics & Supply Chain',
        '--primary' => '#1e293b',
        '--accent' => '#0ea5e9',
        '--phone' => '+15551234567',
        '--email' => 'contact@acme.example',
        '--address' => '100 Acme Way, Suite 400',
        '--whatsapp' => '+15559876543',
        '--admin-email' => 'admin@acme.example',
        '--admin-password' => 'AcmeP@ssw0rd!2026',
        '--force' => true,
    ])
        ->expectsOutputToContain('New client site provisioned successfully')
        ->assertExitCode(0);

    // Verify SiteSetting
    $setting = SiteSetting::singleton();
    expect($setting->site_name)->toBe('Acme Corporation')
        ->and($setting->tagline)->toBe('Global Logistics & Supply Chain')
        ->and($setting->primary_color)->toBe('#1e293b')
        ->and($setting->accent_color)->toBe('#0ea5e9')
        ->and($setting->phone)->toBe('+15551234567')
        ->and($setting->email)->toBe('contact@acme.example');

    // Verify Admin User
    $admin = User::query()->where('email', 'admin@acme.example')->first();
    expect($admin)->not->toBeNull()
        ->and(Hash::check('AcmeP@ssw0rd!2026', $admin->password))->toBeTrue();
});

it('seeds and renders all public routes for business template', function () {
    $this->artisan('bina:new-client', [
        '--template' => 'business',
        '--name' => 'Vanguard Strategy',
        '--force' => true,
    ])->assertExitCode(0);

    $routes = [
        '/' => 'Strategic Excellence for Modern Enterprises',
        '/about' => 'Decades of Purposeful Leadership',
        '/services' => 'End-to-End Enterprise Solutions',
        '/contact' => 'Connect With Our Leadership',
    ];

    foreach ($routes as $uri => $expectedContent) {
        $response = $this->get($uri);
        $response->assertStatus(200);
        $response->assertSee('<!DOCTYPE html>', false);
        $response->assertSee('Vanguard Strategy');
        $response->assertSee($expectedContent);
    }
});

it('seeds and renders all public routes for portfolio template', function () {
    $this->artisan('bina:new-client', [
        '--template' => 'portfolio',
        '--name' => 'Studio Forma',
        '--force' => true,
    ])->assertExitCode(0);

    $routes = [
        '/' => 'Crafting Distinctive Identities & Modern Experiences',
        '/about' => 'About Studio Forma',
        '/contact' => 'Start a Conversation',
        '/projects' => 'Our Projects',
    ];

    foreach ($routes as $uri => $expectedContent) {
        $response = $this->get($uri);
        $response->assertStatus(200);
        $response->assertSee('<!DOCTYPE html>', false);
        $response->assertSee('Studio Forma');
        $response->assertSee($expectedContent);
    }

    // Detail route
    $project = Project::query()->published()->first();
    expect($project)->not->toBeNull();

    $detailResponse = $this->get("/projects/{$project->slug}");
    $detailResponse->assertStatus(200);
    $detailResponse->assertSee($project->title);
});

it('seeds and renders all public routes for charity template', function () {
    $this->artisan('bina:new-client', [
        '--template' => 'charity',
        '--name' => 'HopeForward Global',
        '--force' => true,
    ])->assertExitCode(0);

    $routes = [
        '/' => 'Together, We Can Change Lives Forever',
        '/about' => 'About HopeForward Global',
        '/donate' => 'Support Our Mission',
        '/contact' => 'Connect With Our Team',
    ];

    foreach ($routes as $uri => $expectedContent) {
        $response = $this->get($uri);
        $response->assertStatus(200);
        $response->assertSee('<!DOCTYPE html>', false);
        $response->assertSee('HopeForward Global');
        $response->assertSee($expectedContent);
    }
});

it('seeds and renders all public routes for store template', function () {
    $this->artisan('bina:new-client', [
        '--template' => 'store',
        '--name' => 'Artisan & Oak',
        '--force' => true,
    ])->assertExitCode(0);

    $routes = [
        '/' => 'Simple, Beautiful Objects Made to Last',
        '/about' => 'Our Workshop & Philosophy',
        '/contact' => 'Customer Care & Wholesale',
        '/shop' => 'Catalog',
        '/cart' => 'Shopping Cart',
    ];

    foreach ($routes as $uri => $expectedContent) {
        $response = $this->get($uri);
        $response->assertStatus(200);
        $response->assertSee('<!DOCTYPE html>', false);
        $response->assertSee('Artisan & Oak');
        $response->assertSee($expectedContent);
    }

    // Product detail route
    $product = Product::query()->published()->first();
    expect($product)->not->toBeNull();

    $productResponse = $this->get("/shop/{$product->slug}");
    $productResponse->assertStatus(200);
    $productResponse->assertSee($product->name);

    // Checkout route (with items in cart)
    $checkoutResponse = $this->withSession([
        'cart' => [
            $product->id => [
                'name' => $product->name,
                'price' => $product->price,
                'qty' => 1,
            ],
        ],
    ])->get('/checkout');

    $checkoutResponse->assertStatus(200);
    $checkoutResponse->assertSee('Checkout');
    $checkoutResponse->assertSee($product->name);
});

it('resets demo content safely via bina:reset-demo', function () {
    // Populate some demo content
    $this->artisan('bina:new-client', ['--template' => 'portfolio', '--force' => true]);
    expect(Project::count())->toBeGreaterThan(0);

    // Reset to charity template
    $this->artisan('bina:reset-demo', [
        '--template' => 'charity',
        '--force' => true,
    ])
        ->expectsOutputToContain('Demo content successfully reset')
        ->assertExitCode(0);

    // Projects should be wiped
    expect(Project::count())->toBe(0);

    // Charity pages should exist
    expect(Page::query()->where('slug', 'donate')->exists())->toBeTrue();
});

it('strictly refuses to run bina:reset-demo in production', function () {
    Config::set('app.env', 'production');

    $this->artisan('bina:reset-demo', [
        '--template' => 'business',
        '--force' => true,
    ])
        ->expectsOutputToContain('Cannot reset demo content in production')
        ->assertExitCode(1);
});

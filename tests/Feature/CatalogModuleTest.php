<?php

use App\Blocks\BlockRegistry;
use App\Blocks\ProductGridBlock;
use App\Filament\Resources\OrderRequestResource;
use App\Filament\Resources\ProductCategoryResource;
use App\Filament\Resources\ProductResource;
use App\Models\OrderRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function () {
    RateLimiter::clear('shop.checkout.submit:127.0.0.1');
    Cart::clear();
});

function enableCatalogModule(): void
{
    $setting = SiteSetting::singleton();
    $setting->update([
        'enabled_modules' => [
            'portfolio' => false,
            'catalog' => true,
        ],
        'telegram_bot_token' => 'mock_bot_token_123',
        'telegram_chat_id' => '-100987654321',
        'currency' => 'USD',
    ]);
}

test('it returns 404 for public shop routes and hides admin resources when catalog module is disabled', function () {
    $setting = SiteSetting::singleton();
    $setting->update([
        'enabled_modules' => [
            'portfolio' => false,
            'catalog' => false,
        ],
    ]);

    $this->get('/shop')->assertStatus(404);
    $this->get('/shop/test-product')->assertStatus(404);
    $this->get('/cart')->assertStatus(404);
    $this->get('/checkout')->assertStatus(404);
    $this->get('/order-confirmed/BSK-20261026-ABCD')->assertStatus(404);

    expect(ProductCategoryResource::shouldRegisterNavigation())->toBeFalse()
        ->and(ProductCategoryResource::canViewAny())->toBeFalse()
        ->and(ProductResource::shouldRegisterNavigation())->toBeFalse()
        ->and(ProductResource::canViewAny())->toBeFalse()
        ->and(OrderRequestResource::shouldRegisterNavigation())->toBeFalse()
        ->and(OrderRequestResource::canViewAny())->toBeFalse()
        ->and(OrderRequestResource::getNavigationBadge())->toBeNull();
});

test('it enables public routes and admin navigation when catalog module is enabled', function () {
    enableCatalogModule();

    $this->get('/shop')->assertOk()->assertSee('Product Catalog');
    $this->get('/cart')->assertOk()->assertSee('Your Shopping Cart');

    expect(ProductCategoryResource::shouldRegisterNavigation())->toBeTrue()
        ->and(ProductCategoryResource::canViewAny())->toBeTrue()
        ->and(ProductResource::shouldRegisterNavigation())->toBeTrue()
        ->and(ProductResource::canViewAny())->toBeTrue()
        ->and(OrderRequestResource::shouldRegisterNavigation())->toBeTrue()
        ->and(OrderRequestResource::canViewAny())->toBeTrue();
});

test('it filters catalog products by category and search keyword', function () {
    enableCatalogModule();

    $catElectronics = ProductCategory::create(['name' => 'Electronics', 'slug' => 'electronics']);
    $catClothing = ProductCategory::create(['name' => 'Clothing', 'slug' => 'clothing']);

    Product::create([
        'name' => 'Wireless Keyboard',
        'slug' => 'wireless-keyboard',
        'price' => 49.99,
        'product_category_id' => $catElectronics->id,
        'is_published' => true,
    ]);

    Product::create([
        'name' => 'Gaming Mouse',
        'slug' => 'gaming-mouse',
        'price' => 29.99,
        'product_category_id' => $catElectronics->id,
        'is_published' => true,
    ]);

    Product::create([
        'name' => 'Cotton T-Shirt',
        'slug' => 'cotton-t-shirt',
        'price' => 19.99,
        'product_category_id' => $catClothing->id,
        'is_published' => true,
    ]);

    // All products
    $this->get('/shop')
        ->assertOk()
        ->assertSee('Wireless Keyboard')
        ->assertSee('Gaming Mouse')
        ->assertSee('Cotton T-Shirt');

    // Filter by category
    $this->get('/shop?category=electronics')
        ->assertOk()
        ->assertSee('Wireless Keyboard')
        ->assertSee('Gaming Mouse')
        ->assertDontSee('Cotton T-Shirt');

    // Search by name
    $this->get('/shop?q=Mouse')
        ->assertOk()
        ->assertSee('Gaming Mouse')
        ->assertDontSee('Wireless Keyboard')
        ->assertDontSee('Cotton T-Shirt');
});

test('it displays product detail and restricts unpublished product to admin preview', function () {
    enableCatalogModule();

    $product = Product::create([
        'name' => 'Ergonomic Desk',
        'slug' => 'ergonomic-desk',
        'description' => '<p>High quality motorized standing desk.</p>',
        'price' => 399.00,
        'is_published' => false,
    ]);

    // Guest cannot view unpublished product
    $this->get('/shop/ergonomic-desk')->assertStatus(404);

    // Admin can preview
    $admin = User::factory()->create(['email' => config('bina.admin_email')]);
    $this->actingAs($admin)
        ->get('/shop/ergonomic-desk')
        ->assertOk()
        ->assertSee('Ergonomic Desk')
        ->assertSee('399.00');

    // Published product viewable by guest
    $product->update(['is_published' => true]);
    $this->get('/shop/ergonomic-desk')
        ->assertOk()
        ->assertSee('Ergonomic Desk')
        ->assertSee('High quality motorized standing desk.');
});

test('it handles session cart operations', function () {
    enableCatalogModule();

    $productA = Product::create([
        'name' => 'Notebook',
        'slug' => 'notebook',
        'price' => 12.50,
        'is_published' => true,
    ]);

    $productB = Product::create([
        'name' => 'Fountain Pen',
        'slug' => 'fountain-pen',
        'price' => 25.00,
        'is_published' => true,
    ]);

    // Add to cart via POST
    $this->post('/cart/add', [
        'product_id' => $productA->id,
        'qty' => 2,
    ])->assertRedirect();

    expect(Cart::count())->toBe(2)
        ->and(Cart::total())->toBe(25.00);

    // Add another item via JSON
    $this->postJson('/cart/add', [
        'product_id' => $productB->id,
        'qty' => 1,
    ])->assertStatus(200)
        ->assertJson([
            'success' => true,
            'count' => 3,
            'total' => 50.00,
        ]);

    // Update quantity
    $this->postJson('/cart/update', [
        'product_id' => $productA->id,
        'qty' => 4,
    ])->assertStatus(200);

    expect(Cart::count())->toBe(5) // 4 Notebooks + 1 Pen
        ->and(Cart::total())->toBe(75.00); // (4 * 12.50) + (1 * 25.00)

    // Remove product
    $this->post('/cart/remove/'.$productB->id)->assertRedirect();
    expect(Cart::count())->toBe(4);

    // Clear cart
    $this->post('/cart/clear')->assertRedirect();
    expect(Cart::isEmpty())->toBeTrue();
});

test('it executes end-to-end checkout, stores order, snapshots prices, and calls telegram once with correct totals', function () {
    enableCatalogModule();

    Http::fake([
        'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
    ]);

    $laptop = Product::create([
        'name' => 'Developer Laptop',
        'slug' => 'dev-laptop',
        'price' => 1500.00,
        'is_published' => true,
    ]);

    $stand = Product::create([
        'name' => 'Laptop Stand',
        'slug' => 'laptop-stand',
        'price' => 50.00,
        'is_published' => true,
    ]);

    // Add items to cart
    Cart::add($laptop, 1);
    Cart::add($stand, 2);

    expect(Cart::count())->toBe(3)
        ->and(Cart::total())->toBe(1600.00);

    // Submit Checkout
    $response = $this->post('/checkout', [
        'customer_name' => 'Sarah Connor',
        'phone' => '+15559871122',
        'email' => 'sarah@example.com',
        'address' => '742 Evergreen Terrace, Springfield',
        'notes' => 'Please call 15 minutes before arrival.',
        'hp_website' => '',
        '_token_time' => Crypt::encrypt(time() - 6),
    ]);

    // Assert order was created in DB
    $this->assertDatabaseCount('order_requests', 1);

    $order = OrderRequest::first();
    expect($order->customer_name)->toBe('Sarah Connor')
        ->and($order->phone)->toBe('+15559871122')
        ->and((float) $order->total)->toBe(1600.00)
        ->and($order->status)->toBe(OrderRequest::STATUS_NEW)
        ->and($order->reference)->toMatch('/^BSK-\d{8}-[A-Z0-9]{4}$/')
        ->and($order->items)->toHaveCount(2);

    // Assert Cart is emptied
    expect(Cart::isEmpty())->toBeTrue();

    // Assert redirected to confirmation page
    $response->assertRedirect(route('shop.order-confirmed', ['reference' => $order->reference]));

    // Assert confirmation page displays the reference and totals
    $this->get(route('shop.order-confirmed', ['reference' => $order->reference]))
        ->assertOk()
        ->assertSee($order->reference)
        ->assertSee('Sarah Connor')
        ->assertSee('1,600.00');

    // Assert Telegram HTTP was called exactly once with accurate details
    Http::assertSentCount(1);
    Http::assertSent(function ($request) use ($order) {
        $body = $request->data();

        return str_contains($request->url(), 'mock_bot_token_123')
            && $body['chat_id'] === '-100987654321'
            && str_contains($body['text'], $order->reference)
            && str_contains($body['text'], 'Sarah Connor')
            && str_contains($body['text'], '+15559871122')
            && str_contains($body['text'], 'Developer Laptop')
            && str_contains($body['text'], 'Laptop Stand')
            && str_contains($body['text'], '1,600.00');
    });
});

test('it blocks checkout spam via honeypot and time-based check', function () {
    enableCatalogModule();

    $product = Product::create([
        'name' => 'Book',
        'slug' => 'book',
        'price' => 20.00,
        'is_published' => true,
    ]);

    Cart::add($product, 1);

    // 1. Honeypot check
    $this->postJson('/checkout', [
        'customer_name' => 'Bot',
        'phone' => '+1111111111',
        'hp_website' => 'http://spam.com',
        '_token_time' => Crypt::encrypt(time() - 5),
    ])->assertStatus(422)->assertJsonValidationErrors(['hp_website']);

    // 2. Instantaneous submission (< 2s)
    $this->postJson('/checkout', [
        'customer_name' => 'Bot',
        'phone' => '+1111111111',
        'hp_website' => '',
        '_token_time' => Crypt::encrypt(time()),
    ])->assertStatus(422)->assertJsonValidationErrors(['_token_time']);

    $this->assertDatabaseCount('order_requests', 0);
});

test('it throttles checkout attempts to 5 per hour per IP', function () {
    enableCatalogModule();

    Http::fake([
        'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
    ]);

    $product = Product::create([
        'name' => 'Gadget',
        'slug' => 'gadget',
        'price' => 10.00,
        'is_published' => true,
    ]);

    for ($i = 1; $i <= 5; $i++) {
        Cart::add($product, 1);
        $this->postJson('/checkout', [
            'customer_name' => "Buyer {$i}",
            'phone' => '+1555000000',
            'hp_website' => '',
            '_token_time' => Crypt::encrypt(time() - 5),
        ])->assertStatus(201);
    }

    $this->assertDatabaseCount('order_requests', 5);

    // 6th checkout attempt within the same hour is throttled (429)
    Cart::add($product, 1);
    $this->postJson('/checkout', [
        'customer_name' => 'Buyer 6',
        'phone' => '+1555000000',
        'hp_website' => '',
        '_token_time' => Crypt::encrypt(time() - 5),
    ])->assertStatus(429);

    $this->assertDatabaseCount('order_requests', 5);
});

test('it shows unread badge in OrderRequestResource and supports status transitions', function () {
    enableCatalogModule();

    expect(OrderRequestResource::getNavigationBadge())->toBeNull();

    $order = OrderRequest::create([
        'reference' => 'BSK-20261026-TEST',
        'customer_name' => 'Michael Scott',
        'phone' => '+15554443333',
        'items' => [['name' => 'Paper Ream', 'price' => 30.00, 'qty' => 5, 'subtotal' => 150.00]],
        'total' => 150.00,
        'currency' => 'USD',
        'status' => OrderRequest::STATUS_NEW,
    ]);

    expect(OrderRequestResource::getNavigationBadge())->toBe('1')
        ->and(OrderRequestResource::getNavigationBadgeColor())->toBe('warning');

    $order->confirm();
    expect($order->status)->toBe(OrderRequest::STATUS_CONFIRMED)
        ->and(OrderRequestResource::getNavigationBadge())->toBeNull();

    $order->deliver();
    expect($order->status)->toBe(OrderRequest::STATUS_DELIVERED);

    $order->cancel();
    expect($order->status)->toBe(OrderRequest::STATUS_CANCELLED);
});

test('it registers and renders ProductGridBlock', function () {
    enableCatalogModule();

    $registry = BlockRegistry::getBlockClasses();
    expect($registry)->toHaveKey('product_grid')
        ->and($registry['product_grid'])->toBe(ProductGridBlock::class);

    $product = Product::create([
        'name' => 'Smart Watch',
        'slug' => 'smart-watch',
        'price' => 199.99,
        'is_featured' => true,
        'is_published' => true,
    ]);

    $view = view('blocks.product-grid', [
        'data' => [
            'heading' => 'Featured Collection',
            'count' => 4,
        ],
    ])->render();

    expect($view)->toContain('Featured Collection')
        ->toContain('Smart Watch')
        ->toContain('199.99');
});

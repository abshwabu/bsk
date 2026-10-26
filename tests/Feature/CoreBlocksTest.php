<?php

use App\Blocks\BlockRegistry;
use App\Models\Page;
use Database\Seeders\PageSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Support\Facades\Blade;

it('discovers all nine core blocks in BlockRegistry', function () {
    BlockRegistry::flush();

    $blocks = BlockRegistry::getBlockClasses();

    expect($blocks)->toHaveKeys([
        'hero',
        'rich_text',
        'features_grid',
        'image_gallery',
        'testimonials',
        'faq',
        'stats',
        'cta_banner',
        'contact',
    ]);
});

it('renders every registered block with empty or default data without error', function () {
    $classes = BlockRegistry::getBlockClasses();

    foreach ($classes as $name => $class) {
        $view = $class::view();

        // 1. Test direct view rendering with empty data
        $viewHtml = view($view, ['data' => []])->render();
        expect($viewHtml)->toBeString();

        // 2. Test rendering through <x-blocks> wrapper
        $dummyPage = new Page([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'blocks' => [
                ['type' => $name, 'data' => []],
            ],
        ]);

        $renderedWrapper = Blade::render('<x-blocks :page="$page" />', ['page' => $dummyPage]);
        expect($renderedWrapper)->toBeString();
    }
});

it('seeds a Home page demonstrating every core block and renders successfully on root URL', function () {
    $this->seed(SiteSettingSeeder::class);
    $this->seed(PageSeeder::class);

    $homePage = Page::query()->where('is_home', true)->first();

    expect($homePage)->not->toBeNull()
        ->and($homePage->slug)->toBe('home')
        ->and($homePage->is_published)->toBeTrue()
        ->and($homePage->blocks)->toHaveCount(9);

    $response = $this->get('/')
        ->assertOk();

    // 1. Hero
    $response->assertSee('Elevate Your Digital Presence with Bina Site Kit');
    // 2. Features Grid
    $response->assertSee('Modular Block System');
    // 3. Rich Text
    $response->assertSee('Designed with Intent, Crafted for Speed');
    // 4. Stats
    $response->assertSee('Websites Delivered');
    // 5. Image Gallery
    $response->assertSee('Showcase Gallery');
    // 6. Testimonials
    $response->assertSee('Elena Rostova');
    // 7. FAQ
    $response->assertSee('How does the block builder work?');
    // 8. CTA Banner
    $response->assertSee('Ready to Launch Your New Website?');
    // 9. Contact
    $response->assertSee('Connect With Our Team');
});

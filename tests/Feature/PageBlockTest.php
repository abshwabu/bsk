<?php

use App\Blocks\BlockRegistry;
use App\Blocks\RichTextBlock;
use App\Filament\Resources\PageResource\Pages\CreatePage;
use App\Models\Page;
use App\Models\User;
use Filament\Forms\Components\Builder\Block;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;

it('auto-discovers page blocks implementing the PageBlock interface', function () {
    BlockRegistry::flush();

    $classes = BlockRegistry::getBlockClasses();

    expect($classes)->toHaveKey('rich_text')
        ->and($classes['rich_text'])->toBe(RichTextBlock::class)
        ->and(BlockRegistry::find('rich_text'))->toBe(RichTextBlock::class)
        ->and(BlockRegistry::getView('rich_text'))->toBe('blocks.rich-text');

    $allSchemas = BlockRegistry::all();
    expect($allSchemas)->toHaveCount(11)
        ->and($allSchemas)->each->toBeInstanceOf(Block::class)
        ->and(collect($allSchemas)->map(fn (Block $b) => $b->getName()))->toContain('rich_text');
});

it('enforces that only one page may have is_home = true', function () {
    $firstPage = Page::query()->create([
        'title' => 'First Home',
        'slug' => 'first-home',
        'is_home' => true,
        'is_published' => true,
    ]);

    expect($firstPage->fresh()->is_home)->toBeTrue();

    // Create a second page with is_home = true
    $secondPage = Page::query()->create([
        'title' => 'Second Home',
        'slug' => 'second-home',
        'is_home' => true,
        'is_published' => true,
    ]);

    // First page should now be unset
    expect($firstPage->fresh()->is_home)->toBeFalse()
        ->and($secondPage->fresh()->is_home)->toBeTrue()
        ->and(Page::query()->where('is_home', true)->count())->toBe(1);

    // Updating first page back to is_home = true unsets the second
    $firstPage->refresh()->update(['is_home' => true]);

    expect($firstPage->fresh()->is_home)->toBeTrue()
        ->and($secondPage->fresh()->is_home)->toBeFalse()
        ->and(Page::query()->where('is_home', true)->count())->toBe(1);
});

it('renders blocks through the x-blocks component and skips unknown blocks silently', function () {
    $page = new Page([
        'title' => 'Component Test',
        'slug' => 'component-test',
        'blocks' => [
            [
                'type' => 'rich_text',
                'data' => [
                    'content' => '<p>Rendered paragraph content</p>',
                ],
            ],
            [
                'type' => 'unknown_ghost_block',
                'data' => [
                    'foo' => 'bar',
                ],
            ],
            [
                'type' => 'rich_text',
                'data' => [
                    'content' => '<h2>Another valid block</h2>',
                ],
            ],
        ],
    ]);

    $rendered = Blade::render('<x-blocks :page="$page" />', ['page' => $page]);

    expect($rendered)->toContain('Rendered paragraph content')
        ->and($rendered)->toContain('Another valid block')
        ->and($rendered)->not->toContain('unknown_ghost_block');
});

it('allows an authenticated admin to view PageResource index and create page with rich text blocks', function () {
    $user = User::factory()->create(['email' => config('bina.admin_email')]);

    $this->actingAs($user)
        ->get('/admin/pages')
        ->assertOk()
        ->assertSee('Pages');

    Livewire::actingAs($user)
        ->test(CreatePage::class)
        ->assertSuccessful()
        ->fillForm([
            'title' => 'About Our Agency',
            'slug' => 'about-our-agency',
            'is_published' => true,
            'is_home' => false,
            'blocks' => [
                [
                    'type' => 'rich_text',
                    'data' => [
                        'content' => '<p>We craft remarkable web experiences.</p>',
                    ],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $page = Page::query()->where('slug', 'about-our-agency')->first();

    expect($page)->not->toBeNull()
        ->and($page->is_published)->toBeTrue()
        ->and($page->blocks)->toHaveCount(1)
        ->and($page->blocks[0]['type'])->toBe('rich_text')
        ->and($page->blocks[0]['data']['content'])->toBe('<p>We craft remarkable web experiences.</p>');

    // Public visitor can view the published page and see the rendered block
    $this->get('/about-our-agency')
        ->assertOk()
        ->assertSee('We craft remarkable web experiences.', false);
});

it('renders home page blocks on the root URL when a published home page exists', function () {
    $homePage = Page::query()->create([
        'title' => 'Home Page',
        'slug' => 'home',
        'is_published' => true,
        'is_home' => true,
        'blocks' => [
            [
                'type' => 'rich_text',
                'data' => [
                    'content' => '<p>Welcome to our official website homepage.</p>',
                ],
            ],
        ],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Welcome to our official website homepage.', false);
});

it('renders blocks in their reordered sequence', function () {
    $page = Page::query()->create([
        'title' => 'Reorder Test',
        'slug' => 'reorder-test',
        'is_published' => true,
        'blocks' => [
            [
                'type' => 'rich_text',
                'data' => [
                    'content' => '<p>First Paragraph</p>',
                ],
            ],
            [
                'type' => 'rich_text',
                'data' => [
                    'content' => '<p>Second Paragraph</p>',
                ],
            ],
        ],
    ]);

    $response = $this->get('/reorder-test')->assertOk();
    $content = $response->getContent();
    expect(strpos($content, 'First Paragraph'))->toBeLessThan(strpos($content, 'Second Paragraph'));

    // Reorder the blocks: second paragraph first
    $page->update([
        'blocks' => [
            [
                'type' => 'rich_text',
                'data' => [
                    'content' => '<p>Second Paragraph</p>',
                ],
            ],
            [
                'type' => 'rich_text',
                'data' => [
                    'content' => '<p>First Paragraph</p>',
                ],
            ],
        ],
    ]);

    $reorderedResponse = $this->get('/reorder-test')->assertOk();
    $reorderedContent = $reorderedResponse->getContent();
    expect(strpos($reorderedContent, 'Second Paragraph'))->toBeLessThan(strpos($reorderedContent, 'First Paragraph'));
});

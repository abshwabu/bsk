<?php

use App\Filament\Resources\ProjectCategoryResource;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\ProjectResource\Pages\ListProjects;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
});

it('returns 404 for public portfolio routes and hides admin resources when portfolio module is disabled', function () {
    $settings = SiteSetting::singleton();
    $settings->update([
        'enabled_modules' => ['portfolio' => false, 'catalog' => false],
    ]);

    expect(site()->isModuleEnabled('portfolio'))->toBeFalse()
        ->and(ProjectResource::shouldRegisterNavigation())->toBeFalse()
        ->and(ProjectResource::canViewAny())->toBeFalse()
        ->and(ProjectCategoryResource::shouldRegisterNavigation())->toBeFalse()
        ->and(ProjectCategoryResource::canViewAny())->toBeFalse();

    $this->get('/projects')->assertNotFound();
    $this->get('/projects/some-slug')->assertNotFound();
});

it('enables public routes and admin navigation when portfolio module is enabled', function () {
    $settings = SiteSetting::singleton();
    $settings->update([
        'enabled_modules' => ['portfolio' => true, 'catalog' => false],
    ]);

    expect(site()->isModuleEnabled('portfolio'))->toBeTrue()
        ->and(ProjectResource::shouldRegisterNavigation())->toBeTrue()
        ->and(ProjectResource::canViewAny())->toBeTrue()
        ->and(ProjectCategoryResource::shouldRegisterNavigation())->toBeTrue()
        ->and(ProjectCategoryResource::canViewAny())->toBeTrue();

    $this->get('/projects')->assertOk()->assertSee('Our Projects');
});

it('filters projects by category query string without requiring javascript', function () {
    $settings = SiteSetting::singleton();
    $settings->update(['enabled_modules' => ['portfolio' => true]]);

    $branding = ProjectCategory::query()->create(['name' => 'Branding', 'slug' => 'branding', 'sort' => 1]);
    $development = ProjectCategory::query()->create(['name' => 'Development', 'slug' => 'development', 'sort' => 2]);

    $p1 = Project::query()->create([
        'title' => 'Nebula Identity Design',
        'slug' => 'nebula-identity',
        'project_category_id' => $branding->id,
        'is_published' => true,
    ]);

    $p2 = Project::query()->create([
        'title' => 'Quantum SaaS Platform',
        'slug' => 'quantum-saas',
        'project_category_id' => $development->id,
        'is_published' => true,
    ]);

    // All projects visible
    $this->get('/projects')
        ->assertOk()
        ->assertSee('Nebula Identity Design')
        ->assertSee('Quantum SaaS Platform');

    // Filter by branding category
    $this->get('/projects?category=branding')
        ->assertOk()
        ->assertSee('Nebula Identity Design')
        ->assertDontSee('Quantum SaaS Platform');

    // Filter by development category
    $this->get('/projects?category=development')
        ->assertOk()
        ->assertSee('Quantum SaaS Platform')
        ->assertDontSee('Nebula Identity Design');
});

it('renders project detail page with next and previous navigation links', function () {
    $settings = SiteSetting::singleton();
    $settings->update(['enabled_modules' => ['portfolio' => true]]);

    $category = ProjectCategory::query()->create(['name' => 'Web Design', 'slug' => 'web-design']);

    $proj1 = Project::query()->create([
        'title' => 'Alpha App',
        'slug' => 'alpha-app',
        'project_category_id' => $category->id,
        'client_name' => 'Alpha Corp',
        'summary' => 'Summary of Alpha project',
        'body' => '<p>Deep case study content for Alpha app.</p>',
        'is_published' => true,
        'sort' => 10,
    ]);

    $proj2 = Project::query()->create([
        'title' => 'Beta Portal',
        'slug' => 'beta-portal',
        'project_category_id' => $category->id,
        'client_name' => 'Beta Ltd',
        'summary' => 'Summary of Beta portal',
        'body' => '<p>Deep case study content for Beta portal.</p>',
        'is_published' => true,
        'sort' => 20,
    ]);

    // View Proj2: previous should be Proj1
    $this->get('/projects/beta-portal')
        ->assertOk()
        ->assertSee('Beta Portal')
        ->assertSee('Deep case study content for Beta portal.', false)
        ->assertSee('Beta Ltd')
        ->assertSee('Previous Project')
        ->assertSee('Alpha App');

    // View Proj1: next should be Proj2
    $this->get('/projects/alpha-app')
        ->assertOk()
        ->assertSee('Next Project')
        ->assertSee('Beta Portal');
});

it('returns 404 for unpublished project to guests and allows admin preview', function () {
    $settings = SiteSetting::singleton();
    $settings->update(['enabled_modules' => ['portfolio' => true]]);

    $unpublished = Project::query()->create([
        'title' => 'Secret Stealth Project',
        'slug' => 'secret-stealth',
        'body' => '<p>Confidential Client Deliverable</p>',
        'is_published' => false,
    ]);

    // Guest gets 404
    $this->get('/projects/secret-stealth')->assertNotFound();

    // Authenticated admin can preview
    $admin = User::factory()->create(['email' => config('bina.admin_email')]);
    $this->actingAs($admin)
        ->get('/projects/secret-stealth')
        ->assertOk()
        ->assertSee('Confidential Client Deliverable', false);
});

it('optimizes uploaded image to max 1600px and converts to webp', function () {
    $imageResource = imagecreatetruecolor(2400, 1200);
    $bg = imagecolorallocate($imageResource, 0, 100, 200);
    imagefill($imageResource, 0, 0, $bg);

    $tempFile = tempnam(sys_get_temp_dir(), 'test_upload').'.jpg';
    imagejpeg($imageResource, $tempFile);
    imagedestroy($imageResource);

    $uploaded = new UploadedFile($tempFile, 'huge-photo.jpg', 'image/jpeg', null, true);

    $storedPath = ImageOptimizer::optimizeAndStore($uploaded, 'projects');

    expect($storedPath)->not->toBeNull()
        ->and($storedPath)->toEndWith('.webp')
        ->and(Storage::disk('public')->exists($storedPath))->toBeTrue();

    // Check stored dimensions are scaled <= 1600
    $fullPath = Storage::disk('public')->path($storedPath);
    $loaded = imagecreatefromstring(file_get_contents($fullPath));
    expect(imagesx($loaded))->toBe(1600)
        ->and(imagesy($loaded))->toBe(800);
    imagedestroy($loaded);

    @unlink($tempFile);
});

it('renders FeaturedProjectsBlock with heading and view all link', function () {
    $settings = SiteSetting::singleton();
    $settings->update(['enabled_modules' => ['portfolio' => true]]);

    Project::query()->create([
        'title' => 'Highlighted Showcase Work',
        'slug' => 'highlighted-showcase',
        'is_featured' => true,
        'is_published' => true,
        'sort' => 1,
    ]);

    $rendered = Blade::render('<x-blocks :blocks="$blocks" />', [
        'blocks' => [
            [
                'type' => 'featured_projects',
                'data' => [
                    'heading' => 'Spotlight Deliverables',
                    'count' => 3,
                    'view_all_label' => 'See All Case Studies',
                    'view_all_url' => '/projects',
                ],
            ],
        ],
    ]);

    expect($rendered)->toContain('Spotlight Deliverables')
        ->and($rendered)->toContain('Highlighted Showcase Work')
        ->and($rendered)->toContain('See All Case Studies')
        ->and($rendered)->toContain('/projects');
});

it('supports bulk publishing in ProjectResource table', function () {
    $settings = SiteSetting::singleton();
    $settings->update(['enabled_modules' => ['portfolio' => true]]);

    $admin = User::factory()->create(['email' => config('bina.admin_email')]);

    $p1 = Project::query()->create(['title' => 'Draft 1', 'slug' => 'draft-1', 'is_published' => false]);
    $p2 = Project::query()->create(['title' => 'Draft 2', 'slug' => 'draft-2', 'is_published' => false]);

    Livewire::actingAs($admin)
        ->test(ListProjects::class)
        ->callTableBulkAction('publish', [$p1, $p2]);

    expect($p1->fresh()->is_published)->toBeTrue()
        ->and($p2->fresh()->is_published)->toBeTrue();
});

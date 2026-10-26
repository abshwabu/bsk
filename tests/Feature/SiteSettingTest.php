<?php

use App\Filament\Pages\SiteSettings;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

it('seeds default settings with neutral placeholder branding', function () {
    $this->seed(SiteSettingSeeder::class);

    $settings = SiteSetting::query()->first();

    expect($settings)->not->toBeNull()
        ->and($settings->site_name)->toBe(config('app.name', 'Bina Site Kit'))
        ->and($settings->primary_color)->toBe('#0f172a')
        ->and($settings->accent_color)->toBe('#3b82f6')
        ->and($settings->font_heading)->toBe('Inter')
        ->and($settings->font_body)->toBe('Inter')
        ->and($settings->enabled_modules)->toBeArray();
});

it('provides cached singleton via site() helper and retrieves field via site(key)', function () {
    $created = SiteSetting::query()->create([
        'site_name' => 'Acme Agency',
        'primary_color' => '#123456',
        'tagline' => 'Creative Web Solutions',
    ]);

    // Test site() helper returns singleton
    $singleton = site();
    expect($singleton)->toBeInstanceOf(SiteSetting::class)
        ->and($singleton->id)->toBe($created->id)
        ->and(site('site_name'))->toBe('Acme Agency')
        ->and(site('primary_color'))->toBe('#123456')
        ->and(site('tagline'))->toBe('Creative Web Solutions')
        ->and(site('non_existing_key', 'fallback'))->toBe('fallback');
});

it('caches the singleton forever and flushes cache on save', function () {
    $setting = SiteSetting::query()->create([
        'site_name' => 'Original Name',
        'primary_color' => '#111111',
    ]);

    expect(site('site_name'))->toBe('Original Name')
        ->and(Cache::has(SiteSetting::CACHE_KEY))->toBeTrue();

    // Modify the record and save
    $setting->update([
        'site_name' => 'Updated Name',
        'primary_color' => '#222222',
    ]);

    // Cache should be flushed and re-cached with updated values
    expect(site('site_name'))->toBe('Updated Name')
        ->and(site('primary_color'))->toBe('#222222');
});

it('encrypts telegram_bot_token in the database', function () {
    $token = '123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ';

    $setting = SiteSetting::query()->create([
        'site_name' => 'Secure Site',
        'telegram_bot_token' => $token,
    ]);

    // Eloquent decrypts on access
    expect($setting->telegram_bot_token)->toBe($token);

    // Direct database query contains encrypted text, not the plaintext token
    $rawToken = DB::table('site_settings')->where('id', $setting->id)->value('telegram_bot_token');
    expect($rawToken)->not->toBe($token)
        ->and($rawToken)->not->toBeNull();
});

it('handles json casts for social_links and enabled_modules', function () {
    $setting = SiteSetting::query()->create([
        'site_name' => 'Module Test',
        'social_links' => [
            ['platform' => 'github', 'url' => 'https://github.com/my-agency'],
        ],
        'enabled_modules' => [
            'portfolio' => true,
            'catalog' => false,
        ],
    ]);

    expect($setting->social_links)->toBeArray()
        ->and($setting->social_links[0]['platform'])->toBe('github')
        ->and($setting->enabled_modules['portfolio'])->toBeTrue()
        ->and($setting->enabled_modules['catalog'])->toBeFalse()
        ->and($setting->isModuleEnabled('portfolio'))->toBeTrue()
        ->and($setting->isModuleEnabled('catalog'))->toBeFalse();
});

it('allows authenticated panel user to access Site Settings page in Filament', function () {
    $user = User::factory()->create(['email' => config('bina.admin_email')]);

    $this->actingAs($user)
        ->get('/admin/site-settings')
        ->assertOk()
        ->assertSee('Site Settings');
});

it('updates settings through the Filament Site Settings page and flushes cache', function () {
    $user = User::factory()->create(['email' => config('bina.admin_email')]);
    $setting = SiteSetting::singleton();

    Livewire::actingAs($user)
        ->test(SiteSettings::class)
        ->assertSuccessful()
        ->fillForm([
            'site_name' => 'Brand New Name',
            'primary_color' => '#e11d48',
            'accent_color' => '#059669',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(site('site_name'))->toBe('Brand New Name')
        ->and(site('primary_color'))->toBe('#e11d48')
        ->and(site('accent_color'))->toBe('#059669');
});

it('updates the public site immediately when primary color and site name are changed', function () {
    $setting = SiteSetting::singleton();

    $setting->update([
        'site_name' => 'Spectacular Studios',
        'primary_color' => '#4338ca',
        'font_heading' => 'Poppins',
        'font_body' => 'Plus Jakarta Sans',
    ]);

    $rgb = SiteSetting::hexToRgb('#4338ca');

    $response = $this->get('/')
        ->assertOk()
        ->assertSee('Spectacular Studios')
        ->assertSee('--color-primary: '.$rgb, false)
        ->assertSee('--color-primary-hex: #4338ca', false)
        ->assertSee('--font-heading: \'Poppins\', sans-serif;', false)
        ->assertSee('--font-body: \'Plus Jakarta Sans\', sans-serif;', false)
        ->assertSee('fonts.googleapis.com/css2?family=Poppins', false)
        ->assertSee('family=Plus+Jakarta+Sans', false);
});

it('returns null for google fonts url when using system fonts', function () {
    $setting = new SiteSetting([
        'font_heading' => 'sans-serif',
        'font_body' => 'serif',
    ]);

    expect($setting->googleFontsUrl())->toBeNull();
});

it('correctly checks module enabled status', function () {
    $setting = new SiteSetting([
        'enabled_modules' => [
            'portfolio' => true,
            'catalog' => false,
        ],
    ]);

    expect($setting->isModuleEnabled('portfolio'))->toBeTrue()
        ->and($setting->isModuleEnabled('catalog'))->toBeFalse()
        ->and($setting->isModuleEnabled('non_existing'))->toBeFalse();
});

it('flushes cache on deletion', function () {
    $setting = SiteSetting::query()->create([
        'site_name' => 'Will Delete',
        'primary_color' => '#333333',
    ]);

    expect(site('site_name'))->toBe('Will Delete')
        ->and(Cache::has(SiteSetting::CACHE_KEY))->toBeTrue();

    $setting->delete();

    expect(Cache::has(SiteSetting::CACHE_KEY))->toBeFalse();
});

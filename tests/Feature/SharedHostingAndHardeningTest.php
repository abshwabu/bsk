<?php

use App\Filament\Pages\Auth\Login;
use App\Models\SiteSetting;
use App\Support\Rules\StrongPassword;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Livewire\Livewire;

beforeEach(function () {
    SiteSetting::singleton()->update([
        'site_name' => 'Bina Test Site',
        'enabled_modules' => ['portfolio', 'catalog'],
    ]);
});

it('runs bina:doctor diagnostics and prints a formatted status table', function () {
    $this->artisan('bina:doctor')
        ->expectsOutputToContain('Running Bina Site Kit Doctor Diagnostics')
        ->expectsOutputToContain('PHP Version')
        ->expectsOutputToContain('Database Connection')
        ->assertExitCode(0);
});

it('fails bina:doctor if production debug mode is enabled', function () {
    Config::set('app.env', 'production');
    Config::set('app.debug', true);

    $this->artisan('bina:doctor')
        ->expectsOutputToContain('APP_DEBUG is true in production')
        ->assertExitCode(1);
});

it('publishes storage assets via bina:publish-storage', function () {
    $sourceDir = storage_path('app/public');
    File::ensureDirectoryExists($sourceDir);
    File::put($sourceDir.'/test-bina-asset.txt', 'Bina Test Content');

    // Test publishing
    $this->artisan('bina:publish-storage', ['--clean' => true])
        ->expectsOutputToContain('Storage assets successfully published')
        ->assertExitCode(0);

    $publishedFile = public_path('storage/test-bina-asset.txt');
    expect(File::exists($publishedFile))->toBeTrue();
    expect(File::get($publishedFile))->toBe('Bina Test Content');

    // Cleanup test file
    File::delete($sourceDir.'/test-bina-asset.txt');
    File::delete($publishedFile);
});

it('blocks web maintenance routes when maintenance is disabled', function () {
    Config::set('bina.maintenance_enabled', false);
    Config::set('bina.maintenance_token', 'super-secret-maintenance-token-1234');

    $this->get('/_bina/super-secret-maintenance-token-1234/cache-clear')->assertStatus(404);
    $this->get('/_bina/super-secret-maintenance-token-1234/migrate')->assertStatus(404);
});

it('blocks web maintenance routes when token is empty or blank', function () {
    Config::set('bina.maintenance_enabled', true);
    Config::set('bina.maintenance_token', null);

    $this->get('/_bina/any-token/cache-clear')->assertStatus(404);
    $this->get('/_bina/any-token/migrate')->assertStatus(404);
});

it('aborts with 403 on invalid maintenance token', function () {
    Config::set('bina.maintenance_enabled', true);
    Config::set('bina.maintenance_token', 'correct-secret-token-1234567890');

    $this->get('/_bina/wrong-token-value/cache-clear')->assertStatus(403);
    $this->get('/_bina/wrong-token-value/migrate')->assertStatus(403);
});

it('executes cache-clear successfully via web maintenance route with valid token', function () {
    Config::set('bina.maintenance_enabled', true);
    Config::set('bina.maintenance_token', 'valid-maintenance-token-123456');

    // HTML response
    $response = $this->get('/_bina/valid-maintenance-token-123456/cache-clear');
    $response->assertStatus(200);
    $response->assertSee('Cache Cleared Successfully');

    // JSON response
    $jsonResponse = $this->getJson('/_bina/valid-maintenance-token-123456/cache-clear');
    $jsonResponse->assertStatus(200);
    $jsonResponse->assertJsonFragment([
        'status' => 'success',
        'message' => 'Cache cleared successfully.',
    ]);
});

it('executes migrate successfully via web maintenance route with valid token', function () {
    Config::set('bina.maintenance_enabled', true);
    Config::set('bina.maintenance_token', 'valid-maintenance-token-123456');

    // HTML response
    $response = $this->get('/_bina/valid-maintenance-token-123456/migrate');
    $response->assertStatus(200);
    $response->assertSee('Migrations Executed Successfully');

    // JSON response
    $jsonResponse = $this->getJson('/_bina/valid-maintenance-token-123456/migrate');
    $jsonResponse->assertStatus(200);
    $jsonResponse->assertJsonFragment([
        'status' => 'success',
        'message' => 'Migrations executed successfully.',
    ]);
});

it('enforces strong password rule for admin users', function () {
    $rule = StrongPassword::rule();

    // Weak passwords: too short, missing uppercase, missing numbers, missing symbols
    $weakPasswords = [
        'short',
        'nouppercase1!',
        'NOLOWERCASE1!',
        'NoSpecialChar123',
        'NoNumbersHere!!',
    ];

    foreach ($weakPasswords as $weak) {
        $validator = Validator::make(['password' => $weak], ['password' => $rule]);
        expect($validator->fails())->toBeTrue("Expected '{$weak}' to fail strong password validation.");
    }

    // Strong password: 8+ chars, upper, lower, number, symbol
    $validPassword = 'ValidPassw0rd!2026';
    $validValidator = Validator::make(['password' => $validPassword], ['password' => $rule]);
    expect($validValidator->passes())->toBeTrue();
});

it('configures session cookie security and rotating daily logging channel', function () {
    expect(config('session.same_site'))->toBe('lax');
    expect(config('logging.default'))->toBe('daily');
    expect(config('logging.channels.daily.days'))->toBe(14);
});

it('prevents page from using reserved slug _bina', function () {
    $this->get('/_bina')->assertStatus(404);
});

it('logs a warning when admin login authentication fails', function () {
    Log::spy();

    Livewire::test(Login::class)
        ->fillForm([
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ])
        ->call('authenticate')
        ->assertHasErrors(['data.email']);

    Log::shouldHaveReceived('warning')
        ->withArgs(function ($message, $context) {
            return str_contains($message, 'Failed Filament admin login') &&
                ($context['email'] ?? null) === 'nonexistent@example.com';
        })
        ->once();
});

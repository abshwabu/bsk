<?php

use App\Models\User;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Support\Facades\Hash;

it('renders the public placeholder through the site layout', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Your website starts here')
        ->assertSee(site_name());
});

it('shows the admin login screen', function () {
    $this->get('/admin/login')->assertOk();
});

it('seeds one super-admin with the configured credentials', function () {
    $this->seed(SuperAdminSeeder::class);

    $admin = User::query()->sole();

    expect($admin->email)->toBe(config('bina.admin_email'))
        ->and(Hash::check(config('bina.admin_password'), $admin->password))->toBeTrue();
});

it('allows an authenticated panel user to access the admin panel', function () {
    $this->actingAs(User::factory()->create(['email' => config('bina.admin_email')]))
        ->get('/admin')
        ->assertOk();
});

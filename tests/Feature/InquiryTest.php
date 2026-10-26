<?php

use App\Filament\Pages\SiteSettings;
use App\Filament\Resources\InquiryResource;
use App\Mail\NewInquiryNotification;
use App\Models\Inquiry;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\TelegramNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    RateLimiter::clear('inquiry:127.0.0.1');
});

function validInquiryPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+15551234567',
        'subject' => 'Project Inquiry',
        'message' => 'Hello, I would like to build a custom website with Bina Site Kit.',
        '_token_time' => Crypt::encrypt(time() - 5), // 5 seconds ago
        'source_url' => 'http://localhost/contact',
        'hp_website' => '',
    ], $overrides);
}

test('it stores an inquiry and fires telegram notification with mock http', function () {
    Http::fake([
        'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
    ]);

    $setting = SiteSetting::singleton();
    $setting->update([
        'telegram_bot_token' => '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11',
        'telegram_chat_id' => '-1001234567890',
    ]);

    $response = $this->postJson('/inquiry', validInquiryPayload([
        'name' => 'Alice Bob',
        'message' => 'Need a brand new website redesign.',
    ]));

    $response->assertStatus(201);
    $response->assertJson([
        'success' => true,
    ]);

    $this->assertDatabaseHas('inquiries', [
        'name' => 'Alice Bob',
        'email' => 'john@example.com',
        'status' => Inquiry::STATUS_NEW,
    ]);

    Http::assertSent(function ($request) {
        $body = $request->data();

        return str_contains($request->url(), '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11')
            && $body['chat_id'] === '-1001234567890'
            && str_contains($body['text'], 'Alice Bob')
            && str_contains($body['text'], 'Need a brand new website redesign.')
            && str_contains($body['text'], 'View in Admin Panel');
    });
});

test('it escapes html in telegram notification text to prevent injection', function () {
    Http::fake([
        'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
    ]);

    $setting = SiteSetting::singleton();
    $setting->update([
        'telegram_bot_token' => 'dummy_token',
        'telegram_chat_id' => 'dummy_chat',
    ]);

    $this->postJson('/inquiry', validInquiryPayload([
        'name' => '<script>alert("hack")</script>',
        'message' => 'Hello & welcome <b>test</b>',
    ]))->assertStatus(201);

    Http::assertSent(function ($request) {
        $text = $request->data()['text'];

        return ! str_contains($text, '<script>')
            && str_contains($text, '&lt;script&gt;')
            && str_contains($text, '&amp; welcome')
            && str_contains($text, '&lt;b&gt;test&lt;/b&gt;');
    });
});

test('it handles empty telegram credentials gracefully without making http calls', function () {
    Http::fake();

    $setting = SiteSetting::singleton();
    $setting->update([
        'telegram_bot_token' => null,
        'telegram_chat_id' => null,
    ]);

    $response = $this->postJson('/inquiry', validInquiryPayload());

    $response->assertStatus(201);
    $this->assertDatabaseCount('inquiries', 1);

    Http::assertNothingSent();
});

test('it logs telegram failure and does not fail the visitor request when telegram api fails', function () {
    Http::fake([
        'https://api.telegram.org/*' => Http::response(['ok' => false, 'description' => 'Unauthorized'], 401),
    ]);

    $setting = SiteSetting::singleton();
    $setting->update([
        'telegram_bot_token' => 'invalid_token',
        'telegram_chat_id' => '12345',
    ]);

    $response = $this->postJson('/inquiry', validInquiryPayload());

    $response->assertStatus(201);
    $this->assertDatabaseCount('inquiries', 1);
});

test('it rejects spam when honeypot field is filled', function () {
    Http::fake();

    $response = $this->postJson('/inquiry', validInquiryPayload([
        'hp_website' => 'http://spambot.example.com',
    ]));

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['hp_website']);
    $this->assertDatabaseCount('inquiries', 0);
    Http::assertNothingSent();
});

test('it rejects spam when time check is missing, instantaneous, or expired', function () {
    Http::fake();

    // 1. Missing time token
    $payloadMissing = validInquiryPayload();
    unset($payloadMissing['_token_time']);
    $this->postJson('/inquiry', $payloadMissing)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['_token_time']);

    // 2. Instantaneous submission (under 2 seconds elapsed)
    $payloadFast = validInquiryPayload([
        '_token_time' => Crypt::encrypt(time()),
    ]);
    $this->postJson('/inquiry', $payloadFast)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['_token_time']);

    // 3. Expired token (> 24 hours ago)
    $payloadExpired = validInquiryPayload([
        '_token_time' => Crypt::encrypt(time() - 90000),
    ]);
    $this->postJson('/inquiry', $payloadExpired)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['_token_time']);

    $this->assertDatabaseCount('inquiries', 0);
});

test('it enforces throttle limit of 5 requests per hour per IP', function () {
    Http::fake([
        'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
    ]);

    // Send 5 successful requests
    for ($i = 1; $i <= 5; $i++) {
        $response = $this->postJson('/inquiry', validInquiryPayload([
            'email' => "user{$i}@example.com",
            'message' => "Message number {$i}",
        ]));
        $response->assertStatus(201);
    }

    $this->assertDatabaseCount('inquiries', 5);

    // 6th request within the same hour should be throttled (429)
    $response = $this->postJson('/inquiry', validInquiryPayload([
        'email' => 'user6@example.com',
        'message' => 'Message number 6',
    ]));

    $response->assertStatus(429);
    $this->assertDatabaseCount('inquiries', 5);
});

test('it validates required fields and requires at least email or phone', function () {
    // Missing name and message
    $this->postJson('/inquiry', [
        '_token_time' => Crypt::encrypt(time() - 5),
    ])->assertStatus(422)->assertJsonValidationErrors(['name', 'message']);

    // Providing only name and message without email or phone fails
    $this->postJson('/inquiry', [
        'name' => 'No Contact Person',
        'message' => 'This is a test message.',
        '_token_time' => Crypt::encrypt(time() - 5),
    ])->assertStatus(422)->assertJsonValidationErrors(['email', 'phone']);

    // Providing phone only succeeds
    $this->postJson('/inquiry', validInquiryPayload([
        'email' => null,
        'phone' => '+15559876543',
    ]))->assertStatus(201);

    // Providing email only succeeds
    $this->postJson('/inquiry', validInquiryPayload([
        'email' => 'emailonly@example.com',
        'phone' => null,
    ]))->assertStatus(201);
});

test('it sends email notification when mailer is configured and site email is present', function () {
    Mail::fake();
    Http::fake();

    $setting = SiteSetting::singleton();
    $setting->update([
        'email' => 'notify@example.com',
    ]);

    $this->postJson('/inquiry', validInquiryPayload());

    Mail::assertSent(NewInquiryNotification::class, function ($mail) {
        return $mail->hasTo('notify@example.com')
            && $mail->inquiry->name === 'John Doe';
    });
});

test('it supports progressive enhancement with standard form post redirect', function () {
    Http::fake();

    $response = $this->from('/contact')->post('/inquiry', validInquiryPayload());

    $response->assertRedirect('/contact');
    $response->assertSessionHas('inquiry_success');
    $this->assertDatabaseCount('inquiries', 1);
});

test('it displays unread count badge in InquiryResource navigation', function () {
    expect(InquiryResource::getNavigationBadge())->toBeNull();

    Inquiry::create([
        'name' => 'Lead One',
        'email' => 'lead1@example.com',
        'message' => 'First message',
        'status' => Inquiry::STATUS_NEW,
    ]);

    Inquiry::create([
        'name' => 'Lead Two',
        'email' => 'lead2@example.com',
        'message' => 'Second message',
        'status' => Inquiry::STATUS_NEW,
    ]);

    Inquiry::create([
        'name' => 'Old Lead',
        'email' => 'lead3@example.com',
        'message' => 'Third message',
        'status' => Inquiry::STATUS_READ,
    ]);

    expect(InquiryResource::getNavigationBadge())->toBe('2');
    expect(InquiryResource::getNavigationBadgeColor())->toBe('warning');
});

test('it marks inquiry as read when viewed by admin in Filament', function () {
    $admin = User::factory()->create([
        'email' => config('bina.admin_email'),
    ]);

    $inquiry = Inquiry::create([
        'name' => 'Unread Prospect',
        'email' => 'prospect@example.com',
        'message' => 'Interested in your agency services.',
        'status' => Inquiry::STATUS_NEW,
    ]);

    expect($inquiry->status)->toBe(Inquiry::STATUS_NEW);
    expect($inquiry->handled_at)->toBeNull();

    $this->actingAs($admin)
        ->get('/admin/inquiries/'.$inquiry->id)
        ->assertSuccessful();

    $inquiry->refresh();
    expect($inquiry->status)->toBe(Inquiry::STATUS_READ);
    expect($inquiry->handled_at)->not->toBeNull();
});

test('it marks inquiry status using model helper methods', function () {
    $inquiry = Inquiry::create([
        'name' => 'Test Lead',
        'email' => 'test@example.com',
        'message' => 'Testing lifecycle methods.',
        'status' => Inquiry::STATUS_NEW,
    ]);

    $inquiry->markAsRead();
    expect($inquiry->status)->toBe(Inquiry::STATUS_READ);
    expect($inquiry->handled_at)->not->toBeNull();

    $inquiry->markAsReplied();
    expect($inquiry->status)->toBe(Inquiry::STATUS_REPLIED);

    $inquiry->markAsArchived();
    expect($inquiry->status)->toBe(Inquiry::STATUS_ARCHIVED);
});

test('telegram notifier sendTestMessage sends formatted test payload', function () {
    Http::fake([
        'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
    ]);

    $notifier = new TelegramNotifier('test_bot_token', 'test_chat_id');
    $success = $notifier->sendTestMessage();

    expect($success)->toBeTrue();

    Http::assertSent(function ($request) {
        $body = $request->data();

        return str_contains($request->url(), 'test_bot_token')
            && $body['chat_id'] === 'test_chat_id'
            && str_contains($body['text'], 'Test Notification');
    });
});

test('it triggers telegram test message from SiteSettings filament page', function () {
    Http::fake([
        'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
    ]);

    $user = User::factory()->create(['email' => config('bina.admin_email')]);
    $setting = SiteSetting::singleton();
    $setting->update([
        'telegram_bot_token' => 'livewire_token',
        'telegram_chat_id' => 'livewire_chat',
    ]);

    Livewire::actingAs($user)
        ->test(SiteSettings::class)
        ->call('sendTestTelegram')
        ->assertNotified('Test message sent successfully!');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'livewire_token')
            && $request->data()['chat_id'] === 'livewire_chat';
    });
});

<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TelegramNotifier
{
    protected ?string $token;

    protected ?string $chatId;

    public function __construct(?string $token = null, ?string $chatId = null)
    {
        $setting = SiteSetting::current();
        $this->token = $token ?? $setting->telegram_bot_token;
        $this->chatId = $chatId ?? $setting->telegram_chat_id;
    }

    public static function make(?string $token = null, ?string $chatId = null): self
    {
        return new self($token, $chatId);
    }

    /**
     * Send an HTML formatted message to Telegram.
     */
    public function send(string $text): bool
    {
        if (empty($this->token) || empty($this->chatId)) {
            return false;
        }

        try {
            $response = Http::timeout(5)
                ->asJson()
                ->post("https://api.telegram.org/bot{$this->token}/sendMessage", [
                    'chat_id' => $this->chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('Telegram notification failed: '.$response->body(), [
                'status' => $response->status(),
            ]);

            return false;
        } catch (Throwable $e) {
            Log::warning('Telegram notification error: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return false;
        }
    }

    /**
     * Escape special HTML characters for Telegram HTML mode.
     */
    public static function escape(?string $text): string
    {
        return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Send formatted new inquiry notification.
     */
    public function sendInquiry(Inquiry $inquiry): bool
    {
        $siteName = self::escape(site('site_name') ?? config('app.name', 'Bina Site Kit'));
        $name = self::escape($inquiry->name);
        $email = $inquiry->email ? self::escape($inquiry->email) : '<i>Not provided</i>';
        $phone = $inquiry->phone ? self::escape($inquiry->phone) : '<i>Not provided</i>';
        $subject = $inquiry->subject ? self::escape($inquiry->subject) : 'General Inquiry';
        $messagePreview = self::escape(Str::limit($inquiry->message, 500));
        $source = $inquiry->source_url ? self::escape($inquiry->source_url) : '<i>Direct</i>';

        $adminUrl = url('/admin/inquiries/'.$inquiry->id);

        $text = "📬 <b>New Inquiry on {$siteName}</b>\n\n"
            ."👤 <b>Name:</b> {$name}\n"
            ."📧 <b>Email:</b> {$email}\n"
            ."📞 <b>Phone:</b> {$phone}\n"
            ."🏷️ <b>Subject:</b> {$subject}\n"
            ."🌐 <b>Source:</b> {$source}\n\n"
            ."💬 <b>Message:</b>\n{$messagePreview}\n\n"
            ."🔗 <a href=\"{$adminUrl}\">View in Admin Panel</a>";

        return $this->send($text);
    }

    /**
     * Send a test message to verify the configuration.
     */
    public function sendTestMessage(): bool
    {
        $siteName = self::escape(site('site_name') ?? config('app.name', 'Bina Site Kit'));
        $time = now()->toDayDateTimeString();

        $text = "🤖 <b>Test Notification from {$siteName}</b>\n\n"
            ."Your Telegram bot integration is configured correctly and ready to receive inquiry notifications!\n\n"
            ."⏰ <i>Sent at: {$time}</i>";

        return $this->send($text);
    }
}

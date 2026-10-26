<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'site_settings';

    protected $table = 'site_settings';

    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'enabled_modules' => 'array',
            'telegram_bot_token' => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            static::flushCache();
        });

        static::deleted(function () {
            static::flushCache();
        });
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Get the cached singleton instance.
     */
    public static function current(): self
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, function () {
                return static::query()->first() ?? new static(static::defaults());
            });
        } catch (\Throwable $e) {
            return new static(static::defaults());
        }
    }

    /**
     * Get or create the persisted singleton record.
     */
    public static function singleton(): self
    {
        $setting = static::query()->first();

        if (! $setting) {
            $setting = static::query()->create(static::defaults());
        }

        return $setting;
    }

    /**
     * Default placeholder branding and settings.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        $appName = config('app.name', 'Bina Site Kit');

        return [
            'site_name' => $appName,
            'tagline' => 'A clean and modern starter for client websites.',
            'logo' => null,
            'favicon' => null,
            'primary_color' => '#0f172a',
            'accent_color' => '#3b82f6',
            'font_heading' => 'Inter',
            'font_body' => 'Inter',
            'phone' => '+15550000000',
            'email' => 'hello@example.com',
            'address' => "123 Starter St, Suite 100\nCity, ST 12345",
            'whatsapp' => '+15550000000',
            'social_links' => [
                ['platform' => 'github', 'url' => 'https://github.com'],
                ['platform' => 'twitter', 'url' => 'https://x.com'],
            ],
            'footer_text' => '© '.date('Y').' '.$appName.'. All rights reserved.',
            'analytics_id' => null,
            'telegram_bot_token' => null,
            'telegram_chat_id' => null,
            'enabled_modules' => [
                'portfolio' => false,
                'catalog' => false,
            ],
        ];
    }

    public function logoUrl(): ?string
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : null;
    }

    public function faviconUrl(): ?string
    {
        return $this->favicon ? Storage::disk('public')->url($this->favicon) : null;
    }

    public function primaryRgb(): string
    {
        return static::hexToRgb($this->primary_color ?? '#0f172a');
    }

    public function accentRgb(): string
    {
        return static::hexToRgb($this->accent_color ?? '#3b82f6');
    }

    public static function hexToRgb(string $color): string
    {
        $color = trim($color);

        if (preg_match('/^rgba?\(([\d\s,]+)\)/i', $color, $matches)) {
            $parts = preg_split('/[\s,]+/', trim($matches[1]));

            return implode(' ', array_slice($parts, 0, 3));
        }

        $hex = ltrim($color, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) === 6 && ctype_xdigit($hex)) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));

            return "{$r} {$g} {$b}";
        }

        return '15 23 42';
    }

    public function googleFontsUrl(): ?string
    {
        $systemFonts = [
            'sans-serif', 'serif', 'monospace', 'system-ui', 'inherit', 'initial',
            'arial', 'helvetica', 'times new roman', 'georgia', 'courier new', 'verdana', 'tahoma',
        ];

        $heading = trim((string) $this->font_heading);
        $body = trim((string) $this->font_body);

        $families = [];

        foreach ([$heading, $body] as $font) {
            if ($font === '' || in_array(strtolower($font), $systemFonts, true)) {
                continue;
            }

            $families[$font] = true;
        }

        if (empty($families)) {
            return null;
        }

        $query = [];
        foreach (array_keys($families) as $family) {
            $encodedFamily = str_replace(' ', '+', $family);
            $query[] = 'family='.$encodedFamily.':wght@400;500;600;700';
        }

        return 'https://fonts.googleapis.com/css2?'.implode('&', $query).'&display=swap';
    }

    public function isModuleEnabled(string $module): bool
    {
        $modules = $this->enabled_modules ?? [];

        if (is_array($modules)) {
            if (array_is_list($modules)) {
                return in_array($module, $modules, true);
            }

            return (bool) ($modules[$module] ?? false);
        }

        return false;
    }
}

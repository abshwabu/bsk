<?php

namespace App\StarterTemplates;

use App\Models\SiteSetting;
use App\Services\Navigation;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class TemplateManager
{
    /**
     * Registered starter template classes.
     *
     * @var array<string, class-string<StarterTemplateInterface>>
     */
    protected static array $templates = [
        'business' => BusinessTemplate::class,
        'portfolio' => PortfolioTemplate::class,
        'charity' => CharityTemplate::class,
        'store' => StoreTemplate::class,
    ];

    /**
     * Get all registered template instances.
     *
     * @return array<string, StarterTemplateInterface>
     */
    public static function all(): array
    {
        $instances = [];
        foreach (self::$templates as $id => $class) {
            $instances[$id] = new $class;
        }

        return $instances;
    }

    /**
     * Get a template instance by id.
     */
    public static function find(string $id): ?StarterTemplateInterface
    {
        $id = strtolower(trim($id));
        if (! isset(self::$templates[$id])) {
            return null;
        }

        $class = self::$templates[$id];

        return new $class;
    }

    /**
     * Get list of supported template IDs.
     *
     * @return list<string>
     */
    public static function ids(): array
    {
        return array_keys(self::$templates);
    }

    /**
     * Apply a starter template to a SiteSetting and seed content.
     *
     * @param  array<string, mixed>  $customSettings
     */
    public static function apply(string $templateId, SiteSetting $setting, array $customSettings = []): StarterTemplateInterface
    {
        $template = self::find($templateId);

        if (! $template) {
            $available = implode(', ', self::ids());
            throw new InvalidArgumentException("Unknown starter template [{$templateId}]. Available templates: {$available}");
        }

        $defaults = $template->defaultSettings();
        $merged = array_merge($defaults, array_filter($customSettings, fn ($v) => ! is_null($v) && $v !== ''));

        // Ensure enabled_modules handles booleans properly
        if (isset($merged['enabled_modules'])) {
            $currentModules = (array) ($setting->enabled_modules ?? []);
            if (is_array($merged['enabled_modules'])) {
                $merged['enabled_modules'] = array_merge($currentModules, $merged['enabled_modules']);
            }
        }

        $setting->update($merged);
        $setting->refresh();

        // Seed template content
        $template->seed($setting);

        // Flush caches
        SiteSetting::flushCache();
        Navigation::flushCache();
        Cache::forget('sitemap.xml');

        return $template;
    }
}

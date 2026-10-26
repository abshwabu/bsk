<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Support\Facades\Cache;

class Navigation
{
    public const CACHE_KEY = 'site_navigation_items';

    /**
     * Get the cached navigation items.
     *
     * @return array<int, array{label: string, url: string, slug: ?string, is_home: bool, open_in_new_tab: bool}>
     */
    public static function getItems(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $pages = Page::query()
                ->where('show_in_nav', true)
                ->where('is_published', true)
                ->orderBy('nav_order')
                ->orderBy('title')
                ->get(['id', 'title', 'slug', 'is_home', 'nav_order'])
                ->map(fn (Page $page) => [
                    'label' => $page->title,
                    'url' => $page->is_home ? url('/') : url('/'.$page->slug),
                    'slug' => $page->slug,
                    'is_home' => (bool) $page->is_home,
                    'open_in_new_tab' => false,
                ])
                ->all();

            $extraLinks = site('extra_nav_links') ?? [];

            if (is_array($extraLinks)) {
                foreach ($extraLinks as $link) {
                    if (empty($link['label']) || empty($link['url'])) {
                        continue;
                    }

                    $pages[] = [
                        'label' => (string) $link['label'],
                        'url' => (string) $link['url'],
                        'slug' => null,
                        'is_home' => false,
                        'open_in_new_tab' => (bool) ($link['open_in_new_tab'] ?? false),
                    ];
                }
            }

            return $pages;
        });
    }

    /**
     * Flush the cached navigation query.
     */
    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Check if a navigation item matches the current request.
     *
     * @param  array{label: string, url: string, slug: ?string, is_home: bool, open_in_new_tab: bool}  $item
     */
    public static function isActive(array $item): bool
    {
        $currentUrl = request()->url();
        $itemUrl = url($item['url']);

        if (! empty($item['is_home'])) {
            return request()->is('/') || $currentUrl === url('/');
        }

        if (! empty($item['slug'])) {
            return request()->is($item['slug']) || request()->is($item['slug'].'/*');
        }

        return $currentUrl === $itemUrl;
    }
}

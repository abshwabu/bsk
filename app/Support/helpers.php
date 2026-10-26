<?php

use App\Models\SiteSetting;

if (! function_exists('site')) {
    /**
     * Get the cached SiteSetting singleton or a specific setting attribute.
     */
    function site(?string $key = null, mixed $default = null): mixed
    {
        $settings = SiteSetting::current();

        if ($key === null) {
            return $settings;
        }

        return $settings->getAttribute($key) ?? $default;
    }
}

if (! function_exists('site_name')) {
    function site_name(): string
    {
        return (string) (site('site_name') ?: config('app.name', 'Bina Site Kit'));
    }
}

<?php

if (! function_exists('site_name')) {
    function site_name(): string
    {
        return (string) config('app.name');
    }
}

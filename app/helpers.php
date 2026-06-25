<?php

if (!function_exists('template_asset')) {
    function template_asset($path)
    {
        return asset(config('template.assets_path') . '/' . ltrim($path, '/'));
    }
}

if (!function_exists('__t')) {
    function __t(string $text, ?string $locale = null): string
    {
        return \App\Helpers\GoogleTranslateHelper::trans($text, $locale);
    }
}

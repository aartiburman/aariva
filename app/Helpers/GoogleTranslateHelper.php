<?php

namespace App\Helpers;

use Stichoza\GoogleTranslate\GoogleTranslate;

class GoogleTranslateHelper
{
    protected static ?GoogleTranslate $tr = null;

    protected static function translator(): GoogleTranslate
    {
        if (! self::$tr) {
            self::$tr = new GoogleTranslate;
            self::$tr->setSource('en');
        }
        return self::$tr;
    }

    public static function trans(string $text, ?string $target = null): string
    {
        if (! $target) {
            $target = session('locale', app()->getLocale());
        }

        if ($target === 'en') {
            return $text;
        }

        $cachePath = storage_path("app/translations/{$target}.json");
        $cache = [];

        if (file_exists($cachePath)) {
            $cache = json_decode(file_get_contents($cachePath), true) ?: [];
        }

        $key = md5($text);

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        try {
            $translated = self::translator()->setTarget($target)->translate($text);
        } catch (\Exception $e) {
            return $text;
        }

        $cache[$key] = $translated;

        $dir = dirname($cachePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($cachePath, json_encode($cache, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return $translated;
    }
}



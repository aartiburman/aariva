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
            if ($target === 'hing') {
                $hindiText = self::translator()->setTarget('hi')->translate($text);
                $translated = self::transliterate($hindiText);
            } else {
                $translated = self::translator()->setTarget($target)->translate($text);
            }
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

    private static function transliterate(string $hindiText): string
    {
        $map = [
            'अ' => 'a', 'आ' => 'aa', 'इ' => 'i', 'ई' => 'ee',
            'उ' => 'u', 'ऊ' => 'oo', 'ऋ' => 'ri', 'ए' => 'e',
            'ऐ' => 'ai', 'ओ' => 'o', 'औ' => 'au',
            'क' => 'k', 'ख' => 'kh', 'ग' => 'g', 'घ' => 'gh',
            'ङ' => 'ng', 'च' => 'ch', 'छ' => 'chh', 'ज' => 'j',
            'झ' => 'jh', 'ञ' => 'ny', 'ट' => 't', 'ठ' => 'th',
            'ड' => 'd', 'ढ' => 'dh', 'ण' => 'n', 'त' => 't',
            'थ' => 'th', 'द' => 'd', 'ध' => 'dh', 'न' => 'n',
            'प' => 'p', 'फ' => 'ph', 'ब' => 'b', 'भ' => 'bh',
            'म' => 'm', 'य' => 'y', 'र' => 'r', 'ल' => 'l',
            'व' => 'v', 'श' => 'sh', 'ष' => 'sh', 'स' => 's',
            'ह' => 'h', '़' => '',
            'ा' => 'aa', 'ि' => 'i', 'ी' => 'ee', 'ु' => 'u',
            'ू' => 'oo', 'ृ' => 'ri', 'े' => 'e', 'ै' => 'ai',
            'ो' => 'o', 'ौ' => 'au', 'ं' => 'n', 'ः' => 'h',
            '्' => '', 'ऽ' => "'",
            '०' => '0', '१' => '1', '२' => '2', '३' => '3',
            '४' => '4', '५' => '5', '६' => '6', '७' => '7',
            '८' => '8', '९' => '9',
            ' ' => ' ', '.' => '.', ',' => ',', '!' => '!',
            '?' => '?', ':' => ':', ';' => ';', '-' => '-',
        ];

        $result = '';
        $len = mb_strlen($hindiText);

        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($hindiText, $i, 1);
            $result .= $map[$char] ?? $char;
        }

        return $result;
    }
}



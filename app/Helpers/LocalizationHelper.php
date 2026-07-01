<?php

namespace App\Helpers;

class LocalizationHelper
{
    public static function getLocaleSuffix(): string
    {
        $locale = app()->getLocale();
        $map = [
            'ar' => '_ar',
            'zh' => '_zh',
            'ja' => '_ja',
            'hi' => '_hi',
            'de' => '_de',
            'fr' => '_fr',
            'ko' => '_ko',
            'pt' => '_pt',
            'es' => '_es',
            'ru' => '_ru',
            'it' => '_it',
            'tr' => '_tr',
            'th' => '_th',
            'vi' => '_vi',
        ];
        return $map[$locale] ?? '';
    }

    public static function getLocalizedField($model, string $field): ?string
    {
        $suffix = self::getLocaleSuffix();
        $localizedField = $field . $suffix;

        if ($suffix && !empty($model->{$localizedField})) {
            return $model->{$localizedField};
        }

        if (!empty($model->{$field})) {
            return $model->{$field};
        }

        return null;
    }
}

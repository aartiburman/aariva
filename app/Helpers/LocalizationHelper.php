<?php

namespace App\Helpers;

class LocalizationHelper
{
    public static function getLocalizedField($model, string $field): ?string
    {
        if (!empty($model->{$field})) {
            return $model->{$field};
        }

        return null;
    }
}

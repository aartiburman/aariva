<?php

namespace App\Helpers;

use App\Models\GeneralSetting;
use Illuminate\Support\Facades\DB;

class GeneralHelper
{
    /**
     * Get a setting value by key from the general_settings table
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get_setting($key, $default = null)
    {
        $setting = GeneralSetting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Get currency based on language
     *
     * @param string $lang
     * @return string
     */
    public static function get_currency_by_lang($lang = 'en')
    {
        if ($lang === 'ar') {
            return 'AED';
        } else {
            return 'INR';
        }
    }

    /**
     * Get variant label from product_variant_labels table
     *
     * @param int|string $id
     * @param string $lang
     * @return string|null
     */
    protected static $variantLabels = null;

    public static function getVariantLabel($id, $lang = 'en')
    {
        if (self::$variantLabels === null) {
            try {
                self::$variantLabels = DB::table('product_variant_labels')->get()->keyBy('id');
            } catch (\Exception $e) {
                // Fallback to empty collection if table doesn't exist yet or other error
                self::$variantLabels = collect();
            }
        }

        $label = self::$variantLabels->get($id);
        if ($label) {
            return $label->{"name_$lang"} ?? $label->name ?? null;
        }

        // Hardcoded fallbacks as a last resort
        $fallbacks = [
            '1' => 'Size',
            '2' => 'Quantity',
            '3' => 'Age Group',
            '4' => 'Pack of',
            '5' => 'Capacity',
        ];

        return $fallbacks[$id] ?? null;
    }
}

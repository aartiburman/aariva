<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    public function changeLanguage(Request $request, string $lang)
    {
        $supported = [
            'en', 'ar', 'zh', 'ja', 'hi', 'de', 'fr', 'ko', 'pt', 'es', 'ru', 'it', 'tr', 'th', 'vi', 'hing',
        ];

        if (! in_array($lang, $supported, true)) {
            return redirect()->back();
        }

        $languageCountryMap = [
            'en' => 'US',
            'ar' => 'SA',
            'zh' => 'CN',
            'ja' => 'JP',
            'hi' => 'IN',
            'hing' => 'IN',
            'de' => 'DE',
            'fr' => 'FR',
            'ko' => 'KR',
            'pt' => 'BR',
            'es' => 'ES',
            'ru' => 'RU',
            'it' => 'IT',
            'tr' => 'TR',
            'th' => 'TH',
            'vi' => 'VN',
        ];

        $countryCode = $languageCountryMap[$lang];
        $country = \App\Models\Country::where('shortname', $countryCode)->first();

        session([
            'locale' => $lang,
            'country_code' => $countryCode,
            'currency_code' => $country->currency_code ?? 'USD',
            'currency_symbol' => $country->currency ?? '$',
        ]);

        App::setLocale($lang);

        return redirect()->back();
    }

    public function changeCountry(Request $request, string $countryCode)
    {
        $countryCode = strtoupper($countryCode);

        $country = \App\Models\Country::where('shortname', $countryCode)->first();
        if (! $country) {
            return redirect()->back();
        }

        $countryLanguageMap = [
            'US' => 'en', 'GB' => 'en', 'AU' => 'en', 'NZ' => 'en', 'CA' => 'en',
            'SA' => 'ar', 'AE' => 'ar', 'EG' => 'ar', 'QA' => 'ar', 'KW' => 'ar', 'OM' => 'ar',
            'CN' => 'zh', 'TW' => 'zh', 'HK' => 'zh',
            'JP' => 'ja',
            'IN' => 'hi',
            'DE' => 'de', 'AT' => 'de', 'CH' => 'de',
            'FR' => 'fr', 'BE' => 'fr',
            'KR' => 'ko',
            'BR' => 'pt', 'PT' => 'pt',
            'ES' => 'es', 'MX' => 'es', 'AR' => 'es',
            'RU' => 'ru',
            'IT' => 'it',
            'TR' => 'tr',
            'TH' => 'th',
            'VN' => 'vi',
            'NL' => 'nl',
            'PL' => 'pl',
            'SE' => 'sv',
            'NO' => 'nb',
            'DK' => 'da',
            'FI' => 'fi',
            'ID' => 'id',
            'MY' => 'ms',
            'PH' => 'fil',
            'PK' => 'ur',
            'BD' => 'bn',
        ];

        $locale = $countryLanguageMap[$countryCode] ?? 'en';

        session([
            'locale'         => $locale,
            'country_code'   => $countryCode,
            'currency_code'  => $country->currency_code ?? 'USD',
            'currency_symbol' => $country->currency ?? '$',
        ]);

        App::setLocale($locale);

        return redirect()->back();
    }
}

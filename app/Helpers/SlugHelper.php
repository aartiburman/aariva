<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use App\Models\Product;

class SlugHelper
{
    public static function uniqueProductSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '_09' . rand(10, 99);
        }

        return $slug;
    }

   
}

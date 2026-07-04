<?php

namespace App\Http\Controllers\Frontend\Template1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use App\Models\Brand;
use App\Models\Blog;

class SitemapController extends Controller
{
    public function index()
    {
        $products = Product::where('status', 1)
            ->whereHas('vendor', fn($q) => $q->where('status', 1))
            ->select('slug', 'updated_at')
            ->get();

        $categories = Category::where('is_active', 1)
            ->select('slug', 'updated_at')
            ->get();

        $subcategories = SubCategory::where('is_active', 1)
            ->select('slug', 'updated_at')
            ->get();

        $childCategories = ChildCategory::where('is_active', 1)
            ->select('slug', 'updated_at')
            ->get();

        $brands = Brand::where('status', 1)
            ->select('slug', 'updated_at')
            ->get();

        $blogs = Blog::where('status', 1)
            ->select('slug', 'updated_at')
            ->get();

        return response()->view('frontend.sitemap', compact(
            'products', 'categories', 'subcategories', 'childCategories', 'brands', 'blogs'
        ))->header('Content-Type', 'application/xml');
    }
}

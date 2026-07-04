<?php

namespace App\Models\Observers;

use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use App\Models\Brand;
use App\Models\Blog;

class SitemapObserver
{
    public function created($model)
    {
        $this->ping();
    }

    public function updated($model)
    {
        $this->ping();
    }

    public function deleted($model)
    {
        $this->ping();
    }

    protected function ping()
    {
        $sitemapUrl = url('sitemap.xml');

        $urls = [
            "https://www.google.com/ping?sitemap=" . urlencode($sitemapUrl),
            "https://www.bing.com/ping?sitemap=" . urlencode($sitemapUrl),
        ];

        foreach ($urls as $url) {
            try {
                @file_get_contents($url);
            } catch (\Exception $e) {
                // silently fail
            }
        }
    }
}

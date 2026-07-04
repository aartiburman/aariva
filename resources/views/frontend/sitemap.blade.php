<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        <priority>1.0</priority>
        <changefreq>daily</changefreq>
    </url>
    <url>
        <loc>{{ route('frontend.products.index') }}</loc>
        <priority>0.9</priority>
        <changefreq>daily</changefreq>
    </url>
    @foreach($categories as $cat)
    <url>
        <loc>{{ route('frontend.products.index', ['category' => $cat->slug]) }}</loc>
        <priority>0.8</priority>
        <changefreq>weekly</changefreq>
        <lastmod>{{ $cat->updated_at->toW3cString() }}</lastmod>
    </url>
    @endforeach
    @foreach($subcategories as $sub)
    <url>
        <loc>{{ route('frontend.products.index', ['subcategory' => $sub->slug]) }}</loc>
        <priority>0.7</priority>
        <changefreq>weekly</changefreq>
        <lastmod>{{ $sub->updated_at->toW3cString() }}</lastmod>
    </url>
    @endforeach
    @foreach($childCategories as $child)
    <url>
        <loc>{{ route('frontend.products.index', ['child_category' => $child->slug]) }}</loc>
        <priority>0.7</priority>
        <changefreq>weekly</changefreq>
        <lastmod>{{ $child->updated_at->toW3cString() }}</lastmod>
    </url>
    @endforeach
    @foreach($brands as $brand)
    <url>
        <loc>{{ route('frontend.products.index', ['brand' => $brand->slug]) }}</loc>
        <priority>0.7</priority>
        <changefreq>weekly</changefreq>
        <lastmod>{{ $brand->updated_at->toW3cString() }}</lastmod>
    </url>
    @endforeach
    @foreach($products as $product)
    <url>
        <loc>{{ route('frontend.products.show', $product->slug) }}</loc>
        <priority>0.6</priority>
        <changefreq>weekly</changefreq>
        <lastmod>{{ $product->updated_at->toW3cString() }}</lastmod>
    </url>
    @endforeach
    @foreach($blogs as $blog)
    <url>
        <loc>{{ route('frontend.blog.single', $blog->slug) }}</loc>
        <priority>0.5</priority>
        <changefreq>monthly</changefreq>
        <lastmod>{{ $blog->updated_at->toW3cString() }}</lastmod>
    </url>
    @endforeach
</urlset>

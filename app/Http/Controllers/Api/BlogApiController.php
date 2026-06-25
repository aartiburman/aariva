<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Helpers\ImageHelper;

class BlogApiController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->get('lang', app()->getLocale());
        $blogs = Blog::where('status', 1)
            ->with('author:id,name')
            ->orderBy('id', 'desc')
            ->paginate($request->per_page ?? 10);

        $blogs->getCollection()->transform(function ($blog) use ($lang) {
            return [
                'id' => $blog->id,
                'title' => $blog->{"title_$lang"} ?? $blog->title,
                'slug' => $blog->slug,
                'description' => $blog->{"description_$lang"} ?? $blog->description,
                'image' => $blog->image ? asset('uploads/blog/' . $blog->image) : null,
                'author' => $blog->author->name ?? 'Admin',
                'created_at' => $blog->created_at->format('M d, Y'),
            ];
        });

        // Trending blogs for the index page
        $trendingBlogs = Blog::where('status', 1)
            ->orderBy('id', 'desc') // You can change this to use views or likes if available
            ->limit(5)
            ->get()
            ->map(function ($item) use ($lang) {
                return [
                    'id' => $item->id,
                    'title' => $item->{"title_$lang"} ?? $item->title,
                    'slug' => $item->slug,
                    'image' => $item->image ? asset('uploads/blog/' . $item->image) : null,
                    'created_at' => $item->created_at->format('M d, Y'),
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $blogs,
            'trending_blogs' => $trendingBlogs
        ], 200);
    }

    public function show(Request $request)
    {
        $lang = $request->get('lang', app()->getLocale());
        $blog = Blog::where('slug', $request->slug)
            ->where('status', 1)
            ->with('author:id,name')
            ->first();

        if (!$blog) {
            return response()->json([
                'status' => false,
                'message' => 'Blog not found'
            ], 404);
        }

        $formattedBlog = [
            'id' => $blog->id,
            'title' => $blog->{"title_$lang"} ?? $blog->title,
            'slug' => $blog->slug,
            'description' => $blog->{"description_$lang"} ?? $blog->description,
            'content' => $blog->{"content_$lang"} ?? $blog->content,
            'image' => $blog->image ? asset('uploads/blog/' . $blog->image) : null,
            'author' => $blog->author->name ?? 'Admin',
            'created_at' => $blog->created_at->format('M d, Y'),
        ];

        // Related blogs (same author or just recent blogs excluding current)
        $relatedBlogs = Blog::where('status', 1)
            ->where('id', '!=', $blog->id)
            ->orderBy('id', 'desc')
            ->limit(4)
            ->get()
            ->map(function ($item) use ($lang) {
                return [
                    'id' => $item->id,
                    'title' => $item->{"title_$lang"} ?? $item->title,
                    'slug' => $item->slug,
                    'image' => $item->image ? asset('uploads/blog/' . $item->image) : null,
                    'created_at' => $item->created_at->format('M d, Y'),
                ];
            });

        // Trending blogs (could be based on views, but here we use latest as fallback)
        $trendingBlogs = Blog::where('status', 1)
            ->where('id', '!=', $blog->id)
            ->inRandomOrder()
            ->limit(4)
            ->get()
            ->map(function ($item) use ($lang) {
                return [ 
                    'id' => $item->id,
                    'title' => $item->{"title_$lang"} ?? $item->title,
                    'slug' => $item->slug,
                    'image' => $item->image ? asset('uploads/blog/' . $item->image) : null,
                    'created_at' => $item->created_at->format('M d, Y'),
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $formattedBlog,
            'related_blogs' => $relatedBlogs,
            'trending_blogs' => $trendingBlogs
        ], 200);
    }
}

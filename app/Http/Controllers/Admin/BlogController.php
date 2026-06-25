<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use Illuminate\Support\Str;
use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::with('author')->orderBy('id', 'desc')->paginate(10)->withQueryString();
        return view('backend.admin.blog.index', compact('blogs'));
    }

    public function add()
    {
        return view('backend.admin.blog.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:blogs,slug|max:255',
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = ImageHelper::compressImage($request->file('image'), 'uploads/blog/');
        }

        Blog::create([
            'title' => $request->title,
            'slug' => Str::slug($request->slug),
            'description' => $request->description,
            'content' => $request->content,
            'image' => $imageName,
            'status' => $request->has('status') ? 1 : 0,
            'author_id' => Auth::id(),
        ]);

        return redirect()->route('admin.blog.index')->with('success', 'Blog created successfully');
    }

    public function edit($id)
    {
        $blog = Blog::findOrFail($id);
        return view('backend.admin.blog.form', compact('blog'));
    }

    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:blogs,slug,' . $id . '|max:255',
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($blog->image && file_exists(public_path('uploads/blog/' . $blog->image))) {
                @unlink(public_path('uploads/blog/' . $blog->image));
            }
            $blog->image = ImageHelper::compressImage($request->file('image'), 'uploads/blog/');
        }

        $blog->update([
            'title' => $request->title,
            'slug' => Str::slug($request->slug),
            'description' => $request->description,
            'content' => $request->content,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.blog.index')->with('success', 'Blog updated successfully');
    }

    public function delete(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);
        if ($blog->image && file_exists(public_path('uploads/blog/' . $blog->image))) {
            @unlink(public_path('uploads/blog/' . $blog->image));
        }
        $blog->delete();

        if ($request->ajax()) {
            return response()->json(['status' => true, 'message' => 'Blog deleted successfully']);
        }

        return redirect()->route('admin.blog.index')->with('success', 'Blog deleted successfully');
    }

    public function update_status(Request $request)
    {
        $blog = Blog::findOrFail($request->id);
        $blog->status = $request->status;
        $blog->save();
        return response()->json(['status' => true, 'message' => 'Status updated successfully']);
    }
}

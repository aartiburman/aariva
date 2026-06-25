<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AboutUs;
use Stichoza\GoogleTranslate\GoogleTranslate;

class AboutUsController extends Controller
{
    public function index(Request $request)
    {
        $query = AboutUs::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('content', 'LIKE', "%{$search}%");
        }

        $about_us = $query->latest()->paginate(10)->withQueryString();
        return view('backend/admin/about-us/index', compact('about_us'));
    }

    public function add(Request $request)
    {
        return view('backend/admin/about-us/add');
    }

    public function edit(Request $request, $id)
    {
        $about = AboutUs::findOrFail($id);
        return view('backend/admin/about-us/add', compact('about'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('ne');

        if ($request->id) {
            $about = AboutUs::findOrFail($request->id);
            $about->update([
                'title' => $request->title,
                'title_ar' => $trAr->translate($request->title),
                'title_ne' => $trNe->translate($request->title),
                'content' => $request->content,
                'content_ar' => $trAr->translate($request->content),
                'content_ne' => $trNe->translate($request->content),
                'status' => $request->status ?? 1,
            ]);
            return redirect()->route('about.us.list')->with('success', 'About Us updated successfully');
        }

        AboutUs::create([
            'title' => $request->title,
            'title_ar' => $trAr->translate($request->title),
            'title_ne' => $trNe->translate($request->title),
            'content' => $request->content,
            'content_ar' => $trAr->translate($request->content),
            'content_ne' => $trNe->translate($request->content),
            'status' => $request->status ?? 1,
        ]);

        return redirect()->route('about.us.list')->with('success', 'About Us created successfully');
    }

    public function delete(Request $request)
    {
        $about = AboutUs::findOrFail($request->id);
        $about->delete();
        return response()->json(['status' => true, 'message' => 'About Us deleted successfully']);
    }
}

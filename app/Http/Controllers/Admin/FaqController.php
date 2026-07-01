<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;
use Stichoza\GoogleTranslate\GoogleTranslate;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $query = Faq::query();
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('question', 'LIKE', "%$search%")
                  ->orWhere('answer', 'LIKE', "%$search%");
        }
        $faqs = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        return view('backend/admin/faq/index', compact('faqs'));
    }

    public function add(Request $request)
    {
        return view('backend/admin/faq/add');
    }

    public function edit(Request $request, $id)
    {
        $faq = Faq::findOrFail($id);
        return view('backend/admin/faq/add', compact('faq'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required',
            'answer' => 'required',
        ]);

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('hi');

        $data = [
            'question' => $request->question,
            'question_ar' => $trAr->translate($request->question),
            'question_ne' => $trNe->translate($request->question),
            'answer' => $request->answer,
            'answer_ar' => $trAr->translate($request->answer),
            'answer_ne' => $trNe->translate($request->answer),
            'status' => $request->status ?? 1,
        ];

        if ($request->id) {
            $faq = Faq::findOrFail($request->id);
            $faq->update($data);
            return redirect()->route('faq.list')->with('success', 'FAQ updated successfully');
        }

        Faq::create($data);

        return redirect()->route('faq.list')->with('success', 'FAQ created successfully');
    }

    public function delete(Request $request)
    {
        $faq = Faq::findOrFail($request->id);
        $faq->delete();
        return response()->json(['status' => true, 'message' => 'FAQ deleted successfully']);
    }
}

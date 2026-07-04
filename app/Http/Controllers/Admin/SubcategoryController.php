<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use Illuminate\Support\Str;
use App\Helpers\ImageHelper;
use Stichoza\GoogleTranslate\GoogleTranslate;

class SubcategoryController extends Controller
{
    public function add_subcategory(Request $request)
    {
        $categories = Category::select('*')->where('is_active', 1)->get();
        $disableGlobalToastr = true;
        return view('backend/admin/subcategory/add-subcategory', compact('categories', 'disableGlobalToastr'));
    }

    public function bulk_delete_subcategory(Request $request)
    {
        $ids = $request->ids;
        if (!empty($ids)) {
            $subcategories = SubCategory::whereIn('id', $ids)->get();
            foreach ($subcategories as $subcategory) {
                // Delete image
                if ($subcategory->image && file_exists(public_path('uploads/subcategory/' . $subcategory->image))) {
                    @unlink(public_path('uploads/subcategory/' . $subcategory->image));
                }
                // Delete related child categories
                ChildCategory::where('subcategory_id', $subcategory->id)->delete();
                $subcategory->delete();
            }
            return response()->json(['status' => true, 'message' => 'Selected subcategories deleted successfully']);
        }
        return response()->json(['status' => false, 'message' => 'No subcategories selected']);
    }


    public function delete_subcategory(Request $request)
    {
        $subcategory = SubCategory::find($request->id);
        if (!$subcategory) {
            return response()->json(['status' => false, 'message' => 'Record not found']);
        }

        // Delete image if exists
        if ($subcategory->image && file_exists(public_path('uploads/subcategory/' . $subcategory->image))) {
            @unlink(public_path('uploads/subcategory/' . $subcategory->image));
        }

        // Delete related child categories
        ChildCategory::where('subcategory_id', $subcategory->id)->delete();

        $subcategory->delete();

        return response()->json(['status' => true, 'message' => 'Subcategory deleted successfully']);
    }

    public function ajax_store_subcategory(Request $request)
    {
        $request->validate([
            'name' => 'required|min:2',
            'category_id' => 'required|exists:categories,id',
        ]);

        try {
            $name = ucfirst(trim($request->name));
            $subcategory = SubCategory::where('name', $name)->where('category_id', $request->category_id)->first();

            if ($subcategory) {
                return response()->json(['success' => true, 'subcategory' => $subcategory, 'message' => 'Subcategory already exists.']);
            }

            $trAr = new GoogleTranslate('ar');
            $trNe = new GoogleTranslate('hi');

            $subcategory = SubCategory::create([
                'category_id' => $request->category_id,
                'name' => $name,
                'name_ar' => $trAr->translate($name),
                'name_ne' => $trNe->translate($name),
                'slug' => Str::slug($name),
                'slug_ar' => Str::slug($name),
                'slug_ne' => Str::slug($name),
                'is_active' => 1,
            ]);

            return response()->json(['success' => true, 'subcategory' => $subcategory, 'message' => 'Subcategory created successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create subcategory: ' . $e->getMessage()]);
        }
    }

    public function subcategory_list(Request $request)
    {
        $base_query = SubCategory::select('sub_categories.*', 'categories.name as category_name')
            ->leftJoin('categories', 'categories.id', '=', 'sub_categories.category_id')
            ->orderBy('sub_categories.updated_at', 'DESC');

        if ($request->filled('search')) {
            $base_query->where('sub_categories.name', 'like', '%' . trim($request->search) . '%');
        }

        if ($request->filled('is_active')) {
            $base_query->where('sub_categories.is_active', $request->is_active);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $base_query->whereDate('sub_categories.created_at', '>=', $dates[0])
                           ->whereDate('sub_categories.created_at', '<=', $dates[1]);
            } else {
                $base_query->whereDate('sub_categories.created_at', $dates[0]);
            }
        }

        $subcategories = $base_query->paginate(12)->withQueryString();

        foreach ($subcategories as $sub) {
            $sub->image = ImageHelper::getSubCategoryImage($sub->image);
            $sub->products_count = \App\Models\Product::where('subcategory_id', $sub->id)->count();
        }

        if ($request->ajax()) {
            return view('backend.admin.subcategory.subcategory-table', compact('subcategories'))->render();
        }

        $total_subcategories = SubCategory::count();
        $active_subcategories = SubCategory::where('is_active', 1)->count();
        $total_products = \App\Models\Product::whereNotNull('subcategory_id')->count();

        $featured_subcategories = SubCategory::select('sub_categories.*', 'categories.name as category_name')
            ->leftJoin('categories', 'categories.id', '=', 'sub_categories.category_id')
            ->get()
            ->each(function($sub) {
                $sub->products_count = \App\Models\Product::where('subcategory_id', $sub->id)->count();
            })
            ->sortByDesc('products_count')
            ->take(4);

        foreach ($featured_subcategories as $sub) {
            $sub->image = ImageHelper::getSubCategoryImage($sub->image);
        }

        return view('backend/admin/subcategory/subcategory', compact(
            'subcategories',
            'total_subcategories',
            'active_subcategories',
            'total_products',
            'featured_subcategories'
        ));
    }

    public function export_subcategories(Request $request)
    {
        $query = SubCategory::select('sub_categories.*', 'categories.name as category_name')
            ->leftJoin('categories', 'categories.id', '=', 'sub_categories.category_id')
            ->orderBy('sub_categories.id', 'DESC');

        if ($request->filled('search')) {
            $query->where('sub_categories.name', 'like', '%' . trim($request->search) . '%');
        }

        if ($request->filled('is_active')) {
            $query->where('sub_categories.is_active', $request->is_active);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('sub_categories.created_at', '>=', $dates[0])
                      ->whereDate('sub_categories.created_at', '<=', $dates[1]);
            } else {
                $query->whereDate('sub_categories.created_at', $dates[0]);
            }
        }

        $filename = "subcategories_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Subcategory Name', 'Parent Category', 'Products Count', 'Status', 'Created At']);

            $query->chunk(100, function($subcategories) use ($file) {
                foreach ($subcategories as $sub) {
                    $products_count = \App\Models\Product::where('subcategory_id', $sub->id)->count();
                    fputcsv($file, [
                        $sub->id,
                        $sub->name,
                        $sub->category_name,
                        $products_count,
                        $sub->is_active ? 'Active' : 'Inactive',
                        $sub->created_at
                    ]);
                }
            });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }



    public function store_subcategory(Request $request)
    {
        $request->validate([
            'name'        => 'required|min:3',
            'slug'        => 'required|unique:sub_categories,slug',
            'category_id' => 'required|exists:categories,id',
        ]);

          $fileName = null;

        if ($request->hasFile('image')) {
            $fileName = ImageHelper::compressImage($request->file('image'), 'uploads/subcategory/');
        }

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('hi');

        $subcategory = SubCategory::create([
            'name'        => $request->name,
            'name_ar'     => $trAr->translate($request->name),
            'name_ne'     => $trNe->translate($request->name),

            'slug'        => Str::slug($request->slug),
            'slug_ar'     => Str::slug($request->slug),
            'slug_ne'     => Str::slug($request->slug),

            'category_id' => $request->category_id,
            'description' => $request->description,
            'description_ar' => $request->description ? $trAr->translate($request->description) : null,
            'description_ne' => $request->description ? $trNe->translate($request->description) : null,
            'meta_title'           => $request->meta_title,
            'meta_description'     => $request->meta_description,

            'is_active'   => $request->has('is_active') ? 1 : 0,
            'image'       => $fileName,
        ]);

        if ($subcategory) {
            return redirect()->back()->with('success', 'Subcategory created successfully');
        }

        return redirect()->back()->with('error', 'Subcategory creation failed');
    }



    public function change_subcategory_status(Request $request)
    {
        $subcategory = SubCategory::find($request->id);
        if ($subcategory) {
            $subcategory->is_active = $request->status;
            $subcategory->save();
            return response()->json(['status' => true, 'message' => 'Status updated successfully']);
        }
        return response()->json(['status' => false, 'message' => 'Subcategory not found']);
    }



    public function edit_subcategory(Request $request, $slug)
    {    
        
        $categories = Category::select('*')->where('is_active', 1)->get();
        $subcategory = SubCategory::select('sub_categories.*')->where('slug', $slug)->first();
             $subcategory->image =ImageHelper::getSubCategoryImage($subcategory->image);
        return view('backend/admin/subcategory/edit-subcategory', compact('subcategory','categories'));
    }

    public function update_subcategory (Request $request)
    {
        $request->validate([
            'name' => 'required|min:3',
            'category_id' => 'required',
            'is_active' => 'required|in:0,1',
        ]);

        // $subcategory = SubCategory::findOrFail($request->subcategory_id);
       $subcategory = SubCategory::where('id',$request->subcategory_id)->first();
        $fileName = $subcategory->image;
        // ✅ Upload only if image exists
        if ($request->hasFile('image')) {

            // Delete old image (optional but recommended)
            if ($subcategory->image && file_exists(public_path('uploads/subcategory/' . $subcategory->image))) {
                unlink(public_path('uploads/subcategory/' . $subcategory->image));
            }

            $fileName = ImageHelper::compressImage($request->image, 'uploads/subcategory/');
        }

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('hi');

        $subcategory->update([
            'name'           => $request->name,
            'name_ar'        => $trAr->translate($request->name),
            'name_ne'        => $trNe->translate($request->name),

            'category_id'    => $request->category_id,
            'is_active'      => $request->is_active,
            'description'    => $request->description,
            'description_ar' => $request->description ? $trAr->translate($request->description) : null,
            'description_ne' => $request->description ? $trNe->translate($request->description) : null,
            'meta_title'     => $request->meta_title,
            'meta_description' => $request->meta_description,
            'image'          => $fileName,
        ]);

        return redirect()
            ->route('subcategory.list')
            ->with('success', 'SubCategory updated successfully');
    }


    
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use App\Helpers\ImageHelper;
use Stichoza\GoogleTranslate\GoogleTranslate;
use App\Models\Product;



class CategoryController extends Controller
{

      
    
    public function add_category(Request $request)
    {
        $langData   = trans('messages');
        $latestCategories = Category::latest()->get();
        $disableGlobalToastr = true;

        return view('backend/admin/category/add-category', compact('latestCategories', 'disableGlobalToastr'));
    }

    public function bulk_delete_category(Request $request)
    {
        $ids = $request->ids;
        if (!empty($ids)) {
            $categories = Category::whereIn('id', $ids)->get();
            foreach ($categories as $category) {
                // Delete image
                if ($category->image && file_exists(public_path('uploads/category/' . $category->image))) {
                    @unlink(public_path('uploads/category/' . $category->image));
                }
                // Delete related subcategories and child categories
                SubCategory::where('category_id', $category->id)->delete();
                ChildCategory::where('category_id', $category->id)->delete();
                $category->delete();
            }
            return response()->json(['status' => true, 'message' => 'Selected categories deleted successfully']);
        }
        return response()->json(['status' => false, 'message' => 'No categories selected']);
    }

    public function store_category(Request $request)
    {
        $request->validate([
            'name'  => 'required|min:2',
            'slug'  => 'required|unique:categories,slug',
            'image' => 'required|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $fileName = null;

        if ($request->hasFile('image')) {
            $fileName = ImageHelper::compressImage($request->file('image'), 'uploads/category/');
        }

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('ne');

        $category = Category::create([
            'name' => $request->name,
            'name_ar' => $trAr->translate($request->name),
            'name_ne' => $trNe->translate($request->name),

            'slug' => Str::slug($request->slug),
            'slug_ar' => Str::slug($request->slug),
            'slug_ne' => Str::slug($request->slug),

            'description' => $request->description,
            'description_ar' => $request->description ? $trAr->translate($request->description) : null,
            'description_ne' => $request->description ? $trNe->translate($request->description) : null,

            'meta_title' => $request->meta_title,
            'meta_title_ar' => $request->meta_title ? $trAr->translate($request->meta_title) : null,
            'meta_title_ne' => $request->meta_title ? $trNe->translate($request->meta_title) : null,

            'meta_description' => $request->meta_description,
            'meta_description_ar' => $request->meta_description ? $trAr->translate($request->meta_description) : null,
            'meta_description_ne' => $request->meta_description ? $trNe->translate($request->meta_description) : null,

            // COMMON FIELDS
            'is_active' => $request->has('is_active') ? 1 : 0,
            'image'     => $fileName,
        ]);

        
        return back()->with(
            $category ? 'success' : 'error',
            $category ? 'Category created successfully' : 'Category creation failed'
        );
    }

    public function edit_category(Request $request, $slug)
    {
        $cat_data = Category::where('slug', $slug)->first();
             $cat_data->image =ImageHelper::getCategoryImage($cat_data->image);

        return view('backend/admin/category/edit-category', compact('cat_data'));
    }

    public function update_category(Request $request)
    {

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|min:2',
            'slug'        => 'required',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $category = Category::findOrFail($request->category_id);

        $fileName = $category->image;
        // ✅ Upload only if image exists
        if ($request->hasFile('image')) {

            // Delete old image (optional but recommended)
            if ($category->image && file_exists(public_path('uploads/category/' . $category->image))) {
                unlink(public_path('uploads/category/' . $category->image));
            }

            $fileName = ImageHelper::compressImage($request->image, 'uploads/category/');
        }

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('ne');

        $category->update([
            'name'             => $request->name,
            'name_ar'          => $trAr->translate($request->name),
            'name_ne'          => $trNe->translate($request->name),

            'slug'             => Str::slug($request->slug),
            'slug_ar'          => Str::slug($request->slug),
            'slug_ne'          => Str::slug($request->slug),

            'description'      => $request->description,
            'description_ar'   => $request->description ? $trAr->translate($request->description) : null,
            'description_ne'   => $request->description ? $trNe->translate($request->description) : null,

            'meta_title'       => $request->meta_title,
            'meta_title_ar'    => $request->meta_title ? $trAr->translate($request->meta_title) : null,
            'meta_title_ne'    => $request->meta_title ? $trNe->translate($request->meta_title) : null,

            'meta_description' => $request->meta_description,
            'meta_description_ar' => $request->meta_description ? $trAr->translate($request->meta_description) : null,
            'meta_description_ne' => $request->meta_description ? $trNe->translate($request->meta_description) : null,

            'is_active'        => $request->has('is_active') ? 1 : 0,
            'image'            => $fileName, // 👈 old image stays if empty
        ]);

        // echo '<pre>';print_r($fileName);die;

        if ($category) {
            return back()->with('success', 'Category updated successfully');
        } else {
            return redirect()->back()->with('status', false)->with('message', 'Category updated failed');
        }
    }



    public function delete_category(Request $request)
    {
        $category = Category::find($request->id);
        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Record not found'
            ]);
        }

        // Delete image if exists
        if ($category->image && file_exists(public_path('uploads/category/' . $category->image))) {
            @unlink(public_path('uploads/category/' . $category->image));
        }

        // Delete related subcategories and child categories
        SubCategory::where('category_id', $category->id)->delete();
        ChildCategory::where('category_id', $category->id)->delete();

        $category->delete();

        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully'
        ]);
    }

    public function ajax_store_category(Request $request)
    {
        $request->validate([
            'name' => 'required|min:2',
        ]);

        try {
            $name = ucfirst(trim($request->name));
            $category = Category::where('name', $name)->first();

            if ($category) {
                return response()->json(['success' => true, 'category' => $category, 'message' => 'Category already exists.']);
            }

            $trAr = new GoogleTranslate('ar');
            $trNe = new GoogleTranslate('ne');

            $category = Category::create([
                'name' => $name,
                'name_ar' => $trAr->translate($name),
                'name_ne' => $trNe->translate($name),
                'slug' => Str::slug($name),
                'slug_ar' => Str::slug($name),
                'slug_ne' => Str::slug($name),
                'is_active' => 1,
            ]);

            return response()->json(['success' => true, 'category' => $category, 'message' => 'Category created successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create category: ' . $e->getMessage()]);
        }
    }

    public function category_list(Request $request)
    {
        $base_query = Category::withCount('products')->orderBy('updated_at', 'desc');

        if ($request->filled('search')) {
            $base_query->where('name', 'like', '%' . trim($request->search) . '%');
        }

        if ($request->filled('is_active')) {
            $base_query->where('is_active', $request->is_active);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $base_query->whereDate('created_at', '>=', $dates[0])
                           ->whereDate('created_at', '<=', $dates[1]);
            } else {
                $base_query->whereDate('created_at', $dates[0]);
            }
        }

        $cat_data = (clone $base_query)->paginate(12)->withQueryString();
        foreach ($cat_data as $key => $value) {
            $value->image = ImageHelper::getCategoryImage($value->image);
        }

        if ($request->ajax()) {
            return response()->json([
                'table' => view('backend.admin.category.category-table', compact('cat_data'))->render(),
                'pagination' => $cat_data->links()->render(),
                'info' => 'Showing ' . $cat_data->firstItem() . ' to ' . $cat_data->lastItem() . ' of ' . $cat_data->total() . ' ' . strtolower(__('messages.category'))
            ]);
        }

        $total_categories = Category::count();
        $active_categories = Category::where('is_active', 1)->count();
        $inactive_categories = Category::where('is_active', 0)->count();
        $total_products = Product::count();

        // Get all categories for the slider
        $all_categories = Category::withCount('products')
            ->orderBy('products_count', 'desc')
            ->get();
        
        foreach ($all_categories as $cat) {
            $cat->image = ImageHelper::getCategoryImage($cat->image);
        }

        return view('backend/admin/category/category', compact(
            'cat_data', 
            'total_categories', 
            'active_categories', 
            'inactive_categories', 
            'total_products',
            'all_categories'
        ));
    }

    public function export_categories(Request $request)
    {
        $query = Category::withCount('products')->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . trim($request->search) . '%');
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])
                      ->whereDate('created_at', '<=', $dates[1]);
            } else {
                $query->whereDate('created_at', $dates[0]);
            }
        }

        $categories = $query->get();

        $filename = "categories_export_" . date('Ymd_His') . ".csv";
        $file = fopen(public_path($filename), 'w');

        // Header row
        fputcsv($file, ['ID', 'Name', 'Description', 'Slug', 'Products Count', 'Status', 'Created At']);

        // Data rows
        foreach ($categories as $category) {
            fputcsv($file, [
                $category->id,
                $category->name,
                $category->description,
                $category->slug,
                $category->products_count,
                $category->is_active == 1 ? 'Active' : 'Inactive',
                $category->created_at,
            ]);
        }

        fclose($file);

        return response()->download(public_path($filename))->deleteFileAfterSend(true);
    }

    public function change_category_status(Request $request)
    {
        $category = Category::find($request->id);
        if ($category) {
            $category->is_active = $request->status;
            $category->save();
            return response()->json(['status' => true, 'message' => 'Status updated successfully']);
        }
        return response()->json(['status' => false, 'message' => 'Category not found']);
    }
}


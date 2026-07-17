<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use Illuminate\Support\Str;
use App\Helpers\ImageHelper;


class ChildCategoryController extends Controller
{
    public function add_child_category(Request $request)
    {
        $categories = Category::select('*')->where('is_active', 1)->get();
        $subcategories = SubCategory::select('*')->where('is_active', 1)->get();
        $disableGlobalToastr = true;

        return view('backend/admin/child_category/add-child-category', compact('categories', 'subcategories', 'disableGlobalToastr'));
    }

    public function bulk_delete_child_category(Request $request)
    {
        $ids = $request->ids;
        if (!empty($ids)) {
            ChildCategory::whereIn('id', $ids)->delete();
            return response()->json(['status' => true, 'message' => 'Selected child categories deleted successfully']);
        }
        return response()->json(['status' => false, 'message' => 'No child categories selected']);
    }

    public function delete_child_category(Request $request)
    {
        $childCategory = ChildCategory::find($request->id);
        if (!$childCategory) {
            return response()->json(['status' => false, 'message' => 'Record not found']);
        }

        $childCategory->delete();

        return response()->json(['status' => true, 'message' => 'Child category deleted successfully']);
    }

    public function getByCategory($category_id)
    {
        $subcategories = SubCategory::where('category_id', $category_id)
            ->where('is_active', 1)
            ->select('id', 'name')
            ->get();
        return response()->json($subcategories);
    }

    public function store_child_category(Request $request)
    {
        // ✅ Validation
        $request->validate([
            'name'            => 'required|min:2',
            'slug'            => 'nullable|unique:child_categories,slug',
            'category_id'     => 'required|exists:categories,id',
            'subcategory_id'  => 'required|exists:sub_categories,id',
            'description'     => 'nullable',
            'meta_title'      => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'is_active'       => 'required|in:0,1',
        ]);

        // ✅ Auto-generate slug if empty
        $slug = $request->slug
            ? Str::slug($request->slug)
            : Str::slug($request->name);

        // ✅ Create Child Category
        $data = [
            'category_id'      => $request->category_id,
            'subcategory_id'   => $request->subcategory_id,
            'name'             => $request->name,

            'slug'             => $slug,

            'description'      => $request->description,

            'meta_title'       => $request->meta_title,

            'meta_description' => $request->meta_description,

            'is_active'           => $request->is_active,
        ];
        // echo '<pre>';print_r($data);die;
        $childCategory = ChildCategory::create($data);

        // ✅ Redirect with message
        return back()
               ->with(
                $childCategory ? 'success' : 'error',
                $childCategory
                    ? 'Child category created successfully'
                    : 'Child category creation failed'
            );
    }

    public function ajax_store_child_category(Request $request)
    {
        $request->validate([
            'name' => 'required|min:2',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:sub_categories,id',
        ]);

        try {
            $name = ucfirst(trim($request->name));
            $child = ChildCategory::where('name', $name)
                ->where('category_id', $request->category_id)
                ->where('subcategory_id', $request->subcategory_id)
                ->first();

            if ($child) {
                return response()->json(['success' => true, 'child' => $child, 'message' => 'Child category already exists.']);
            }

            $child = ChildCategory::create([
                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'name' => $name,
                'slug' => Str::slug($name),
                'is_active' => 1,
            ]);

            return response()->json(['success' => true, 'child' => $child, 'message' => 'Child category created successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create child category: ' . $e->getMessage()]);
        }
    }

    public function child_category_list(Request $request)
    {
        $base_query = ChildCategory::select('child_categories.*', 'categories.name as category_name', 'categories.image as category_image', 'sub_categories.name as sub_categories_name')
            ->leftJoin('categories', 'categories.id', 'child_categories.category_id')
            ->leftJoin('sub_categories', 'sub_categories.id', 'child_categories.subcategory_id')
            ->orderBy('child_categories.updated_at', 'desc');

        if ($request->filled('search')) {
            $base_query->where('child_categories.name', 'like', '%' . trim($request->search) . '%');
        }

        if ($request->filled('is_active')) {
            $base_query->where('child_categories.is_active', $request->is_active);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $base_query->whereDate('child_categories.created_at', '>=', $dates[0])
                           ->whereDate('child_categories.created_at', '<=', $dates[1]);
            } else {
                $base_query->whereDate('child_categories.created_at', $dates[0]);
            }
        }

        $childCategories = $base_query->paginate(12)->withQueryString();

        if ($request->ajax()) {
            foreach ($childCategories as $child) {
                $child->products_count = \App\Models\Product::where('child_category_id', $child->id)->count();
                $child->image = ImageHelper::getCategoryImage($child->category_image);
            }
            return view('backend.admin.child_category.child-category-table', compact('childCategories'))->render();
        }

        foreach ($childCategories as $child) {
            $child->products_count = \App\Models\Product::where('child_category_id', $child->id)->count();
            $child->image = ImageHelper::getCategoryImage($child->category_image);
        }

        $total_child_categories = ChildCategory::count();
        $active_child_categories = ChildCategory::where('is_active', 1)->count();
        $total_products = \App\Models\Product::whereNotNull('child_category_id')->count();

        $featured_child_categories = ChildCategory::select('child_categories.*', 'categories.name as category_name', 'categories.image as category_image', 'sub_categories.name as sub_categories_name')
            ->leftJoin('categories', 'categories.id', 'child_categories.category_id')
            ->leftJoin('sub_categories', 'sub_categories.id', 'child_categories.subcategory_id')
            ->get()
            ->each(function($child) {
                $child->products_count = \App\Models\Product::where('child_category_id', $child->id)->count();
                $child->image = ImageHelper::getCategoryImage($child->category_image);
            })
            ->sortByDesc('products_count')
            ->take(4);

        return view('backend/admin/child_category/child-category', compact(
            'childCategories',
            'total_child_categories',
            'active_child_categories',
            'total_products',
            'featured_child_categories'
        ));
    }

    public function export_child_categories(Request $request)
    {
        $query = ChildCategory::select('child_categories.*', 'categories.name as category_name', 'sub_categories.name as sub_categories_name')
            ->leftJoin('categories', 'categories.id', 'child_categories.category_id')
            ->leftJoin('sub_categories', 'sub_categories.id', 'child_categories.subcategory_id')
            ->orderBy('child_categories.id', 'desc');

        if ($request->filled('search')) {
            $query->where('child_categories.name', 'like', '%' . trim($request->search) . '%');
        }

        if ($request->filled('is_active')) {
            $query->where('child_categories.is_active', $request->is_active);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('child_categories.created_at', '>=', $dates[0])
                      ->whereDate('child_categories.created_at', '<=', $dates[1]);
            } else {
                $query->whereDate('child_categories.created_at', $dates[0]);
            }
        }

        $filename = "child_categories_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Child Category Name', 'Parent Category', 'Subcategory', 'Products Count', 'Status', 'Created At']);

            $query->chunk(100, function($childCategories) use ($file) {
                foreach ($childCategories as $child) {
                    $products_count = \App\Models\Product::where('child_category_id', $child->id)->count();
                    fputcsv($file, [
                        $child->id,
                        $child->name,
                        $child->category_name,
                        $child->sub_categories_name,
                        $products_count,
                        $child->is_active ? 'Active' : 'Inactive',
                        $child->created_at
                    ]);
                }
            });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    public function edit_child_category(Request $request, $slug)
    {

        // echo '<pre>';print_r($slug);die;
        $childCategory = ChildCategory::where('slug', $slug)->first();
        $categories = Category::select('*')->where('is_active', 1)->get();
        $subcategories = SubCategory::select('*')->where('is_active', 1)->get();

        return view('backend/admin/child_category/edit-child-category', compact('childCategory', 'categories', 'subcategories'));
    }

    public function update_child_category(Request $request)
    {
        $request->validate([
            'child_category_id' => 'required|exists:child_categories,id',
            'category_id'       => 'required|exists:categories,id',
            'subcategory_id'    => 'required|exists:sub_categories,id',
            'name'              => 'required|string|min:2',
            'slug'              => 'nullable|unique:child_categories,slug,' . $request->child_category_id,
            'is_active'         => 'required|boolean',
        ]);

        $childCategory = ChildCategory::findOrFail($request->child_category_id);

        $childCategory->update([
            'category_id'       => $request->category_id,
            'subcategory_id'    => $request->subcategory_id,
            'name'              => $request->name,

            'slug'              => $request->slug
                ? Str::slug($request->slug)
                : Str::slug($request->name),

            'is_active'         => $request->is_active,
            'description'       => $request->description,

            'meta_title'        => $request->meta_title,

            'meta_description'  => $request->meta_description,
        ]);

        return redirect()
            ->route('child.category.list')
            ->with('success', 'Child category updated successfully');
    }

    public function change_child_category_status(Request $request)
    {
        $childCategory = ChildCategory::find($request->id);
        if ($childCategory) {
            $childCategory->is_active = $request->status;
            $childCategory->save();
            return response()->json(['status' => true, 'message' => 'Status updated successfully']);
        }
        return response()->json(['status' => false, 'message' => 'Child Category not found']);
    }





    public function get_child_categories($subCategoryId)
    {
        $subcategories = ChildCategory::where('subcategory_id', $subCategoryId)
            ->where('is_active', 1)
            ->select('id', 'name')
            ->get();

        return response()->json($subcategories);
    }
}

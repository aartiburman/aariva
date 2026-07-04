<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Support\Str;
use App\Helpers\ImageHelper;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use Stichoza\GoogleTranslate\GoogleTranslate;

class BrandController extends Controller
{
    public function add_brand(Request $request)
    {
        $categories = Category::select('*')->where('is_active', 1)->get();
        $subcategories = SubCategory::select('*')->where('is_active', 1)->get();
        $childcategory = ChildCategory::select('*')->where('is_active', 1)->get();
        $disableGlobalToastr = true;

        return view('backend/admin/brands/add-brand', compact('categories', 'subcategories', 'childcategory', 'disableGlobalToastr'));
    }

    public function bulk_delete_brand(Request $request)
    {
        $ids = $request->ids;
        if (!empty($ids)) {
            $brands = Brand::whereIn('id', $ids)->get();
            foreach ($brands as $brand) {
                if ($brand->logo && file_exists(public_path('uploads/brands/' . $brand->logo))) {
                    @unlink(public_path('uploads/brands/' . $brand->logo));
                }
                $brand->delete();
            }
            return response()->json(['status' => true, 'message' => 'Selected brands deleted successfully']);
        }
        return response()->json(['status' => false, 'message' => 'No brands selected']);
    }



    public function store_brand(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255|unique:brands,name',
            'logo'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'description'    => 'nullable|string',
           
            'status'         => 'required|in:0,1',
        ]);

        $logoName = null;
        if ($request->hasFile('logo')) {
            $logoName = ImageHelper::compressImage($request->logo, 'uploads/brands');
        }

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('hi');

        
        Brand::create([
            'name'           => $request->name,
            'name_ar'        => $trAr->translate($request->name),
            'name_ne'        => $trNe->translate($request->name),
            'category_id'    => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'childcategory_id' => $request->child_category_id,
            'slug'           => Str::slug($request->name),
            'logo'           => $logoName,
            'description'    => $request->description,
            'description_ar' => $trAr->translate($request->description??''),
            'description_ne' => $trNe->translate($request->description??''),
            'meta_title'     => $request->meta_title,
            'meta_description' => $request->meta_description,
            'status'         => $request->status,
        ]);

        return redirect('brand-list')->with('success', 'Brand added successfully');
    }

    public function brand_list(Request $request)
    {
        $query = Brand::withCount('products')->orderBy('updated_at', 'DESC');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . trim($request->search) . '%');
        }

        if ($request->filled('is_active')) {
            $query->where('status', $request->is_active);
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

        $brands = $query->paginate(12)->withQueryString();

        if ($request->ajax()) {
            foreach ($brands as $brand) {
                $brand->logo = ImageHelper::getBrandImage($brand->logo);
            }
            return view('backend.admin.brands.brand-table', compact('brands'))->render();
        }

        foreach ($brands as $key => $value) {
            $value->logo = ImageHelper::getBrandImage($value->logo);
        }

        $total_brands = Brand::count();
        $active_brands = Brand::where('status', 1)->count();
        $total_products = \App\Models\Product::whereNotNull('brand_id')->count();

        // Featured brands (top 4 by product count)
        $featured_brands = Brand::withCount('products')
            ->orderBy('products_count', 'desc')
            ->take(4)
            ->get();

        foreach ($featured_brands as $brand) {
            $brand->logo = ImageHelper::getBrandImage($brand->logo);
        }

        return view('backend/admin/brands/brand-list', compact(
            'brands',
            'total_brands',
            'active_brands',
            'total_products',
            'featured_brands'
        ));
    }

    public function export_brands(Request $request)
    {
        $query = Brand::orderBy('id', 'DESC');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . trim($request->search) . '%');
        }

        if ($request->filled('is_active')) {
            $query->where('status', $request->is_active);
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

        $filename = "brands_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Brand Name', 'Products Count', 'Status', 'Created At']);

            $query->chunk(100, function($brands) use ($file) {
                foreach ($brands as $brand) {
                    $products_count = \App\Models\Product::where('brand_id', $brand->id)->count();
                    fputcsv($file, [
                        $brand->id,
                        $brand->name,
                        $products_count,
                        $brand->status ? 'Active' : 'Inactive',
                        $brand->created_at
                    ]);
                }
            });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    public function edit_brand(Request $request, $slug)
    {

        $brand = Brand::where('slug', $slug)->first();

        $brand->logo = ImageHelper::getBrandImage($brand->logo);

         $categories = Category::select('*')->where('is_active', 1)->get();
        $subcategories = SubCategory::select('*')->where('is_active', 1)->get();
        $childcategory = ChildCategory::select('*')->where('is_active', 1)->get();


        return view('backend/admin/brands/edit-brand',compact('brand','categories', 'subcategories', 'childcategory',));
    }


    public function update_brand(Request $request)
    {
        
        $id = $request->id;
        $brand = Brand::findOrFail($id);
        $request->validate([
            'name'           => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'description'    => 'nullable|string',
            'status'         => 'required|in:0,1',
        ]);

        $logoName = $brand->logo;
        if ($request->hasFile('logo')) {
            if ($brand->logo && file_exists(public_path('uploads/brands/' . $brand->logo))) {
                unlink(public_path('uploads/brands/' . $brand->logo));
            }

            $logoName = ImageHelper::compressImage($request->logo, 'uploads/brands');
        }

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('hi');
        // echo '<pre>';print_r($trAr->translate($request->name));die;

      
      $data =   [
            'name'           => $request->name,
            'name_ar'        => $trAr->translate($request->name),
            'name_ne'        => $trNe->translate($request->name),
            'category_id'    => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'childcategory_id' => $request->child_category_id,
            'slug'           => Str::slug($request->name),
            'logo'           => $logoName,
            'description'    => $request->description,
            'description_ar' => $request->description ? $trAr->translate($request->description) : null,
            'description_ne' => $request->description ? $trNe->translate($request->description) : null,
            'meta_title'     => $request->meta_title,
            'meta_description' => $request->meta_description,
            'status'         => $request->status,
      ];
    //   echo "<pre>";print_r($data);die;
        $brand->update($data);
        return redirect('brand-list')->with('success', 'Brand updated successfully');
    }



    public function change_brand_status(Request $request)
    {
        $brand = Brand::find($request->id);
        if ($brand) {
            $brand->status = $request->status;
            $brand->save();
            return response()->json(['status' => true, 'message' => 'Status updated successfully']);
        }
        return response()->json(['status' => false, 'message' => 'Brand not found']);
    }


    public function delete_brand(Request $request)
    {
        $brand = Brand::findOrFail($request->id);

        // Delete logo file if exists
        if ($brand->logo && file_exists(public_path('uploads/brands/' . $brand->logo))) {
            @unlink(public_path('uploads/brands/' . $brand->logo));
        }

        $brand->delete();

        return response()->json([
            'status' => true,
            'message' => 'Brand deleted successfully'
        ]);
    }

    

    public function get_brands_by_category($categoryId)
    {
        $brands = Brand::where('category_id', $categoryId)
            ->where('status', 1)
            ->select('id', 'name')
            ->get();
        return response()->json($brands);
    }

    public function get_brands_by_subcategory($subcategoryId)
    {
        $subcategory = SubCategory::find($subcategoryId);
        $brands = Brand::where(function ($query) use ($subcategoryId, $subcategory) {
            $query->where('subcategory_id', $subcategoryId);
            if ($subcategory) {
                $query->orWhere('category_id', $subcategory->category_id);
            }
        })
            ->where('status', 1)
            ->select('id', 'name')
            ->get();
        return response()->json($brands);
    }

    public function get_brands_by_childcategory($childcategoryId)
    {
        $childcategory = ChildCategory::find($childcategoryId);
        $brands = Brand::where(function ($query) use ($childcategoryId, $childcategory) {
            $query->where('childcategory_id', $childcategoryId);
            if ($childcategory) {
                $query->orWhere('subcategory_id', $childcategory->subcategory_id);
                $subcategory = SubCategory::find($childcategory->subcategory_id);
                if ($subcategory) {
                    $query->orWhere('category_id', $subcategory->category_id);
                }
            }
        })
            ->where('status', 1)
            ->select('id', 'name')
            ->get();
        return response()->json($brands);
    }

public function createBrand(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'category_id' => 'nullable|exists:categories,id',
        'subcategory_id' => 'nullable|exists:sub_categories,id',
        'child_category_id' => 'nullable|exists:child_categories,id',
    ]);

    try {
        $name = ucfirst(trim($request->name));
        $brand = Brand::where('name', $name)->first();

        if ($brand) {
            // Update category associations if not set
            if (!$brand->category_id) $brand->category_id = $request->category_id;
            if (!$brand->subcategory_id) $brand->subcategory_id = $request->subcategory_id;
            if (!$brand->childcategory_id) $brand->childcategory_id = $request->child_category_id;
            $brand->save();

            return response()->json([
                'success' => true,
                'message' => 'Brand already exists and is now associated with this category!',
                'brand' => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                ]
            ]);
        }

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('hi');

        $brand = Brand::create([
            'name' => $name,
            'name_ar' => $trAr->translate($name),
            'name_ne' => $trNe->translate($name),
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'childcategory_id' => $request->child_category_id,
            'slug' => Str::slug($name),
            'status' => 1, // Default to active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Brand "' . $name . '" created successfully!',
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to create brand: ' . $e->getMessage()
        ], 500);
    }
}

}

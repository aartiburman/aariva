<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Str;
use Nette\Utils\Image;
use App\Helpers\ImageHelper;

class BannerController extends Controller
{
    public function banner_list(Request $request)
    {
        $banners = Banner::orderBy('order_by', 'ASC')->orderBy('updated_at', 'DESC')->paginate(10)->withQueryString();
        foreach ($banners as $banner) {
            $image = $banner->image;
            $bannerImages = [];

            // Check if it's JSON (old format) or string (new format)
            $decoded = json_decode($image, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                foreach ($decoded as $img) {
                    $bannerImages[] = [
                        'name' => $img,
                        'url'  => ImageHelper::getBannerImage($img)
                    ];
                }
            } elseif ($image) {
                $bannerImages[] = [
                    'name' => $image,
                    'url'  => ImageHelper::getBannerImage($image)
                ];
            }

            // Replace banner images with structured data
            $banner->image_data = $bannerImages;
        }
        return view('backend/admin/banner/banner-list', compact('banners'));
    }

    public function add_banner(Request $request)
    {
        return view('backend/admin/banner/add-banner');
    }

    public function store_banner(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'position' => 'required|string',
        ]);

        if ($request->image_type === 'multiple') {
            $request->validate([
                'images'    => 'required|array|min:1',
                'images.*'  => 'image|mimes:jpg,jpeg,png,webp|max:4096',
            ]);
        } else {
            $request->validate([
                'image'    => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
            ]);
        }

        /* =========================
         * CHECK BANNER LIMITS
         * ========================= */
        $limits = [
            'top'            => 5,
            'deal'           => 2,
            'middle'         => 1,
            'bottom'         => 1,
            'promo'          => 10,
            'wishlist'       => 1,
            'cart'           => 1,
            'product_detail' => 1,
        ];

        $position = $request->position;
        if (isset($limits[$position])) {
            $count = Banner::where('position', $position)->count();
            if ($count >= $limits[$position]) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "The $position banner has reached its limit of {$limits[$position]} records.");
            }
        }

        /* =========================
         * UPLOAD IMAGE(S)
         * ========================= */
        $imageValue = null;

        if ($request->image_type === 'multiple' && $request->hasFile('images')) {
            $uploaded = [];
            foreach ($request->file('images') as $img) {
                $uploaded[] = ImageHelper::compressImage($img, 'uploads/banners');
            }
            $imageValue = json_encode($uploaded);
        } elseif ($request->hasFile('image')) {
            $imageValue = ImageHelper::compressImage($request->file('image'), 'uploads/banners');
        }

        /* =========================
     * STORE BANNER
     * ========================= */
        $baseSlug = Str::slug($request->title);
        $slug = $baseSlug;
        $count = 1;
        while (Banner::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '_0' . $count;
            $count++;
        }

        $data = [
            'slug'       => $slug,
            'image'      => $imageValue,
            'link_type'  => $request->link_type,
            'link_id'    => $request->link_id,
            'link_url'   => $request->link_url,
            'position'   => $request->position,
            'order_by'   => $request->order_by ?? 0,
            'status'     => $request->status,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
        ];
        Banner::create($data);

        return redirect('banner-list')->with('success', 'Banner added successfully');
    }


    public function edit_banner(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $image = $banner->image;
        $bannerImages = [];

        // Check if it's JSON (old format) or string (new format)
        $decoded = json_decode($image, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            foreach ($decoded as $img) {
                $bannerImages[] = [
                    'name' => $img,
                    'url'  => ImageHelper::getBannerImage($img)
                ];
            }
        } elseif ($image) {
            $bannerImages[] = [
                'name' => $image,
                'url'  => ImageHelper::getBannerImage($image)
            ];
        }

        // Replace banner images with structured data
        $banner->image_data = $bannerImages;

        return view('backend/admin/banner/edit-banner', compact('banner'));
    }

    public function update_banner(Request $request)
    {
        $banner = Banner::findOrFail($request->id);

        /* =========================
         * CHECK BANNER LIMITS (IF POSITION CHANGED)
         * ========================= */
        $limits = [
            'top'            => 5,
            'deal'           => 2,
            'middle'         => 1,
            'bottom'         => 1,
            'promo'          => 10,
            'wishlist'       => 1,
            'cart'           => 1,
            'product_detail' => 1,
        ];

        $newPosition = $request->position;
        if ($newPosition !== $banner->position && isset($limits[$newPosition])) {
            $count = Banner::where('position', $newPosition)->count();
            if ($count >= $limits[$newPosition]) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "The $newPosition banner has reached its limit of {$limits[$newPosition]} records.");
            }
        }

        /* =========================
         * HANDLE NEW UPLOADED IMAGE(S)
         * ========================= */
        $imageValue = $banner->image;

        if ($request->image_type === 'multiple' && $request->hasFile('images')) {
            $existing = json_decode($banner->image, true);
            $images = is_array($existing) ? $existing : [];
            foreach ($request->file('images') as $img) {
                $images[] = ImageHelper::compressImage($img, 'uploads/banners');
            }
            $imageValue = json_encode($images);
        } elseif ($request->hasFile('image')) {
            if ($banner->image && !is_array(json_decode($banner->image, true))) {
                $oldPath = public_path('uploads/banners/' . $banner->image);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $imageValue = ImageHelper::compressImage($request->file('image'), 'uploads/banners');
        }

        /* =========================
 * UPDATE BANNER
 * ========================= */
        $baseSlug = Str::slug($request->title);
        $slug = $baseSlug;
        $count = 1;
        while (Banner::where('slug', $slug)->where('id', '!=', $banner->id)->exists()) {
            $slug = $baseSlug . '_0' . $count;
            $count++;
        }

        $banner->update([
            'title'      => $request->title,
            'slug'       => $slug,
            'image'      => $imageValue,
            'link_type'  => $request->link_type,
            'link_url'   => $request->link_url,
            'position'   => $request->position,
            'order_by'   => $request->order_by ?? 0,
            'status'     => $request->status,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
        ]);


        return redirect('banner-list')->with('success', 'Banner updated successfully');
    }


    public function change_banner_status(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:banners,id',
            'status' => 'required|in:0,1',
        ]);

        $banner = Banner::find($request->id);
        $banner->status = $request->status;
        $banner->save();

        return response()->json([
            'status' => true,
            'message' => 'Banner status updated successfully'
        ]);
    }

    public function delete_banner(Request $request)
    {
        $banner = Banner::findOrFail($request->id);

        if ($banner->image && file_exists(public_path('uploads/banners/' . $banner->image))) {
            @unlink(public_path('uploads/banners/' . $banner->image));
        }

        $banner->delete();

        return response()->json([
            'status' => true,
            'message' => 'Banner deleted successfully'
        ]);
    }

    public function delete_banner_image(Request $request)
    {
        $banner = Banner::findOrFail($request->banner_id);
        $imageName = $request->image_name;

        $images = json_decode($banner->image, true);

        if (($key = array_search($imageName, $images)) !== false) {
            unset($images[$key]);

            // Delete physical file
            $filePath = public_path('uploads/banners/' . $imageName);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            $banner->image = json_encode(array_values($images));
            $banner->save();

            return response()->json([
                'status' => true,
                'message' => 'Image deleted successfully'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Image not found'
        ]);
    }

    public function bulk_delete_banner(Request $request)
    {
        $ids = $request->ids;
        if (!empty($ids)) {
            $banners = Banner::whereIn('id', $ids)->get();
            foreach ($banners as $banner) {
                // Delete images
                $image = $banner->image;
                $decoded = json_decode($image, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    foreach ($decoded as $img) {
                        $filePath = public_path('uploads/banners/' . $img);
                        if (file_exists($filePath)) {
                            @unlink($filePath);
                        }
                    }
                } elseif ($image) {
                     $filePath = public_path('uploads/banners/' . $image);
                     if (file_exists($filePath)) {
                         @unlink($filePath);
                     }
                }
                $banner->delete();
            }
            return response()->json(['status' => true, 'message' => 'Banners deleted successfully']);
        }
        return response()->json(['status' => false, 'message' => 'No records selected']);
    }

    public function bulk_banner_status(Request $request)
    {
        $ids = $request->ids;
        $status = $request->status;
        if (!empty($ids)) {
            Banner::whereIn('id', $ids)->update(['status' => $status]);
            return response()->json(['status' => true, 'message' => 'Banners status updated successfully']);
        }
        return response()->json(['status' => false, 'message' => 'No records selected']);
    }

    public function export_banners(Request $request)
    {
        $ids = $request->ids;
        $query = Banner::orderBy('order_by', 'ASC');
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }
        $banners = $query->get();

        $csvFileName = 'banners_export_' . date('Y-m-d') . '.csv';
        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$csvFileName\"",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($banners) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Title', 'Position', 'Status', 'Start Date', 'End Date', 'Link URL']);

            foreach ($banners as $banner) {
                fputcsv($handle, [
                    $banner->id,
                    $banner->title,
                    $banner->position,
                    $banner->status ? 'Active' : 'Inactive',
                    $banner->start_date ? $banner->start_date->format('Y-m-d') : '',
                    $banner->end_date ? $banner->end_date->format('Y-m-d') : '',
                    $banner->link_url
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}

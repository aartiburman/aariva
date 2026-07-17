<?php

namespace App\Helpers;

use Session;
use App;
use Carbon\Carbon;
//use Image;
use url;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;


class ImageHelper
{
  public static $getCategoryImagePath  = 'public/uploads/category/';
  public static $getSubCategoryImagePath  = 'public/uploads/subcategory/';

  public static $getVendorsImagePath  = 'public/uploads/vendors/';
  public static $getUserImagePath  = 'public/uploads/users/';
  public static $productImagePath  = 'public/uploads/products/';
  public static $getBrandImagePath  = 'public/uploads/brands/';
  public static $getProfileImagePath  = 'public/uploads/images/profile/';
  public static $getVendorDocImagePath  = 'public/uploads/vendors/documents/';
  public static $getBannerDocImagePath  = 'public/uploads/banners/';
  public static $getVendorsCancelledChequeImagePath  = 'public/uploads/vendors/cancelled_cheque/';
  public static $getWebsiteLogoImagePath  = 'public/uploads/settings/';
  public static $getRefundImagePath  = 'public/uploads/refunds/';
  public static $getDirhamImage  = 'public/currency/dirham.png';
  public static $getINRImage  = 'public/currency/rupee.png';
  public static $getPaymentGatewayImagePath  = 'public/uploads/payment_gateways/';
  public static $getPaymentGatewayLogoPath  = 'public/uploads/payment_gateways/';
  public static $getBlogImagePath  = 'public/uploads/blogs/';
  public static $getTicketAttachmentPath  = 'public/uploads/ticket/';
  public static $NoImage = 'public/backend/no_image.jpg';

  /**
   * Apply watermark (website logo) to an image at a random position
   * @param resource $image GD image resource
   * @return resource Watermarked GD image resource
   */
  private static function applyWatermark($image)
  {
    $logoName = GeneralSetting::where('key', 'website_logo_dark')->value('value');
    if (!$logoName) {
      $logoName = GeneralSetting::where('key', 'website_logo_light')->value('value');
      if (!$logoName) return $image;
    }

    $logoPath = public_path('uploads/settings/' . $logoName);
    if (!file_exists($logoPath)) return $image;

    $logoExt = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
    switch ($logoExt) {
      case 'jpeg':
      case 'jpg':
        $logoImg = @\imagecreatefromjpeg($logoPath);
        break;
      case 'png':
        $logoImg = @\imagecreatefrompng($logoPath);
        \imagealphablending($logoImg, false);
        \imagesavealpha($logoImg, true);
        break;
      case 'webp':
        $logoImg = @\imagecreatefromwebp($logoPath);
        break;
      default:
        return $image;
    }

    if (!$logoImg) return $image;

    $imgWidth = \imagesx($image);
    $imgHeight = \imagesy($image);
    $logoWidth = \imagesx($logoImg);
    $logoHeight = \imagesy($logoImg);

    $watermarkWidth = intval($imgWidth * 0.10);
    $watermarkHeight = intval($logoHeight * ($watermarkWidth / $logoWidth));
    if ($watermarkHeight < 1) $watermarkHeight = 1;

    $watermarkImg = \imagecreatetruecolor($watermarkWidth, $watermarkHeight);
    \imagealphablending($watermarkImg, false);
    \imagesavealpha($watermarkImg, true);
    \imagecopyresampled($watermarkImg, $logoImg, 0, 0, 0, 0, $watermarkWidth, $watermarkHeight, $logoWidth, $logoHeight);

    $padding = 15;
    $maxX = $imgWidth - $watermarkWidth - $padding;
    $maxY = $imgHeight - $watermarkHeight - $padding;
    $x = \rand($padding, max($padding, $maxX));
    $y = \rand($padding, max($padding, $maxY));

    \imagealphablending($image, true);
    \imagecopymerge($image, $watermarkImg, $x, $y, 0, 0, $watermarkWidth, $watermarkHeight, 50);

    \imagedestroy($logoImg);
    \imagedestroy($watermarkImg);

    return $image;
  }

  /**
   * Compress and save an image using GD library
   * @param \Illuminate\Http\UploadedFile $file
   * @param string $path Target directory path
   * @param int $quality Compression quality (0-100)
   * @param int|null $maxWidth Optional max width to resize
   * @param bool $addWatermark Whether to apply watermark
   * @return string Filename of the saved image
   */
  public static function compressImage($file, $path, $quality = 60, $maxWidth = 1200, $addWatermark = false)
  {
    $extension = $file->getClientOriginalExtension();
    $filename = time() . '_' . uniqid() . '.' . $extension;
    $targetPath = public_path($path);

    if (!file_exists($targetPath)) {
      mkdir($targetPath, 0777, true);
    }

    $fullPath = $targetPath . '/' . $filename;

    // Check if GD functions are available
    if (!function_exists('imagecreatefromjpeg')) {
      $file->move($targetPath, $filename);
      return $filename;
    }

    // Load image based on extension
    switch (strtolower($extension)) {
      case 'jpeg':
      case 'jpg':
        $image = \imagecreatefromjpeg($file->getRealPath());
        break;
      case 'png':
        $image = \imagecreatefrompng($file->getRealPath());
        // Preserve transparency for PNG
        \imagealphablending($image, false);
        \imagesavealpha($image, true);
        break;
      case 'webp':
        $image = \imagecreatefromwebp($file->getRealPath());
        break;
      default:
        // If not supported by GD for specific compression, just move it
        $file->move($targetPath, $filename);
        return $filename;
    }

    if ($image) {
      // Optional: Resize if width is too large
      $width = \imagesx($image);
      $height = \imagesy($image);

      if ($maxWidth && $width > $maxWidth) {
        $newWidth = $maxWidth;
        $newHeight = floor($height * ($maxWidth / $width));
        $tmpImage = \imagecreatetruecolor($newWidth, $newHeight);

        if (strtolower($extension) == 'png') {
          \imagealphablending($tmpImage, false);
          \imagesavealpha($tmpImage, true);
        }

        \imagecopyresampled($tmpImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        \imagedestroy($image);
        $image = $tmpImage;
      }

      // Apply watermark if requested
      if ($addWatermark) {
        $image = static::applyWatermark($image);
      }

      // Save compressed image
      switch (strtolower($extension)) {
        case 'jpeg':
        case 'jpg':
          \imagejpeg($image, $fullPath, $quality);
          break;
        case 'png':
          // PNG quality is 0-9 (9 is max compression)
          $pngQuality = floor((100 - $quality) / 10);
          \imagepng($image, $fullPath, $pngQuality);
          break;
        case 'webp':
          \imagewebp($image, $fullPath, $quality);
          break;
      }

      \imagedestroy($image);
      return $filename;
    }

    // Fallback
    $file->move($targetPath, $filename);
    return $filename;
  }

  

   
  public static function getWebsiteLogo($image, $absolute = false)
  {
    if ($image) {
      $fullPath = public_path(str_replace('public/', '', static::$getWebsiteLogoImagePath) . $image);
      if (file_exists($fullPath)) {
        $path = str_replace('public/', '', static::$getWebsiteLogoImagePath);
        return $absolute ? url($path . $image) : asset($path . $image);
      }
    }
    return $absolute ? url(str_replace('public/', '', static::$NoImage)) : asset(str_replace('public/', '', static::$NoImage));
  }


  public static function getCategoryImage($image)
  {
    if ($image) {
      if (file_exists(static::$getCategoryImagePath . $image)) {
        $path = str_replace('public/', '', static::$getCategoryImagePath);
        return asset($path . $image);
      }
    }
    return asset(str_replace('public/', '', static::$NoImage));
  }

  public static function getSubCategoryImage($image)
  {
    if ($image) {
      if (file_exists(static::$getSubCategoryImagePath . $image)) {
        $path = str_replace('public/', '', static::$getSubCategoryImagePath);
        return asset($path . $image);
      }
    }
    return asset(str_replace('public/', '', static::$NoImage));
  }


  public static function getDirhamImage()
  {
    return asset(str_replace('public/', '', static::$getDirhamImage));
  }

  public static function getINRImage()
  {
    return asset(str_replace('public/', '', static::$getINRImage));
  }

  public static function getPaymentGatewayImage($image)
  {
    if ($image) {
      if (file_exists(static::$getPaymentGatewayImagePath . $image)) {
        $path = str_replace('public/', '', static::$getPaymentGatewayImagePath);
        return asset($path . $image);
      }
    }
    return asset(str_replace('public/', '', static::$NoImage));
  }

  public static function getPaymentGatewayLogo($logo)
  {
    if ($logo) {
      if (file_exists(static::$getPaymentGatewayLogoPath . $logo)) {
        $path = str_replace('public/', '', static::$getPaymentGatewayLogoPath);
        return asset($path . $logo);
      }
    }
    return asset(str_replace('public/', '', static::$NoImage));
  }


  public static function getBannerImage($image)
  {
    if ($image) {
      if (file_exists(static::$getBannerDocImagePath . $image)) {
        $path = str_replace('public/', '', static::$getBannerDocImagePath);
        return asset($path . $image);
      }
    }
    return asset(str_replace('public/', '', static::$NoImage));
  }

  public static function getRefundImage($image)
  {
    if ($image) {
      if (file_exists(static::$getRefundImagePath . $image)) {
        $path = str_replace('public/', '', static::$getRefundImagePath);
        return asset($path . $image);
      }
    }
    return asset(str_replace('public/', '', static::$NoImage));
  }

  public static function getProfileImage($image)
  {
    if ($image) {
      if (file_exists(static::$getProfileImagePath . $image)) {
        $path = str_replace('public/', '', static::$getProfileImagePath);
        return asset($path . $image);
      }
    }
    return asset(str_replace('public/', '', static::$NoImage));
  }


  public static function getVendorsCancelledChequeImage($image)
  {
    if ($image) {
      if (file_exists(static::$getVendorsCancelledChequeImagePath . $image)) {
        $path = str_replace('public/', '', static::$getVendorsCancelledChequeImagePath);
        return asset($path . $image);
      }
    }
    return asset(str_replace('public/', '', static::$NoImage));
  }

  public static function extractImage($imageData)
  {
    if (!$imageData) return null;

    if (str_starts_with($imageData, '[')) {
      $decoded = json_decode($imageData, true);
      return is_array($decoded) ? ($decoded[0] ?? null) : null;
    }

    if (str_contains($imageData, ',')) {
      return explode(',', $imageData)[0];
    }

    return $imageData;
  }

  public static $productSingularImagePath  = 'public/uploads/product/';

  
  public static function getProductImage($image)
  {
    if ($image) {
      $image = static::extractImage($image);

      // Try plural folder first
      if (file_exists(static::$productImagePath . $image)) {
        $path = str_replace('public/', '', static::$productImagePath);
        return asset($path . $image);
      }

      // Try singular folder
      if (file_exists(static::$productSingularImagePath . $image)) {
        $path = str_replace('public/', '', static::$productSingularImagePath);
        return asset($path . $image);
      }

      // If it's already a path, try checking it directly
      if (file_exists($image)) {
        return asset(str_replace('public/', '', $image));
      }

      if (file_exists('public/' . $image)) {
        return asset($image);
      }
    }
    return asset(str_replace('public/', '', static::$NoImage));
  }


  public static function getUserImage($image)
  {
    // echo static::$getVendorsImagePath . $image;die;
    if ($image) {
      if (file_exists(static::$getUserImagePath . $image)) {
        $path = str_replace('public/', '', static::$getUserImagePath);
        return asset($path . $image);
      }
    }
    return asset(str_replace('public/', '', static::$NoImage));
  }

  public static function getVendorsImage($image)
  {
    if ($image) {
      if (file_exists(static::$getVendorsImagePath . $image)) {
        $path = str_replace('public/', '', static::$getVendorsImagePath);
        return asset($path . $image);
      }
    }
    return asset(str_replace('public/', '', static::$NoImage));
  }

  public static function getBrandImage($image)
  {
    if ($image) {
      if (file_exists(static::$getBrandImagePath . $image)) {
        $path = str_replace('public/', '', static::$getBrandImagePath);
        return asset($path . $image);
      }
    }
    return asset(str_replace('public/', '', static::$NoImage));
  }

  public static function getVendorDocImage($image)
  {
    if ($image) {
      if (file_exists(static::$getVendorDocImagePath . $image)) {
        $path = str_replace('public/', '', static::$getVendorDocImagePath);
        return asset($path . $image);
      }
    }
    return asset(str_replace('public/', '', static::$NoImage));
  }

  public static function getBlogImage($image)
  {
    if ($image) {
      if (file_exists(static::$getBlogImagePath . $image)) {
        $path = str_replace('public/', '', static::$getBlogImagePath);
        return asset($path . $image);
      }
    }
    return asset(str_replace('public/', '', static::$NoImage));
  }

  public static function getTicketAttachment($image)
  {
    if ($image) {
      if (file_exists(static::$getTicketAttachmentPath . $image)) {
        $path = str_replace('public/', '', static::$getTicketAttachmentPath);
        return asset($path . $image);
      }
    }
    return asset(str_replace('public/', '', static::$NoImage));
  }

  public static function upload(string $path, $file): string
  {
    // Ensure directory exists
    // print_r($path);die;
    if (!File::exists(public_path($path))) {
      File::makeDirectory(public_path($path), 0755, true);
    }

    // Generate unique filename

    $fileName = time() . '.' . Str::random(10) . '.' . $file->extension();
    $file->move(public_path('uploads/products/'), $fileName);


    // Move file

    // Return relative path (store in DB)
    return $fileName;
  }

  public static function uploadImage($file, $path)
  {
    if ($file) {
      $fileName = time() . '.' . Str::random(10) . '.' . $file->extension();
      $file->move(public_path($path), $fileName);
      return $fileName;
    }
    return null;
  }
}

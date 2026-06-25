<?php

namespace App\Helpers;

use App\Models\Offer;
use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\GeneralSetting;
use Carbon\Carbon;

class PriceCalculationHelper
{
    /**
     * Calculate price for a single item (product or variant)
     * Includes: Product/Variant Discount, Offer, and Campaign
     */
    public static function calculateItemPrice($product, $variantId = null, $qty = 1)
    {
        if (is_numeric($product)) {
            $product = Product::with('vendor:id,vendor_tax')->find($product);
        }

        if (!$product) {
            return null;
        }

        // Ensure vendor is loaded if $product was passed as a model
        if (!$product->relationLoaded('vendor')) {
            $product->load('vendor:id,vendor_tax');
        }

        $variant = null;
        if ($variantId) {
            $variant = ProductVariant::find($variantId);
        }

        $unitPrice = (float) ($variant->price ?? $product->price);
        $today = Carbon::now();

        // 1. Calculate Product/Variant Level Discount
        $productUnitDiscount = 0.0;
        if ($variant) {
            if (($variant->discount_type ?? "") === "off") {
                $productUnitDiscount = (float) ($variant->discount_value ?? 0);
            } elseif (in_array(($variant->discount_type ?? ""), ["percent", "%"])) {
                $productUnitDiscount = round($unitPrice * ((float) ($variant->discount_value ?? 0)) / 100, 2);
            }
        }

        $priceAfterProductDiscount = max(0, $unitPrice - $productUnitDiscount);

        // 2. Check for Offers
        $offerUnitDiscount = 0.0;
        $offerIdField = $product->offer_id ?? null;
        $offerIds = [];
        if ($offerIdField) {
            if (is_numeric($offerIdField)) {
                $offerIds = [(int)$offerIdField];
            } else {
                $decoded = is_string($offerIdField) ? json_decode($offerIdField, true) : (is_array($offerIdField) ? $offerIdField : [$offerIdField]);
                $offerIds = array_filter((array)$decoded);
            }
        }

        if (!empty($offerIds)) {
            $offer = Offer::whereIn('id', $offerIds)
                ->where('status', 1)
                ->where(function ($q) use ($today) {
                    $q->whereNull('valid_from')->orWhere('valid_from', '<=', $today);
                })
                ->where(function ($q) use ($today) {
                    $q->whereNull('valid_until')->orWhere('valid_until', '>=', $today);
                })
                ->first();

            if ($offer) {
                if (in_array((string)$offer->type, ['1', 'percent', '%'])) { // Percent
                    $offerUnitDiscount = round($priceAfterProductDiscount * ((float)$offer->value) / 100, 2);
                } else { // Fixed
                    $offerUnitDiscount = round((float)$offer->value, 2);
                }
            }
        }

        // 3. Check for Campaigns
        $campaignUnitDiscount = 0.0;
        $activeCampaign = null;
        
        $activeCampaign = $product->campaigns()
            ->where('campaigns.is_active', 1)
            ->where('campaigns.status', 1)
            ->where(function ($q) use ($today) {
                $q->whereNull('campaigns.start_date')->orWhere('campaigns.start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('campaigns.end_date')->orWhere('campaigns.end_date', '>=', $today);
            })
            ->wherePivot('status', 1) // campaign_products.status = 1
            ->whereHas('vendors', function ($q) use ($product) {
                $q->where('users.id', $product->vendor_id)
                    ->where('campaign_vendors.active', 1)
                    ->whereIn('campaign_vendors.status', ['approved', '1'])
                    // Check budget: total must be greater than spent if budget is set
                    ->where(function ($qb) {
                        $qb->whereColumn('campaign_vendors.budget_spent', '<', 'campaign_vendors.budget_total')
                           ->orWhere('campaign_vendors.budget_total', '<=', 0);
                    });
            })
            ->with('offer')
            ->first();

        if ($activeCampaign) {
            // If campaign exists, it takes precedence or overlaps with offer
            if ($activeCampaign->offer_id && $activeCampaign->offer) {
                // Campaign related with offer: overlap campaign discount with offer discount
                if (in_array((string)$activeCampaign->offer->type, ['1', 'percent', '%'])) {
                    $campaignUnitDiscount = round($priceAfterProductDiscount * ((float)$activeCampaign->offer->value) / 100, 2);
                } else {
                    $campaignUnitDiscount = round((float)$activeCampaign->offer->value, 2);
                }
                // When campaign has an offer, we zero out the standalone offer to "overlap" / replace it
                $offerUnitDiscount = 0.0;
            } else {
                // Campaign has no relation with offer: take campaign discount
                $campaignUnitDiscount = round($priceAfterProductDiscount * ((float)$activeCampaign->discount_percent) / 100, 2);
                // Also zero out standalone offer if campaign takes precedence
                $offerUnitDiscount = 0.0;
            }
        }

        $priceAfterAllDiscounts = max(0, $priceAfterProductDiscount - $offerUnitDiscount - $campaignUnitDiscount);

        // When didn't find vendor_tax should take vat_percent in general setting
        // When get vendor_tax so take both vat addition
        $vendorTax = (float) ($product->vendor->vendor_tax ?? 0.0);
        $vatPercent = (float) (GeneralSetting::where('key', 'vat_percent')->value('value') ?? 0.0);
        
        if ($vendorTax > 0) {
            $taxRate = $vendorTax + $vatPercent;
        } else {
            $taxRate = $vatPercent;
        }

        return [
            'product_id' => $product->id,
            'variant_id' => $variantId,
            'vendor_id' => $product->vendor_id,
            'campaign_id' => $activeCampaign->id ?? null,
            'qty' => (int)$qty,
            'unit_price' => round($unitPrice, 2),
            'product_unit_discount' => round($productUnitDiscount, 2),
            'offer_unit_discount' => round($offerUnitDiscount, 2),
            'campaign_unit_discount' => round($campaignUnitDiscount, 2),
            'price_after_discounts' => round($priceAfterAllDiscounts, 2),
            'total_line_cost' => round($priceAfterAllDiscounts * $qty, 2),
            'vendor_tax' => $taxRate,
            'tax_amount' => round(($priceAfterAllDiscounts * $qty) * ($taxRate / 100), 2),
            'vendor_delivery' => (bool) ($product->vendor_delivery ?? false)
        ];
    }

    public static function calculateShippingFee($cityId = null, $subtotal = 0.0, $userCityId = null)
    {
        $shippingRate = (float) (GeneralSetting::where('key', 'shipping_charge')->value('value') ?? 100.0);
        $freeShippingMin = (float) (GeneralSetting::where('key', 'free_shipping_min')->value('value') ?? 500.0);
        $freeForSameCity = (bool) (GeneralSetting::where('key', 'free_shipping_same_city')->value('value') ?? true);

        if (!$cityId) {
            return $subtotal >= $freeShippingMin ? 0.0 : $shippingRate;
        }

        if ($freeForSameCity && $userCityId && $cityId == $userCityId) {
            return 0.0;
        }

        return $subtotal >= $freeShippingMin ? 0.0 : $shippingRate;
    }

    /**
     * Calculate summary for a list of items
     * Includes: Subtotal, Total Discounts, Coupon, Taxes, Delivery
     */
    public static function calculateSummary($itemsData, $couponCode = null, $cityId = null, $userCityId = null)
    {
        $subTotalBeforeAnyDiscount = 0.0;
        $totalProductDiscount = 0.0;
        $totalOfferDiscount = 0.0;
        $totalCampaignDiscount = 0.0;
        $totalCouponDiscount = 0.0;
        $totalTaxes = 0.0;
        $hasVendorDelivery = false;
        
        $processedItems = [];

        foreach ($itemsData as $item) {
            // Handle if $item is a model or an array of data
            if ($item instanceof \App\Models\Cart) {
                $calc = self::calculateItemPrice($item->product, $item->variant_id, $item->qty);
            } elseif (is_object($item) && isset($item->product_id)) {
                $calc = self::calculateItemPrice($item->product_id, $item->variant_id, $item->qty);
            } else {
                // If it's already calculated data
                $calc = (array)$item;
            }

            if (!$calc) continue;

            if (!empty($calc['vendor_delivery'])) {
                $hasVendorDelivery = true;
            }

            $subTotalBeforeAnyDiscount += ($calc['unit_price'] * $calc['qty']);
            $totalProductDiscount += ($calc['product_unit_discount'] * $calc['qty']);
            $totalOfferDiscount += ($calc['offer_unit_discount'] * $calc['qty']);
            $totalCampaignDiscount += ($calc['campaign_unit_discount'] * $calc['qty']);
            $totalTaxes += $calc['tax_amount'];

            $processedItems[] = $calc;
        }

        $grandSubTotal = round($subTotalBeforeAnyDiscount - $totalProductDiscount - $totalOfferDiscount - $totalCampaignDiscount, 2);

        // Dynamic Shipping Fee Logic (Passed grandSubTotal for free delivery check)
        $shippingFee = self::calculateShippingFee($cityId, $grandSubTotal, $userCityId);

        if ($hasVendorDelivery) {
            $shippingFee = 0.0;
        }

        // Calculate Total Before Coupon (Subtotal + Shipping + Taxes)
        $totalBeforeCoupon = $grandSubTotal + $shippingFee + $totalTaxes;

        // Coupon Logic - Deduct from final total (after VAT/Tax and other deductions)
        $appliedCouponId = null;
        if ($couponCode) {
            $today = Carbon::now();
            $coupon = Coupon::where("code", $couponCode)
                ->where("status", 1)
                ->where(function ($q) use ($today) {
                    $q->whereNull('valid_from')->orWhere('valid_from', '<=', $today);
                })
                ->where(function ($q) use ($today) {
                    $q->whereNull('valid_until')->orWhere('valid_until', '>=', $today);
                })
                ->first();

            if ($coupon) {
                $isApplicable = false;
                $productIdsInCart = collect($processedItems)->pluck('product_id')->unique()->toArray();
                $vendorIdsInCart = collect($processedItems)->pluck('vendor_id')->unique()->toArray();
                
                // Get Category IDs from the actual product models
                $categoryIdsInCart = Product::whereIn('id', $productIdsInCart)->pluck('category_id')->unique()->toArray();
                
                // Check for restrictions
                $hasProductRestriction = method_exists($coupon, 'products') ? $coupon->products()->exists() : !empty($coupon->product_ids);
                $hasCategoryRestriction = method_exists($coupon, 'categories') ? $coupon->categories()->exists() : !empty($coupon->category_ids);
                $hasVendorRestriction = method_exists($coupon, 'vendors') ? $coupon->vendors()->exists() : !empty($coupon->vendor_ids);

                if (!$hasProductRestriction && !$hasCategoryRestriction && !$hasVendorRestriction) {
                    $isApplicable = true;
                } else {
                    if ($hasProductRestriction) {
                        $restrictedProductIds = method_exists($coupon, 'products') ? $coupon->products()->pluck('products.id')->toArray() : $coupon->product_ids;
                        if (array_intersect($productIdsInCart, $restrictedProductIds)) $isApplicable = true;
                    }
                    if (!$isApplicable && $hasCategoryRestriction) {
                        $restrictedCategoryIds = method_exists($coupon, 'categories') ? $coupon->categories()->pluck('categories.id')->toArray() : $coupon->category_ids;
                        if (array_intersect($categoryIdsInCart, $restrictedCategoryIds)) $isApplicable = true;
                    }
                    if (!$isApplicable && $hasVendorRestriction) {
                        $restrictedVendorIds = method_exists($coupon, 'vendors') ? $coupon->vendors()->pluck('users.id')->toArray() : $coupon->vendor_ids;
                        if (array_intersect($vendorIdsInCart, $restrictedVendorIds)) $isApplicable = true;
                    }
                }

                if ($isApplicable) {
                    if (in_array((string)$coupon->type, ['1', 'percent', '%'])) { // Percent
                        $totalCouponDiscount = round($totalBeforeCoupon * ((float)$coupon->value) / 100, 2);
                    } else { // Flat
                        $totalCouponDiscount = (float)$coupon->value;
                    }
                    $totalCouponDiscount = min($totalCouponDiscount, $totalBeforeCoupon);
                    $appliedCouponId = $coupon->id;
                }
            }
        }

        $totalCost = max(0, $totalBeforeCoupon - $totalCouponDiscount);

        return [
            "sub_total" => round($subTotalBeforeAnyDiscount, 2),
            "sub_total_formatted" => \App\Helpers\PriceHelper::formatPrice(round($subTotalBeforeAnyDiscount, 2)),
            "product_discounts" => round($totalProductDiscount, 2),
            "offer_discounts" => round($totalOfferDiscount, 2),
            "campaign_discounts" => round($totalCampaignDiscount, 2),
            "coupon_discounts" => round($totalCouponDiscount, 2),
            "total_discount" => round($totalProductDiscount + $totalOfferDiscount + $totalCampaignDiscount + $totalCouponDiscount, 2),
            "total_discount_formatted" => round($totalProductDiscount + $totalOfferDiscount + $totalCampaignDiscount + $totalCouponDiscount, 2) > 0 ? \App\Helpers\PriceHelper::formatPrice(round($totalProductDiscount + $totalOfferDiscount + $totalCampaignDiscount + $totalCouponDiscount, 2)) : '--',
            "taxes" => round($totalTaxes, 2),
            "taxes_formatted" => \App\Helpers\PriceHelper::formatPrice(round($totalTaxes, 2)),
            "delivery_charges" => round($shippingFee, 2),
            "delivery_charges_formatted" => round($shippingFee, 2) > 0 ? \App\Helpers\PriceHelper::formatPrice(round($shippingFee, 2)) : 'Free',
            "total_cost" => round($totalCost, 2),
            "total_cost_formatted" => \App\Helpers\PriceHelper::formatPrice(round($totalCost, 2)),
            "coupon_id" => $appliedCouponId,
            "items" => $processedItems
        ];
    }

    /**
     * Get list of applicable coupons for the given items
     */
    public static function getApplicableCoupons($itemsData)
    {
        $today = Carbon::now();
        $processedItems = [];
        foreach ($itemsData as $item) {
            if ($item instanceof \App\Models\Cart) {
                $processedItems[] = ['product_id' => $item->product_id, 'vendor_id' => $item->vendor_id];
            } elseif (is_object($item) && isset($item->product_id)) {
                $processedItems[] = ['product_id' => $item->product_id, 'vendor_id' => $item->vendor_id];
            } else {
                $processedItems[] = (array)$item;
            }
        }

        $productIdsInCart = collect($processedItems)->pluck('product_id')->unique()->toArray();
        $vendorIdsInCart = collect($processedItems)->pluck('vendor_id')->unique()->toArray();
        $categoryIdsInCart = Product::whereIn('id', $productIdsInCart)->pluck('category_id')->unique()->toArray();

        $coupons = Coupon::where("status", 1)
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', $today);
            })
            ->get();

        $applicableCoupons = [];

        foreach ($coupons as $coupon) {
            $isApplicable = false;

            // Check restrictions
            $hasProductRestriction = method_exists($coupon, 'products') ? $coupon->products()->exists() : !empty($coupon->product_ids);
            $hasCategoryRestriction = method_exists($coupon, 'categories') ? $coupon->categories()->exists() : !empty($coupon->category_ids);
            $hasVendorRestriction = method_exists($coupon, 'vendors') ? $coupon->vendors()->exists() : !empty($coupon->vendor_ids);

            if (!$hasProductRestriction && !$hasCategoryRestriction && !$hasVendorRestriction) {
                $isApplicable = true;
            } else {
                if ($hasProductRestriction) {
                    $restrictedProductIds = method_exists($coupon, 'products') ? $coupon->products()->pluck('products.id')->toArray() : $coupon->product_ids;
                    if (array_intersect($productIdsInCart, $restrictedProductIds)) $isApplicable = true;
                }
                if (!$isApplicable && $hasCategoryRestriction) {
                    $restrictedCategoryIds = method_exists($coupon, 'categories') ? $coupon->categories()->pluck('categories.id')->toArray() : $coupon->category_ids;
                    if (array_intersect($categoryIdsInCart, $restrictedCategoryIds)) $isApplicable = true;
                }
                if (!$isApplicable && $hasVendorRestriction) {
                    $restrictedVendorIds = method_exists($coupon, 'vendors') ? $coupon->vendors()->pluck('users.id')->toArray() : $coupon->vendor_ids;
                    if (array_intersect($vendorIdsInCart, $restrictedVendorIds)) $isApplicable = true;
                }
            }

            if ($isApplicable) {
                $applicableCoupons[] = [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'type' => $coupon->type, // 1 for percent, 2 for flat (based on controller logic)
                    'value' => (float)$coupon->value,
                    'valid_until' => $coupon->valid_until ? $coupon->valid_until->format('Y-m-d') : null,
                ];
            }
        }

        return $applicableCoupons;
    }
}

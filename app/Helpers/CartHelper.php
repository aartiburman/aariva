<?php

namespace App\Helpers;

use App\Models\Offer;

class CartHelper
{
    public static function calculateCart($cartItems, $coupon = null)
    {
        $subTotal = 0;

        $offerTotal = 0;
        $campaignTotal = 0;
        $couponTotal = 0;

        foreach ($cartItems as $item) {
            $product = $item->product;
            $price   = $item->price;
            $qty     = $item->qty;

            $base = $price * $qty;

            /* ===== OFFER ===== */
            $offerDiscount = 0;
            $offer_ids = json_decode($product->offer_id);
            $offer = Offer::whereIn('id', $offer_ids)->first();

            if ($offer) {
                if ($offer->type == 'percent') {
                    $offerDiscount = $base * $offer->value / 100;
                } else {
                    $offerDiscount = $offer->value;
                }
            }
            $afterOffer = $base - $offerDiscount;

            /* ===== COUPON ===== */
            $couponDiscount = 0;
            if ($coupon) {
                $eligible =
                    in_array($product->id, $coupon->product_ids ?? []) ||
                    in_array($product->category_id, $coupon->category_ids ?? []) ||
                    in_array($product->vendor_id, $coupon->vendor_ids ?? []);

                if ($eligible) {
                    if ($coupon->type == 1) { // percent
                        $couponDiscount = $afterOffer * $coupon->value / 100;
                    } else { // flat
                        $couponDiscount = $coupon->value;
                    }
                }
            }
            $final = $afterOffer - $couponDiscount;
            $final = max(0, $final);

            /* ===== TOTALS ===== */
            $subTotal += $final;
            $offerTotal += $offerDiscount;
            $couponTotal += $couponDiscount;
        }

        /* ===== TAX & DELIVERY ===== */
        $delivery = 50;
        $tax = $subTotal * 0.05;
        $totalDiscount = $offerTotal  + $couponTotal;
        $totalCost = $subTotal + $delivery + $tax;

        return [
            'sub_total'          => $subTotal,
            'delivery_charges'   => $delivery,
            'taxes'              => $tax,
            'offer_discounts'    => $offerTotal,
            'coupon_discounts'   => $couponTotal,
            'total_discount'     => $totalDiscount,
            'total_cost'         => $totalCost
        ];
    }
}

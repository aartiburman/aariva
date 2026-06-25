<?php

namespace App\Http\Controllers\Frontend\Template1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\ShippingAddress;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use App\Helpers\GeneralHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function profile()
    {
        $user = Auth::user();
        $countries = Country::all();
        $states = State::where('country_id', $user->country_id)->get();
        $cities = City::where('state_id', $user->state_id)->get();

        return view('frontend.user.profile', compact('user', 'countries', 'states', 'cities'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|max:255',
            'phone'      => 'required|string|max:20',
            'address'    => 'nullable|string',
            'country_id' => 'nullable|exists:countries,id',
            'state_id'   => 'nullable|exists:states,id',
            'city_id'    => 'nullable|exists:cities,id',
            'zip'        => 'nullable|string',
            'gender'     => 'nullable|in:male,female,other',
            'dob'        => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user->update($request->only([
            'name', 'phone', 'address', 'country_id', 'state_id', 'city_id', 'zip', 'gender', 'dob',
        ]));

        return redirect()->route('frontend.user.profile')->with('success', 'Profile updated successfully');
    }

    public function orders(Request $request)
    {
        $statusMap = [
            'pending'    => 0,
            'processing' => 1,
            'completed'  => 2,
            'cancelled'  => 3,
        ];

        $orders = Order::where('user_id', Auth::id())
            ->with('items.product', 'shippingAddress')
            ->when($request->status && isset($statusMap[$request->status]), function ($q) use ($request, $statusMap) {
                $q->where('status', $statusMap[$request->status]);
            })
            ->latest()
            ->paginate(10);

        return view('frontend.user.orders', compact('orders'));
    }

    public function orderDetail($id)
    {
        $order = Order::with('items.product', 'items.variant', 'shippingAddress', 'items.vendor')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('frontend.user.order-detail', compact('order'));
    }

    public function addresses()
    {
        $addresses = ShippingAddress::where('user_id', Auth::id())->latest()->get();
        $countries = Country::all();

        return view('frontend.user.addresses', compact('addresses', 'countries'));
    }
}

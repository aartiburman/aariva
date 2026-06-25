<?php

namespace App\Http\Controllers\Admin;

use Stevebauman\Location\Facades\Location;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\State;
use App\Models\City;

class LocationController extends Controller
{
    public function getLocation(Request $request)
    {
        $ip = request()->ip(); // or request()->getClientIp()
        $location = Location::get($ip);

        if ($location) {
            $country = $location->countryName; // India
            $countryCode = $location->countryCode; // IN
            $city = $location->cityName;
            $region = $location->regionName;
        }
    }
        
    public function getStates($country_id)
    {
        $states = State::where('country_id', $country_id)->get();
        return response()->json($states);
    }       
    public function getCities($state_id)
    {
        $cities = City::where('state_id', $state_id)->get();
        return response()->json($cities);
    }       
}

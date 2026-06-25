<?php

namespace App\Http\Controllers\Admin;
use Stevebauman\Location\Facades\Location;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoctionController extends Controller
{
     public function getLocation(Request $request){
        $ip = request()->ip(); // or request()->getClientIp()
$location = Location::get($ip);

if ($location) {
    $country = $location->countryName; // India
    $countryCode = $location->countryCode; // IN
    $city = $location->cityName;
    $region = $location->regionName;
}
    }
}

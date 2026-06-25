<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NewHomeController extends Controller
{ public function home(Request $request)
    {
        return view('backend.dashboard');
    }
    public function productList(Request $request)
    {
        return view('backend_new.product-list');
    }
    public function productAdd(Request $request)
    {
        return view('backend_new.product-add');
    }

     public function categoryAdd(Request $request)
    {
        return view('backend_new.category-add');
    }

    public function categoryList(Request $request)
    {
        return view('backend_new.category-list');
    }
    
       public function new_form(Request $request)
    {
        return view('backend_new.form');
    }

}

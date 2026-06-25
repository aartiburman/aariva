<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    //create_email_template
      public function create_email_template(Request $request)
    {
        return view('backend/admin/email/create-email-template');
    }
    
      public function add_email_template(Request $request)
    {
        return view('backend/admin/email/add_email_template');
    } 

    public function store_email_template(Request $request)
    {
        // Validation logic here
        // Store logic here
        return redirect()->back()->with('success', 'Email template stored successfully (Stub).');
    } 

}

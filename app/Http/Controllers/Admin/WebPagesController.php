<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class WebPagesController extends Controller
{
    public function downloadHandoverPdf()
    {
        $handoverPath = base_path('HANDOVER.md');
        
        if (!File::exists($handoverPath)) {
            return redirect()->back()->with('error', 'Handover file not found.');
        }

        $markdownContent = File::get($handoverPath);
        $htmlContent = Str::markdown($markdownContent);

        // For now, let's return as a well-formatted web page that can be printed to PDF
        // since external PDF libraries might not be installed in this environment.
        return view('backend.admin.handover-view', compact('htmlContent'));
    }

    public function paymentSuccess(Request $request)
    {
        $order = session('order');
        $orderReference = $order ? $order->order_reference_id : null;
        $message = session('message');
        return view('payment.success', compact('orderReference', 'message', 'order'));
    }

    public function paymentFailure(Request $request)
    {
        $error = session('error');
        return view('payment.failure', compact('error'));
    }
}

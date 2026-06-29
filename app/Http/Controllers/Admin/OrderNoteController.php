<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderNote;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderNoteController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'note' => 'required|string|max:2000',
            'is_staff_only' => 'nullable|boolean',
        ]);

        OrderNote::create([
            'order_id' => $request->order_id,
            'user_id' => Auth::id(),
            'note' => $request->note,
            'is_staff_only' => $request->boolean('is_staff_only'),
        ]);

        return redirect()->back()->with('success', 'Note added to order');
    }

    public function destroy($id)
    {
        $note = OrderNote::findOrFail($id);
        $note->delete();
        return redirect()->back()->with('success', 'Note deleted');
    }
}

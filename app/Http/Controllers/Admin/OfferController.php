<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Offer;
use App\Models\Category;
use App\Models\Product;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        // Auto-expire offers
        $now = now();
        Offer::where('status', 1)
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', $now)
            ->update(['status' => 0]);

        $query = Offer::query();

        if ($request->has('search') && !empty($request->search)) {
            $query->where('code', 'LIKE', '%' . trim($request->search) . '%');
        }

        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        $offers = $query->orderBy('updated_at', 'DESC')->paginate(10)->withQueryString();
        return view('backend.admin.offers.offer-list', compact('offers'));
    }

    public function create()
    {
        return view('backend.admin.offers.add-offer');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:offers,code',
            'type' => 'required|in:0,1',
            'value' => 'required|numeric|min:0',
            'status' => 'required|in:1,0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        Offer::create($request->all());

        return redirect()->route('offer.list')->with('success', 'Offer created successfully');
    }

    public function edit($id)
    {
        $offer = Offer::findOrFail($id);
        return view('backend.admin.offers.edit-offer', compact('offer'));
    }

    public function update(Request $request)
    {
        $offer = Offer::findOrFail($request->id);

        $request->validate([
            'code' => 'required|string|unique:offers,code,' . $offer->id,
            'type' => 'required|in:0,1',
            'value' => 'required|numeric|min:0',
            'status' => 'required|in:1,0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        $offer->update($request->all());

        return redirect()->route('offer.list')->with('success', 'Offer updated successfully');
    }

    public function destroy(Request $request)
    {
        $offer = Offer::findOrFail($request->id);
        $offer->delete();

        return response()->json([
            'status' => true,
            'message' => 'Offer deleted successfully'
        ]);
    }

    public function change_status(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:offers,id',
            'status' => 'required|in:1,0',
        ]);

        $offer = Offer::findOrFail($request->id);
        $offer->update(['status' => $request->status]);

        return response()->json([
            'status' => true,
            'message' => 'Offer status changed successfully'
        ]);
    }

    public function bulk_delete(Request $request)
    {
        $ids = $request->ids;
        if (!empty($ids)) {
            Offer::whereIn('id', $ids)->delete();
            return response()->json([
                'status' => true,
                'message' => 'Selected offers deleted successfully'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'No offers selected'
        ]);
    }

    public function export_offers(Request $request)
    {
        $ids = $request->ids;
        $query = Offer::query();
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $offers = $query->get();

        $filename = "offers_" . date('Y-m-d_H-i-s') . ".csv";
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Add CSV header
        fputcsv($handle, ['ID', 'Code', 'Type', 'Value', 'Valid From', 'Valid Until', 'Max Uses', 'Used Count', 'Status']);

        foreach ($offers as $offer) {
            fputcsv($handle, [
                $offer->id,
                $offer->code,
                $offer->type == 1 ? 'Percent' : 'Fixed',
                $offer->value,
                $offer->valid_from,
                $offer->valid_until,
                $offer->max_uses,
                $offer->used_count,
                $offer->status == 1 ? 'Active' : 'Inactive'
            ]);
        }

        fclose($handle);
        exit;
    }
}

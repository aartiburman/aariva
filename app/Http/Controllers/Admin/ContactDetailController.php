<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactDetail;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Stichoza\GoogleTranslate\GoogleTranslate;

class ContactDetailController extends Controller
{
    public function index()
    {
        $contacts = ContactDetail::orderBy('order_by', 'ASC')->orderBy('updated_at', 'DESC')->get();
        return view('backend/admin/contact_detail/contact-detail-list', compact('contacts'));
    }

    public function create()
    {
        $countries = Country::orderBy('name')->get();
        return view('backend/admin/contact_detail/add-contact-detail', compact('countries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'map_url' => 'nullable|url',
            'facebook_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'opening_hours' => 'nullable|string',
            'status' => 'required|in:0,1',
            'order_by' => 'nullable|integer|min:0',
        ]);

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('hi');

        $data = $request->all();
        $data['title_ar'] = $request->title ? $trAr->translate($request->title) : null;
        $data['title_ne'] = $request->title ? $trNe->translate($request->title) : null;
        $data['address_ar'] = $request->address ? $trAr->translate($request->address) : null;
        $data['address_ne'] = $request->address ? $trNe->translate($request->address) : null;
        $data['city_ar'] = $request->city ? $trAr->translate($request->city) : null;
        $data['city_ne'] = $request->city ? $trNe->translate($request->city) : null;
        $data['state_ar'] = $request->state ? $trAr->translate($request->state) : null;
        $data['state_ne'] = $request->state ? $trNe->translate($request->state) : null;
        $data['country_ar'] = $request->country ? $trAr->translate($request->country) : null;
        $data['country_ne'] = $request->country ? $trNe->translate($request->country) : null;
        $data['opening_hours_ar'] = $request->opening_hours ? $trAr->translate($request->opening_hours) : null;
        $data['opening_hours_ne'] = $request->opening_hours ? $trNe->translate($request->opening_hours) : null;

        ContactDetail::create($data);

        return redirect()->route('contact.detail.list')->with('success', 'Contact details added successfully');
    }

    public function edit($id)
    {
        $contact = ContactDetail::findOrFail($id);
        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        $states = $contact->country_id ? State::where('country_id', $contact->country_id)->orderBy('name')->get() : collect([]);
        $cities = $contact->state_id ? City::where('state_id', $contact->state_id)->orderBy('name')->get() : collect([]);
        return view('backend/admin/contact_detail/edit-contact-detail', compact('contact','countries','states','cities'));
    }

    public function update(Request $request)
    {
        $contact = ContactDetail::findOrFail($request->id);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'map_url' => 'nullable|url',
            'facebook_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'opening_hours' => 'nullable|string',
            'status' => 'required|in:0,1',
            'order_by' => 'nullable|integer|min:0',
        ]);

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('hi');

        $data = $request->all();
        $data['title_ar'] = $request->title ? $trAr->translate($request->title) : null;
        $data['title_ne'] = $request->title ? $trNe->translate($request->title) : null;
        $data['address_ar'] = $request->address ? $trAr->translate($request->address) : null;
        $data['address_ne'] = $request->address ? $trNe->translate($request->address) : null;
        $data['city_ar'] = $request->city ? $trAr->translate($request->city) : null;
        $data['city_ne'] = $request->city ? $trNe->translate($request->city) : null;
        $data['state_ar'] = $request->state ? $trAr->translate($request->state) : null;
        $data['state_ne'] = $request->state ? $trNe->translate($request->state) : null;
        $data['country_ar'] = $request->country ? $trAr->translate($request->country) : null;
        $data['country_ne'] = $request->country ? $trNe->translate($request->country) : null;
        $data['opening_hours_ar'] = $request->opening_hours ? $trAr->translate($request->opening_hours) : null;
        $data['opening_hours_ne'] = $request->opening_hours ? $trNe->translate($request->opening_hours) : null;

        $contact->update($data);

        return redirect()->route('contact.detail.list')->with('success', 'Contact details updated successfully');
    }

    public function change_status(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:contact_details,id',
            'status' => 'required|in:0,1',
        ]);

        $contact = ContactDetail::find($request->id);
        $contact->status = $request->status;
        $contact->save();

        return response()->json([
            'status' => true,
            'message' => 'Status updated successfully'
        ]);
    }

    public function destroy(Request $request)
    {
        $contact = ContactDetail::findOrFail($request->id);
        $contact->delete();

        return response()->json([
            'status' => true,
            'message' => 'Contact details deleted successfully'
        ]);
    }
}

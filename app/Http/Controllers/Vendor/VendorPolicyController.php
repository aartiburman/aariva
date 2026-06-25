<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\VendorPolicy;
use App\Models\VendorPolicyAcceptance;
use App\Models\User;

class VendorPolicyController extends Controller
{
    public function accept(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role != '2') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $policyId = $request->input('policy_id');
        if (!$policyId) {
            $policy = VendorPolicy::where('status', 1)->latest('created_at')->first();
            if (!$policy) {
                return response()->json(['status' => false, 'message' => 'No active policy found'], 404);
            }
            $policyId = $policy->id;
        } else {
            $policy = VendorPolicy::where('status',1)->find($policyId);
            if (!$policy) {
                return response()->json(['status' => false, 'message' => 'Invalid policy'], 404);
            }
        }

        VendorPolicyAcceptance::updateOrCreate(
            ['vendor_id' => $user->id, 'policy_id' => $policyId],
            ['accepted_at' => now()]
        );

        // Update user agreement status
        User::where('id', $user->id)->update([
            'agreement' => 1,
            'agreement_id' => $policyId
        ]);

        return response()->json(['status' => true, 'message' => 'Policy accepted']);
    }
}


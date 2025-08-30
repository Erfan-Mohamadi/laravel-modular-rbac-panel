<?php

namespace Modules\Customer\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Customer\Models\Customer;

class CustomerController extends Controller
{
    /**
     * Get authenticated customer profile.
     */
    public function profile(Request $request)
    {
        $customer = Auth::user();

        return response()->json([
            'success' => true,
            'data' => $customer,
        ]);
    }

    /**
     * Update authenticated customer profile.
     */
    public function updateProfile(Request $request)
    {
        $customer = Auth::user();

        $validated = $request->validate([
            'name'     => 'nullable|string|max:100',
            'email'    => 'nullable|email|max:191|unique:customers,email,' . $customer->id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data'    => $customer,
        ]);
    }
}

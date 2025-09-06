<?php

namespace Modules\Customer\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Customer\Http\Requests\ProfileUpdateRequest;

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
    public function updateProfile(ProfileUpdateRequest $request)
    {
        $customer = Auth::user();

        $validated = $request->validated();

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'پروفایل با موفقیت به‌روزرسانی شد.',
            'data'    => $customer,
        ]);
    }
}

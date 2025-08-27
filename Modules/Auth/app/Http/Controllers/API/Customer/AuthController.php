<?php

namespace Modules\Auth\Http\Controllers\API\Customer;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Modules\Customer\Models\Customer;

class AuthController extends Controller
{
    // Customer login
    public function login(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string|exists:customers,mobile',
            // 'password' => 'required|string', // uncomment if using password login
        ]);

        $customer = Customer::where('mobile', $request->mobile)->first();

        // If password login is required
        // if (!Hash::check($request->password, $customer->password)) {
        //     return response()->json(['message' => 'Invalid credentials'], 401);
        // }

        // Update last login
        $customer->update(['last_login_date' => now()]);

        // Create token
        $token = $customer->createToken('customer-token')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully',
            'token' => $token,
            'customer' => $customer
        ]);
    }

    // Customer register
    public function register(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string|unique:customers,mobile',
            'name' => 'nullable|string|max:100',
            // 'password' => 'nullable|string|min:6' // optional
        ]);

        $customer = Customer::create([
            'mobile' => $request->mobile,
            'name' => $request->name,
            // 'password' => isset($request->password) ? Hash::make($request->password) : null,
        ]);

        $token = $customer->createToken('customer-token')->plainTextToken;

        return response()->json([
            'message' => 'Registered successfully',
            'token' => $token,
            'customer' => $customer
        ]);
    }

    // Customer profile
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}

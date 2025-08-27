<?php

namespace Modules\Auth\Http\Controllers\API\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Customer\Models\Customer;

class CustomerAuthController extends Controller
{
    /**
     * Register a new customer
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'unique:customers,email'],
            'mobile' => ['required', 'string', 'max:20', 'unique:customers,mobile'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $customer = Customer::create([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'mobile' => $data['mobile'],
            'password' => Hash::make($data['password']),
        ]);

        // Create Sanctum token
        $token = $customer->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'ثبت‌نام با موفقیت انجام شد.',
            'customer' => $customer,
            'token' => $token,
        ], 201);
    }

    /**
     * Login existing customer
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'mobile' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $customer = Customer::where('mobile', $credentials['mobile'])->first();

        if (! $customer || ! Hash::check($credentials['password'], $customer->password)) {
            return response()->json([
                'message' => 'شماره موبایل یا رمز عبور اشتباه است!',
            ], 401);
        }

        // Revoke old tokens (optional, if you want single-device login)
        $customer->tokens()->delete();

        // Create new token
        $token = $customer->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'ورود موفقیت‌آمیز بود.',
            'customer' => $customer,
            'token' => $token,
        ]);
    }

    /**
     * Logout (revoke current token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'خروج از حساب کاربری انجام شد.',
        ]);
    }

    /**
     * Get current authenticated customer
     */
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }
}

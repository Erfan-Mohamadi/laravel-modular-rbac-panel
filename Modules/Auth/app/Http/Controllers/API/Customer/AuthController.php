<?php

namespace Modules\Auth\Http\Controllers\API\Customer;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Modules\Customer\Models\Customer;
use Carbon\Carbon;

class AuthController extends Controller
{
    // Step 1: Send OTP for registration
    public function sendOtp(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string|unique:customers,mobile',
        ]);

        // Generate 6-digit OTP
        $otp = rand(100000, 999999);

        // Create customer with unverified status
        $customer = Customer::create([
            'mobile' => $request->mobile,
            'otp' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(5), // 5 minute expiry
            'status' => 0, // Unverified
        ]);

        // TODO: Send SMS here using your SMS provider
        // $this->sendSMS($request->mobile, "Your verification code is: $otp");

        return response()->json([
            'message' => 'OTP sent successfully',
            'otp' => $otp, // Remove this in production!
            'customer_id' => $customer->id
        ]);
    }

    // Step 2: Verify OTP and complete registration
    public function verifyOtpAndRegister(Request $request)
    {
        // Force customer_id to integer
        $request->merge([
            'customer_id' => (int) $request->customer_id
        ]);


        $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'otp' => 'required|string|size:6',
            'name' => 'required|string|max:100',
            'password' => 'required|string|min:6',
        ]);

        $customer = Customer::find($request->customer_id);

        // Check OTP
        if ($customer->otp !== $request->otp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        if (Carbon::now()->gt($customer->otp_expires_at)) {
            return response()->json(['message' => 'OTP has expired'], 400);
        }

        // Complete registration
        $customer->update([
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'mobile_verified_at' => now(),
            'status' => 1,
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        $token = $customer->createToken('customer-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration completed successfully',
            'token' => $token,
            'customer' => $customer
        ]);
    }


    // Resend OTP
    public function resendOtp(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        $customer = Customer::query()->find($request->customer_id);

        if ($customer->status == 1) {
            return response()->json(['message' => 'Customer already verified'], 400);
        }

        $otp = rand(100000, 999999);

        $customer->update([
            'otp' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(5),
        ]);

        // TODO: Send SMS
        // $this->sendSMS($customer->mobile, "Your verification code is: $otp");

        return response()->json([
            'message' => 'OTP resent successfully',
            'otp' => $otp, // Remove this in production!
        ]);
    }

    // Login (after registration is complete)
    public function login(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string|exists:customers,mobile',
            'password' => 'required|string',
        ]);

        $customer = Customer::where('mobile', $request->mobile)->first();

        if (!$customer->mobile_verified_at) {
            return response()->json(['message' => 'Mobile number not verified'], 401);
        }

        if (!Hash::check($request->password, $customer->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $customer->update(['last_login_date' => now()]);
        $token = $customer->createToken('customer-token')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully',
            'token' => $token,
            'customer' => $customer
        ]);
    }

    // Profile
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

    // TODO: Implement SMS sending method
    // private function sendSMS($mobile, $message)
    // {
    //     // Integrate with your SMS provider (Kavenegar, etc.)
    // }
}

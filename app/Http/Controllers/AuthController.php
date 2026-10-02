<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Register a new user and return an API token.
     * POST /api/register
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'nullable|string',
            'department' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'User',
            'department' => $validated['department'] ?? 'General',
            'status' => 'Active',
            'phone' => $validated['phone'] ?? null,
            'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($validated['name']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Authenticate user and return an API token.
     * POST /api/login
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials. Please check your email and password.'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * Get the authenticated User profile.
     * GET /api/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'user' => $request->user(),
        ], 200);
    }

    /**
     * Revoke current API token (Logout).
     * POST /api/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Successfully logged out',
        ], 200);
    }

    /**
     * Send Password Reset OTP to Email via Resend.
     * POST /api/forgot-password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'No user account found with this email address.',
        ]);

        $otp = (string) rand(100000, 999999);

        // Store or update OTP in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        // Send Email OTP via Resend API
        $sent = $this->sendOtpEmail($request->email, $otp);

        $response = [
            'status' => true,
            'message' => 'A 6-digit OTP verification code has been sent to your email.',
            'email' => $request->email,
            'email_sent' => $sent,
        ];

        // If Resend API key is not configured yet, include OTP in response for testing
        if (!env('RESEND_API_KEY')) {
            $response['otp_debug'] = $otp;
            $response['message'] .= ' (Debug Mode: Demo OTP is ' . $otp . ')';
        }

        return response()->json($response, 200);
    }

    /**
     * Resend Password Reset OTP via Resend.
     * POST /api/resend-otp
     */
    public function resendOtp(Request $request): JsonResponse
    {
        return $this->forgotPassword($request);
    }

    /**
     * Verify 6-digit Password Reset OTP.
     * POST /api/verify-otp
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record) {
            return response()->json([
                'status' => false,
                'message' => 'No OTP request found for this email. Please request a new code.'
            ], 400);
        }

        // Check if OTP is expired (15 minutes limit)
        if (now()->diffInMinutes($record->created_at) > 15) {
            return response()->json([
                'status' => false,
                'message' => 'OTP code has expired. Please request a new code.'
            ], 400);
        }

        if (!Hash::check($request->otp, $record->token)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP verification code. Please check and try again.'
            ], 400);
        }

        return response()->json([
            'status' => true,
            'message' => 'OTP verified successfully.',
        ], 200);
    }

    /**
     * Reset Password using OTP.
     * POST /api/reset-password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->otp, $record->token)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired OTP code.'
            ], 400);
        }

        // Update user password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete used reset token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully! You can now sign in with your new password.',
        ], 200);
    }

    /**
     * Helper to send OTP email using Resend Platform API.
     */
    private function sendOtpEmail(string $email, string $otp): bool
    {
        $resendApiKey = env('RESEND_API_KEY');

        if (!$resendApiKey) {
            Log::info("RESEND_API_KEY is not set in .env. OTP for {$email} is: {$otp}");
            return false;
        }

        try {
            $fromEmail = env('RESEND_FROM_EMAIL', 'onboarding@resend.dev');
            $appName = config('app.name', 'AdminPulse');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $resendApiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.resend.com/emails', [
                'from' => "{$appName} <{$fromEmail}>",
                'to' => [$email],
                'subject' => "{$otp} is your password reset code",
                'html' => "
                    <div style=\"font-family: Arial, sans-serif; max-width: 580px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 16px; background-color: #ffffff;\">
                        <h2 style=\"color: #FF4500; font-size: 22px; margin-top: 0;\">AdminPulse Security</h2>
                        <p style=\"font-size: 15px; color: #334155; line-height: 1.5;\">We received a request to reset your password. Use the verification code below to proceed:</p>
                        <div style=\"background-color: #f8fafc; padding: 20px; text-align: center; border-radius: 12px; margin: 24px 0; border: 1px solid #cbd5e1;\">
                            <span style=\"font-size: 36px; font-weight: 800; letter-spacing: 10px; color: #0f172a;\">{$otp}</span>
                        </div>
                        <p style=\"font-size: 13px; color: #64748b; margin-bottom: 0;\">This OTP is valid for 15 minutes. If you did not request this code, no action is required.</p>
                    </div>
                ",
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Failed to send OTP email via Resend: " . $e->getMessage());
            return false;
        }
    }
}

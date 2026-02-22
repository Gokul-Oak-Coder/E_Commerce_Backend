<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Auth;

class CustomerAuthController extends Controller
{
    public function __construct(protected Auth $auth)
    {
    }

    public function firebaseLogin(Request $request)
    {
        $request->validate([
            'firebase_token' => 'required|string',
        ]);

        try {
            // Verify the Firebase ID token
            $verifiedToken = $this->auth->verifyIdToken($request->firebase_token);

            $uid = $verifiedToken->claims()->get('sub');
            $phone = $verifiedToken->claims()->get('phone_number');

            if (!$phone) {
                return response()->json(['message' => 'Phone number not found'], 422);
            }

            // Find or create customer
            $user = User::firstOrCreate(
                ['phone' => $phone],
                [
                    'name' => 'Customer ' . substr($phone, -4),
                    'email' => $uid . '@firebase.com', // placeholder
                    'password' => bcrypt($uid),
                    'role' => 'customer',
                    'phone' => $phone,
                ]
            );

            // Revoke old tokens and issue new one
            $user->tokens()->delete();
            $token = $user->createToken('customer-token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Invalid Firebase token',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}
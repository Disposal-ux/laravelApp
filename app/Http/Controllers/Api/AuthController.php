<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;

class AuthController extends Controller
{
    public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

         if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }


         $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function register(Request $request){

       try {

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'mobile' => 'nullable|string|max:10',
                'status' => 'required|in:1,2',
                'role_type' => 'required|in:1,2',
            ]);
            $otp = rand(100000, 999999);
            Log::info('Generated OTP: '.$otp);
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'mobile' => $request->mobile,
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(10),
                'status' => $request->status,
                'role_type' => $request->role_type,
            ]);
            // dd($user);
            Log::info('User created', $user->toArray());
            Log::info('Sending OTP to: '.$user->email);
            Mail::to($user->email)->send(new OtpMail($otp));
            Log::info('Mail sent');

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully. Please check your email for the OTP.',
                'data' => [
                    'otp' => $otp,
                    'otp_expires_at' => $user->otp_expires_at,
                ]
            ], 201);


        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        if ($user->otp !== $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 400);
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired'
            ], 400);
        }

        //  $user->update([
        // 'otp' => null,
        // 'otp_expires_at' => null,
        // 'email_verified_at' => now()
        //      ]);

        $user->otp_verify = '2';
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully'
        ]);
    }


}

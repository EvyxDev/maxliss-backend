<?php

namespace App\Http\Controllers\Api\V2\Expert;

use App\Models\Expert;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\ExpertResource;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function constract()
    {
        $this->middleware('auth:expert', ['only' => 'logout']);
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric|exists:experts,phone',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => 'Validation error',
                    'errors' => $validator->errors()->all()
                ],
                422
            );
        }

        $expert = Expert::where('phone', $request->phone)->first();
        if ($expert) {
            $expert->otp = 1111;
            $expert->save();

            return response()->json([
                'result' => true,
                'message' => 'OTP sent successfully',
                'data' => []
            ], 200);
        }

        // $credentials = $request->only('phone');

        // if (Auth::guard('expert')->attempt($credentials)) {
        //     $user = Auth::guard('expert')->user();
        //     $token = $user->createToken('authToken')->plainTextToken;

        //     return response()->json([
        //         'token' => $token,
        //         'user' => ExpertResource::make($user)
        //     ]);
        // }

        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    }
    // check otp to login

    public function checkOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric|exists:experts,phone',
            'otp' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => 'Validation error',
                    'errors' => $validator->errors()->all()
                ],
                422
            );
        }

        $expert = Expert::where('phone', $request->phone)->first();
        if ($expert->otp == $request->otp) {
            $expert->otp = null;
            $expert->save();
            $token = $expert->createToken('authToken')->plainTextToken;
            Auth::guard('expert')->login($expert);
            return response()->json([
                'result' => true,
                'token' => $token,
                'user' => ExpertResource::make($expert)
            ]);
        }

        return response()->json([
            'message' => 'Invalid OTP'
        ], 401);
    }
	
	
	public function updateLonLat(Request $request){
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lon' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'result' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()->all()
                ],
                422
            );
        }

        $user = Auth::guard('expert')->user();

        $expert = Expert::find($user->id);

        $expert->update([
            'lat' => $request->lat,
            'lon' => $request->lon,
        ]);

        return response()->json(
            [
                'result' => true,
                'message' => 'Set Location',
                'errors' => []
            ],
            200
        );
    }
    // Logout in guard expert
    public function logout(Request $request)
    {
        if (!auth()->guard('expert')->check()) {
            return response()->json([
                'message' => 'Expert unauthenticated'
            ], 404);
        }
        auth()->guard('expert')->logout();
        return response()->json([
            'result' => true,
            'message' => 'Logged out'
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $valitor = Validator::make($request->all(), [
            'phone' => 'required|exists:experts,phone'
        ]);

        if ($valitor->fails()) {
            return response()->json([
                'result' => false,
                'message' => 'Validation error',
                'errors' => $valitor->errors()
            ], 422);
        }
        $otp = 1111;
        $expert = Expert::where('phone', $request->phone)->first();
        $expert->otp = $otp;
        $expert->save();

        return response()->json([
            'result' => true,
            'message' => 'OTP sent successfully',
            'data' => []
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $valitor = Validator::make($request->all(), [
            'phone' => 'required|exists:experts,phone',
            'otp' => 'required'
        ]);

        if ($valitor->fails()) {
            return response()->json([
                'result' => false,
                'message' => 'Validation error',
                'errors' => $valitor->errors()
            ], 422);
        }

        $expert = Expert::where('phone', $request->phone)->first();
        if ($expert->otp == $request->otp) {
            return response()->json([
                'result' => true,
                'message' => 'OTP verified successfully',
                'data' => []
            ], 200);
        } else {
            return response()->json([
                'result' => false,
                'message' => 'OTP not verified',
                'data' => []
            ], 422);
        }
    }

    public function resetPassword(Request $request)
    {
        $valitor = Validator::make($request->all(), [
            'phone' => 'required|exists:experts,phone',
            'password' => 'required|min:6|confirmed'
        ]);

        if ($valitor->fails()) {
            return response()->json([
                'result' => false,
                'message' => 'Validation error',
                'errors' => $valitor->errors()
            ], 422);
        }

        $expert = Expert::where('phone', $request->phone)->first();
        $expert->password = Hash::make($request->password);
        $expert->otp = null;
        $expert->save();

        return response()->json([
            'result' => true,
            'message' => 'Password reset successfully',
            'data' => $expert
        ], 200);
    }
}

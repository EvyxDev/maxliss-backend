<?php

namespace App\Http\Controllers\Api\V2\Expert;

use Notification;
use App\Models\Expert;
use App\Models\Address;
use App\Models\Community;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\ExpertResource;
use App\Http\Resources\CommunityResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\NotificationExpertResource;

class AuthController extends Controller
{
    public function constract()
    {
        $this->middleware('auth:expert', ['only' => 'logout', 'getWithCity','info', 'notifications']);
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

    // profile
    public function profile(){
        $expert = auth()->guard('expert')->user();

        $expertData = ExpertResource::make($expert)->toArray(request());
        unset($expertData['slots']);
        $data['expert'] = $expertData;

        $posts = Community::where('expert_id', $expert->id)->get();
        $data['posts'] = CommunityResource::collection($posts);
        return response()->json([
            'result' => true,
            'message' => 'Experet Profile',
            'data' => $data
        ], 200);
    }

    public function getWithCity(Request $request){

        $validator = Validator::make($request->all(), [
            'adderss_id' => 'required|exists:addresses,id'
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
        $address = Address::find($request->adderss_id);
        $city = $address->city_id;
        $experts = Expert::where('city_id', $city)->with('communities')->get();

        if (!$experts) {
            return response()->json([
                'result' => false,
                'message' => 'No Experts',
                'data' => []
            ], 404);
        }
        $data['experts'] = ExpertResource::collection($experts);
        return response()->json([
            'result' => true,
            'message' => 'Experts',
            'data' => $data
        ], 200);
    }

    // profile
    public function info(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expert_id' => 'required|exists:experts,id'
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

        $expert = Expert::find($request->expert_id);

        $data['expert'] = ExpertResource::make($expert);

        $posts = Community::where('expert_id', $expert->id)->get();
        $data['posts'] = CommunityResource::collection($posts);
        return response()->json([
            'result' => true,
            'message' => 'Experet Info',
            'data' => $data
        ], 200);
    }

    // Notification
    public function notifications()
    {
        $expert = auth()->guard('expert')->user();
        $notifications = NotificationExpertResource::collection($expert->notifications);

        foreach ($expert->notifications as $notification) {
            $notification->is_read = 1;
            $notification->save();
        }

        return response()->json([
            'result' => true,
            'message' => 'Notifications',
            'data' => $notifications
        ], 200);


    }

    // show
    public function show($id)
    {
        $expert = Expert::find($id);
        if (!$expert) {
            return response()->json([
                'result' => false,
                'message' => 'Expert not found',
                'data' => []
            ], 404);
        }
        return response()->json([
            'result' => true,
            'message' => 'Expert',
            'data' => ExpertResource::make($expert)
        ], 200);
    }
}

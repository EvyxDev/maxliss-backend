<?php

namespace App\Http\Controllers\Api\V2;

use App\Notifications\AppEmailVerificationNotification;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Notifications\PasswordResetRequest;
use Illuminate\Support\Str;
use App\Http\Controllers\OTPVerificationController;

use Hash;

class PasswordResetController extends Controller
{
    public function forgetRequest(Request $request)
    {
        if ($request->send_code_by == 'email') {
            $user = User::where('email', $request->email_or_phone)->first();
        } else {
            $user = User::where('phone', $request->email_or_phone)->first();
        }


        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => translate('User is not found')
            ], 404);
        }

        if ($user) {
            $user->verification_code = 1234;
            $user->save();
            if ($request->send_code_by == 'phone') {

                $otpController = new OTPVerificationController();
                $otpController->send_code($user);
            } else {
                try {

                    $user->notify(new AppEmailVerificationNotification());
                } catch (\Exception $e) {
                }
            }
        }

        return response()->json([
            'result' => true,
            'message' => translate('A code is sent')
        ], 200);
    }
    public function ValidateOtp(Request $request)
    {
        $messages = array(
            'email_or_phone.required' => $request->verify_by == 'email' ? translate('Email is required') : translate('Phone is required'),
            'email_or_phone.email' => translate('Email must be a valid email address'),
            'email_or_phone.numeric' => translate('Phone must be a number.'),
            'verification_code.required' => translate('Verification Code is required'),
            'verify_by' => translate('Verification By Is Required')
        );
        $validator = Validator::make($request->all(), [
            'verification_code' => 'required',
            'verify_by' => 'required|in:email,phone',
            'email_or_phone' => [
                'required',
                Rule::when($request->verify_by === 'email', ['email', 'required']),
                Rule::when($request->verify_by === 'phone', ['numeric', 'required']),
            ]
        ], $messages);
        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->first()
            ]);
        }
        $user = User::where('verification_code', $request->verification_code)->where(function ($query) use ($request) {
            $query->where('email', $request->email_or_phone)
                ->orWhere('phone', $request->email_or_phone);
        })
            ->first();
        if ($user != null) {
            Auth::login($user);
            $auth = new AuthController();
            return $auth->loginSuccess($user);
            return response()->json([
                'result' => true,
                'message' => translate('User Already Exists'),
            ], 200);
        } else {
            return response()->json([
                'result' => false,
                'message' => translate('No user is found'),
            ], 200);
        }
    }

    public function confirmReset(Request $request)
    {
        $user = User::find(auth()->user()->id);
        if ($user != null) {
            $user->verification_code = null;
            $user->password = Hash::make($request->password);
            $user->save();
            return response()->json([
                'result' => true,
                'message' => translate('Your password is reset.Please login'),
            ], 200);
        } else {
            return response()->json([
                'result' => false,
                'message' => translate('No user is found'),
            ], 200);
        }
    }

    public function resendCode(Request $request)
    {

        if ($request->verify_by == 'email') {
            $user = User::where('email', $request->email_or_phone)->first();
        } else {
            $user = User::where('phone', $request->email_or_phone)->first();
        }


        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => translate('User is not found')
            ], 404);
        }

        $user->verification_code = 1234;
        $user->save();

        if ($request->verify_by == 'email') {
            $user->notify(new AppEmailVerificationNotification());
        } else {
            $otpController = new OTPVerificationController();
            $otpController->send_code($user);
        }



        return response()->json([
            'result' => true,
            'message' => translate('A code is sent again'),
        ], 200);
    }
}

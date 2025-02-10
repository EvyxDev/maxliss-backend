<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers\Api\V2;

use Hash;
use Mail;
use Socialite;
use App\Models\Cart;
use App\Models\User;
use App\Models\Address;
use App\Rules\Recaptcha;
use Illuminate\Http\Request;
use App\Utility\EmailUtility;
use App\Models\BusinessSetting;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;
use App\Mail\GuestAccountOpeningMailManager;
use App\Http\Controllers\OTPVerificationController;
use App\Notifications\AppEmailVerificationNotification;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $messages = array(
            'name.required' => translate('Name is required'),
            'phone.required' =>  translate('Phone is required'),
            'email.required' =>  translate('Email is required'),
            'email.email' => translate('Email must be a valid email address'),
            'phone.numeric' => translate('Phone must be a string.'),
            'phone.unique' => translate('The phone has already been taken'),
            'email.unique' =>  translate('The email has already been taken'),
            'password.required' => translate('Password is required'),
            'password.confirmed' => translate('Password confirmation does not match'),
            'password.min' => translate('Minimum 6 digits required for password')
        );
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required|min:6|confirmed',
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'unique:users,phone'],
        ], $messages);
        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->first()
            ]);
        }
        $user = new User();
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->verification_code = 1234;
        $user->wss_token = uniqid(bin2hex(random_bytes(16)), true);
        $user->email_verified_at = null;
        $user->save();
        $user->createToken('tokens')->plainTextToken;
        $tempUserId = $request->has('temp_user_id') ? $request->temp_user_id : null;
        return $this->loginSuccess($user, '', $tempUserId);
    }
    public function SendVerification(Request $request)
    {
        $messages = array(
            'verify_by.required' => translate('Verify By is required'),
            'verify_by.in' => translate('Verify By must be either email or phone'),
        );
        $validator = Validator::make($request->all(), [
            'verify_by' => 'required|in:email,phone',
        ], $messages);
        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->first()
            ]);
        }
        $user_id = auth()->user()->id;
        $user = User::find($user_id);
        if ($request->verify_by == 'email') {
            try {
                $user->notify(new AppEmailVerificationNotification());
            } catch (\Exception $e) {
                Log::info("Error In Send Verification Function: " . $e);
                return response()->json([
                    'result' => false,
                    'message' => $e->getMessage(),
                ]);
            }
        } else {
            $otpController = new OTPVerificationController();
            $otpController->send_code($user);
        }
        return response()->json([
            'result' => true,
            'message' => translate('Verification code is sent successfully'),
        ], 200);
    }
    public function resendCode(Request $request)
    {
        $messages = array(
            'verify_by.required' => translate('Verify By is required'),
            'verify_by.in' => translate('Verify By must be either email or phone'),
        );
        $validator = Validator::make($request->all(), [
            'verify_by' => 'required|in:email,phone',
        ], $messages);
        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->first()
            ]);
        }
        $user_id = auth()->user()->id;
        $user = User::find($user_id);
        if ($request->verify_by == 'email') {
            try {
                $user->notify(new AppEmailVerificationNotification());
            } catch (\Exception $e) {
                Log::info("Error In Send Verification Function: " . $e);
                return response()->json([
                    'result' => false,
                    'message' => $e->getMessage(),
                ]);
            }
        } else {
            $otpController = new OTPVerificationController();
            $otpController->send_code($user);
        }
        return response()->json([
            'result' => true,
            'message' => translate('Verification code is sent again'),
        ], 200);
    }
    public function confirmCode(Request $request)
    {
        $user_id = auth()->user()->id;
        $user = User::find($user_id);
        if ($user->verification_code == $request->verification_code) {
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->verification_code = null;
            $user->save();
            return response()->json([
                'result' => true,
                'message' => translate('Your account is now verified'),
            ], 200);
        } else {
            return response()->json([
                'result' => false,
                'message' => translate('Code does not match, you can request for resending the code'),
            ], 200);
        }
    }

    public function login(Request $request)
    {
        $messages = array(
            'email.required' => $request->login_by == 'email' ? translate('Email is required') : translate('Phone is required'),
            'email.email' => translate('Email must be a valid email address'),
            'email.numeric' => translate('Phone must be a number.'),
            'password.required' => translate('Password is required'),
        );
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'login_by' => 'required',
            'email' => [
                'required',
                Rule::when($request->login_by === 'email', ['email', 'required']),
                Rule::when($request->login_by === 'phone', ['numeric', 'required']),
            ]
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $delivery_boy_condition = $request->has('user_type') && $request->user_type == 'delivery_boy';
        $seller_condition = $request->has('user_type') && $request->user_type == 'seller';
        $req_email = $request->email;

        if ($delivery_boy_condition) {
            $user = User::whereIn('user_type', ['delivery_boy'])
                ->where(function ($query) use ($req_email) {
                    $query->where('email', $req_email)
                        ->orWhere('phone', $req_email);
                })
                ->first();
        } elseif ($seller_condition) {
            $user = User::whereIn('user_type', ['seller'])
                ->where(function ($query) use ($req_email) {
                    $query->where('email', $req_email)
                        ->orWhere('phone', $req_email);
                })
                ->first();
        } else {
            $user = User::whereIn('user_type', ['customer'])
                ->where(function ($query) use ($req_email) {
                    $query->where('email', $req_email)
                        ->orWhere('phone', $req_email);
                })
                ->first();
        }
        // if (!$delivery_boy_condition) {
        if (!$delivery_boy_condition && !$seller_condition) {
            if (\App\Utility\PayhereUtility::create_wallet_reference($request->identity_matrix) == false) {
                return response()->json(['result' => false, 'message' => 'Identity matrix error', 'user' => null], 401);
            }
        }

        if ($user != null) {
            if (!$user->banned) {
                if (Hash::check($request->password, $user->password)) {
                    $tempUserId = $request->has('temp_user_id') ? $request->temp_user_id : null;
                    return $this->loginSuccess($user, '', $tempUserId);
                } else {
                    return response()->json(['result' => false, 'message' => translate('Unauthorized'), 'user' => null], 401);
                }
            } else {
                return response()->json(['result' => false, 'message' => translate('User is banned'), 'user' => null], 401);
            }
        } else {
            return response()->json(['result' => false, 'message' => translate('User not found'), 'user' => null], 401);
        }
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {

        $user = request()->user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();

        return response()->json([
            'result' => true,
            'message' => translate('Successfully logged out')
        ]);
    }

    public function socialLogin(Request $request)
    {
        if (!$request->provider) {
            return response()->json([
                'result' => false,
                'message' => translate('User not found'),
                'user' => null
            ]);
        }

        switch ($request->social_provider) {
            case 'facebook':
                $social_user = Socialite::driver('facebook')->fields([
                    'name',
                    'first_name',
                    'last_name',
                    'email'
                ]);
                break;
            case 'google':
                $social_user = Socialite::driver('google')
                    ->scopes(['profile', 'email']);
                break;
            case 'twitter':
                $social_user = Socialite::driver('twitter');
                break;
            case 'apple':
                $social_user = Socialite::driver('sign-in-with-apple')
                    ->scopes(['name', 'email']);
                break;
            default:
                $social_user = null;
        }
        if ($social_user == null) {
            return response()->json(['result' => false, 'message' => translate('No social provider matches'), 'user' => null]);
        }

        if ($request->social_provider == 'twitter') {
            $social_user_details = $social_user->userFromTokenAndSecret($request->access_token, $request->secret_token);
        } else {
            $social_user_details = $social_user->userFromToken($request->access_token);
        }

        if ($social_user_details == null) {
            return response()->json(['result' => false, 'message' => translate('No social account matches'), 'user' => null]);
        }

        $existingUserByProviderId = User::where('provider_id', $request->provider)->first();

        if ($existingUserByProviderId) {
            $existingUserByProviderId->access_token = $social_user_details->token;
            if ($request->social_provider == 'apple') {
                $existingUserByProviderId->refresh_token = $social_user_details->refreshToken;
                if (!isset($social_user->user['is_private_email'])) {
                    $existingUserByProviderId->email = $social_user_details->email;
                }
            }
            $existingUserByProviderId->save();
            return $this->loginSuccess($existingUserByProviderId);
        } else {
            $existing_or_new_user = User::firstOrNew(
                [['email', '!=', null], 'email' => $social_user_details->email]
            );

            // $existing_or_new_user->user_type = 'customer';
            $existing_or_new_user->provider_id = $social_user_details->id;

            if (!$existing_or_new_user->exists) {
                if ($request->social_provider == 'apple') {
                    if ($request->name) {
                        $existing_or_new_user->name = $request->name;
                    } else {
                        $existing_or_new_user->name = 'Apple User';
                    }
                } else {
                    $existing_or_new_user->name = $social_user_details->name;
                }
                $existing_or_new_user->email = $social_user_details->email;
                $existing_or_new_user->email_verified_at = date('Y-m-d H:m:s');
            }

            $existing_or_new_user->save();

            return $this->loginSuccess($existing_or_new_user);
        }
    }

    // Guest user Account Create
    public function guestUserAccountCreate(Request $request)
    {
        $success = 1;
        $password = substr(hash('sha512', rand()), 0, 8);
        $isEmailVerificationEnabled = get_setting('email_verification');

        // User Create
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = addon_is_activated('otp_system') ? $request->phone : null;
        $user->password = Hash::make($password);
        $user->email_verified_at = $isEmailVerificationEnabled != 1 ? date('Y-m-d H:m:s') : null;
        $user->save();

        // Account Opening and verification(if activated) eamil send
        try {
            EmailUtility::customer_registration_email('registration_from_system_email_to_customer', $user, $password);
        } catch (\Exception $e) {
            $success = 0;
            $user->delete();
        }

        if ($success == 0) {
            return response()->json([
                'result' => false,
                'message' => translate('Something went wrong!')
            ]);
        }

        if ($isEmailVerificationEnabled == 1) {
            $user->notify(new AppEmailVerificationNotification());
        }

        // User Address Create
        $address = new Address();
        $address->user_id       = $user->id;
        $address->address       = $request->address;
        $address->country_id    = $request->country_id;
        $address->state_id      = $request->state_id;
        $address->city_id       = $request->city_id;
        $address->postal_code   = $request->postal_code;
        $address->phone         = $request->phone;
        $address->longitude     = $request->longitude;
        $address->latitude      = $request->latitude;
        $address->save();

        Cart::where('temp_user_id', $request->temp_user_id)
            ->update([
                'user_id' => $user->id,
                'temp_user_id' => null,
                'address_id' => $address->id
            ]);

        //create token
        $user->createToken('tokens')->plainTextToken;

        return $this->loginSuccess($user);
    }

    public function loginSuccess($user, $token = null, $tempUserId = null)
    {
        if (!$token) {
            $token = $user->createToken('API Token')->plainTextToken;
        }
        if ($tempUserId != null) {
            Cart::where('temp_user_id', $tempUserId)
                ->update([
                    'user_id' => $user->id,
                    'temp_user_id' => null
                ]);
        }
        return response()->json([
            'result' => true,
            'message' => translate('Successfully logged in'),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => null,
            'user' => [
                'id' => $user->id,
                'type' => $user->user_type,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'avatar_original' => uploaded_asset($user->avatar_original),
                'phone' => $user->phone,
                'points' => $user->points,
                'wss_token' => $user->wss_token,
                'email_verified' => $user->email_verified_at != null
            ]
        ]);
    }
    protected function loginFailed()
    {

        return response()->json([
            'result' => false,
            'message' => translate('Login Failed'),
            'access_token' => '',
            'token_type' => '',
            'expires_at' => null,
            'user' => [
                'id' => 0,
                'type' => '',
                'name' => '',
                'email' => '',
                'avatar' => '',
                'avatar_original' => '',
                'phone' => ''
            ]
        ]);
    }


    public function account_deletion()
    {
        if (auth()->user()) {
            Cart::where('user_id', auth()->user()->id)->delete();
        }

        $user_id = auth()->user()->id;
        $auth_user = User::find($user_id);
        $auth_user->tokens()->where('id', $auth_user->currentAccessToken()->id)->delete();
        $auth_user->customer_products()->delete();

        User::destroy(auth()->user()->id);

        return response()->json([
            "result" => true,
            "message" => translate('Your account deletion successfully done')
        ]);
    }

    public function getUserInfoByAccessToken(Request $request)
    {
        $token = PersonalAccessToken::findToken($request->access_token);
        if (!$token) {
            return $this->loginFailed();
        }
        $user = $token->tokenable;

        if ($user == null) {
            return $this->loginFailed();
        }

        return $this->loginSuccess($user, $request->access_token);
    }
}

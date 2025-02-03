<?php

namespace App\Http\Controllers\Api\V2;

use Hash;
use Storage;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Upload;
use App\Models\Address;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppEmailVerificationMail;
use Illuminate\Support\Facades\Validator;
use App\Notifications\AppEmailVerificationNotification;

class ProfileController extends Controller
{
    public function counters()
    {
        return response()->json([
            'cart_item_count' => Cart::where('user_id', auth()->user()->id)->count(),
            'wishlist_item_count' => Wishlist::where('user_id', auth()->user()->id)->count(),
            'order_count' => Order::where('user_id', auth()->user()->id)->count(),
        ]);
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = User::find(auth()->user()->id);
            if (!$user) {
                return response()->json([
                    'result' => false,
                    'message' => translate("User not found.")
                ]);
            }
            if (isset($request->name)) {
                $user->name = $request->name;
            }
            // if (isset($request->phone)) {
            //     $user->phone = $request->phone;
            // }
            // if (isset($request->email)) {
            //     $user->email = $request->email;
            // }
            if (isset($request->password) && $request->password != null) {
                $user->password = Hash::make($request->password);
            }
            $user->save();
            if (isset($request->state_id, $request->city_id, $request->address, $request->street, $request->apartment_no)) {
                $address = new Address();
                $address->country_id = 1;
                $address->user_id = $user->id;
                $address->state_id = $request->state_id;
                $address->city_id = $request->city_id;
                $address->address = $request->address;
                $address->street = $request->street;
                $address->apartment_no = $request->apartment_no;
                $address->save();
            }
            DB::commit();
            return response()->json([
                'result' => true,
                'message' => translate("Profile information updated")
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => false,
                'message' => translate("An error occurred: ") . $e->getMessage()
            ], 500);
        }
    }

    public function update_email_or_phone(Request $request)
    {
        DB::beginTransaction();
        try {
            $rules = [
                'verify_by' => 'required|in:phone,email',
            ];

            if ($request->verify_by == "email") {
                $rules['email_or_phone'] = 'required|email|unique:users,email';
            } elseif ($request->verify_by == "phone") {
                $rules['email_or_phone'] = 'required|unique:users,phone';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::find(auth()->user()->id);
            if (!$user) {
                return response()->json([
                    'result' => false,
                    'message' => translate("User not found.")
                ]);
            }
            $otp = "1111";
            if ($request->verify_by == "phone") {
                $user->verification_code = $otp;
                $user->new_phone = $request->email_or_phone;
            } elseif ($request->verify_by == "email") {
                $user->verification_code = $otp;
                $user->new_email = $request->email_or_phone;
                Mail::to($user->new_email)->send(new AppEmailVerificationMail($otp));
                // $user->notify(new AppEmailVerificationNotification());
            }
            $user->save();
            DB::commit();
            return response()->json([
                'result' => true,
                'message' => translate("OTP sent successfully")
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => false,
                'message' => translate("An error occurred: ") . $e->getMessage()
            ], 500);
        }
    }

    public function verify_otp(Request $request)
    {
        DB::beginTransaction();
        try {
            $rules = [
                'otp' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::find(auth()->user()->id);
            if (!$user) {
                return response()->json([
                    'result' => false,
                    'message' => translate("User not found.")
                ]);
            }

            if ($user->verification_code == $request->otp) {
                if ($user->new_phone != null) {
                    $user->phone = $user->new_phone;
                    $user->new_phone = null;
                    $user->new_phone = null;
                } elseif ($user->new_email != null) {
                    $user->email = $user->new_email;
                    $user->new_email = null;
                    $user->new_phone = null;
                }
                $user->verification_code = null;
                $user->save();
                DB::commit();
                return response()->json([
                    'result' => true,
                    'message' => translate("Profile information updated")
                ]);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => translate("OTP not verified")
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => false,
                'message' => translate("An error occurred: ") . $e->getMessage()
            ], 500);
        }
    }

    public function update_device_token(Request $request)
    {
        $user = User::find(auth()->user()->id);
        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => translate("User not found.")
            ]);
        }

        $user->device_token = $request->device_token;


        $user->save();

        return response()->json([
            'result' => true,
            'message' => translate("device token updated")
        ]);
    }

    public function updateImage(Request $request)
    {
        $user = User::find(auth()->user()->id);
        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => translate("User not found."),
                'path' => ""
            ]);
        }

        $type = array(
            "jpg" => "image",
            "jpeg" => "image",
            "png" => "image",
            "svg" => "image",
            "webp" => "image",
            "gif" => "image",
        );

        try {
            $image = $request->image;
            $request->filename;
            $realImage = base64_decode($image);

            $dir = public_path('uploads/all');
            $full_path = "$dir/$request->filename";

            $file_put = file_put_contents($full_path, $realImage); // int or false

            if ($file_put == false) {
                return response()->json([
                    'result' => false,
                    'message' => "File uploading error",
                    'path' => ""
                ]);
            }


            $upload = new Upload;
            $extension = strtolower(File::extension($full_path));
            $size = File::size($full_path);

            if (!isset($type[$extension])) {
                unlink($full_path);
                return response()->json([
                    'result' => false,
                    'message' => "Only image can be uploaded",
                    'path' => ""
                ]);
            }


            $upload->file_original_name = null;
            $arr = explode('.', File::name($full_path));
            for ($i = 0; $i < count($arr) - 1; $i++) {
                if ($i == 0) {
                    $upload->file_original_name .= $arr[$i];
                } else {
                    $upload->file_original_name .= "." . $arr[$i];
                }
            }

            //unlink and upload again with new name
            unlink($full_path);
            $newFileName = rand(10000000000, 9999999999) . date("YmdHis") . "." . $extension;
            $newFullPath = "$dir/$newFileName";

            $file_put = file_put_contents($newFullPath, $realImage);

            if ($file_put == false) {
                return response()->json([
                    'result' => false,
                    'message' => "Uploading error",
                    'path' => ""
                ]);
            }

            $newPath = "uploads/all/$newFileName";

            if (env('FILESYSTEM_DRIVER') == 's3') {
                Storage::disk('s3')->put(
                    $newPath,
                    file_get_contents(base_path('public/') . $newPath),
                    ['visibility' => 'public']
                );
                unlink(base_path('public/') . $newPath);
            }

            $upload->extension = $extension;
            $upload->file_name = $newPath;
            $upload->user_id = $user->id;
            $upload->type = $type[$upload->extension];
            $upload->file_size = $size;
            $upload->save();

            $user->avatar_original = $upload->id;
            $user->save();

            return response()->json([
                'result' => true,
                'message' => translate("Image updated"),
                'path' => uploaded_asset($upload->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
                'path' => ""
            ]);
        }
    }

    // not user profile image but any other base 64 image through uploader
    public function imageUpload(Request $request)
    {
        $user = User::find(auth()->user()->id);
        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => translate("User not found."),
                'path' => "",
                'upload_id' => 0
            ]);
        }

        $type = array(
            "jpg" => "image",
            "jpeg" => "image",
            "png" => "image",
            "svg" => "image",
            "webp" => "image",
            "gif" => "image",
        );

        try {
            $image = $request->image;
            $request->filename;
            $realImage = base64_decode($image);

            $dir = public_path('uploads/all');
            $full_path = "$dir/$request->filename";

            $file_put = file_put_contents($full_path, $realImage); // int or false

            if ($file_put == false) {
                return response()->json([
                    'result' => false,
                    'message' => "File uploading error",
                    'path' => "",
                    'upload_id' => 0
                ]);
            }


            $upload = new Upload;
            $extension = strtolower(File::extension($full_path));
            $size = File::size($full_path);

            if (!isset($type[$extension])) {
                unlink($full_path);
                return response()->json([
                    'result' => false,
                    'message' => "Only image can be uploaded",
                    'path' => "",
                    'upload_id' => 0
                ]);
            }


            $upload->file_original_name = null;
            $arr = explode('.', File::name($full_path));
            for ($i = 0; $i < count($arr) - 1; $i++) {
                if ($i == 0) {
                    $upload->file_original_name .= $arr[$i];
                } else {
                    $upload->file_original_name .= "." . $arr[$i];
                }
            }

            //unlink and upload again with new name
            unlink($full_path);
            $newFileName = rand(10000000000, 9999999999) . date("YmdHis") . "." . $extension;
            $newFullPath = "$dir/$newFileName";

            $file_put = file_put_contents($newFullPath, $realImage);

            if ($file_put == false) {
                return response()->json([
                    'result' => false,
                    'message' => "Uploading error",
                    'path' => "",
                    'upload_id' => 0
                ]);
            }

            $newPath = "uploads/all/$newFileName";

            if (env('FILESYSTEM_DRIVER') == 's3') {
                Storage::disk('s3')->put($newPath, file_get_contents(base_path('public/') . $newPath));
                unlink(base_path('public/') . $newPath);
            }

            $upload->extension = $extension;
            $upload->file_name = $newPath;
            $upload->user_id = $user->id;
            $upload->type = $type[$upload->extension];
            $upload->file_size = $size;
            $upload->save();

            return response()->json([
                'result' => true,
                'message' => translate("Image updated"),
                'path' => uploaded_asset($upload->id),
                'upload_id' => $upload->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
                'path' => "",
                'upload_id' => 0
            ]);
        }
    }

    public function checkIfPhoneAndEmailAvailable()
    {
        $phone_available = false;
        $email_available = false;
        $phone_available_message = translate("User phone number not found");
        $email_available_message = translate("User email  not found");

        $user = User::find(auth()->user()->id);

        if ($user->phone != null || $user->phone != "") {
            $phone_available = true;
            $phone_available_message = translate("User phone number found");
        }

        if ($user->email != null || $user->email != "") {
            $email_available = true;
            $email_available_message = translate("User email found");
        }
        return response()->json(
            [
                'phone_available' => $phone_available,
                'email_available' => $email_available,
                'phone_available_message' => $phone_available_message,
                'email_available_message' => $email_available_message,
            ]
        );
    }
}

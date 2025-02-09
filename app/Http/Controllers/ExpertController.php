<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Shop;
use App\Models\User;
use App\Models\Order;
use App\Models\State;
use App\Models\Expert;
use App\Models\Wishlist;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Models\ExpertTranaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ExpertController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search = $request->search ?? null;
        $approved = $request->approved_status ?? null;
        $verification_status = $request->verification_status ?? null;

        // Initialize the query
        $experts = Expert::query();

        // Apply search filter
        if ($sort_search != null) {
            $experts->where(function ($query) use ($sort_search) {
                $query->where('name', 'like', '%' . $sort_search . '%')
                    ->orWhere('email', 'like', '%' . $sort_search . '%');
            });
        }
        // Apply approved status filter
        if ($approved != null) {
            $experts->where('approved', $approved);
        }

        // Apply verification status filter
        if ($verification_status != null) {
            $experts->where('verification_status', $verification_status);
        }

        // Paginate the results
        $experts = $experts->with(['state:id,name', 'city:id,name', 'transactions'])->paginate(15);

        return view('backend.expert.index', compact('experts', 'sort_search', 'approved', 'verification_status'));
    }
    public function create()
    {
        $states = State::with('cities')->get();
        $users = User::where('user_type', 'staff')->get();
        return view('backend.expert.create', compact(['states', 'users']));
    }
    public function store(Request $request)
    {
        try {
            $request->validate(
                [
                    'name' => 'required|max:255',
                    'email' => 'required|email|unique:experts,email',
                    'phone' => ['required', 'regex:/^[0-9]{10,15}$/', 'unique:experts,phone'],
                    'state_id' => 'required|exists:states,id',
                    'city_id' => 'required|exists:cities,id',
                    'experience' => 'required|integer|min:1',
                    'price' => 'required|numeric|min:0',
                    'user_id' => 'required|numeric',
                    'password' => 'required|min:8|confirmed',
                ],
                [
                    'name.required' => translate('Name is required'),
                    'name.max' => translate('Max 255 characters'),
                    'email.required' => translate('Email is required'),
                    'email.email' => translate('Email must be a valid email address'),
                    'email.unique' => translate('A user exists with this email'),
                    'phone.required' => translate('Phone number is required'),
                    'phone.regex' => translate('Phone number must be between 10-15 digits'),
                    'phone.unique' => translate('A user exists with this phone number'),
                    'state_id.required' => translate('State is required'),
                    'state_id.exists' => translate('Invalid state selected'),
                    'city_id.required' => translate('City is required'),
                    'city_id.exists' => translate('Invalid city selected'),
                    'experience.required' => translate('Experience is required'),
                    'experience.integer' => translate('Experience must be a number'),
                    'experience.min' => translate('Experience must be at least 1 year'),
                    'price.required' => translate('Price is required'),
                    'price.numeric' => translate('Price must be a number'),
                    'price.min' => translate('Price must be a positive number'),
                    'password.required' => translate('Password is required'),
                    'password.min' => translate('Password must be at least 8 characters'),
                    'password.confirmed' => translate('Passwords do not match'),
                ]
            );
            $password = substr(hash('sha512', rand()), 0, 8);
            $expert = new Expert;
            $expert->name     = $request->name;
            $expert->email    = $request->email;
            $expert->phone    = $request->phone;
            $expert->state_id    = $request->state_id;
            $expert->city_id    = $request->city_id;
            $expert->experience    = $request->experience;
            $expert->price    = $request->price;
            $expert->user_id    = $request->user_id;
            $expert->password = Hash::make($password);
            $expert->wss_token = uniqid(bin2hex(random_bytes(16)), true);
            $expert->save();
            flash(translate('Expert has been added successfully'))->success();
            return back();
        } catch (\Exception $e) {
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }
    public function edit($id)
    {
        $states = State::with('cities')->get();
        $expert = Expert::findOrFail(decrypt($id));
        $users = User::where('user_type', 'staff')->get();
        return view('backend.expert.edit', compact(['expert', 'states', 'users']));
    }
    public function update(Request $request, $id)
    {
        try {
            $request->validate(
                [
                    'name' => 'required|max:255',
                    'email' => ['required', 'email', Rule::unique('experts', 'email')->ignore($id)],
                    'phone' => ['required', 'regex:/^[0-9]{10,15}$/', Rule::unique('experts', 'phone')->ignore($id)],
                    'state_id' => 'required|exists:states,id',
                    'city_id' => 'required|exists:cities,id',
                    'experience' => 'required|integer|min:1',
                    'price' => 'required|numeric|min:0',
                ],
                [
                    'name.required' => translate('Name is required'),
                    'name.max' => translate('Max 255 characters'),
                    'email.required' => translate('Email is required'),
                    'email.email' => translate('Email must be a valid email address'),
                    'email.unique' => translate('A user exists with this email'),
                    'phone.required' => translate('Phone number is required'),
                    'phone.regex' => translate('Phone number must be between 10-15 digits'),
                    'phone.unique' => translate('A user exists with this phone number'),
                    'state_id.required' => translate('State is required'),
                    'state_id.exists' => translate('Invalid state selected'),
                    'city_id.required' => translate('City is required'),
                    'city_id.exists' => translate('Invalid city selected'),
                    'experience.required' => translate('Experience is required'),
                    'experience.integer' => translate('Experience must be a number'),
                    'experience.min' => translate('Experience must be at least 1 year'),
                    'price.required' => translate('Price is required'),
                    'price.numeric' => translate('Price must be a number'),
                    'price.min' => translate('Price must be a positive number'),
                ]
            );
            $expert = Expert::findOrFail($id);
            $expert->name = $request->name;
            $expert->email = $request->email;
            $expert->phone = $request->phone;
            $expert->experience = $request->experience;
            $expert->state_id = $request->state_id;
            $expert->city_id = $request->city_id;
            $expert->save();
            flash(translate('Expert has been updated successfully'))->success();
            return redirect()->route('experts.index');
        } catch (\Exception $e) {

            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $shop = Shop::findOrFail($id);

        // Seller Product and product related data delete
        $products = $shop->user->products;
        foreach ($products as $product) {
            $product_id = $product->id;
            $product->product_translations()->delete();
            $product->categories()->detach();
            $product->stocks()->delete();
            $product->taxes()->delete();
            $product->frequently_bought_products()->delete();
            $product->last_viewed_products()->delete();
            $product->flash_deal_products()->delete();

            if ($product->delete()) {
                Cart::where('product_id', $product_id)->delete();
                Wishlist::where('product_id', $product_id)->delete();
            }
        }
        $orders = Order::where('user_id', $shop->user_id)->get();

        foreach ($orders as $key => $order) {
            OrderDetail::where('order_id', $order->id)->delete();
        }
        Order::where('user_id', $shop->user_id)->delete();

        User::destroy($shop->user->id);

        if (Shop::destroy($id)) {
            flash(translate('Seller has been deleted successfully'))->success();
            return redirect()->route('sellers.index');
        } else {
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    public function expert_transaction(Request $request){
        // validation 
        $expert = Expert::findOrFail($request->expert_id);
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1|max:'.$expert->amount,
            'body' => 'required|string',
            'date' => 'required|date|before_or_equal:today'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $expert->amount -= $request->amount;
        $expert->save();

        ExpertTranaction::create([
            'expert_id' => $request->expert_id,
            'amount' => $request->amount,
            'body' => $request->body,
            'title' => 'Add New Balance Is:' . $request->amount,
            'date' => $request->date
        ]);

        flash(translate('Transaction has been added successfully'))->success();
        return back();
    }
}

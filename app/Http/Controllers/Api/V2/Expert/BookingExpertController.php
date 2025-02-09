<?php

namespace App\Http\Controllers\Api\V2\Expert;

use Illuminate\Http\Request;
use App\Models\BookingExpert;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\BookingExpertResource;

class BookingExpertController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except('expert_index');
        $this->middleware('auth:expert')->only('expert_index');
    }
    public function expert_index()
    {
        $bookingExperts = BookingExpert::where('expert_id', auth()->guard('expert')->user()->id)->get();
        return response()->json([
            'message' => 'Booking experts fetched successfully',
            'data' => BookingExpertResource::collection($bookingExperts),
        ]);
    }
    public function index()
    {
        $bookingExperts = BookingExpert::where('user_id', auth()->guard('sanctum')->user()->id)->get();
        return response()->json([
            'message' => 'Booking experts fetched successfully',
            'data' => BookingExpertResource::collection($bookingExperts),
        ]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expert_id' => 'required|integer|exists:experts,id',
            'expert_slot_id' => 'required|integer|exists:expert_solts,id',
            'order_id' => 'required|integer|exists:orders,id',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $bookingExpert = BookingExpert::create([
            'expert_id' => $request->expert_id,
            'expert_slot_id' => $request->expert_slot_id,
            'order_id' => $request->order_id,
            'user_id' => auth()->guard('sanctum')->user()->id,
            'date' => $request->date,
        ]);

        return response()->json([
            'message' => 'Booking expert created successfully',
            'data' => BookingExpertResource::make($bookingExpert),
        ]);
    }
    public function show(string $id)
    {
        $bookingExpert = BookingExpert::find($id);
        if (!$bookingExpert) {
            return response()->json([
                'message' => 'Booking expert not found',
            ], 404);
        }
        return response()->json([
            'message' => 'Booking expert fetched successfully',
            'data' => BookingExpertResource::make($bookingExpert),
        ]);
    }
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'expert_id' => 'nullable|integer|exists:experts,id',
            'expert_slot_id' => 'nullable|integer|expert_solts,id',
            'order_id' => 'nullable|integer|exists:orders,id',
            'date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $bookingExpert = BookingExpert::find($id);
        if (!$bookingExpert) {
            return response()->json([
                'message' => 'Booking expert not found',
            ], 404);
        }

        $bookingExpert->update([
            'expert_id' => $request->expert_id ?? $bookingExpert->expert_id,
            'expert_slot_id' => $request->expert_slot_id ?? $bookingExpert->expert_slot_id,
            'order_id' => $request->order_id ?? $bookingExpert->order_id,
            'user_id' => auth()->guard('sanctum')->user()->id,
            'date' => $request->date ?? $bookingExpert->date,
        ]);

        return response()->json([
            'message' => 'Booking expert updated successfully',
            'data' => BookingExpertResource::make($bookingExpert),
        ]);
    }
    public function destroy(string $id)
    {
        $bookingExpert = BookingExpert::find($id);
        if (!$bookingExpert) {
            return response()->json([
                'message' => 'Booking expert not found',
            ], 404);
        }
        $bookingExpert->delete();
        return response()->json([
            'message' => 'Booking expert deleted successfully',
        ]);
    }

    public function booking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()->all(),
            ], 422);
        }
        $booking = BookingExpert::where('date', $request->date)
        ->where('expert_id',auth()->guard('expert')->user()->id)
        ->get();

        if ($booking->isEmpty()) {
            return response()->json([
                'result' => false,
                'message' => 'Not Booking Yet',
                'errors' => [],
            ], 404);
        }
        return response()->json([
            'result' => true,
            'message' => 'All Booking',
            'data' => BookingExpertResource::collection($booking),
        ], 200);
    }

    public function changeStatus(Request $request, $id)
    {
        $booking = BookingExpert::find($id);
        if (!$booking) {
            return response()->json([
                'result' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|numeric|in:1,2,3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $booking->status = $request->status;
        $booking->save();

        return response()->json([
            'result' => true,
            'message' => 'Booking status updated successfully',
            'data' => BookingExpertResource::make($booking),
        ]);
    }
}

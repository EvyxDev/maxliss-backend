<?php

namespace App\Http\Controllers\Api\V2\Expert;

use Illuminate\Http\Request;
use App\Models\BookingExpert;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\BookingExpertResource;
use App\Http\Resources\ExpertSlotResource;
use App\Models\ExpertSolt;

class SlotExpertController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:expert')->except('get');
    // }
    // public function index()
    // {
    //     $slotExpers = ExpertSolt::where('expert_id', auth()->guard('expert')->user()->id)->get();
    //     return response()->json([
    //         'result' => true,
    //         'message' => 'Slots experts fetched successfully',
    //         'data' => ExpertSlotResource::collection($slotExpers),
    //     ]);
    // }
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'expert_id' => 'required|integer|exists:experts,id',
    //         'day_id' => 'required|integer|exists:days,id',
    //         'start' => 'required|string',
    //         'end' => 'required|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'result' => false,
    //             'message' => 'Validation error',
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     $bookingExpert = BookingExpert::create([
    //         'expert_id' => $request->expert_id,
    //         'day_id' => $request->day_id,
    //         'order_id' => $request->order_id,
    //         'start' => $request->start,
    //         'end' => $request->end,
    //     ]);

    //     return response()->json([
    //         'result' => true,
    //         'message' => 'Slot expert created successfully',
    //         'data' => ExpertSlotResource::make($bookingExpert),
    //     ]);
    // }

    // public function show(string $id)
    // {
    //     $slotExpert = ExpertSolt::find($id);
    //     if (!$slotExpert) {
    //         return response()->json([
    //             'result' => false,
    //             'message' => 'Slot expert not found',
    //         ], 404);
    //     }
    //     return response()->json([
    //         'message' => 'Booking expert fetched successfully',
    //         'data' => ExpertSlotResource::make($slotExpert),
    //     ]);
    // }
    // public function update(Request $request, string $id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'expert_id' => 'nullable|integer|exists:experts,id',
    //         'day_id' => 'nullable|integer|days,id',
    //         'start' => 'nullable|string',
    //         'end' => 'nullable|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'result' => false,
    //             'message' => 'Validation error',
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     $expertSlot = ExpertSolt::find($id);
    //     if (!$expertSlot) {
    //         return response()->json([
    //             'result' => false,
    //             'message' => 'Slot expert not found',
    //         ], 404);
    //     }

    //     $expertSlot->update([
    //         'expert_id' => $request->expert_id ?? $expertSlot->expert_id,
    //         'day_id' => $request->day_id ?? $expertSlot->day_id,
    //         'order_id' => $request->order_id ?? $expertSlot->order_id,
    //         'start' => $request->start ?? $expertSlot->start,
    //         'end' => $request->end ?? $expertSlot->end,
    //     ]);

    //     return response()->json([
    //         'result' => true,
    //         'message' => 'Slot expert updated successfully',
    //         'data' => BookingExpertResource::make($expertSlot),
    //     ]);
    // }
    // public function destroy(string $id)
    // {
    //     $slotExpert = ExpertSolt::find($id);
    //     if (!$slotExpert) {
    //         return response()->json([
    //             'result' => false,
    //             'message' => 'Slot expert not found',
    //         ], 404);
    //     }
    //     $slotExpert->delete();
    //     return response()->json([
    //         'result'
    //         'message' => 'Slot expert deleted successfully',
    //     ]);
    // }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expert_id' => 'required|integer|exists:experts,id',
            'day_id' => 'required|integer|exists:days,id',
            'date' => 'required|date_format:Y-m-d'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $slots = ExpertSolt::with('booking')
            ->where('expert_id', $request->expert_id)
            ->where('day_id', $request->day_id)
            ->get();

        $filteredSlots = $slots->reject(function ($slot) use ($request) {
            return $slot->booking->contains('date', $request->date);
        });

        return response()->json([
            'result' => true,
            'message' => 'Slots fetched successfully',
            'data' => ExpertSlotResource::collection($filteredSlots),
        ], 200);
    }


}

<?php

namespace App\Http\Controllers\Api\V2\Expert;

use App\Models\Expert;
use App\Models\ExpertSolt;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\ExpertResource;
use Illuminate\Support\Facades\Validator;

class ExpertController extends Controller
{
    public function index()
    {
        $experts = Expert::get();
        return response()->json([
            'message' => 'All expert fetched successfully',
            'data' => ExpertResource::collection($experts)
        ], 200);
    }

    public function show($id)
    {
        $expert = Expert::find($id);
        return response()->json([
            'message' => 'Expert fetched successfully',
            'data' => ExpertResource::make($expert)
        ], 200);
    }

    public function filter(Request $request)
    {
        $valitor = Validator::make($request->all(), [
            'day_id' => 'required|exists:days,id',
            'expert_id' => 'required|exists:experts,id'
        ]);

        if ($valitor->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $valitor->errors()
            ], 422);
        }

        $filter = ExpertSolt::where('day_id', $request->day_id)
        ->where('expert_id', $request->expert_id)
        ->select('start', 'end')
        ->get();
        return response()->json([
            'message' => 'Expert fetched successfully',
            'data' => $filter
        ], 200);
    }
}

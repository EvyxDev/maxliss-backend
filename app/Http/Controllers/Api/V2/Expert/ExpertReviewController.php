<?php

namespace App\Http\Controllers\Api\V2\Expert;

use App\Models\ExpertReview;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ExpertReviewResource;

class ExpertReviewController extends Controller
{
    public function constract()
    {
        $this->middleware('auth:expert')->except('store');
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'review' => 'nullable|string',
            'expert_id' => 'required|exists:experts,id',
            'id_card' => 'required|boolean',
            'lab_coat' => 'required|boolean',
            'efficiency' => 'required|numeric|min:0|max:5',
            'personal_hygiene' => 'required|numeric|min:0|max:5',
            'smell' => 'required|numeric|min:0|max:5',
            'overall_appearance' => 'required|numeric|min:0|max:5',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $rateing = 0;
        if ($request->id_card) {
            $rateing += 5;
        }
        if ($request->lab_coat) {
            $rateing += 5;
        }
        $rateing += $request->efficiency;
        $rateing += $request->personal_hygiene;
        $rateing += $request->smell;
        $rateing += $request->overall_appearance;
        $rateing /= 6;

        $rateing = round($rateing, 1);

        $review = ExpertReview::create([
            'review' => $request->review,
            'rating' => $rateing,
            'expert_id' => $request->expert_id,
            'user_id' => auth()->guard('sanctum')->user()->id,
            'id_card' => $request->id_card,
            'lab_coat' => $request->lab_coat,
            'efficiency' => $request->efficiency,
            'personal_hygiene' => $request->personal_hygiene,
            'smell' => $request->smell,
            'overall_appearance' => $request->overall_appearance,
            'image' => $request->hasFile('image') ? uploadImage($request, 'image', 'expert/review') : null,
        ]);

        return response()->json([
            'message' => 'Expert review stored successfully',
            'data' => ExpertReviewResource::make($review)
        ], 201);
    }

    // show expert review
    public function show($id)
    {
        $review = ExpertReview::where('expert_id', $id)->get();
        
        return response()->json([
            'message' => 'Expert review fetched successfully',
            'data' => ExpertReviewResource::collection($review)
        ], 200);
    }
}

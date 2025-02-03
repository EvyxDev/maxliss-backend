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
        $this->middleware('auth:sanctum');
    }
    // store expert review
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'review' => 'required|string',
            'rating' => 'required|numeric|min:1|max:5',
            'expert_id' => 'required|exists:experts,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        $review = ExpertReview::create([
            'review' => $request->review,
            'rating' => $request->rating,
            'expert_id' => $request->expert_id,
            'user_id' => auth()->user()->id,
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

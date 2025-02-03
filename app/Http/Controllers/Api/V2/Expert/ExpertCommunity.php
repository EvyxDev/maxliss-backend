<?php

namespace App\Http\Controllers\Api\V2\Expert;

use App\Models\Community;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommunityResource;
use Illuminate\Support\Facades\Validator;

class ExpertCommunity extends Controller
{
    public function constract()
    {
        $this->middleware('auth:expert');
    }
    // Get expert community
    public function index()
    {
        // if (!auth()->guard('expert')->check()) {
        //     return response()->json([
        //         'kresult' => false,
        //         'message' => 'Expert unauthenticated',
        //         'data' => []
        //     ], 404);
        // }
        $communities = Community::where('expert_id', auth()->guard('expert')->user()->id)->latest()->get();
        return response()->json([
            'result' => true,
            'message' => 'Expert community fetched successfully',
            'data' => CommunityResource::collection($communities)
        ], 200);
    }
    // Store expert community
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'images' => 'required|array',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $images = '';
        if ($request->hasFile('images')) {
            $images = uploadImages($request, 'images', 'community');
        }
        $community = Community::create([
            'title' => $request->title,
            'image' => $images,
            'expert_id' => auth()->guard('expert')->user()->id,
        ]);

        return response()->json([
            'result' => true,
            'message' => 'Expert community stored successfully',
            'data' => CommunityResource::make($community)
        ], 201);
    }
    // Update expert community
    public function update(Request $request, $id)
    {
        $community = Community::where('id', $id)->where('expert_id', auth()->guard('expert')->user()->id)->first();
        if (!$community) {
            return response()->json([
                'result' => false,
                'message' => 'Community not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        $images = updateImages($request, 'images', 'community',$id,$request->old_images);
        $community->update([
            'title' => $request->title,
            'image' => $images,
        ]);

        return response()->json([
            'result' => true,
            'message' => 'Expert community updated successfully',
            'data' => CommunityResource::make($community)
        ], 200);
    }
    // Delete expert community
    public function destroy($id)
    {
        $community = Community::where('id', $id)->where('expert_id', auth()->guard('expert')->user()->id)->first();
        if (!$community) {
            return response()->json([
                'result' => false,
                'message' => 'Community not found',
                'data' => []
            ], 404);
        }
        deleteImages($community->image);
        $community->delete();
        return response()->json([
            'result' => true,
            'message' => 'Expert community deleted successfully',
            'data' => []
        ], 200);
    }
    // show
    public function show($id)
    {

        $community = Community::where('id', $id)->where('expert_id', auth()->guard('expert')->user()->id)->first();
        if (!$community) {
            return response()->json([
                'result' => false,
                'message' => 'Community not found',
                'data' => []
            ], 404);
        }
        return response()->json([
            'result' => true,
            'message' => 'Expert community fetched successfully',
            'data' => CommunityResource::make($community)
        ], 200);
    }
    // likes
    public function like(Request $request)
    {
        // dd(auth()->guard('expert')->user()->id);
        $validator = Validator::make($request->all(), [
            'community_id' => 'required|exists:communities,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $community = Community::find($request->community_id);
        if (!$community) {
            return response()->json([
                'result' => false,
                'message' => 'Community not found',
                'data' => []
            ], 404);
        }
        if (auth()-> guard('expert')->check()) {
            $user = auth()->guard('expert')->user();
            $like = $community->likes()->where('user_id', $user->id)->where('type',2)->first();
            if ($like) {
                $like->delete();
                return response()->json([
                    'result' => true,
                    'message' => 'Community unliked successfully',
                    'data' => []
                ], 200);
            }else{
                $community->likes()->create([
                    'user_id' => $user->id,
                    'type' => '2'
                ]);
                return response()->json([
                    'result' => true,
                    'message' => 'Community liked successfully',
                    'data' => []
                ], 200);
            }
        }
        if (auth()->guard('web')->check()) {
            $user = auth()->guard('web')->user();
            $like = $community->likes()->where('user_id', $user->id)->where('type',1)->first();
            if ($like) {
                $like->delete();
                return response()->json([
                    'result' => true,
                    'message' => 'Community unliked successfully',
                    'data' => []
                ], 200);
            }elseif(auth()->guard('web')->check()){
                $community->likes()->create([
                    'user_id' => auth()->guard('web')->user()->id,
                    'type' => 1
                ]);
                return response()->json([
                    'result' => true,
                    'message' => 'Community liked successfully',
                    'data' => []
                ], 200);
            }
        } 
        if(!auth()->guard('expert')->check()  || !auth()->guard('web')->check()) {
            return response()->json([
                'result' => false,
                'message' => 'User unauthenticated',
                'data' => []
            ], 404);
        }
    }


    public function get()
    {

        $communities = Community::latest()->get();
        return response()->json([
            'result' => true,
            'message' => 'Expert community fetched successfully',
            'data' => CommunityResource::collection($communities)
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Api\V2\Expert;

use App\Models\Answer;
use App\Models\Message;
use App\Models\NewMessage;
use App\Models\UserAnswers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessagesResource;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:expert')->only('index');
        $this->middleware('auth:sanctum')->only(['indexUser', 'indexBootUser']);
    }
    public function index()
    {
        $messages = NewMessage::where('chat_id', auth()->guard('expert')->user()->id)
            ->where('chat_type', 'expert')
            ->with(['sender_expert:id,name,image', 'receiver:id,name,avatar'])
            ->select(['id', 'sender_id', 'receiver_id', 'message', 'created_at'])
            ->get();

        return response()->json([
            'result' => true,
            'message' => 'Messages fetched successfully',
            'data' => MessagesResource::collection($messages)
        ], 200);
    }
    public function indexUser()
    {
        $messages = NewMessage::where('chat_id', auth()->guard('sanctum')->user()->id)
        ->where('chat_type', 'user')
        ->with(['sender', 'receiver'])
        ->get();
        return response()->json([
            'result' => true,
            'message' => 'Messages fetched successfully',
            'data' => MessagesResource::collection($messages)
        ], 200);
    }
    public function indexBootUser()
    {
        $userId = auth()->guard('sanctum')->user()->id;

        $answers = UserAnswers::where('user_id', $userId)
            ->selectRaw('MIN(id) as id, unique_id, user_id, GROUP_CONCAT(answer_id) as answer_ids')
            ->groupBy('unique_id', 'user_id')
            ->get();

        $answers->transform(function ($item) {
            $answerIds = explode(',', $item->answer_ids);
            $item->answers = Answer::whereIn('id', $answerIds)
            ->with('question:id,name')
            ->get();
            return $item;
        });

        return response()->json([
            'result' => true,
            'message' => 'Messages fetched successfully',
            'data' => $answers
        ], 200);
    }



    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {

    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}

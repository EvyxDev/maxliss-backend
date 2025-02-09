<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V2\Seller\OrderCollection;

class BookingExpertResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => UserResource::make($this->user),
            'expert' => ExpertResource::make($this->expert),
            'expert_slot' => ExpertSlotResource::make($this->expertSlot),
            'order_id' => $this->order_id,
            'date' => $this->date,
            'status' => (int) $this->status,
            'lon' => $this->address->longitude ?? null,
            'lat' => $this->address->latitude ?? null,
            'answer&question' => $this->getAnswersWithQuestions($this->answer_id),
            'created_at' => $this->created_at->format('Y-m-d'),
            'updated_at' => $this->updated_at->format('Y-m-d'),
        ];
    }

    public function getAnswersWithQuestions($ids)
    {
        $answerIds = explode(",", $ids);

        $answersWithQuestions = Answer::whereIn('id', $answerIds)
            ->with('question')
            ->get()
            ->map(function ($answer) {
                return [
                    'question' => $answer->question->name,
                    'answer' => $answer->answer,
                ];
            });

        return $answersWithQuestions;
    }
}

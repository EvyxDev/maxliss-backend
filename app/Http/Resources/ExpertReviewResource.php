<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpertReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return
        [
            'id' => $this->id,
            'user' => $this->user->name ?? null,
            'expert_id' => $this->expert->name ?? null,
            'rate' => $this->rating,
            'review' => $this->review,
            'image' => $this->image ? env('APP_URL') . '/public/' . $this->image ?? null : null,
            'created_at' => $this->created_at,
        ];
    }
}

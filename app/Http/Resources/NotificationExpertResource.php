<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V2\Seller\OrderCollection;

class NotificationExpertResource extends JsonResource
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
            'expert_id' => $this->expert->name ?? null,
            'title' => $this->title ?? null,
            'body' => $this->body ?? null,
            'is_read' => $this->is_read,
            'created_at' => $this->created_at->format('Y-m-d'),
            'updated_at' => $this->updated_at->format('Y-m-d'),
        ];
    }
}

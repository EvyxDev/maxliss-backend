<?php

namespace App\Http\Resources;

use App\Http\Resources\V2\Seller\OrderCollection;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'created_at' => $this->created_at->format('Y-m-d'),
            'updated_at' => $this->updated_at->format('Y-m-d'),
        ];
    }
}

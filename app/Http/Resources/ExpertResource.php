<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpertResource extends JsonResource
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
            'name' => $this->name ?? null,
            'email' => $this->email ?? null,
            'image' => env('APP_URL') . '/public/' . $this->image ?? null,
            'phone' => $this->phone ?? null,
            'city' => $this->city->name ?? null,
            'state' => $this->state->name ?? null,
            'lat' => $this->lat ?? null,
            'lon' => $this->lon ?? null,
            'price' => $this->price ?? null,
            'experience' => $this->experience,
            'rating_count' => count($this->reviews),
            'rating' => $this->reviews->avg('rating'),
            'created_at' => $this->created_at->format('d-m-Y'),
            'updated_at' => $this->updated_at->format('d-m-Y'),
            'slots' => ExpertSlotResource::collection(resource: $this->slots),
            'community' => CommunityResource::collection( $this->communities),
        ];
    }
}

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
            'name' => $this->name,
            'email' => $this->email,
            'image' => env('APP_URL') . '/public/' . $this->image,
            'phone' => $this->phone,
            'city' => $this->city->name,
            'state' => $this->state->name,
            'lat' => $this->lat,
            'lon' => $this->lon,
            'price' => $this->price,
            'experience' => $this->experience,
            'rating' => $this->reviews->avg('rating'),
            'created_at' => $this->created_at->format('d-m-Y'),
            'updated_at' => $this->updated_at->format('d-m-Y'),
            'slots' => ExpertSlotResource::collection(resource: $this->slots),
        ];
    }
}

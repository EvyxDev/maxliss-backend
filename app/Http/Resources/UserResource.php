<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'phone' => $this->phone,
            'user_type' => $this->user_type,
            'city' => $this->city,
            'avatar' => $this->avatar ? env('APP_URL') . '/public/' . $this->avatar : null,
            'postal_code' => $this->postal_code,
            'points' => $this->points,
            'created_at' => $this->created_at->format('Y-m-d') ?? null,
            'updated_at' => $this->updated_at->format('Y-m-d') ?? null,
        ];
    }
}

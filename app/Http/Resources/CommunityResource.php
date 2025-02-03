<?php

namespace App\Http\Resources;

use App\Models\PostLike;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommunityResource extends JsonResource
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
            'expert' => [
                'id' => $this->expert->id ?? null,
                'name' => $this->expert->name,
                'email' => $this->expert->email,
                'location' => $this->expert->state->name .' , '. $this->expert->city->name,
                'image' => env('APP_URL') . '/public/' . trim($this->expert->image),
            ],
            'title' => $this->title,
            'images' => array_map(function ($image) {
                return env('APP_URL') . '/public/' . trim($image);
            }, explode(',', $this->image)),
            'time' => Carbon::parse($this->created_at)->diffForHumans(),
            'likes' => $this->likes->count(),
            'is_wishlist' => $this->checkWishlist(),
        ];
    }
    protected function checkWishlist(): bool
    {
        if (auth()->guard('expert')->check()) {
            return PostLike::where('user_id', auth()->guard('expert')->user()->id)
                ->where('type', 2)
                ->exists();
        } elseif (auth()->guard('sanctum')->check()) {
            return PostLike::where('user_id', auth()->guard('sanctum')->user()->id)
                ->where('type', 1)
                ->exists();
        }

        return false;
    }
}

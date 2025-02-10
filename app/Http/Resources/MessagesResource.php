<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessagesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [];
        if($this->sender_expert != null){
            $data = [
                'id' => $this->id,
                'message' => $this->message,
                'sender' => [
                    'id' => $this->sender_expert->id ?? null,
                    'name' => $this->sender_expert->name ?? null,
                    'image' => $this->sender_expert->image ? env('APP_URL') . '/public/' . $this->sender_expert->image : null,
                ],
                'receiver' => [
                    'id' => $this->receiver->id ?? null,
                    'name' => $this->receiver->name ?? null,
                    'avatar' => $this->receiver->avatar ? env('APP_URL') . '/public/' . $this->receiver->avatar : null,
                ],
                'created_at' => $this->created_at,
            ];
        }
        else{
            $data = [
                'id' => $this->id,
                'message' => $this->message,
                'sender' => [
                    'id' => $this->sender->id ?? null,
                    'name' => $this->sender->name ?? null,
                    'avatar' => $this->sender->avatar ? env('APP_URL') . '/public/' . $this->sender->avatar : null,
                ],
                'receiver' => [
                    'id' => $this->receiver->id ?? null,
                    'name' => $this->receiver->name ?? null,
                    // 'avatar' => $this->receiver->avatar ? env('APP_URL') . '/public/' . $this->receiver->avatar : null,
                    'avatar' => !empty($this->receiver->avatar)
                    ? env('APP_URL') . '/public/' . $this->receiver->avatar
                        : null,
                ],
                'created_at' => $this->created_at,
            ];
        }
        return $data;
    }
}

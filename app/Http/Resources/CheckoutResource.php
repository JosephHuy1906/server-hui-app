<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutResource extends JsonResource
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
            'user' =>  [
                'user_id' => $this->user_id,
                'user_name' => $this->user->name,
                'user_avatar' => $this->user->avatar
            ],
            'price' => $this->price,
            'room' =>  $this->room_id,
            'description' => $this->description,
            'status' => $this->status,
            'user_win_hui' =>  $this->user_win_hui_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

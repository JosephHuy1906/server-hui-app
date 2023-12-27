<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
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
            'title' => $this->title,
            'price_room' => $this->price_room,
            'avatar' => $this->avatar,
            'commission_percentage' => $this->commission_percentage,
            'date_room_end' => $this->date_room_end,
            'total_user' => $this->total_user,
            'payment_time' => $this->payment_time,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user_count' => $this->users_count ?? 0,
            'is_near_end' => false,
        ];
    }
}
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'room' => [
                'room_id' => $this->room_id,
                'room_title' => $this->room->title, 
                'room_total_user' => $this->room->total_user,
            ],
            'user' =>  [
                'user_id' => $this->user_id,
                'user_name' => $this->user->name,
                'user_avatar' => $this->user->avatar
            ],
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

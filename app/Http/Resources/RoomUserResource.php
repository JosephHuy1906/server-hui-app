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
                'user_id' => $this->user->id,
                'user_name' => $this->user->name,
                'user_avatar' => $this->user->avatar
            ],
            'payments' => $this->payments->map(function ($payment) {
                return [
                    'user_id' => $payment->user_id,
                    'status' => $payment->status,
                    'description' => $payment->description,
                    'price_pay' => $payment->price_pay,
                    'created_at' => $payment->created_at,
                ];
            }),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

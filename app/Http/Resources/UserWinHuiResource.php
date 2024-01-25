<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserWinHuiResource extends JsonResource
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
            'user' => [
                'user_id' => $this->user_id,
                'name' => $this->user->name,
                'avatar' => $this->user->avatar,
                'phone' => $this->user->phone,
            ],
            'commission_percentage' => $this->commission_percentage,
            'price_pay_hui' => $this->price_pay_hui,
            'total_auction' => $this->total_auction,
            'room' => [
                'room_id' => $this->room_id,
                'room_name' => $this->room->title,
            ],
            'total_money_received' => $this->total_money_received,
            'total_amount_payable' => $this->total_amount_payable,
            'status_admin' => $this->status_admin,
            'status_user' => $this->status_user,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

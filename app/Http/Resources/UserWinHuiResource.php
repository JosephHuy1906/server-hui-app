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
            'user_id' => $this->user_id,
            'commission_percentage' => $this->commission_percentage,
            'price_pay_hui' => $this->price_pay_hui,
            'total_auction' => $this->total_auction,
            'room_id' => $this->room_id,
            'total_money_received' => $this->total_money_received,
            'total_amount_payable' => $this->total_amount_payable,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

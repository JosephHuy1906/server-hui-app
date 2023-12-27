<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionHuiDetailResource extends JsonResource
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
                'user_name' => $this->user->name,
                'user_avatar' => $this->user->avatar,
            ],
            'auction_hui_id ' => $this->auction_hui_id,
            'starting_price' => $this->starting_price,
            'total_price' => $this->total_price,
            'auction_percentage' => $this->auction_percentage,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

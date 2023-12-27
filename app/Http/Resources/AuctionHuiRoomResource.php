<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionHuiRoomResource extends JsonResource
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
            'room_id' => $this->room_id,
            'starting_price' => $this->starting_price,
            'time_end' => $this->time_end,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

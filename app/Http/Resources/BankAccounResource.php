<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankAccounResource extends JsonResource
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
            'number_bank' => $this->price,
            'code' =>  $this->room_id,
            'short_name' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

    }
}

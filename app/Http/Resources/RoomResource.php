<?php

namespace App\Http\Resources;

use App\Models\AuctionHuiRoom;
use App\Models\RoomUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        $auctionHuiRoomId = AuctionHuiRoom::where('room_id', $this->id)->get();
        $room_user = RoomUser::where('room_id', $this->id)->get();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'price_room' => $this->price_room,
            'avatar' => $this->avatar,
            'commission_percentage' => $this->commission_percentage,
            'date_room_end' => $this->date_room_end,
            'total_user' => $this->total_user,
            'payment_time' => $this->payment_time,
            'accumulated_amount' => $this->accumulated_amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user_count' => ($this->users_count - 1) ?? 0,
            'status' => $this->status,
            'auction_hui_room_id' => $auctionHuiRoomId->map(function ($auction) {
                return [
                    'id' => $auction->id,
                    'starting_price' => $auction->starting_price,
                    'time_end' => $auction->time_end,
                ];
            }),
            'is_near_end' => false,
            'room_user' => $room_user->map(function ($item) {
                $user = User::find($item->user_id);
                return [
                    'room_user_id' => $item->id,
                    'status' => $item->status,
                    'user' => [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'avatar' => $user->avatar,
                        'role' => $user->role,
                        'rank' => $user->rank,
                        'phone' => $user->phone,
                    ]
                ];
            }),

        ];
    }
}

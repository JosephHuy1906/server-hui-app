<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoomResource;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{

    public function getAllRoomsWithUsers()
    {
        $rooms = Room::withCount('users')->with(['users'])->get();

        return response()->json([
            'success' => true,
            'data' => RoomResource::collection($rooms),
        ]);
    }

    public function getRoomsByUserId($userId)
    {
        $userRooms = Room::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->withCount('users')->with(['users'])->get();

        return response()->json([
            'success' => true,
            'data' => RoomResource::collection($userRooms),
        ]);
    }
}

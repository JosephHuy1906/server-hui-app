<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoomResource;
use App\Models\AuctionHuiRoom;
use App\Models\Room;
use App\Models\RoomUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{

    public function getAllRoomsWithUsers($item)
    {
        try {
            $response = new ResponseController();
            $rooms = Room::withCount('users')->with(['users'])
                ->take($item)
                ->get();
            return $response->successResponse('Get room all success',  RoomResource::collection($rooms), 200);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }

    public function getRoomsByUserId($userId, $item)
    {
        try {
            $response = new ResponseController();
            $noti = new NotificationController();
            $us = User::find($userId);
            if (!$us) {
                return $response->errorResponse('User không tồn tại', null, 404);
            }
            $userRooms = Room::whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->withCount('users')->with(['users'])->take($item)->get();
            $currentDate = now();

            $check_day = $userRooms->filter(function ($room) use ($currentDate) {
                $dateEnd = $room->date_room_end;
                if ($dateEnd && $currentDate->diffInDays($dateEnd) < 1) {
                    $room->is_near_end = true;
                    return true;
                }
                $room->is_near_end = false;
                return true;
            });

            if (!$check_day) {
                $noti->postNotification($item, 'user', 'Nhóm còn một ngày nữa sẽ giải tán', $userId);
            }
            return $response->successResponse('Get room by User success', RoomResource::collection($userRooms), 200);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }

    public function addroom(Request $request)
    {
        try {
            $response = new ResponseController();
            $admin = new RoomUserController();
            $validate = Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'price_room' => 'required',
                    'commission_percentage' => 'required',
                    'date_room_end' => 'required',
                    'payment_time' => 'required',
                    'avatar' => 'sometimes|required|image|mimes:jpeg,png,jpg|max:4048',
                    'total_user' => 'required'
                ]
            );

            if ($validate->fails()) {
                return $response->errorResponse('Input error value', $validate->errors(), 400);
            }

            $addroom = Room::create($request->all());
            $room_id = $addroom->id;
            $admin->addAdminForRoom($room_id, '3a7a300a-13b3-4028-88d0-4f4ba1394099');
            return $response->successResponse('Create room success', new RoomResource($addroom), 201);
        } catch (\Throwable $err) {
            return $response->errorResponse('Create room faill', $err->getMessage(), 500);
        }
    }

    public function getRoomsByCount($item)
    {
        try {
            $response = new ResponseController();
            $rooms = Room::withCount('users')->with(['users'])
                ->take($item)
                ->orderBy('users_count', 'desc')
                ->get();
            return $response->successResponse('Get room all success',  RoomResource::collection($rooms), 200);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }
    public function getRoomsByPrice($item)
    {
        try {
            $response = new ResponseController();
            $rooms = Room::withCount('users')->with(['users'])
                ->take($item)
                ->orderBy('price_room', 'asc')
                ->get();
            return $response->successResponse('Get room all success',  RoomResource::collection($rooms), 200);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }
}

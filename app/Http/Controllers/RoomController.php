<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoomResource;
use App\Models\Room;
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

            return $response->successResponse('Get room all success', RoomResource::collection($rooms), 200);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }

    public function getRoomsByUserId($userId, $item)
    {
        try {
            $response = new ResponseController();
            $noti = new NotificationController();
            $userRooms = Room::whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->withCount('users')->with(['users'])->take($item)->get();
            $currentDate = now();

            $check_day = $userRooms->filter(function ($room) use ($currentDate) {
                // Kiểm tra nếu date_room_end còn một ngày nữa là đến
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
            $admin->addAdminForRoom($room_id, 'f472bb03-2020-4c64-a376-ab9c3ee5a684');
            return $response->successResponse('Create room success', new RoomResource($addroom), 201);
        } catch (\Throwable $err) {
            return $response->errorResponse('Create room faill', $err->getMessage(), 500);
        }
    }
}

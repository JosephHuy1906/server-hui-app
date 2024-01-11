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

            $this->checkAndUpdateRoomsStatus();
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
            $this->checkAndUpdateRoomsStatus();
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
                    'total_user' => 'required',
                    'status' => 'Open'
                ]
            );

            if ($validate->fails()) {
                return $response->errorResponse('Input error value', $validate->errors(), 400);
            }

            $addroom = Room::create($request->all());
            $room_id = $addroom->id;
            $admin->addAdminForRoom($room_id, '4bdc395e-77d4-4602-8e0f-af6bb401560f');
            return $response->successResponse('Create room success', new RoomResource($addroom), 201);
        } catch (\Throwable $err) {
            return $response->errorResponse('Create room faill', $err->getMessage(), 500);
        }
    }

    public function updateStatusRoom(Request $request)
    {
        try {
            $response = new ResponseController();
            $validate = Validator::make(
                $request->all(),
                [
                    'id' => 'required',
                    'status' => 'required'
                ]
            );

            if ($validate->fails()) {
                return $response->errorResponse('Input error value', $validate->errors(), 400);
            }
            $find = Room::find($request->id);
            if (!$find)  return $response->errorResponse("Room does not exist", null, 404);
            $update = $find->update([
                'status' => $request->status
            ]);
            return $response->successResponse('Update status room success', null, 201);
        } catch (\Throwable $err) {
            return $response->errorResponse('Update status room faill', $err->getMessage(), 500);
        }
    }
    public function updateInfoRoom(Request $request, $id)
    {
        try {
            $response = new ResponseController();
            $validate = Validator::make(
                $request->all(),
                [
                    'title' => 'sometimes|required',
                    'price_room' => 'sometimes|required',
                    'commission_percentage' => 'sometimes|required',
                    'payment_time' => 'sometimes|required',
                    'date_room_end' => 'sometimes|required',
                    'total_user' => 'sometimes|required',
                ]
            );
            if ($validate->fails()) {
                return $response->errorResponse('Input error value', $validate->errors(), 400);
            }
            $find = Room::find($id);
            if (!$find)  return $response->errorResponse("Room does not exist", null, 404);

            $data = $request->all();
            $find->update($data);
            $updatedRoom = Room::find($id);
            return $response->successResponse('Cập nhập chi tiết phòng thành công', new RoomResource($updatedRoom), 201);
        } catch (\Throwable $err) {
            return $response->errorResponse('Update info room faill', $err->getMessage(), 500);
        }
    }

    public function getRoomsByCount($item)
    {
        try {
            $response = new ResponseController();
            $this->checkAndUpdateRoomsStatus();
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
            $this->checkAndUpdateRoomsStatus();
            return $response->successResponse('Get room all success',  RoomResource::collection($rooms), 200);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }
    public function getDetailRoom($id)
    {
        try {
            $response = new ResponseController();
            $data = Room::withCount('users')->with(['users'])->findOrFail($id);
            $this->checkAndUpdateRoomsStatus();
            return $response->successResponse('Get Detail room', new RoomResource($data), 200);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }

    protected function checkAndUpdateRoomsStatus()
    {
        try {
            $response = new ResponseController();
            $notication = new NotificationController();
            $rooms = Room::withCount('users')->get();
            foreach ($rooms as $room) {
                if ($room->total_user == ($room->users_count - 1) && $room->status === 'Open') {
                    $room->update(['status' => 'Lock']);
                    $usersInRoom = $room->users;
                    foreach ($usersInRoom as $user) {
                        $notication->postNotification($user->id, $user->role, 'Phòng ' . $room->title . ' đã đủ người và đã bắt đầu chơi', $room->id);
                    }
                }
            }

            return $response->successResponse('Check and update room status success', null, 200);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }
}

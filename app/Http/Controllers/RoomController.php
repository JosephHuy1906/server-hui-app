<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoomResource;
use App\Models\Payment;
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

            $rooms = Room::withCount('users')->with(['users'])
                ->take($item)
                ->get();
            return $this->successResponse('Get room all success',  RoomResource::collection($rooms), 200);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }
    public function getRoomsByUserId($userId, $item)
    {
        try {
            $noti = new NotificationController();
            $us = User::find($userId);
            if (!$us) {
                return $this->errorResponse('User không tồn tại',  404);
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
                $noti->postNotification(
                    $item,
                    'user',
                    'Nhóm còn một ngày nữa sẽ giải tán',
                    $userId,
                    "room_all"
                );
            }

            return $this->successResponse('Get room by User success', RoomResource::collection($userRooms), 200);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }

    public function addroom(Request $request)
    {
        try {
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
                return $this->errorResponse('Thông tin truyền vào chưa đúng',  400);
            }

            $addroom = Room::create($request->all());
            $room_id = $addroom->id;
            $admin->addAdminForRoom($room_id, '4bdc395e-77d4-4602-8e0f-af6bb401560f');
            return $this->successResponse('Create room success', new RoomResource($addroom), 201);
        } catch (\Throwable $err) {
            return $this->errorResponse('Create room faill',  500);
        }
    }




    public function updateStatusRoom(Request $request)
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'id' => 'required',
                    'status' => 'required'
                ]
            );

            if ($validate->fails()) {
                return $this->errorResponse('Thông tin truyền vào chưa đúng',  400);
            }
            $find = Room::find($request->id);
            if (!$find)  return $this->errorResponse("Room không tồn tại",  404);
            $update = $find->update([
                'status' => $request->status
            ]);
            return $this->successResponse('Update status room success', null, 201);
        } catch (\Throwable $err) {
            return $this->errorResponse('Update status room faill',  500);
        }
    }
    public function updateInfoRoom(Request $request, $id)
    {
        try {
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
                return $this->errorResponse('Thông tin truyền vào chưa đúng',  400);
            }
            $find = Room::find($id);
            if (!$find)  return $this->errorResponse("Room không tồn tại",  404);

            $data = $request->all();
            $find->update($data);
            $updatedRoom = Room::find($id);
            return $this->successResponse('Cập nhập chi tiết phòng thành công', new RoomResource($updatedRoom), 201);
        } catch (\Throwable $err) {
            return $this->errorResponse('Update info room faill',  500);
        }
    }

    public function getRoomsByCount($item)
    {
        try {
            $rooms = Room::withCount('users')->with(['users'])
                ->take($item)
                ->orderBy('users_count', 'desc')
                ->get();
            return $this->successResponse('Get room all success',  RoomResource::collection($rooms), 200);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }
    public function getRoomsByPrice($item)
    {
        try {
            $rooms = Room::withCount('users')->with(['users'])
                ->take($item)
                ->orderBy('price_room', 'asc')
                ->get();
            return $this->successResponse('Get room all success',  RoomResource::collection($rooms), 200);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }
    public function getDetailRoom($id)
    {
        try {
            $data = Room::withCount('users')->with(['users'])->findOrFail($id);
            return $this->successResponse('Get Detail room', new RoomResource($data), 200);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }

    protected function checkAndUpdateRoomsStatus()
    {
        try {
            $notication = new NotificationController();
            $rooms = Room::withCount('users')->get();
            foreach ($rooms as $room) {
                if ($room->total_user == ($room->users_count - 1) && $room->status === 'Open') {
                    $room->update(['status' => 'Lock']);
                    $usersInRoom = $room->users;
                    foreach ($usersInRoom as $user) {
                        $notication->postNotification(
                            $user->id,
                            $user->role,
                            'Phòng ' . $room->title . ' đã đủ người và đã bắt đầu chơi',
                            $room->id,
                            "room_all"
                        );
                    }
                }
            }

            return $this->successResponse('Check and update room status success', null, 200);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller

{
    public function postMessage(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'room_id' => 'required',
                'user_id' => 'required',
                'message' => 'required'
            ]);
            $user = User::find($request->user_id);
            $room = Room::find($request->room_id);
            if ($validator->fails()) {
                return $this->errorResponse('Thông tin truyền vào chưa đúng',  400);
            }
            if (!$user) {
                return $this->errorResponse('User_id không tồn tại',  400);
            }
            if (!$room) {
                return $this->errorResponse('room_id Không tồn tại', 400);
            }
            $existingRecord = DB::table('room_user')
                ->where('room_id', $request->room_id)
                ->where('user_id', $request->user_id)
                ->first();
            if (!$existingRecord) {
                return $this->errorResponse('User không có tham gia vào room',  400);
            }
            Message::create($request->all());
            return $this->successResponse('Post Message successfully', null, 201);
        } catch (\Throwable $err) {
            return $this->errorResponse("Server Error",  500);
        }
    }
    public function getMessage($id)
    {
        try {

            $messages = Message::where('room_id', $id)
                ->get();
            return $this->successResponse('Get messages by room_id success', MessageResource::collection($messages), 200);
        } catch (\Throwable $err) {
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

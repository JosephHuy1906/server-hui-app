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
            $response = new ResponseController();
            $validator = Validator::make($request->all(), [
                'room_id' => 'required',
                'user_id' => 'required',
                'message' => 'required'
            ]);
            $user = User::find($request->user_id);
            $room = Room::find($request->room_id);
            if ($validator->fails()) {
                return $response->errorResponse('Input value error', $validator->errors(), 400);
            }
            if (!$user) {
                return $response->errorResponse('User_id does not exist', null, 400);
            }
            if (!$room) {
                return $response->errorResponse('room_id does not exist', null, 400);
            }
            $existingRecord = DB::table('room_user')
                ->where('room_id', $request->room_id)
                ->where('user_id', $request->user_id)
                ->first();
            if (!$existingRecord) {
                return $response->errorResponse('User does not room', $validator->errors(), 400);
            }
            Message::create($request->all());
            return $response->successResponse('Post Message successfully', null, 201);
        } catch (\Throwable $err) {
            return $response->errorResponse("Server Error", $err->getMessage(), 500);
        }
    }
    public function getMessage($id)
    {
        try {
            $response = new ResponseController();
            $messages = Message::where('room_id', $id)
                ->get();
            $this->checkAndUpdateRoomsStatus();
            return $response->successResponse('Get messages by room_id success', MessageResource::collection($messages), 200);
        } catch (\Throwable $err) {
            return $response->errorResponse("Server Error", $err->getMessage(), 500);
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

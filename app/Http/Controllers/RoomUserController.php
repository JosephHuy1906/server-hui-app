<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoomUserResource;
use App\Models\Room;
use App\Models\RoomUser;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoomUserController extends Controller
{
    public function addUserForRoom(Request $request)
    {
        try {
            $response = new ResponseController();
            $noti = new NotificationController();
            $validator = Validator::make($request->all(), [
                'room_id' => 'required',
                'user_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $response->errorResponse('Input value error', $validator->errors(), 400);
            }
            $existingRecord = DB::table('room_user')
                ->where('room_id', $request->room_id)
                ->where('user_id', $request->user_id)
                ->first();

            $user = User::find($request->user_id);
            if (!$user) {
                return $response->errorResponse('User_id does not exist', null, 400);
            }
            $room = Room::find($request->room_id);
            if (!$room) {
                return $response->errorResponse('room_id does not exist', null, 400);
            }

            if ($existingRecord) {
                DB::table('room_user')
                    ->where('room_id', $request->room_id)
                    ->where('user_id', $request->user_id)
                    ->delete();

                $noti->postNotification($request->user_id, 'user', 'Bạn đã thoát khỏi khóm hụi', $request->room_id);
                return $response->successResponse('Remove User for room successfully', null, 201);
            } else {
                DB::table('room_user')->insert([
                    'room_id' => $request->room_id,
                    'user_id' => $request->user_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $noti->postNotification($request->user_id, 'user', 'Bạn đã tham gia vào phòng hụi thành công', $request->room_id);
                return $response->successResponse('Add User for room successfully', null, 201);
            }
        } catch (\Throwable $err) {
            return $response->errorResponse("Server Error", $err->getMessage(), 500);
        }
    }

    public function addAdminForRoom($room_id, $user_id)
    {
        try {
            $response = new ResponseController();
            DB::table('room_user')->insert([
                'room_id' => $room_id,
                'user_id' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return $response->successResponse('Add Admin for room successfully', null, 201);
        } catch (\Throwable $err) {
            return $response->errorResponse("Server Error", $err->getMessage(), 500);
        }
    }
}

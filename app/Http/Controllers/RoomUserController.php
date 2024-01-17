<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoomUserController extends Controller
{
    public function addUserForRoom(Request $request)
    {
        try {

            $noti = new NotificationController();
            $validator = Validator::make($request->all(), [
                'room_id' => 'required',
                'user_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Thông tin truyền vào chưa đúng', 400);
            }
            $existingRecord = DB::table('room_user')
                ->where('room_id', $request->room_id)
                ->where('user_id', $request->user_id)
                ->first();

            $user = User::find($request->user_id);

            if (!$user) {
                return $this->errorResponse('User không tồn tại',  400);
            }
            $room = Room::find($request->room_id);

            if (!$room) {
                return $this->errorResponse('room không tồn tại',  400);
            }

            if ($room->status == 'Open') {
                if ($existingRecord) {
                    DB::table('room_user')
                        ->where('room_id', $request->room_id)
                        ->where('user_id', $request->user_id)
                        ->delete();

                    $noti->postNotification($request->user_id, 'user', 'Bạn đã thoát khỏi khóm hụi ' . $room->title, $request->room_id);
                    return $this->successResponse('Remove User for room successfully', null, 201);
                } else {
                    DB::table('room_user')->insert([
                        'room_id' => $request->room_id,
                        'user_id' => $request->user_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $noti->postNotification($request->user_id, 'user', 'Bạn đã tham gia vào phòng hụi ' . $room->title . ' thành công', $request->room_id);
                    return $this->successResponse('Add User for room successfully', null, 201);
                }
            }

            if ($room->status == 'Lock') {
                $room_user =  DB::table('room_user')
                    ->where('room_id', $request->room_id)
                    ->where('user_id', $request->user_id)
                    ->first();

                if (!$room_user) {
                    return $this->errorResponse('Phòng hụi ' . $room->title . ' hiện tại đã bị khoá',  400);
                }

                return $this->successResponse('Bạn đã vào phòng hụi ' . $room->title . '  thành công', null, 201);
            }
            if ($room->status == 'Close') {
                return $this->errorResponse('Phòng hụi ' . $room->title . '  này hiện đã đóng và không còn hoạt động',  400);
            }
        } catch (\Throwable $err) {
            return $this->errorResponse("Server Error",  500);
        }
    }

    public function addAdminForRoom($room_id, $user_id)
    {
        try {

            DB::table('room_user')->insert([
                'room_id' => $room_id,
                'user_id' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return $this->successResponse('Add Admin for room successfully', null, 201);
        } catch (\Throwable $err) {
            return $this->errorResponse("Server Error",  500);
        }
    }
}

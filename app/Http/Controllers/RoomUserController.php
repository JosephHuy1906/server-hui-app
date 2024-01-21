<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoomUserResource;
use App\Models\Room;
use App\Models\RoomUser;
use App\Models\User;
use Carbon\Carbon;
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

    public function updateStatusUser(Request $request, $id)
    {
        $noti = new NotificationController();
        $room_user = RoomUser::find($id);

        if (!$room_user) {
            return $this->errorResponse('Không tìm thấy user room id', 404);
        }

        $room_user->update([
            "status" => 'Đã bị khoá'
        ]);
        $noti->postNotification($room_user->user_id, "User", "Bạn đã bị khoá trong phòng hụi và không thể chơi", $room_user->room_id);
        return $this->successResponse("Khoá người dùng thành công", null, 201);
    }

    public function getUserRoomPayment($id)
    {
        $today = now()->toDateString();
        $roomUser = RoomUser::where('room_id', $id)
            ->with(['user', 'payments' => function ($query) use ($today) {
                $query->whereDate('created_at', $today);
            }])
            ->where('user_id', '!=', '4bdc395e-77d4-4602-8e0f-af6bb401560f')
            ->where('status', 'Đang hoạt động')
            ->get();

        return $this->successResponse('kiểm tra user đã thanh toán tiền hụi theo phòng', RoomUserResource::collection($roomUser), 200);
    }
}

<?php

namespace App\Http\Controllers;

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
        $today = Carbon::now()->toDateString();
        $usersInRoom = DB::table('room_user')
            ->select('users.id as user_id', 'users.name', 'users.phone', 'users.avatar', 'users.email', 'users.birthday', 'users.sex', 'users.address', 'users.role', 'users.rank', 'room_user.status as user_status', 'payments.*')
            ->join('users', 'room_user.user_id', '=', 'users.id')
            ->leftJoin('payments', function ($join) use ($id, $today) {
                $join->on('room_user.id', '=', 'payments.room_user_id')
                    ->where('payments.created_at', '>=', $today . ' 00:00:00')
                    ->where('payments.created_at', '<=', $today . ' 23:59:59');
            })
            ->where('room_user.room_id', $id)
            ->where('room_user.user_id', '!=', '4bdc395e-77d4-4602-8e0f-af6bb401560f')
            ->where('room_user.status', '!=', 'Đã bị khoá')
            ->get();
        $result = [];
        foreach ($usersInRoom as $user) {
            $userData = [
                'id' => $user->user_id,
                'name' => $user->name,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'email' => $user->email,
                'birthday' => $user->birthday,
                'sex' => $user->sex,
                'address' => $user->address,
                'role' => $user->role,
                'rank' => $user->rank,
                'user_status' => $user->user_status,
                'payment' => [
                    'status' => $user->status,
                    'description' => $user->description,
                    'price_pay' => $user->price_pay,
                    'created_at' => $user->created_at,
                ],
            ];
            $result[] = $userData;
        }

        return $this->successResponse('kiểm tra user đã thanh toán tiền hụi theo phòng', $result, 200);
    }
}

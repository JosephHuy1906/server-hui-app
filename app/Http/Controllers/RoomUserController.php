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

                    if ($request->user_id === "4bdc395e-77d4-4602-8e0f-af6bb401560f") {
                        return $this->errorResponse('Bạn không thể xoá admin ra khỏi phòng', 400);
                    }
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
        $validator = Validator::make($request->all(), [
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Thông tin truyền vào chưa đúng', 400);
        }
        $room_user = RoomUser::find($id);
        $room = Room::find($room_user->room_id);
        $user = User::find($room_user->user_id);
        if (!$room_user) {
            return $this->errorResponse('Không tìm thấy user room id', 404);
        }

        $room_user->update([
            "status" => $request->status
        ]);
        if ($request->status === "Đã bị khoá") {
            $noti->postNotification(
                $room_user->user_id,
                "User",
                "Bạn đã bị khoá trong phòng hụi và không thể chơi",
                $room_user->room_id
            );
            if ($user->device_id !== null) {
                $this->sendNoticationApp(
                    $user->device_id,
                    "Bạn đã bị khoá trong phòng " . $room->title . " và không thể chơi"
                );
            }
        }
        return $this->successResponse("Cập nhập trạng thái người chơi thành công", new RoomUserResource($room_user), 201);
    }

    public function unLockUser($id)
    {
        $noti = new NotificationController();
        $room_user = RoomUser::find($id);
        $room = Room::find($room_user->room_id);
        $user = User::find($room_user->user_id);
        if (!$room_user) {
            return $this->errorResponse('Không tìm thấy user room id', 404);
        }

        $room_user->update([
            "status" => 'Đang hoạt động'
        ]);

        $noti->postNotification(
            $room_user->user_id,
            "User",
            "Bạn đã được admin mở khoá trong phòng " . $room->title . " và có thể bắt đầu chơi ngay bây giờ",
            $room_user->room_id
        );
        if ($user->device_id !== null) {
            $this->sendNoticationApp(
                $user->device_id,
                "Bạn đã được admin mở khoá trong phòng " . $room->title . " và có thể bắt đầu chơi ngay bây giờ"
            );
        }
        return $this->successResponse("Khoá người dùng thành công", null, 201);
    }

    public function getUserRoomPayment($id)
    {
        $today = now()->toDateString();
        $activeStatus = 'Đang hoạt động';

        $roomUser = RoomUser::where('room_id', $id)
            ->where('user_id', '!=', '4bdc395e-77d4-4602-8e0f-af6bb401560f')
            ->where('status', $activeStatus)
            ->with(['user', 'payments' => function ($query) use ($today) {
                $query->whereDate('created_at', $today);
            }])
            ->get();

        return $this->successResponse('Kiểm tra user đã thanh toán tiền hụi theo phòng vào ngày ' . $today, RoomUserResource::collection($roomUser), 200);
    }

    public function getUsersWithoutApprovedPayments($id)
    {
        $today = now()->toDateString();
        $excludedUserId = '4bdc395e-77d4-4602-8e0f-af6bb401560f';

        $usersWithoutApprovedPayments = RoomUser::where('room_id', $id)
            ->where('status', 'Đang hoạt động')
            ->with(['user', 'payments' => function ($query) use ($today) {
                $query->whereDate('created_at', $today)
                    ->whereNotIn('status', ['approved']);
            }])
            ->where(function ($query) use ($today) {
                $query->orWhereHas('payments', function ($subQuery) use ($today) {
                    $subQuery->whereDate('created_at', $today)
                        ->whereNotIn('status', ['approved']);
                })
                    ->orWhereDoesntHave('payments');
            })
            ->whereNotIn('user_id', [$excludedUserId])
            ->get();

        return $usersWithoutApprovedPayments;
        // return $this->successResponse('Danh sách người dùng chưa có thanh toán tiền hụi ngày ' . $today, RoomUserResource::collection($usersWithoutApprovedPayments), 200);
    }
}

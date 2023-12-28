<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    public function getNotiByUser($id)
    {
        try {
            $response = new ResponseController();
            $user = User::find($id);

            if (!$user) {
                return $response->errorResponse('Người dùng không tồn tại', null, 404);
            }

            // Lấy toàn bộ notifications của user, sắp xếp theo thời gian tạo mới nhất
            $notifications = Notification::where('user_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Kiểm tra xem có notifications hay không
            if ($notifications->isEmpty()) {
                return $response->successResponse('Bạn chưa có thông báo nào', [], 200);
            }

            return $response->successResponse('Danh sách thông báo', $notifications, 200);
        } catch (\Throwable $err) {
            return $response->errorResponse('Error Server', $err->getMessage(), 500);
        }
    }
    public function postNotification($user_id, $status, $description, $room_id)
    {
        try {
            $response = new ResponseController();

            $data = [
                'user_id' => $user_id,
                'status' => $status,
                'description' => $description,
                'room_id' => $room_id,
            ];
            $notifications = Notification::create($data);

            return $response->successResponse('Create notification success', $notifications, 200);
        } catch (\Throwable $err) {
            return $response->errorResponse('Error Server', $err->getMessage(), 500);
        }
    }
    public function sendNotification(Request $request)
    {
        try {
            $response = new ResponseController();
            $validator = Validator::make($request->all(), [
                'status' => 'required',
                'user_id' => 'required',
                'description' => 'required',
                'room_id' => 'required',
            ]);

            $notifications = Notification::create($request->all());
            if ($validator->fails()) {
                return $response->errorResponse('Input value error', $validator->errors(), 400);
            }
            return $response->successResponse('Post notification success', new NotificationResource($notifications), 200);
        } catch (\Throwable $err) {
            return $response->errorResponse('Error Server', $err->getMessage(), 500);
        }
    }

    public function removeNotiByUser($id)
    {
        try {
            $response = new ResponseController();
            $noti = Notification::find($id);

            if (!$noti) {
                return $response->errorResponse('Thông báo không tồn tại không tồn tại', null, 404);
            }

            $noti->delete();

            return $response->successResponse('Đã xoá thông báo', null, 201);
        } catch (\Throwable $err) {
            return $response->errorResponse('Error Server', $err->getMessage(), 500);
        }
    }
}

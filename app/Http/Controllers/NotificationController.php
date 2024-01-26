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

            $user = User::find($id);

            if (!$user) {
                return $this->errorResponse('Người dùng không tồn tại',  404);
            }

            $notifications = Notification::where('user_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($notifications->isEmpty()) {
                return $this->successResponse('Bạn chưa có thông báo nào', [], 200);
            }

            return $this->successResponse('Danh sách thông báo của user', NotificationResource::collection($notifications), 200);
        } catch (\Throwable $err) {
            return $this->errorResponse($err->getMessage(), 500);
        }
    }
    public function getNotiByAdmin($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return $this->errorResponse('Người dùng không tồn tại',  404);
            }
            $notifications = Notification::where('user_id', $id)
                ->where('status', 'admin')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($notifications->isEmpty()) {
                return $this->successResponse('Admin chưa có thông báo nào', [], 200);
            }
            return $this->successResponse('Danh sách thông báo của admin', NotificationResource::collection($notifications), 200);
        } catch (\Throwable $err) {
            return $this->errorResponse('Error Server',  500);
        }
    }
    public function postNotification($user_id, $status, $description, $room_id)
    {
        try {
            $data = [
                'user_id' => $user_id,
                'status' => $status,
                'description' => $description,
                'room_id' => $room_id,
            ];
            $notifications = Notification::create($data);

            return $this->successResponse('Create notification success', $notifications, 200);
        } catch (\Throwable $err) {
            return $this->errorResponse('Error Server',  500);
        }
    }
    public function sendNotification(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required',
                'user_id' => 'required',
                'description' => 'required',
                'room_id' => 'required',
            ]);

            $notifications = Notification::create($request->all());
            if ($validator->fails()) {
                return $this->errorResponse('Input value error',  400);
            }
            return $this->successResponse('Post notification success', new NotificationResource($notifications), 200);
        } catch (\Throwable $err) {
            return $this->errorResponse('Error Server',  500);
        }
    }

    public function removeNotiByUser($id)
    {
        try {
            $noti = Notification::find($id);
            if (!$noti) {
                return $this->errorResponse('Thông báo không tồn tại không tồn tại',  404);
            }

            $noti->delete();
            return $this->successResponse('Đã xoá thông báo', null, 201);
        } catch (\Throwable $err) {
            return $this->errorResponse('Error Server',  500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserWinHuiResource;
use App\Models\UserWinHui;
use Illuminate\Http\Request;

class UserWinHuiController extends Controller
{
    public function updatePaid($id)
    {
        try {

            $notication = new NotificationController();
            $find = UserWinHui::find($id);
            if (!$find)  return $this->errorResponse("User chiến thắng không tồn tại",  404);
            $find->update([
                'status' => 'approved'
            ]);
            $notication->postNotification($find->user_id, $find->role, 'Bạn đã thanh toán ' . $find->total_amount_payable . 'đ tiền đấu hụi', $find->room_id);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }
    public function getHuiByUser($id)
    {
        try {


            $data = UserWinHui::where('user_id', $id)->get();

            return $this->successResponse('Danh sách đấu hụi thành công', UserWinHuiResource::collection($data), 200);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }
    public function calculateTotalAmountsByRoom($userId)
    {

        $data = UserWinHui::where('user_id', $userId)->with('room')->get();
        if ($data->isEmpty()) {
            return $this->errorResponse("User không tồn tại hoặc không có dữ liệu liên quan",  404);
        }
        $totals = [];
        foreach ($data as $item) {
            $roomId = $item->room_id;
            if (!isset($totals[$roomId])) {
                $totals[$roomId] = [
                    'room_id' => $item->room_id,
                    'room_name' => $item->room->title,
                    'room_time_date_created' => $item->room->created_at,
                    'room_time_date_end' => $item->room->date_room_end,
                    'total_money_received' => 0,
                    'total_amount_payable' => 0,
                ];
            }

            $totals[$roomId]['total_money_received'] += $item->total_money_received;
            $totals[$roomId]['total_amount_payable'] += $item->total_amount_payable;
        }

        $result = array_values($totals);

        return $this->successResponse("Tổng tiền bạn đã thắng và bạn phải nộp trong các phòng", $result, 200);
    }
    public function getAll()
    {
        try {
            $data = UserWinHui::all();
            return $this->successResponse('Tất cả  Danh sách đấu hụi thành công', UserWinHuiResource::collection($data), 200);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }
}

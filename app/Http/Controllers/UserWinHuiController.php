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
            $response = new ResponseController();
            $notication = new NotificationController();
            $find = UserWinHui::find($id);
            if (!$find)  return $response->errorResponse("User Win id does not exist", null, 404);
            $find->update([
                'status' => 'paid'
            ]);
            $notication->postNotification($find->user_id, 'user', 'Bạn đã thanh toán ' . $find->total_amount_payable . 'đ tiền đấu hụi', $find->room_id);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }
    public function getHuiByUser($id)
    {
        try {
            $response = new ResponseController();

            $data = UserWinHui::where('user_id', $id)->get();

            return $response->successResponse('Danh sách đấu hụi thành công', UserWinHuiResource::collection($data), 200);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }
    public  function calculateTotalAmountsByRoom($userId)
    {
        $response = new ResponseController();
        $data = UserWinHui::where('user_id', $userId)->get();

        if (!$data) {
            return $response->errorResponse("User does not exist", null, 404);
        }

        $totals = [];

        foreach ($data as $item) {
            $roomId = $item->room_id;

            if (!isset($totals[$roomId])) {
                $totals[$roomId] = [
                    'total_money_received' => 0,
                    'total_amount_payable' => 0,
                ];
            }

            $totals[$roomId]['total_money_received'] += $item->total_money_received;
            $totals[$roomId]['total_amount_payable'] += $item->total_amount_payable;
        }

        return $response->successResponse("Tổng tiền bạn đã thắng và bạn phải nộp trong các phòng", $totals, 200);
    }
    public function getAll()
    {
        try {
            $response = new ResponseController();

            $data = UserWinHui::all();

            return $response->successResponse('Tất cả  Danh sách đấu hụi thành công', UserWinHuiResource::collection($data), 200);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuctionHuiDetailResource;
use App\Http\Resources\UserWinHuiResource;
use App\Models\AuctionHuiDetail;
use App\Models\UserWinHui;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuctionHuiDetailController extends Controller
{
    public function auctionHui(Request $request)
    {
        try {
            $response = new ResponseController();
            $noti = new NotificationController();
            $validator = Validator::make($request->all(), [
                'auction_hui_id' => 'required',
                'user_id' => 'required',
                'starting_price' => 'required',
                'auction_percentage' => 'required',
                'total_price' => 'required',
            ]);

            if ($validator->fails()) {
                return $response->errorResponse('Input value error', $validator->errors(), 400);
            }

            $create = AuctionHuiDetail::create($request->all());
            $noti->postNotification($request->user_id, 'user', 'Bạn đã đấu giá hụi thành công', $request->room_id);
            return $response->successResponse('Bạn đã đấu giá hụi thành công', new AuctionHuiDetailResource($create), 201);
        } catch (\Throwable $err) {
            return $response->errorResponse("Server Error", $err->getMessage(), 500);
        }
    }
    public function getAuctionHui($id)
    {
        try {
            $response = new ResponseController();
            $data = AuctionHuiDetail::where('auction_hui_id', $id)->get();
            return $response->successResponse('Lấy danh sách đấu giá hụi theo phòng thành công', AuctionHuiDetailResource::collection($data), 200);
        } catch (\Throwable $err) {
            return $response->errorResponse("Server Error", $err->getMessage(), 500);
        }
    }

    public function getTotal($id)
    {
        try {
            $response = new ResponseController();

            $maxTotalPrice = AuctionHuiDetail::where('auction_hui_id', $id)
                ->select(DB::raw('MAX(total_price) as max_total_price'))
                ->first()
                ->max_total_price;

            $usersWithMaxTotalPrice = AuctionHuiDetail::where('auction_hui_id', $id)
                ->where('total_price', $maxTotalPrice)
                ->get();

            return $response->successResponse('Người đấu giá hụi thành công', AuctionHuiDetailResource::collection($usersWithMaxTotalPrice), 200);
        } catch (\Throwable $err) {
            return $response->errorResponse("Server Error", $err->getMessage(), 500);
        }
    }
    public function postUserWin(Request $request)
    {
        try {
            $response = new ResponseController();
            $notication = new NotificationController();
            $validator = Validator::make($request->all(), [
                'room_id' => 'required',
                'auction_hui_id' => 'required',
                'price_room' => 'required',
                'total_user' => 'required',
                'commission_percentage' => 'required',
            ]);

            if ($validator->fails()) {
                return $response->errorResponse('Input value error', $validator->errors(), 400);
            }

            $usersWithMaxTotalPrice = $this->getTotal($request->auction_hui_id);

            $responseData = $usersWithMaxTotalPrice->getData();

            if (!isset($responseData->status) || !isset($responseData->success) || $responseData->status != 200 || !$responseData->success) {
                return $response->errorResponse('Failed to get winning bidder', null, 500);
            }

            $winningBidder = $responseData->data[0];

            $total_amount_payable = $winningBidder->total_price;
            $total_auction = $request->price_room * ($request->total_user - 1);
            $total_money_received = $total_auction - (($total_auction * $request->commission_percentage) / 100);

            $addUserWin = [
                'user_id' => $winningBidder->user->user_id,
                'commission_percentage' => $request->commission_percentage,
                'price_pay_hui' => $winningBidder->total_price,
                'total_auction' => $total_auction,
                'room_id' => $request->room_id,
                'total_amount_payable' => $total_amount_payable,
                'total_money_received' => $total_money_received,
            ];

            $addUser =  UserWinHui::create($addUserWin);
            $totalAmountPayable = number_format($total_money_received, 0, ',', '.') . ' đ';
            $notication->postNotification(
                $winningBidder->user->user_id,
                'User',
                'Bạn đã đấu hụi thành công với số tiền: ' . $totalAmountPayable . 'đ.Vui lòng thanh toán để nhận số tiền trên',
                $request->room_id
            );
            return $response->successResponse('Create user win hui successfully', new UserWinHuiResource($addUser), 201);
        } catch (\Throwable $err) {
            return $response->errorResponse("Server Error", $err->getMessage(), 500);
        }
    }
}

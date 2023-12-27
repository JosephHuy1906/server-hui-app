<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuctionHuiDetailResource;
use App\Models\AuctionHuiDetail;
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
}
